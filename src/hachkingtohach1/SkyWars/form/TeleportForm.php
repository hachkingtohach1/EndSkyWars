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

class TeleportForm{

    /**
     * @param Player $player
     * @return void
     */
    public static function getForm(Player $player){
		$dataPlayer = SkyWars::getInstance()->getPlayer($player);
		if($dataPlayer->getNameArena() != ""){
			$dataArena = SkyWars::getInstance()->arenas[$dataPlayer->getNameArena()];
			if($dataArena->isSpectator($player)){
				$i = 0;
				$players = [];
				foreach($dataArena->getPlayers() as $xuid => $dataPlayer){
					$players[$i] = $dataPlayer;
					$i++;
				}
				$api = Server::getInstance()->getPluginManager()->getPlugin("FormAPI");
	    		$form = $api->createSimpleForm(function (Player $player, int $data = null) use ($players, $dataArena){
		    		$result = $data;
		    		if($result === null){
			    		return true;
					}
					if(count($players) >= 1){
				    	if(isset($players[$result])){
							$target = $players[$result];
							//debug
							if($dataArena->isSpectator($player)){
							    $player->teleport($target->getLocation());
							}
							return true;
						}
					}
					return false;
				});
				$form->setTitle(TextFormat::BOLD.TextFormat::GREEN."Teleporter");
				foreach($players as $case => $dataPlayer){
					$form->addButton(TextFormat::GREEN.$dataPlayer->getName(), 0, "");
				}
				$form->sendToPlayer($player);
				return $form;
			}
		}else{
			$player->sendMessage(TextFormat::RED."You must in-game!");
		}
	}
}