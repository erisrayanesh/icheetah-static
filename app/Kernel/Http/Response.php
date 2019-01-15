<?php

namespace App\Kernel\Http;

use ArrayObject;
use JsonSerializable;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

class Response extends BaseResponse
{

	public function setContent($content)
	{
		if ($this->shouldConvertToJson($content)) {
			$this->headers->set('Content-Type', 'application/json');
			$content = $this->convertToJson($content);
		}

		parent::setContent($content);

		return $this;
	}

	protected function shouldConvertToJson($content)
	{
		return $content instanceof ArrayObject ||
			$content instanceof JsonSerializable ||
			is_array($content);
	}

	protected function convertToJson($content)
	{
		return json_encode($content);
	}
}