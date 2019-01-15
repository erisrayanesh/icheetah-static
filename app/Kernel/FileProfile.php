<?php

namespace App\Kernel;

use App\Kernel\Http\Request;
use App\Kernel\Tools\Arr;
use \App\Kernel\Tools\Collection;

class FileProfile extends Collection
{

	public static function capture(Request $request)
	{
		return new static($request->only([
			"w", "h", "f", "wm",
		]));
	}

	public function __construct(array $items = array())
	{
		parent::__construct($items);
	}

	public function humanize()
	{
		$set = [];
		foreach ($this->items as $key => $item) {
			$set[] = "$key-$item";
		}
		return implode("_", $set);
	}

	public function getFitPos()
	{
		if (!$this->has("f")){
			return "center";
		}

		return $this->getPos($this->get("f"));
	}

	protected function getPos($pos, $default = "center")
	{
		return Arr::get([
			"tl" => "top-left",
			"t" => "top",
			"tr" => "top-right",
			"l" => "left",
			"c" => "center",
			"r" => "right",
			"bl" => "bottom-left",
			"b" => "bottom",
			"br" => "bottom-right",
		], $pos, $default);
	}

}