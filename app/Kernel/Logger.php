<?php

namespace App\Kernel;

class Logger extends \Psr\Log\AbstractLogger
{

	private $filePath;

	public function __construct($filePath)
	{
		$this->filePath = $filePath;
	}

	public function log($level, $message, array $context = array())
	{
		if (is_object($message)){
			$message = json_decode(json_encode($message), true);
		}
		if (is_array($message)){
			$message = print_r($message, true);
		}
		$message = "[" . Date('Y-m-d H:m:s') . "] $level : $message\n";
		file_put_contents($this->filePath, $message, FILE_APPEND);
	}
}