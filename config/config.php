<?php

// CAUTION! NEVER CHANGE THIS FILE. IF YOU THINK ABOUT ANY UPDATE!
// THIS FILE CONTAINS DEFAULT CONFIGURATIONS

$arr = include ('config.env.php');

if (!is_array($arr)) {
	$arr = [];
}

$config = collect([
	"debug" => true,
	"app_key" => "",
	"app_url" => "",
	"media_dir" => "media",
	"log_file" => "logs" . DIRECTORY_SEPARATOR . "static.log",
	"cache" => [
		"enable" => true,
		"extensions" => ['jpg','png'],
	],
	"image" => [
		"driver" => "imagick",
		"fit" => "center",
		"watermarks" => [
			[
				"type" => "image",
				"file" => "media/logo_256.png",
				"position" => "top-left",
				"ratio" => 0.05,
				"offset" => [
					"x" => "15",
					"y" => "15",
				]
			],
			[
				"type" => "text",
				"text" => "westabuy.com",
				"position" => "bottom-right",
				"font_file" => "resources" . DIRECTORY_SEPARATOR . "fonts" . DIRECTORY_SEPARATOR . "Roboto-Regular.ttf",
				"font_size" => 12,
				"color" => "#000",
				"align" => "left",
				"offset" => [
					"x" => "10",
					"y" => "10",
				]
			]
		]
	]
]);

return $config->merge($arr);