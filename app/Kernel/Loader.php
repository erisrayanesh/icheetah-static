<?php

namespace App\Kernel;

use App\Kernel\Http\Request;
use App\Kernel\Tools\Str;
use Intervention\Image\ImageManager;

class Loader
{
	private $app;

	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	public function isLoaded($key)
	{
		return $this->app->getService($key) === false;
	}

	public function load($key)
	{
		try {
			$method = "load" . Str::title($key);
			$this->{$method}();
			return true;
		} catch (\Exception $e){
			return false;
		}
	}

	protected function loadConfig()
	{
		return $this->app->register("config", function () {
			return require_once($this->app->getPathTo('config' . DIRECTORY_SEPARATOR . 'config.php'));
		}, true);
	}

	protected function loadRequest()
	{
		return $this->app->register('request', function (){
			return Request::capture();
		}, true);
	}

	protected function loadLogger()
	{
		$logFile = $this->app->getPathTo(config()->get('log_file'));
		return $this->app->register("logger", function () use ($logFile) {
			return new Logger($logFile);
		});
	}

	protected function loadEncrypter()
	{
		return $this->app->register('encrypter', function (){
			return new Encrypter(config('api_key') , config('cipher.method'));
		}, true);
	}

	protected function loadImage()
	{
		return $this->app->register('image', function (){
			return new ImageManager(['driver' => config('image.driver')]);
		});
	}

	protected function loadFiles()
	{
		return $this->app->register('files', function (){
			return new FileSystem();
		});
	}
}