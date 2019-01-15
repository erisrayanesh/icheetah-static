<?php

namespace App\Kernel\Tools;

class Path
{

	/**
	 * @param $path
	 * @param bool $allowDots
	 * @return \SplFileInfo
	 */
    public static function make($path, $allowDots = false)
	{
		if ($path instanceof \SplFileInfo){
			return $path;
		}

		return new \SplFileInfo(self::cleanSequentialDS($allowDots? self::cleanDots($path) : $path));
	}
    
	public static function append($path, $append, $allowDots = false)
    {
		$path = self::make($path, $allowDots);

		if (!$path->isDir()){
			throw new \InvalidArgumentException('Can not append: Argument 1 is not a directory: "' . $path->getRealPath() . '"');
		}

		$append = Path::make($append, $allowDots);

		return new \SplFileInfo($path->getRealPath() . DIRECTORY_SEPARATOR . $append->getPathname());
    }

	public static function prepend($path, $prepend, $allowDots = false)
	{

	}

	public static function cleanDots($path)
	{
		//remove sequential dot
		return preg_replace("/\.+/", "", $path);
	}

	public static function cleanSequentialDS($path)
	{
        //remove backslash
        $path = preg_replace("/\\+/", "/", $path);
        
        //remove sequential slash
        $path = preg_replace("/\/\/+/", "/", $path);
        
        return $path;
    }

    
}