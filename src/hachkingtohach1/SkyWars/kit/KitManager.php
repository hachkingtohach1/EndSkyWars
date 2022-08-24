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
 * do
 */
 
namespace hachkingtohach1\SkyWars\kit;

use pocketmine\player\Player;
use hachkingtohach1\SkyWars\SkyWars;
use hachkingtohach1\SkyWars\utils\Utils;

class KitManager{
	/*@var array*/
	private static array $kitsNormal = [];
    /*@var array*/
	private static array $kitsInsane = [];
	/*@var array*/
	private static array $kitsRanked = [];
	/*@var array*/
	private static array $kitsMega = [];
	
	const MODE_NORMAL = "normal";
	const MODE_INSANE = "insane";
	const MODE_RANKED = "ranked";
	const MODE_MEGA = "mega";
	
	public const PERMISSION_KIT = "skywars.kit";

    /**
     * @return array
     */
    public static function getKitsNormal() :array{
		self::$kitsNormal = [];
		Utils::callDirectory("kit/normal", function (string $namespace): void{
			$class = new $namespace();
			KitManager::$kitsNormal[$class->getName()."Normal"] = $class;
		});
		return self::$kitsNormal;
	}

    /**
     * @return array
     */
    public static function getKitsInsane() :array{
		self::$kitsInsane = [];
		Utils::callDirectory("kit/insane", function (string $namespace): void{
			$class = new $namespace();
			KitManager::$kitsInsane[$class->getName()."Insane"] = $class;
		});
		return self::$kitsInsane;
	}
	
	/**
     * @return array
     */
    public static function getKitsRanked() :array{
		self::$kitsRanked = [];
		Utils::callDirectory("kit/ranked", function (string $namespace): void{
			$class = new $namespace();
			KitManager::$kitsRanked[$class->getName()."Ranked"] = $class;
		});
		return self::$kitsRanked;
	}
	
	/**
     * @return array
     */
    public static function getKitsMega() :array{
		self::$kitsMega = [];
		Utils::callDirectory("kit/mega", function (string $namespace): void{
			$class = new $namespace();
			KitManager::$kitsMega[$class->getName()."Mega"] = $class;
		});
		return self::$kitsMega;
	}

    /**
     * @param Player $player
     * @param string $mode
     * @return bool
     */
    public static function getKit(Player $player, string $mode) :bool{
		$kitNormal = self::getKitsNormal();
		$kitInsane = self::getKitsInsane();
		$kitRanked = self::getKitsRanked();
		$kitMega = self::getKitsMega();
		$kitPlayer = false;
		switch($mode){
			case self::MODE_NORMAL:
			    $kitPlayer = SkyWars::getInstance()->getDataBase()->getKitNormal($player);
			    break;
			case self::MODE_INSANE:
			    $kitPlayer = SkyWars::getInstance()->getDataBase()->getKitInsane($player);
			    break;
			case self::MODE_RANKED:
			    $kitPlayer = SkyWars::getInstance()->getDataBase()->getKitRanked($player);
			    break;
			case self::MODE_MEGA:
			    $kitPlayer = SkyWars::getInstance()->getDataBase()->getKitMega($player);
			    break;
		}
		if($kitPlayer){
			if(isset($kitNormal[$kitPlayer])){
				$class = $kitNormal[$kitPlayer];
				$class->get($player);
				return true;
			}
		    if(isset($kitInsane[$kitPlayer])){
				$class = $kitInsane[$kitPlayer];
				$class->get($player);
				return true;
			}
			if(isset($kitRanked[$kitPlayer])){
				$class = $kitRanked[$kitPlayer];
				$class->get($player);
				return true;
			}
			if(isset($kitMega[$kitPlayer])){
				$class = $kitMega[$kitPlayer];
				$class->get($player);
				return true;
			}
		}
		return false;
	}

    /**
     * @param Player $player
     * @param string $kit
     * @return void
     */
    public static function setKit(Player $player, string $kit){
		$kitNormal = self::getKitsNormal();
		$kitInsane = self::getKitsInsane();
		$kitRanked = self::getKitsRanked();
		$kitMega = self::getKitsMega();
		if(isset($kitNormal[$kit])){
			SkyWars::getInstance()->getDataBase()->setKitNormal($player, $kit);
		}
		if(isset($kitInsane[$kit])){
			SkyWars::getInstance()->getDataBase()->setKitInsane($player, $kit);
		}
		if(isset($kitRanked[$kit])){
			SkyWars::getInstance()->getDataBase()->setKitRanked($player, $kit);
		}
		if(isset($kitMega[$kit])){
			SkyWars::getInstance()->getDataBase()->setKitMega($player, $kit);
		}
	}
}
