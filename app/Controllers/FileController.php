<?php

namespace App\Controllers;

use App\Kernel\Controller;
use App\Kernel\FileProfile;
use App\Kernel\Tools\Arr;
use App\Kernel\Tools\Path;
use App\Kernel\Tools\Str;
use Intervention\Image\Image;
use Intervention\Image\Size;

class FileController extends Controller
{
	public function show()
	{
		try {
			$original = app()->getPathTo("media" . DIRECTORY_SEPARATOR . request()->get("file"));
			$profile = FileProfile::capture(request());

			$cached = $this->getCachedFile($original, $profile);

			if ($cached){
				return response()->file($cached)->prepare(request());
			}

			$img = image()->make($original);
			if ($profile->has('f')){
				$img = $img->fit($profile->w, $profile->h, function ($constraint) {
					$constraint->upsize();
				}, $profile->getFitPos());
			}

			if ($profile->has('wm')){
				$wms = config('image.watermarks', []);
				foreach ($wms as $wm) {
					$type = Arr::get($wm, 'type');
					$method = "apply" . Str::title($type) . "Watermark";
					if (method_exists($this, $method)){
						$this->{$method}($img, $wm);
					}
				}
			}

			// Cache generated file
			$file = $this->getTokenizedFileName($original, $profile);

			$img->save($file);
			return response()->file($file)->prepare(request());

		} catch (\Exception $e) {
			dd($e);
//			return response()->file($file)->prepare(request());
		}

	}

	protected function getTokenizedFileName(\SplFileInfo $original, FileProfile $profile)
	{
		$file = basename($original->getFilename(), ".".$original->getExtension());
		return Path::append($original->getPath(), $file . "_" . $profile->humanize() . "." . $original->getExtension());
	}

	protected function getCachedFile(\SplFileInfo $original, FileProfile $profile)
	{
		$file = $this->getTokenizedFileName($original, $profile);
		if (file_exists($file)){
			return $file;
		}
		return false;
	}

	protected function applyImageWatermark(Image &$image, array $watermark)
	{
		$file = Arr::get($watermark, 'file');

		if (empty($file)){
			return;
		}

		$wm = image()->make(app()->getPathTo($file));
		$r = $this->calculateWatermarkRatio($image->width(), $image->height(), $wm->width(), $wm->height(), Arr::get( $watermark,'ratio', '0.02'));
		$wm = $wm->resize(
			$wm->width() * $r,
			$wm->height() * $r
		);

		$image->insert(
			$wm,
			Arr::get($watermark, 'position', 'top_left'),
			Arr::get($watermark, 'offset.x', 0),
			Arr::get($watermark, 'offset.y', 0)
		);
	}

	protected function applyTextWatermark(Image &$image, array $watermark)
	{
		$text = Arr::get($watermark, 'text');
		if (empty($text)){
			return;
		}

		$point = $this->calculateTextPosition($image, $text, $watermark);

		$image->text(
			$text,
			$point->x,
			$point->y,
			function($font) use ($watermark){
				$this->setupTextFont($font, $watermark);
			}
		);
	}

	protected function calculateWatermarkRatio($srcWidth, $srcHeight, $wmWidth, $wmHeight, $wmRatio)
	{
		$wmArea = ($srcWidth * $srcHeight) * $wmRatio;
		return sqrt($wmArea / ($wmWidth * $wmHeight));
	}

	/**
	 * @param Image $src
	 * @param $text
	 * @param $properties
	 * @return \Intervention\Image\Point
	 */
	protected function calculateTextPosition(Image $src, $text, $properties)
	{
		$fontClassName = sprintf('\Intervention\Image\%s\Font', $src->getDriver()->getDriverName());
		$font = new $fontClassName($text);
		$this->setupTextFont($font, $properties);
		$box = $font->getBoxSize();
		$size = new Size($box['width'], $box['height']);
		return $this->alignInsertionPoint(
			$src->getSize(),
			$size,
			Arr::get($properties, 'position', 'top_left'),
			Arr::get($properties, 'offset.x', 0),
			Arr::get($properties, 'offset.y', 0)
		);
	}

	protected function setupTextFont(&$font, array $properties = [])
	{
		$font->file(Arr::get($properties, 'font_file'));
		$font->size(Arr::get($properties, 'font_size', 12));
		$font->color(Arr::get($properties, 'color', "#000"));
		$font->align(Arr::get($properties, 'align', "center"));
		$font->valign(Arr::get($properties, 'valign', "top"));
		//$font->angle(45);
	}

	/**
	 * @param Size $srcSize
	 * @param Size $dstSize
	 * @param $position
	 * @param int $offsetX
	 * @param int $offsetY
	 * @return \Intervention\Image\Point
	 */
	protected function alignInsertionPoint(Size $srcSize, Size $dstSize, $position, $offsetX = 0, $offsetY = 0)
	{
		$image_size = $srcSize->align($position, $offsetX, $offsetY);
		$watermark_size = $dstSize->align($position);
		return $image_size->relativePosition($watermark_size);
	}

}