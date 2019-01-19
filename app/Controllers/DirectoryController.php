<?php

namespace App\Controllers;

use App\Kernel\Controller;
use App\Kernel\Tools\Arr;
use App\Kernel\Tools\FileSystem;
use App\Kernel\Tools\Path;
use App\Kernel\Tools\Str;

class DirectoryController extends Controller
{

	public function index()
	{
		$path = $this->getRequestedPath();

		$retVal = [
			"path" => $path
		];

		$dir  = request()->get('dir', 1);
		if ($dir){
			$retVal['directories'] = $this->getDirectories($path);
		}

		$files  = request()->get('files', 1);
		if ($files){
			$retVal['files'] = $this->getFiles($path);
		}

		return response()->json($retVal);
	}


	private function getFiles($path = "")
	{
		$realPath = app()->getPathTo($path)->getRealPath();
		$items = array_map(function($value) use ($realPath, $path){
			$value = new \SplFileInfo($value);
			//dd($value);
			return [
				"name" => $value->getFilename(),
				"ext" => $value->getExtension(),
				"size" => human_file_size($value->getSize()),
				"url" => app()->getUriTo($path . "/" . $value->getFilename()),
			];
		}, files()->files($realPath));

		return $items;
	}

	private function getDirectories($path = "")
	{
		$realPath = app()->getPathTo($path)->getRealPath();
		$items = array_map(function($value) use ($realPath, $path){
			$basename = basename($value);
			return [
				"name" => $basename,
				"empty" => count(files()->files($realPath . DIRECTORY_SEPARATOR . $basename)) == 0,
			];
		}, files()->directories($realPath));
		return $items;
	}

	/**
	 * @return \SplFileInfo
	 */
	private function getRequestedPath()
	{
		return config('media_dir') . "/" . trim(request()->get('path'), "\\\/");
	}


}