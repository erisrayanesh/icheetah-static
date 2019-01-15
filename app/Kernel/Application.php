<?php

namespace App\Kernel;

use App\Controllers\FileController;
use App\Controllers\HomeController;
use \Symfony\Component\HttpFoundation\Response as BaseResponse;
use App\Kernel\Tools\Path;
use Whoops\Run as Whoops;

class Application
{

	private static $instance;

	/**
	 * @var \SplFileInfo
	 */
	private $basePath;

	private $services = [];

	private $sharedServices = [];

	/**
	 * @var Loader
	 */
	private $loader;

	public static function getInstance($basePath = null)
	{
		if (is_null(self::$instance)) {
			self::$instance = new static($basePath);
		}

		return self::$instance;
	}

	private function __construct($basePath)
	{
		$this->basePath = path($basePath);
		$this->loader = new Loader($this);
	}

	public function run()
	{
		$this->initErrorHandlers();
		$response = $this->route();

		if (!$response instanceof BaseResponse) {
			$response = response()->create($response);
		}

		logger()->info(SYSTEM_START);
		logger()->info(microtime(true));
		$response->send();
	}

	public function getService($key)
	{
		if (isset($this->sharedServices[$key])) {
			return $this->sharedServices[$key];
		}

		if (isset($this->services[$key])) {
			return value($this->services[$key]);
		}

		if ($this->getLoader()->load($key) === true){
			return $this->getService($key);
		}

		return false;
	}

	public function register($key, \Closure $value, $shared = false)
	{
		if ($shared) {
			if (isset($this->sharedServices[$key])){
				return false;
			}
			$value = value($value);
			$this->sharedServices[$key] = $value;
			return true;
		}
		if (!isset($this->services[$key])){
			$this->services[$key] = $value;
			return true;
		}
		return false;
	}

	protected function initErrorHandlers()
	{
		error_reporting(-1);
		set_error_handler([$this, 'handleError']);
		set_exception_handler([$this, 'handleException']);
		register_shutdown_function([$this, 'handleShutdown']);
		ini_set('display_errors', 'Off');
	}

	public function handleError($level, $message, $file = '', $line = 0, $context = [])
	{
		if (error_reporting() & $level) {
			throw new \ErrorException($message, 0, $level, $file, $line);
		}
	}

	public function handleException($e)
	{
		if (!$e instanceof \Exception) {
			if ($e instanceof \ParseError) {
				$severity = E_PARSE;
			} elseif ($e instanceof \TypeError) {
				$severity = E_RECOVERABLE_ERROR;
			} else {
				$severity = E_ERROR;
			}

			$e = new \ErrorException($e->getMessage(), $e->getCode(), $severity, $e->getFile(), $e->getLine(), $e->getPrevious());
		}
		$this->renderHttpResponse($e);
	}

	public function handleShutdown()
	{
		if (!is_null($error = error_get_last()) && $this->isFatalError($error['type'])) {
			$this->handleException(new \ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
		}
	}

	public function getBasePath()
	{
		return $this->basePath;
	}

	/**
	 * @param $path
	 * @return \SplFileInfo
	 */
	public function getPathTo($path)
	{
		return Path::append($this->basePath, $path);
	}

	/**
	 * @return Loader
	 */
	public function getLoader()
	{
		return $this->loader;
	}

	protected function route()
	{
		if (request()->get('file')) {
			return (new FileController())->show();
		}
//
//		if (Request::exist('list')) {
//
//		}

		return (new HomeController())->index();

	}

	protected function isFatalError($type)
	{
		return in_array($type, [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE]);
	}

	protected function renderHttpResponse(\Exception $exception)
	{
		(config()->get('debug')?
			$this->renderExceptionWithWhoops($exception) :
			$this->renderFriendlyError($exception))->send();
	}

	protected function renderExceptionWithWhoops(\Exception $e)
	{
		$whoops = new Whoops();
		if (request()->ajax()){
			$handler = new \Whoops\Handler\JsonResponseHandler();
		} else {
			$handler = new \Whoops\Handler\PrettyPageHandler();
			$handler->handleUnconditionally(true);
			$handler->addDataTable('Application (Request)', array(
				'URI'         => request()->getScheme().'://' . request()->server('HTTP_HOST') . request()->server('REQUEST_URI'),
				'Request URI' => request()->server('REQUEST_URI'),
				'Path Info'   => request()->server('PATH_INFO'),
				'HTTP Method' => request()->getMethod(),
				'Script Name' => request()->server('SCRIPT_NAME'),
				//'Base Path'   => $request->getBasePath(),
				//'Base URL'    => $request->getBaseUrl(),
				'Scheme'      => request()->getScheme(),
				'Port'        => request()->server('SERVER_PORT'),
				'Host'        => request()->server('HTTP_HOST'),
			));
		}
		$whoops->pushHandler($handler);
		$whoops->writeToOutput(false);
		$whoops->allowQuit(false);
		return response()->create($whoops->handleException($e), 500, []);
	}

	protected function renderFriendlyError(\Exception $e)
	{
		if (request()->ajax()){
			$response = response()->json(["code" => 500, "message" => $e->getMessage()], 500);
		} else {
			$response = response()->create("Whoops! Something went wrong", 500);
		}
		return $response;
	}


}