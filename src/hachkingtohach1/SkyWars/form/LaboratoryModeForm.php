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
use hachkingtohach1\SkyWars\SkyWars;

class SoloModeForm{

    /**
     * @param Player $player
     * @return mixed
     */
    public static function getForm(Player $player){
		$dataPlayer = SkyWars::getInstance()->getPlayer($player);
		$api = Server::getInstance()->getPluginManager()->getPlugin("FormAPI");
	    $form = $api->createSimpleForm(function (Player $player, int $data = null) use($dataPlayer){
		    $result = $data;
		    if($result === null){
			    return true;
			}
		    switch($result){				
                case "0";				
				    if($dataPlayer->getNameArena() != ""){
					    $dataArena = SkyWars::getInstance()->arenas[$dataPlayer->getNameArena()];
				        if($dataArena->isSpectator($player)){
					        $dataArena->removePlayer($player);
						}else{
					        $dataArena->removePlayer($player, false, true);
						}
					}
                    Server::getInstance()->dispatchCommand($player, "sw solo normal");			
			        break;
                case "1";
				    if($dataPlayer->getNameArena() != ""){
					    $dataArena = SkyWars::getInstance()->arenas[$dataPlayer->getNameArena()];
				        if($dataArena->isSpectator($player)){
					        $dataArena->removePlayer($player);
						}else{
					        $dataArena->removePlayer($player, false, true);
						}
					}		
                    Server::getInstance()->dispatchCommand($player, "sw solo insane");				
			        break;					
                default: break;					
			}
		});
		$normal = SkyWars::getInstance()->getTotalCountPlayers("normal");
		$insane = SkyWars::getInstance()->getTotalCountPlayers("insane");
		$form->setTitle("§l§1Solo");
		$form->addButton("§aNormal §8".$normal." players", 0, "");
		$form->addButton("§cInsane §8".$insane." players", 0, "");
		$form->sendToPlayer($player);
		return $form;
	}
}
