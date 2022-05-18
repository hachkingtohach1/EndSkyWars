<?php

/**
 *  Copyright (c) 2022 hachkingtohach1
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *  SOFTWARE.
 */

namespace hachkingtohach1\SkyWars\utils;

use hachkingtohach1\SkyWars\SkyWars;

class Utils{

    /**
     * @param string $file
	 * @return string
     */
    public static function getResourceFile(string $file): string{
        return str_replace(["\\utils", "/utils"], DIRECTORY_SEPARATOR . "resources", __DIR__) . DIRECTORY_SEPARATOR . $file;
    }

    /**
     * @param string $directory
	 * @param callable $callable
	 * @return void
     */
    public static function callDirectory(string $directory, callable $callable): void{
        //get directory plugin from description in plugin.yml
		$main = explode("\\", SkyWars::getInstance()->getDescription()->getMain());
        //get last key in array
		unset($main[array_key_last($main)]);
        $main = implode("/", $main);
		//remove "/" in directory
        $directory = rtrim(str_replace(DIRECTORY_SEPARATOR, "/", $directory), "/");
		$dir = SkyWars::getInstance()->getFile() . "src/$main/" . $directory;
        foreach(array_diff(scandir($dir), [".", ".."]) as $file){
            $path = $dir . "/$file";
            $extension = pathinfo($path)["extension"] ?? null;
            if($extension === null){
                self::callDirectory($directory."/".$file, $callable);
            }elseif($extension === "php"){
                $namespaceDirectory = str_replace("/", "\\", $directory);
                $namespaceMain = str_replace("/", "\\", $main);
                $namespace = $namespaceMain."\\$namespaceDirectory\\". basename($file, ".php");
                $callable($namespace);
            }
        }
    }
}