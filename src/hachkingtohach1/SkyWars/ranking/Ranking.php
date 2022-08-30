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
 
namespace hachkingtohach1\SkyWars\ranking;

use pocketmine\utils\TextFormat;
use pocketmine\player\Player;
use hachkingtohach1\SkyWars\SkyWars;
use hachkingtohach1\SkyWars\economy\Economy;

class Ranking{
	
	const BASIC_XP = 50;
	
	/**
     * @param Player $player
     * @return float
     */
	public static function getLevel(Player $player) :float{
		return SkyWars::getInstance()->getDataBase()->getLevel($player);
	}
	
	/**
     * @param Player $player
     * @return float
     */
	public static function addLevel(Player $player) :float{
		return SkyWars::getInstance()->getDataBase()->addLevel($player, $amount);
	}
	
	/**
     * @param Player $player
	 * @param int $amount
     * @return float
     */
	public static function setLevel(Player $player, int $amount) :float{
		return SkyWars::getInstance()->getDataBase()->setLevel($player, $amount);
	}
	
	/**
     * @param Player $player
     * @return float
     */
	public static function getXp(Player $player) :float{
		return SkyWars::getInstance()->getDataBase()->getXp($player);
	}
	
	/**
     * @param Player $player
	 * @param int $amount
     * @return float
     */
	public static function setXp(Player $player, int $amount) :float{
		return SkyWars::getInstance()->getDataBase()->setXp($player, $amount);
	}
	
	/**
     * @param Player $player
	 * @param int $amount
     * @return float
     */
	public static function addXp(Player $player, int $amount) :float{
		return SkyWars::getInstance()->getDataBase()->setXp($player, (self::getXp($player) + $amount));
	}
	
	/**
     * @param Player $player
     * @return float
     */
	public static function getRating(Player $player) :float{
		return SkyWars::getInstance()->getDataBase()->getRating($player);
	}
	
	/**
     * @param Player $player
	 * @param int $amount
     * @return float
     */
	public static function setRating(Player $player, int $amount) :float{
		return SkyWars::getInstance()->getDataBase()->setRating($player, $amount);
	}
	
	/**
     * @param Player $player
	 * @param int $amount
     * @return float
     */
	public static function addRating(Player $player, int $amount) :float{
		return SkyWars::getInstance()->getDataBase()->setRating($player, (self::getRating($player) + $amount));
	}
	
	/**
     * @param Player $player
     * @return int
     */
	public static function getNextXp(Player $player) :int{
		$nextXp = (int)(self::BASIC_XP * (self::getLevel($player) + 1));
		return $nextXp;
	}
	
	/**
     * @param Player $player
     * @return string
     */
	public static function getColorLevel(Player $player) :string{
		$result = "";
		$level = self::getLevel($player);
		if($level >= 0){
			$result = TextFormat::GRAY.$level;
		}
		if($level >= 10){
			$result = TextFormat::GREEN.$level;
		}
		if($level >= 20){
			$result = TextFormat::BLUE.$level;
		}
		if($level >= 30){
			$result = TextFormat::YELLOW.$level;
		}
		if($level >= 40){
			$result = TextFormat::RED.$level;
		}
		if($level >= 50){
			$result = TextFormat::GOLD.$level;
		}
		return $result;
	}
	
	/**
     * @param Player $player
     * @return string
     */
	public static function getRanking(Player $player) :string{
		$result = "";
		$rating = self::getRating($player);
		if($rating > 1000){
			$result = TextFormat::BOLD.TextFormat::GRAY."●IRON●";
		}
		if($rating >= 1100){
			$result = TextFormat::GOLD."●BRONZE●";
		}
		if($rating >= 1500){
			$result = TextFormat::BOLD.TextFormat::WHITE."『SLIVER』";
		}
		if($rating >= 2500){
			$result = TextFormat::BOLD.TextFormat::GOLD."【『GOLD』】";
		}
		if($rating >= 3000){
			$result = TextFormat::BOLD.TextFormat::WHITE."❖".TextFormat::DARK_GRAY."PLATINUM".TextFormat::WHITE."❖";
		}
		if($rating >= 3500){
			$result = TextFormat::BOLD.TextFormat::GREEN."♦◊".TextFormat::DARK_GREEN."EMERALD".TextFormat::GREEN."◊♦";
		}
		if($rating >= 4000){
			$result = TextFormat::BOLD.TextFormat::DARK_AQUA."✦✧".TextFormat::AQUA."DIAMOND".TextFormat::DARK_AQUA."✧✦";
		}
		if($rating >= 4500){
			$result = TextFormat::BOLD.TextFormat::DARK_PURPLE."✠".TextFormat::LIGHT_PURPLE."MASTER".TextFormat::DARK_PURPLE."✠";
		}
		if($rating >= 5000){
			$result = TextFormat::BOLD.TextFormat::DARK_RED."✘".TextFormat::RED."GRANDMASTER".TextFormat::DARK_RED."✘";
		}
		if($rating >= 6500){
			$result = TextFormat::BOLD.TextFormat::WHITE."➷".TextFormat::YELLOW."♛".TextFormat::GOLD."CHALLENGER".TextFormat::YELLOW."♛".TextFormat::WHITE."➹";
		}
		return $result;
	}
	
	public function getDownRanking(){
           // remove
	}
	
	/**
     * @param Player $player
     * @return string
     */
	public static function searchNextXp(Player $player) :string{
		$result = "";
		$level = self::getXp($player);
		$xp = self::getLevel($player);
		if($level >= 25){
	            $result = self::addLevel($player, 1);
		}
		if($level >= 50){
		    $result = self::addLevel($player, 1);
		}
		if($level >= 75){
		    $result = self::addLevel($player, 1);
		}
		if($level >= 100){
		    $result = self::addLevel($player, 1);
		}
		if($level >= 125){
		    $result = self::addLevel($player, 1);
		}		
		if($level >= 150){
		    $result = self::addLevel($player, 1);
		}
		if($level >= 175){
		   $result = self::addLevel($player, 1);
		}
		if($level >= 200){
		    $result = self::addLevel($player, 1);
	        }
                if($level >= 225){
                    $result = self::addLevel($player, 1);
                }
                if($level >= 250){
                    $result = self::addLevel($player, 1);
                }		
		return $result;
	}
	
	/**
     * @param Player $player
     * @return string
     */
	public static function getColorLevel(Player $player) :string{
		$result = "";
		$level = self::getLevel($player);
		if($level >= 0){
			$result = TextFormat::GRAY.$level;
		}
		if($level >= 10){
			$result = TextFormat::GREEN.$level;
		}
		if($level >= 20){
			$result = TextFormat::BLUE.$level;
		}
		if($level >= 30){
			$result = TextFormat::YELLOW.$level;
		}
		if($level >= 40){
			$result = TextFormat::RED.$level;
		}
		if($level >= 50){
			$result = TextFormat::GOLD.$level;
		}
		return $result;
	}

    /**
     * @param Player $player
     * @return bool
     */
	public static function updateXpPlayer(Player $player) :bool{		
		$nextXp = self::getNextXp($player);
		if(self::getXp($player) >= $nextXp){
			$levelNow = self::getLevel($player);
			$nextLevel = self::getLevel($player) + 1;
			$coins = (1000 * (self::getLevel($player) + 1));
			$coinsAdd = number_format($coins);
			$player->sendMessage("§l§a▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃");
		    $player->sendMessage("§l§dSkyWars Level Up! §b{$levelNow}⚔ §7➨ §2{$nextLevel}⚔");
			$player->sendMessage("§8 +§6{$coinsAdd} §7SkyWars Coins");
			$player->sendMessage("§l§a▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃");
			self::setLevel($player, $nextLevel);
			self::setXp($player, 0);
			Economy::addCoins($player, $coins);
			$player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(10), [$player]);
			return true;
		}
		return false;
	}
}
