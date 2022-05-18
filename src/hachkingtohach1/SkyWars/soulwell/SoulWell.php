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
 
namespace hachkingtohach1\SkyWars\soulwell;

use pocketmine\utils\TextFormat;
use pocketmine\player\Player;
use hachkingtohach1\SkyWars\SkyWars;
use hachkingtohach1\SkyWars\economy\Economy;
use hachkingtohach1\SkyWars\kit\KitManager;
use hachkingtohach1\SkyWars\cosmetics\Cosmetics;

class SoulWell{
	
	public const CAGES = 0;
	public const TRAILS = 1;
	public const KITS_NORMAL = 2;
	public const KITS_INSANE = 3;
	public const KITS_RANKED = 4;
	public const KITS_MEGA = 5;
	public const COINS = 6;
	
	public const PRICE_ONE_ROLL = 10;
	
	public const MESSAGE_NOT_ENOUGHT_SOUL = TextFormat::RED."You don't have enought souls to roll!";
	public const MESSAGE_FOUND_REWARD = TextFormat::GRAY."You found ".TextFormat::GOLD."#reward".TextFormat::GRAY." in the well!";
	public const MESSAGE_ALREADY_HAVE_REWARD = TextFormat::GRAY."You already have ".TextFormat::GOLD."#reward".TextFormat::GRAY." and converted into".TextFormat::GOLD." #coins";
	
	/**
     * @param Player $player
     */
	public static function roll(Player $player){
		$souls = Economy::getSouls($player);
		if($souls >= self::PRICE_ONE_ROLL){
			(new SoulWellTick(SkyWars::getInstance()))->registerQueue($player);
			Economy::minusSouls($player, 10);
		}else{
			$player->sendMessage(self::MESSAGE_NOT_ENOUGHT_SOUL);
		}
	}
	
	/**
     * @return array
     */
	public static function getReward() :array{
		$dataCages = Cosmetics::CAGES;
		$dataTrails = Cosmetics::TRAILS;
		$reward = [];
		$cages = [];
		$trails = [];
		$kitsnormal = [];
		$kitsinsane = [];
		$kitsranked = [];
		$kitsmega = [];
		$coins = [100, 1000, 2000, 3000, 4000, 5000, 7000];
		for($cage = Cosmetics::BANANA; $cage <= Cosmetics::BLAZING; $cage++){
			$cages[$cage] = $cage;
		}
		for($trail = Cosmetics::ENDER; $trail <= Cosmetics::FIREWORK; $trail++){
			$trails[$trail] = $trail;
		}
		foreach(KitManager::getKitsNormal() as $name => $class){
			$kitsnormal[$name] = $name;
		}
		foreach(KitManager::getKitsInsane() as $name => $class){
			$kitsinsane[$name] = $name;
		}
		foreach(KitManager::getKitsRanked() as $name => $class){
			$kitsranked[$name] = $name;
		}
		foreach(KitManager::getKitsMega() as $name => $class){
			$kitsmega[$name] = $name;
		}
		$chance = rand(0, 1500);
        if($chance >= 100 and $chance < 200){
            $reward = [
			    "type" => self::CAGES,
			    "result" => str_replace(" ", "", $dataCages[$cages[array_rand($cages, 1)]])
			];			
		}elseif($chance >= 200 and $chance < 300){
            $reward = [
			    "type" => self::TRAILS,
			    "result" => str_replace(" ", "", $dataTrails[$trails[array_rand($trails, 1)]])
			];			
		}elseif($chance >= 300 and $chance < 400){
            $reward = [
			    "type" => self::KITS_NORMAL,
			    "result" => str_replace(" ", "", $kitsnormal[array_rand($kitsnormal, 1)])
			];			
		}elseif($chance >= 400 and $chance < 500){
            $reward = [
			    "type" => self::KITS_INSANE,
			    "result" => str_replace(" ", "", $kitsinsane[array_rand($kitsinsane, 1)])
			];			
		}elseif($chance >= 500 and $chance < 600){
            $reward = [
			    "type" => self::KITS_RANKED,
			    "result" => str_replace(" ", "", $kitsranked[array_rand($kitsranked, 1)])
			];
        }elseif($chance >= 600 and $chance < 700){
            $reward = [
			    "type" => self::KITS_MEGA,
			    "result" => str_replace(" ", "", $kitsmega[array_rand($kitsmega, 1)])
			];				
		}else{
			$reward = [
			    "type" => self::COINS,
			    "result" => $coins[array_rand($coins, 1)]
			];
		}			
		return $reward;
	}
}