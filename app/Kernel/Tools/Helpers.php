<?php

use App\Kernel\Tools\Arr;
use App\Kernel\Tools\Str;
use App\Kernel\Tools\Collection;

if (! function_exists('value')) {
	/**
	 * Return the default value of the given value.
	 *
	 * @param  mixed  $value
	 * @return mixed
	 */
	function value($value)
	{
		return $value instanceof Closure ? $value() : $value;
	}
}

if (!function_exists("collect")){
	/**
	 * @param $items
	 * @return \App\Kernel\Tools\Collection
	 */
    function collect($items)
    {
        return new \App\Kernel\Tools\Collection($items);
    }
}

if (!function_exists("get_real_class_name")){
    
    /**
     * Returns well class name
     * @param string $class
     * @return string
     */
    function get_real_class_name($class)
    {
        return implode("", array_map("ucfirst", explode("_", $class)));
    }
}

if (!function_exists("get_class_name")){
    /**
     * Returns only class name
     * @param string $class
     * @return string
     */
    function get_class_name($class)
    {
        return end(explode("\\", $class));
    }
}

if (!function_exists("normalize_class_name")){
    function normalize_class_name($class)
    {
        $name = get_class_name($class);
        //replace it in original class name
        $class = str_replace($name, get_real_class_name(get_class_name($name)), $class);
        return str_to_studly_case($class, "\\", true);        
    }
}

if (! function_exists('data_fill')) {
	/**
	 * Fill in data where it's missing.
	 *
	 * @param  mixed   $target
	 * @param  string|array  $key
	 * @param  mixed  $value
	 * @return mixed
	 */
	function data_fill(&$target, $key, $value)
	{
		return data_set($target, $key, $value, false);
	}
}

if (! function_exists('data_get')) {
	/**
	 * Get an item from an array or object using "dot" notation.
	 *
	 * @param  mixed   $target
	 * @param  string|array  $key
	 * @param  mixed   $default
	 * @return mixed
	 */
	function data_get($target, $key, $default = null)
	{
		if (is_null($key)) {
			return $target;
		}

		$key = is_array($key) ? $key : explode('.', $key);

		while (! is_null($segment = array_shift($key))) {
			if ($segment === '*') {
				if ($target instanceof Collection) {
					$target = $target->all();
				} elseif (! is_array($target)) {
					return value($default);
				}

				$result = [];

				foreach ($target as $item) {
					$result[] = data_get($item, $key);
				}

				return in_array('*', $key) ? Arr::collapse($result) : $result;
			}

			if (Arr::accessible($target) && Arr::exists($target, $segment)) {
				$target = $target[$segment];
			} elseif (is_object($target) && isset($target->{$segment})) {
				$target = $target->{$segment};
			} else {
				return value($default);
			}
		}

		return $target;
	}
}

if (! function_exists('data_set')) {
	/**
	 * Set an item on an array or object using dot notation.
	 *
	 * @param  mixed  $target
	 * @param  string|array  $key
	 * @param  mixed  $value
	 * @param  bool  $overwrite
	 * @return mixed
	 */
	function data_set(&$target, $key, $value, $overwrite = true)
	{
		$segments = is_array($key) ? $key : explode('.', $key);

		if (($segment = array_shift($segments)) === '*') {
			if (! Arr::accessible($target)) {
				$target = [];
			}

			if ($segments) {
				foreach ($target as &$inner) {
					data_set($inner, $segments, $value, $overwrite);
				}
			} elseif ($overwrite) {
				foreach ($target as &$inner) {
					$inner = $value;
				}
			}
		} elseif (Arr::accessible($target)) {
			if ($segments) {
				if (! Arr::exists($target, $segment)) {
					$target[$segment] = [];
				}

				data_set($target[$segment], $segments, $value, $overwrite);
			} elseif ($overwrite || ! Arr::exists($target, $segment)) {
				$target[$segment] = $value;
			}
		} elseif (is_object($target)) {
			if ($segments) {
				if (! isset($target->{$segment})) {
					$target->{$segment} = [];
				}

				data_set($target->{$segment}, $segments, $value, $overwrite);
			} elseif ($overwrite || ! isset($target->{$segment})) {
				$target->{$segment} = $value;
			}
		} else {
			$target = [];

			if ($segments) {
				data_set($target[$segment], $segments, $value, $overwrite);
			} elseif ($overwrite) {
				$target[$segment] = $value;
			}
		}

		return $target;
	}
}

if (! function_exists('human_file_size')) {
	function human_file_size($bytes, $decimals = 2)
	{
		$sz = 'BKMGTP';
		$factor = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
	}
}


if (!function_exists("encrypt")){
	/**
	 *
	 * @return string
	 */
	function encrypt($data)
	{
		return app('encrypter')->encrypt($data);
	}
}

if (!function_exists("decrypt")){
	/**
	 *
	 * @return string
	 */
	function decrypt($data)
	{
		return app('encrypter')->decrypt($data);
	}
}

if (!function_exists("path")){
	/**
	 *
	 * @return \App\Kernel\Tools\Path
	 */
	function path($data, $allowDots = false)
	{
		return \App\Kernel\Tools\Path::make($data, $allowDots);
	}
}

if (!function_exists("app")){
    /**
     * 
     * @return \App\Kernel\Application
     */
    function app($service = null)
    {
    	if (!is_null($service)){
    		return app()->getService($service);
		}
        return App\Kernel\Application::getInstance();
    }
}

if (!function_exists("loader")){
	/**
	 *
	 * @return \App\Kernel\Loader
	 */
	function loader()
	{
		return app()->getLoader();
	}
}

if (!function_exists("config")){
	/**
	 *
	 * @return \App\Kernel\Tools\Collection
	 */
	function config($key = null, $default = null)
	{
		if (empty($key)){
			return app('config');
		}
		return config()->get($key, $default);
	}
}

if (!function_exists("response")){
	/**
	 *
	 * @return \App\Kernel\Http\ResponseFactory
	 */
	function response()
	{
		return new \App\Kernel\Http\ResponseFactory();
	}
}

if (!function_exists("request")){
	/**
	 *
	 * @return \App\Kernel\Http\Request
	 */
	function request()
	{
		return app('request');
	}
}

if (!function_exists("logger")){
	/**
	 *
	 * @return \App\Kernel\Logger
	 */
	function logger()
	{
		$logger = app('logger');
		$args = func_get_args();

		if (count($args) == 0){
			return $logger;
		}

		foreach ($args as $arg) {
			$logger->info($arg);
		}

		return $logger;
	}
}

if (!function_exists("encrypter")){
	/**
	 *
	 * @return \App\Kernel\Encrypter
	 */
	function encrypter()
	{
		return app('encrypter');
	}
}

if (!function_exists("image")){
	/**
	 *
	 * @return \Intervention\Image\ImageManager
	 */
	function image()
	{
		return app('image');
	}
}

if (!function_exists("files")){
	/**
	 *
	 * @return \App\Kernel\FileSystem
	 */
	function files()
	{
		return app('files');
	}
}
