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

namespace hachkingtohach1\SkyWars\utils;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;
use function mb_strtolower;

/**
 * just is ScoreBoard API
 *
 */
class ScoreBoardAPI{

	private const OBJECTIVE_NAME = "objective";
	private const CRITERIA_NAME = "dummy";
	private const MIN_LINES = 1;
	private const MAX_LINES = 15;
	public const SORT_ASCENDING = 0;
	public const SLOT_SIDEBAR = "sidebar";

	/*@var array $scoreboards*/
	private static $scoreboards = [];

    /**
     * @param Player $player
     * @param string $displayName
     * @param int $slotOrder
     * @param string $displaySlot
     * @param string $objectiveName
     * @param string $criteriaName
     * @return void
     */
    public static function setScore(Player $player, string $displayName, int $slotOrder = self::SORT_ASCENDING, string $displaySlot = self::SLOT_SIDEBAR, string $objectiveName = self::OBJECTIVE_NAME, string $criteriaName = self::CRITERIA_NAME) :void{
		if(isset(self::$scoreboards[mb_strtolower($player->getName())])){
			self::removeScore($player);
		}

		$pk = new SetDisplayObjectivePacket();
		$pk->displaySlot = $displaySlot;
		$pk->objectiveName = $objectiveName;
		$pk->displayName = $displayName;
		$pk->criteriaName = $criteriaName;
		$pk->sortOrder = $slotOrder;
		$player->getNetworkSession()->sendDataPacket($pk);

		self::$scoreboards[mb_strtolower($player->getName())] = $objectiveName;
	}

    /**
     * @param Player $player
     * @return void
     */
    public static function removeScore(Player $player) :void{
		$objectiveName = self::$scoreboards[mb_strtolower($player->getName())] ?? self::OBJECTIVE_NAME;

		$pk = new RemoveObjectivePacket();
		$pk->objectiveName = $objectiveName;
		$player->getNetworkSession()->sendDataPacket($pk);

		unset(self::$scoreboards[mb_strtolower($player->getName())]);
	}

    /**
     * @return array
     */
    public static function getScoreboards() :array{
		return self::$scoreboards;
	}

    /**
     * @param Player $player
     * @return bool
     */
    public static function hasScore(Player $player) :bool{
		return isset(self::$scoreboards[mb_strtolower($player->getName())]);
	}

    /**
     * @param Player $player
     * @param int $line
     * @param string $message
     * @param int $type
     * @return void
     */
    public static function setScoreLine(Player $player, int $line, string $message, int $type = ScorePacketEntry::TYPE_FAKE_PLAYER) :void{
		if(!isset(self::$scoreboards[mb_strtolower($player->getName())])){
			throw new \BadFunctionCallException("Cannot set a score to a player without a scoreboard");
		}	
		if($line > self::MAX_LINES || $line < self::MIN_LINES){
            return;
        }
		$entry = new ScorePacketEntry();
		$entry->objectiveName = self::$scoreboards[mb_strtolower($player->getName())] ?? self::OBJECTIVE_NAME;
		$entry->type = $type;
		$entry->customName = $message;
		$entry->score = $line;
		$entry->scoreboardId = $line;

		$pk = new SetScorePacket();
		$pk->type = $pk::TYPE_CHANGE;
		$pk->entries[] = $entry;
		$player->getNetworkSession()->sendDataPacket($pk);
	}
}