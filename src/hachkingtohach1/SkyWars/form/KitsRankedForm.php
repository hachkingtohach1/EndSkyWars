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
use hachkingtohach1\SkyWars\SkyWars;
use hachkingtohach1\SkyWars\kit\KitManager;

class KitsRankedForm{

    /**
     * @param Player $player
     * @return mixed
     */
    public static function getForm(Player $player){
		$api = Server::getInstance()->getPluginManager()->getPlugin("FormAPI");
	    $i = 0;
		$kits = [];
		foreach((new KitManager())->getKitsRanked() as $name => $kit){
			$kits[$i] = $name;
			$i++;
		}
		$form = $api->createSimpleForm(function (Player $player, int $data = null) use ($kits){
		    $result = $data;
		    if($result === null){
			    return true;
			}
			if(isset($kits[$result])){
				if(!$player->hasPermission(KitManager::PERMISSION_KIT.".".str_replace(" ", "", strtolower($kits[$result])))){
					$player->sendMessage(TextFormat::RED."You don't have permission!");
					return false;
				}
				(new KitManager())->setKit($player, $kits[$result]);
				$player->sendMessage(TextFormat::GREEN."You have selected success!");
				return true;
			}
			return false;
		});
		$form->setTitle(TextFormat::BOLD.TextFormat::GREEN."Kit Selector");
		foreach($kits as $case => $kit){
			$convert = str_replace("Ranked", "", $kit);
			if(str_replace(" ", "", SkyWars::getInstance()->getDataBase()->getKitRanked($player)) == str_replace(" ", "", $kit)){
			    $form->addButton(TextFormat::GREEN.$convert, 0, "");
			}else{
				if($player->hasPermission(KitManager::PERMISSION_KIT.".".str_replace(" ", "", strtolower($kit)))){
				    $form->addButton(TextFormat::GOLD.$convert, 0, "");
				}else{
					$form->addButton(TextFormat::RED.$convert, 0, "");
				}
			}
		}
		$form->sendToPlayer($player);
		return $form;
	}
}