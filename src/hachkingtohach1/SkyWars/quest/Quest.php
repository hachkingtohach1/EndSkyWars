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
 
namespace hachkingtohach1\SkyWars\quest;

use pocketmine\player\Player;
use pocketmine\console\ConsoleCommandSender;
use hachkingtohach1\SkyWars\SkyWars;

class Quest{
	
	public const EVENT_WINS = "win";
	public const EVENT_KILLS = "kill";
	
	/**
     * @return array
	 */
	public static function getEvents() :array{
		return [
		    self::EVENT_WINS,
			self::EVENT_KILLS
		];
	}
	
	/**
     * @return array
	 */
	public static function getDailyQuests() :array{
		return SkyWars::getInstance()->quests->get("daily-quests");
	}
	
	/**
     * @return array
	 */
	public static function getWeeklyQuests() :array{
		return SkyWars::getInstance()->quests->get("weekly-quests");
	}
	
	/**
     * @param string $data
	 */
	public static function setDailyQuestAll(string $data){
	    SkyWars::getInstance()->getDataBase()->setDailyQuestAll($data);
	}
	
	/**
     * @param string $data
	 */
	public static function setWeeklyQuestAll(string $data){
		SkyWars::getInstance()->getDataBase()->setWeeklyQuestAll($data);
	}
	
	/**
     * @param Player $player
     * @param int $maxCountInTeam
     * @return bool
     */
	public static function checkQuestPlayer(Player $player, int $maxCountInTeam, string $event){
		$mode = "";
		switch($maxCountInTeam){
			case 1:
			    $mode = "solo";
				break;
			case 2:
			    $mode = "double";
				break;
			case 3:
			    $mode = "triple";
				break;
		}
		Quest::checkDailyQuest($player, $event, $mode);
		Quest::checkWeeklyQuest($player, $event, $mode);
		Quest::checkDailyQuest($player, $event, "any");
		Quest::checkWeeklyQuest($player, $event, "any");
	}
	
	/**
	 * @param Player $player
	 * @param string $event
	 * @param string $mode
	 */
	public static function checkDailyQuest(Player $player, string $event, string $mode){
		$dataPlayer = explode("|", SkyWars::getInstance()->getDataBase()->getDailyQuest($player));
		$dataBase = self::getDailyQuests();
		if(count($dataPlayer) >= 1){
		    foreach($dataPlayer as $i => $k){
				foreach($dataBase as $case => $t){
					$convertData = explode(",", $k);
					if($t["event"] == $event and $t["mode"] == $mode and $convertData[0] == $case and $t["amount"] > $convertData[1] and $convertData[1] != "done"){
						$dataPlayer[$i] = $convertData[0].",".($convertData[1] + 1);
					}
					if($t["event"] == $event and $t["mode"] == $mode and $convertData[0] == $case and $t["amount"] == $convertData[1] and $convertData[1] != "done"){
						$dataPlayer[$i] = $convertData[0].",done";
						if(isset($dataBase[$convertData[0]])){
							$commands = $dataBase[$convertData[0]]["rewards-commands"];
						    $info = $dataBase[$convertData[0]]["info"];
							$amountNeed = $dataBase[$convertData[0]]["amount"];
							foreach($commands as $command){
								$player->getServer()->dispatchCommand(new ConsoleCommandSender($player->getServer(), $player->getServer()->getLanguage()), str_replace("{PLAYER}", $player->getName(), $command));
							}
							$player->sendMessage("§l§a▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃");
							$pointPlayer = 0;
							foreach(explode("|", SkyWars::getInstance()->getDataBase()->getDailyQuest($player)) as $data){
			                    if(explode(",", $data)[0] == $convertData[0]){
				                    $pointPlayer = explode(",", $data)[1];
								}
							}
							foreach($info as $text){
								$player->sendMessage(str_replace(["#min", "#max"], [$pointPlayer, $amountNeed], $text));
							}
							$player->sendMessage("§l§a▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃");
						}
					}
				}
			}
		}
		SkyWars::getInstance()->getDataBase()->setDailyQuest($player, implode("|", $dataPlayer));
	}
	
	/**
	 * @param Player $player
	 * @param string $event
	 * @param string $mode
	 */
	public static function checkWeeklyQuest(Player $player, string $event, string $mode){
		$dataPlayer = explode("|", SkyWars::getInstance()->getDataBase()->getWeeklyQuest($player));
		$dataBase = self::getWeeklyQuests();
		if(count($dataPlayer) >= 1){
		    foreach($dataPlayer as $i => $k){
				foreach($dataBase as $case => $t){
					$convertData = explode(",", $k);
					if($t["event"] == $event and $t["mode"] == $mode and $convertData[0] == $case and $t["amount"] > $convertData[1] and $convertData[1] != "done"){
						$dataPlayer[$i] = $convertData[0].",".($convertData[1] + 1);
					}
					if($t["event"] == $event and $t["mode"] == $mode and $convertData[0] == $case and $t["amount"] == $convertData[1] and $convertData[1] != "done"){
						$dataPlayer[$i] = $convertData[0].",done";
						if(isset($dataBase[$convertData[0]])){
							$commands = $dataBase[$convertData[0]]["rewards-commands"];
						    $info = $dataBase[$convertData[0]]["info"];
							$amountNeed = $dataBase[$convertData[0]]["amount"];
							foreach($commands as $command){
								$player->getServer()->dispatchCommand(new ConsoleCommandSender($player->getServer(), $player->getServer()->getLanguage()), str_replace("{PLAYER}", $player->getName(), $command));
							}
							$player->sendMessage("§l§a▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃");
							$pointPlayer = 0;
							foreach(explode("|", SkyWars::getInstance()->getDataBase()->getDailyQuest($player)) as $data){
			                    if(explode(",", $data)[0] == $convertData[0]){
				                    $pointPlayer = explode(",", $data)[1];
								}
							}
							foreach($info as $text){
								$player->sendMessage(str_replace(["#min", "#max"], [$pointPlayer, $amountNeed], $text));
							}
							$player->sendMessage("§l§a▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃");
						}
					}
				}
			}
		}
		SkyWars::getInstance()->getDataBase()->setWeeklyQuest($player, implode("|", $dataPlayer));
	}
}