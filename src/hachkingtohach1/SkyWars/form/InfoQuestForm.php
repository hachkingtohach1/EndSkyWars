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

class InfoQuestForm{

    /**
     * @param Player $player
     * @return mixed
     */
    public static function getForm(Player $player, int $case){
		$api = Server::getInstance()->getPluginManager()->getPlugin("FormAPI");		
		$pointPlayer = 0;		
		$dataBase = null;
		foreach(Quest::getDailyQuests($player) as $i => $data){
			if($i == $case){
				$dataBase = $data;
			}
		}
		foreach(Quest::getWeeklyQuests($player) as $k => $data){
			if($k == $case){
				$dataBase = $data;
			}
		}
		if($dataBase == null){
			return;
		}	       
		foreach(explode("|", SkyWars::getInstance()->getDataBase()->getDailyQuest($player)) as $data){
			if(explode(",", $data)[0] == $case){
				$pointPlayer = explode(",", $data)[1];
			}
		}
		foreach(explode("|", SkyWars::getInstance()->getDataBase()->getWeeklyQuest($player)) as $data){
			if(explode(",", $data)[0] == $case){
				$pointPlayer = explode(",", $data)[1];
			}
		}			
		$form = $api->createSimpleForm(function (Player $player, int $data = null){
		    $result = $data;
		    if($result === null){
			    return true;
			}			
		});        	
		$form->setTitle($dataBase["name"]);
		$form->setContent(str_replace(["#min", "#max"], [$pointPlayer, $dataBase["amount"]], implode("\n", $dataBase["info"])));
		$form->addButton("Submit", 0, "");
		$form->sendToPlayer($player);
		return $form;
	}
}