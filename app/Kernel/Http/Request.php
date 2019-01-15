<?php

namespace App\Kernel\Http;

use App\Kernel\Tools\Arr;
use App\Kernel\Tools\Collection;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class Request extends SymfonyRequest
{

	use InteractsWithContentTypes,
		InteractsWithInput;

	/**
	 * All of the converted files for the request.
	 *
	 * @var array
	 */
//	protected $convertedFiles;

	public static function capture()
	{
		static::enableHttpMethodParameterOverride();
		return static::createFromBase(SymfonyRequest::createFromGlobals());
	}

	public static function createFrom(self $from, $to = null)
	{
		$request = $to ?: new static;

		$files = $from->files->all();

		$files = is_array($files) ? array_filter($files) : $files;

		$request->initialize(
			$from->query->all(),
			$from->request->all(),
			$from->attributes->all(),
			$from->cookies->all(),
			$files,
			$from->server->all(),
			$from->getContent()
		);

		$request->setJson($from->json());

		if ($session = $from->getSession()) {
			$request->setLaravelSession($session);
		}

		$request->setUserResolver($from->getUserResolver());

		$request->setRouteResolver($from->getRouteResolver());

		return $request;
	}

	public static function createFromBase(SymfonyRequest $request)
	{
		if ($request instanceof static) {
			return $request;
		}
		$content = $request->content;

		$request = (new static)->duplicate(
			$request->query->all(), $request->request->all(), $request->attributes->all(),
			$request->cookies->all(), $request->files->all(), $request->server->all()
		);

		$request->content = $content;

		$request->request = $request->getInputSource();

		return $request;
	}

	public function duplicate(array $query = null, array $request = null, array $attributes = null, array $cookies = null, array $files = null, array $server = null)
	{
		return parent::duplicate($query, $request, $attributes, $cookies, $this->filterFiles($files), $server);
	}

	public function ajax()
	{
		return $this->isXmlHttpRequest();
	}

	public function pjax()
	{
		return $this->headers->get('X-PJAX') == true;
	}

	public function ip()
	{
		return $this->getClientIp();
	}

	public function ips()
	{
		return $this->getClientIps();
	}

	public function userAgent()
	{
		return $this->headers->get('User-Agent');
	}

	public function url()
	{
		return rtrim(preg_replace('/\?.*/', '', $this->getUri()), '/');
	}

	public function fullUrl()
	{
		$query = $this->getQueryString();

		$question = $this->getBaseUrl().$this->getPathInfo() === '/' ? '/?' : '?';

		return $query ? $this->url().$question.$query : $this->url();
	}

	public function fullUrlWithQuery(array $query)
	{
		$question = $this->getBaseUrl().$this->getPathInfo() === '/' ? '/?' : '?';

		return count($this->query()) > 0
			? $this->url().$question.Arr::query(array_merge($this->query(), $query))
			: $this->fullUrl().$question.Arr::query($query);
	}

	public function path()
	{
		$pattern = trim($this->getPathInfo(), '/');

		return $pattern == '' ? '/' : $pattern;
	}

	public function segment($index, $default = null)
	{
		return Arr::get($this->segments(), $index - 1, $default);
	}

	public function segments()
	{
		$segments = explode('/', $this->decodedPath());

		return array_values(array_filter($segments, function ($value) {
			return $value !== '';
		}));
	}

	public function toArray()
	{
		return $this->all();
	}

	protected function getInputSource()
	{
		if ($this->isJson()) {
			return $this->json();
		}
		return in_array($this->getRealMethod(), ['GET', 'HEAD']) ? $this->query : $this->request;
	}

	protected function filterFiles($files)
	{
		if (! $files) {
			return;
		}

		foreach ($files as $key => $file) {
			if (is_array($file)) {
				$files[$key] = $this->filterFiles($files[$key]);
			}

			if (empty($files[$key])) {
				unset($files[$key]);
			}
		}

		return $files;
	}

    
}