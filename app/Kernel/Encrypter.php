<?php

namespace App\Kernel;

class Encrypter
{

	protected $method = 'AES-128-CTR'; // default cipher method if none supplied
	private $key;
	private $iv_bytes;

	public function __construct($key = null, $method = null)
	{
		if(empty($key)) {
			$key = php_uname(); // default encryption key if none supplied
		}

		$this->setKey($key);

		if(empty($method)) {
			$method = 'AES-128-CTR';
		}

		$this->setMethod($method);
	}

	/**
	 * @return null|string
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * @param null|string $method
	 * @return static
	 */
	public function setMethod($method)
	{
		if(in_array($method, openssl_get_cipher_methods())) {
			$this->method = $method;
			$this->iv_bytes = openssl_cipher_iv_length($this->method);
		} else {
			throw new \InvalidArgumentException("Unrecognised cipher method: {$method}");
		}

		return $this;
	}

	/**
	 * @return null|string
	 */
	public function getKey()
	{
		return $this->key;
	}

	/**
	 * @param null|string $key
	 * @return static
	 */
	public function setKey($key)
	{
		if(ctype_print($key)) {
			// convert ASCII keys to binary format
			$key = openssl_digest($key, 'SHA256', TRUE);
		}

		$this->key = $key;

		return $this;
	}

	public function encrypt($data)
	{
		$iv = openssl_random_pseudo_bytes($this->iv_bytes);
		return bin2hex($iv) . openssl_encrypt($data, $this->method, $this->key, 0, $iv);
	}

	public function decrypt($data)
	{
		$iv_strlen = 2  * $this->iv_bytes;
		if(preg_match("/^(.{" . $iv_strlen . "})(.+)$/", $data, $regs)) {
			list(, $iv, $crypted_string) = $regs;
			if(ctype_xdigit($iv) && strlen($iv) % 2 == 0) {
				return openssl_decrypt($crypted_string, $this->method, $this->key, 0, hex2bin($iv));
			}
		}
		return false;
	}
}