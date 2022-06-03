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

use pocketmine\player\Player;
use hachkingtohach1\SkyWars\SkyWars;
use Vecnavium\FormsUI\Form;
use Vecnavium\FormsUI\SimpleForm;

class Form implements IForm{

  /**
    * @param Player $player
    * @return mixed
    */
  public static function getMenu(Player $player){
		return MenuForm::getForm($player);
	}

  /**
    * @param Player $player
    * @return mixed
    */
  public static function getCosmetics(Player $player){
		return CosmeticsForm::getForm($player);
	}

  /**
    * @param Player $player
    * @return mixed
    */
  public static function getStats(Player $player){
		return StatsForm::getForm($player);
	}

  /**
    * @param Player $player
    * @return mixed
    */
  public static function getCages(Player $player){
		return CagesForm::getForm($player);
	}

  /**
    * @param Player $player
    * @return mixed
    */
  public static function getTrails(Player $player){
		return TrailsForm::getForm($player);
	}

    /**
     * @param Player $player
     * @return mixed
     */
  public static function getMenuSoloMode(Player $player){
		return SoloModeForm::getForm($player);
	}

    /**
     * @param Player $player
     * @return mixed
     */
  public static function getMenuDoubleMode(Player $player){
		return DoubleModeForm::getForm($player);
	}
	
	/**
     * @param Player $player
     * @return mixed
     */
  public static function getMenuRankedMode(Player $player){
		return RankedModeForm::getForm($player);
	}
	
  /**
    * @param Player $player
    * @return void
    */
  public static function getMenuMegaMode(Player $player){
		return MegaModeForm::getForm($player);
	}

    /**
     * @param Player $player
     * @return mixed
     */
  public static function getMenuMode(Player $player){
		return MenuModeForm::getForm($player);
	}

  /**
    * @param Player $player
    * @return void
    */
  public static function getTeleporter(Player $player){
		return TeleportForm::getForm($player);
	}
	
  /**
    * @param Player $player
    * @return void
    */
  public static function getKitsNormalForm(Player $player){
		return KitsNormalForm::getForm($player);
	}
	
	/**
    * @param Player $player
    * @return void
    */
  public static function getKitsInsaneForm(Player $player){
		return KitsInsaneForm::getForm($player);
	}
	
  /**
    * @param Player $player
    * @return void
    */
  public static function getKitsRankedForm(Player $player){
		return KitsRankedForm::getForm($player);
	}
	
  /**
    * @param Player $player
    * @return void
    */
  public static function getKitsMegaForm(Player $player){
		return KitsMegaForm::getForm($player);
	}
	
	/**
    * @param Player $player
    * @return void
    */
  public static function getSoulWellForm(Player $player){
		return SoulWellForm::getForm($player);
	}

  /**
    * @param Player $player
    * @return void
    */
  public static function getExchangeSoulsForm(Player $player){
		return ExchangeSouls::getForm($player);
	}
	
  /**
    * @param Player $player
    * @return void
    */
  public static function getDailyQuestForm(Player $player){
		return DailyQuestForm::getForm($player);
	}
  
  /**
    * @param Player $player
    * @return void
    */
  public static function getWeeklyQuestForm(Player $player){
		return WeeklyQuestForm::getForm($player);
	}
	
  /**
    * @param Player $player
    * @return void
    */
  public static function getInfoQuestForm(Player $player, int $case){
		return InfoQuestForm::getForm($player, $case);
	}
	
  /**
    * @param Player $player
    * @return void
    */
  public static function getQuestsForm(Player $player){
		return QuestsForm::getForm($player);
	}
}
