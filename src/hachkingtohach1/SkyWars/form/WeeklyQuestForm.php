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
use hachkingtohach1\SkyWars\quest\Quest;

class WeeklyQuestForm{

    /**
     * @param Player $player
     * @return mixed
     */
    public static function getForm(Player $player){
		$api = Server::getInstance()->getPluginManager()->getPlugin("FormAPI");
		$convertCase = [];
	    $quests = Quest::getWeeklyQuests($player);
		foreach($quests as $case => $data){
			$convertCase[] = $case;
		}
		$form = $api->createSimpleForm(function (Player $player, int $data = null) use($convertCase){
		    $result = $data;
		    if($result === null){
			    return true;
			}
			if(isset($convertCase[$result])){
				Form::getInfoQuestForm($player, $convertCase[$result]);
			}
		});
		$form->setTitle("§l§bWeekly Quest");
		foreach($quests as $case => $data){
			$form->addButton("§8".$data["name"], 0, "");
		}
		$form->sendToPlayer($player);
		return $form;
	}
}