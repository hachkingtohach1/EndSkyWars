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
 
namespace hachkingtohach1\SkyWars\form;

use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use hachkingtohach1\SkyWars\economy\Economy;

class ExchangeSouls{

	/**
     * @param Player $player
	 * @param int $amount
     * @return bool
     */
	public static function payment(Player $player, int $amount) :bool{
		$formatCoins = number_format($amount);
		$coins = Economy::getCoins($player);
		$souls = Economy::getSouls($player);
		if($coins >= $amount){
			Economy::minusCoins($player, $amount);
			Economy::addSouls($player, $amount/1000);
			$player->sendMessage(TextFormat::GREEN."§l§4» §r§aPurchase Successful!");
			return true;
		}		
		$player->sendMessage(TextFormat::RED."§l§4» §r§c You don't have enough ".TextFormat::GOLD."$formatCoins Coins".TextFormat::RED." to make payment!");
		return false;
	}

    /**
     * @param Player $player
     * @return mixed
     */
    public static function getForm(Player $player){
		$api = Server::getInstance()->getPluginManager()->getPlugin("FormAPI");
		$form = $api->createSimpleForm(function (Player $player, int $data = null){
		    $result = $data;
		    if($result === null){
			    return true;
			}
			switch($result){
				case "0":
					self::payment($player, 1000);
				    break;
				case "1":
					self::payment($player, 5000);
					break;
				case "2":
					self::payment($player, 10000);
					break;
				case "3":
					self::payment($player, 50000);
					break;
				default: break;
			}
			return false;
		});
		$form->setTitle(TextFormat::BOLD.TextFormat::DARK_GRAY."Exchange Souls");
		$txt = TextFormat::GREEN."What is your choice?";
		$form->setContent($txt);
		$form->addButton(TextFormat::DARK_GRAY."1 Souls", 0, "");
		$form->addButton(TextFormat::DARK_GRAY."5 Souls", 0, "");
		$form->addButton(TextFormat::DARK_GRAY."10 Souls", 0, "");
		$form->addButton(TextFormat::DARK_GRAY."50 Souls", 0, "");
		$form->addButton(TextFormat::RED."Cancel", 0, "");
		$form->sendToPlayer($player);
		return $form;
	}
}
