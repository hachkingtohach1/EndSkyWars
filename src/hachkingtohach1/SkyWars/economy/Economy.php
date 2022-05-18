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
 
namespace hachkingtohach1\SkyWars\economy;

use pocketmine\player\Player;
use hachkingtohach1\SkyWars\SkyWars;

class Economy{

    /**
     * @param Player $player
     * @return float
     */
    public static function getTokens(Player $player) :float{
		return SkyWars::getInstance()->getDataBase()->getTokens($player);
	}

    /**
     * @param Player $player
     * @return float
     */
    public static function setTokens(Player $player, int $amount) :float{
		return SkyWars::getInstance()->getDataBase()->setTokens($player, $amount);
	}

    /**
     * @param Player $player
     * @param int $amount
     * @return void
     */
    public static function addTokens(Player $player, int $amount){
		$tokens = SkyWars::getInstance()->getDataBase()->getTokens($player);
		self::setTokens($player, ($tokens + $amount));
	}

    /**
     * @param Player $player
     * @param int $amount
     * @return float
     */
    public static function minusTokens(Player $player, int $amount) :float{
		$tokens = SkyWars::getInstance()->getDataBase()->getTokens($player);
		return self::setTokens($player, ($tokens - $amount));
	}

    /**
     * @param Player $player
     * @return float
     */
    public static function getCoins(Player $player) :float{
		return SkyWars::getInstance()->getDataBase()->getCoins($player);
	}

    /**
     * @param Player $player
     * @return float
     */
    public static function setCoins(Player $player, int $amount) :float{
		return SkyWars::getInstance()->getDataBase()->setCoins($player, $amount);
	}

    /**
     * @param Player $player
     * @param int $amount
     * @return float
     */
    public static function addCoins(Player $player, int $amount) :float{
		$coins = SkyWars::getInstance()->getDataBase()->getCoins($player);
		return self::setCoins($player, ($coins + $amount));
	}

    /**
     * @param Player $player
     * @param int $amount
     * @return float
     */
    public static function minusCoins(Player $player, int $amount) :float{
		$coins = SkyWars::getInstance()->getDataBase()->getCoins($player);
		return self::setCoins($player, ($coins - $amount));
	}

    /**
     * @param Player $player
     * @return float
     */
    public static function getSouls(Player $player) :float{
		return SkyWars::getInstance()->getDataBase()->getSouls($player);
	}

    /**
     * @param Player $player
     * @return float
     */
    public static function setSouls(Player $player, int $amount) :float{
		return SkyWars::getInstance()->getDataBase()->setSouls($player, $amount);
	}

    /**
     * @param Player $player
     * @param int $amount
     * @return float
     */
    public static function addSouls(Player $player, int $amount) :float{
		$souls = SkyWars::getInstance()->getDataBase()->getSouls($player);
		return self::setSouls($player, ($souls + $amount));
	}

    /**
     * @param Player $player
     * @param int $amount
     * @return float 
     */
    public static function minusSouls(Player $player, int $amount) :float{
		$souls = SkyWars::getInstance()->getDataBase()->getSouls($player);
		return self::setSouls($player, ($souls - $amount));
	}
}