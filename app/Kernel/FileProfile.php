<?php

namespace App\Kernel;

use App\Kernel\Http\Request;
use App\Kernel\Tools\Arr;
use \App\Kernel\Tools\Collection;

class FileProfile extends Collection
{

	public static function capture(Request $request)
	{
		return new static(array_filter($request->only([
			"w", "h", "f", "wm",
		]), function ($value){
			return !empty($value);
		}));
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

	public function hasWatermark()
	{
		return $this->has("wm");
	}

	public function hasFit()
	{
		return $this->has("f");
	}

	public function getFitPos()
	{
		if (!$this->has("f")){
			return "center";
		}

		return $this->getPos($this->get("f"));
	}

	public function getWidth()
	{
		return $this->get('w');
	}

	public function getHeight()
	{
		return $this->get('h');
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