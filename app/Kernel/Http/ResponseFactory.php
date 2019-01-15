<?php

namespace App\Kernel\Http;

use App\Kernel\Tools\Collection;
use App\Kernel\Tools\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use \Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class ResponseFactory
{

	public function create($content = '', $status = 200, array $headers = [])
	{
		return new Response($content, $status, $headers);
	}

	public function json($data = [], $status = 200, array $headers = [], $options = 0)
	{
		$response = new JsonResponse($data, $status, $headers);
		$response->setEncodingOptions($options);
		return $response;
	}

	public function stream($callback, $status = 200, array $headers = [])
	{
		return new StreamedResponse($callback, $status, $headers);
	}

	public function streamDownload($callback, $name = null, array $headers = [], $disposition = 'attachment')
	{
		$response = new StreamedResponse($callback, 200, $headers);

		if (! is_null($name)) {
			$response->headers->set('Content-Disposition', $response->headers->makeDisposition(
				$disposition,
				$name,
				$this->toAscii($name)
			));
		}

		return $response;
	}

	public function download($file, $name = null, array $headers = [], $disposition = 'attachment')
	{
		$response = new BinaryFileResponse($file, 200, $headers, true, $disposition);

		if (! is_null($name)) {
			return $response->setContentDisposition($disposition, $name, $this->toAscii($name));
		}

		return $response;
	}

	public function file($file, array $headers = [])
	{
		return (new BinaryFileResponse($file, 200, $headers));
	}

	protected function toAscii($name)
	{
		return str_replace('%', '', Str::str($name)->ascii($name));
	}

}
