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
use hachkingtohach1\SkyWars\soulwell\SoulWell;

class SoulWellForm{

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
				    SoulWell::roll($player);
				    break;
				case "1":
					Form::getExchangeSoulsForm($player);
					break;
				default: break;
			}
			return false;
		});
		$form->setTitle(TextFormat::BOLD.TextFormat::DARK_GRAY."Soulwell");
		$txt = 
		    TextFormat::GRAY."Rolls a random kit, perk or coins bonus.\n".
			TextFormat::GRAY."Cost: ".TextFormat::AQUA."10 souls"
		;
		$form->setContent($txt);
		$form->addButton(TextFormat::DARK_GRAY."Confirm", 0, "");		
		$form->addButton(TextFormat::DARK_GRAY."Exchange Souls", 0, "");
		$form->addButton(TextFormat::RED."Cancel", 0, "");
		$form->sendToPlayer($player);
		return $form;
	}
}