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
 
namespace hachkingtohach1\SkyWars\cosmetics;

use pocketmine\entity\Entity;
use pocketmine\player\Player;
use hachkingtohach1\SkyWars\SkyWars;
use hachkingtohach1\SkyWars\cosmetics\cages\Angel;
use hachkingtohach1\SkyWars\cosmetics\cages\Banana;
use hachkingtohach1\SkyWars\cosmetics\cages\Blazing;
use hachkingtohach1\SkyWars\cosmetics\cages\Blue;
use hachkingtohach1\SkyWars\cosmetics\cages\BubbleGum;
use hachkingtohach1\SkyWars\cosmetics\cages\Cloud;
use hachkingtohach1\SkyWars\cosmetics\cages\Dark;
use hachkingtohach1\SkyWars\cosmetics\cages\DiamondKing;
use hachkingtohach1\SkyWars\cosmetics\cages\Domino;
use hachkingtohach1\SkyWars\cosmetics\cages\Faded;
use hachkingtohach1\SkyWars\cosmetics\cages\FarmHunt;
use hachkingtohach1\SkyWars\cosmetics\cages\Green;
use hachkingtohach1\SkyWars\cosmetics\cages\Ice;
use hachkingtohach1\SkyWars\cosmetics\cages\Lime;
use hachkingtohach1\SkyWars\cosmetics\cages\MagicBox;
use hachkingtohach1\SkyWars\cosmetics\cages\Mist;
use hachkingtohach1\SkyWars\cosmetics\cages\Nether;
use hachkingtohach1\SkyWars\cosmetics\cages\Nicolas;
use hachkingtohach1\SkyWars\cosmetics\cages\Normal;
use hachkingtohach1\SkyWars\cosmetics\cages\NotchApple;
use hachkingtohach1\SkyWars\cosmetics\cages\Orange;
use hachkingtohach1\SkyWars\cosmetics\cages\Plasma;
use hachkingtohach1\SkyWars\cosmetics\cages\Premium;
use hachkingtohach1\SkyWars\cosmetics\cages\Prison;
use hachkingtohach1\SkyWars\cosmetics\cages\Privacy;
use hachkingtohach1\SkyWars\cosmetics\cages\Rage;
use hachkingtohach1\SkyWars\cosmetics\cages\Rainbow;
use hachkingtohach1\SkyWars\cosmetics\cages\RedstoneMaster;
use hachkingtohach1\SkyWars\cosmetics\cages\Royal;
use hachkingtohach1\SkyWars\cosmetics\cages\Sky;
use hachkingtohach1\SkyWars\cosmetics\cages\Slime;
use hachkingtohach1\SkyWars\cosmetics\cages\Toffee;
use hachkingtohach1\SkyWars\cosmetics\cages\Tree;
use hachkingtohach1\SkyWars\cosmetics\cages\VoidC;
use hachkingtohach1\SkyWars\cosmetics\trails\AngryVillager;
use hachkingtohach1\SkyWars\cosmetics\trails\BlackSmoke;
use hachkingtohach1\SkyWars\cosmetics\trails\BlueDust;
use hachkingtohach1\SkyWars\cosmetics\trails\Ender;
use hachkingtohach1\SkyWars\cosmetics\trails\Fire;
use hachkingtohach1\SkyWars\cosmetics\trails\Firework;
use hachkingtohach1\SkyWars\cosmetics\trails\GreenStar;
use hachkingtohach1\SkyWars\cosmetics\trails\Hearts;
use hachkingtohach1\SkyWars\cosmetics\trails\Magic;
use hachkingtohach1\SkyWars\cosmetics\trails\Notes;
use hachkingtohach1\SkyWars\cosmetics\trails\Potion;
use hachkingtohach1\SkyWars\cosmetics\trails\PurpleDust;
use hachkingtohach1\SkyWars\cosmetics\trails\RedDust;
use hachkingtohach1\SkyWars\cosmetics\trails\WhiteSmoke;

class Cosmetics{
    
	/*Cages*/
    //common
    public const BANANA = 0;
	public const BUBBLEGUM = 1;
	public const CLOUD = 2;
	public const GREEN = 3;
	public const LIME = 4;
	public const MIST = 5;
	public const ORANGE = 6;
	public const SKY = 7;
	public const TOFFEE = 8;
	public const VOIDCAGE = 9;
	//rare
	public const ANGEL = 10;
	public const BLUE = 11;
	public const DARK = 12;
	public const PREMIUM = 13;
	public const RAGE = 14;
	public const ROYAL = 15;
	public const FARM_HUNT = 16;
	//epic
	public const NETHER = 17;
	public const NOTCH_APPLE = 18;
	public const REDSTONE_MASTER = 19;
	public const PLASMA = 20;
	public const FADED = 21;
	public const BLAZING = 22;
	//legendary
	public const ICE = 23;
	public const MAGIC_BOX = 24;
	public const SLIME = 25;
	public const TREE = 26;
	public const NICOLAS = 27;
	public const PRIVACY = 28;
	public const PRISON = 29;
	public const DIAMOND_KING = 30;
	public const DOMINO = 31;
	public const RAINBOW = 32;
	/**/
	
	/*Trails*/
	//common
	public const ENDER = 33;
	public const BLACK_SMOKE = 34;
	public const WHITE_SMOKE = 35;
	//rare
	public const BLUE_DUST = 36;
	public const RED_DUST = 37;
	public const PURPLE_DUST = 38;
	//epic
	public const FIRE = 39;
	public const MAGIC = 40;
	public const ANGRY_VILLAGER = 41;
	public const FIREWORK = 42;
	//legendary
	//ranked
	public const POTION = 43;
	public const HEARTS = 44;
	public const NOTES = 45;
	public const GREEN_STAR = 46;
	
	public const CAGES = [
	    "Banana",
	    "Bubblegum",
	    "Cloud",
	    "Green",
	    "Lime",
	    "Mist",
	    "Orang",
	    "Sky",
	    "Toffee",
	    "Void",
	    "Angel",
	    "Blue",
	    "Dark",
	    "Premium",
	    "Rage",
	    "Royal",
	    "Farm hunt",
	    "Nether",
	    "Notch apple",
	    "Redstone master",
	    "Plasma",
	    "Faded",
	    "Blazing",
	    "Ice",
	    "Magic box",
	    "Slime",
	    "Tree",
	    "Nicolas",
	    "Privacy",
	    "Prison",
	    "Diamond king",
	    "Domino",
	    "Rainbow"
	];
	
	//because index this function from id trails
	public const TRAILS = [
	    33 => "Ender",
		34 => "Black Smoke",
		35 => "White Smoke",
		36 => "Blue Dust",
		37 => "Red Dust",
		38 => "Purple Dust",
		39 => "Fire",
		40 => "Magic",
		41 => "Angry Villager",
		42 => "Firework",
		43 => "Potion",
		44 => "Hearts",
		45 => "Notes",
		46 => "Green Star"
	];
	
	public const PERMISSION_CAGES = "skywars.cages";
	public const PERMISSION_TRAILS = "skywars.trails";
	
	/**
	 * @param Player $player
	 * @return bool
	 */
	public function checkCagePlayer(Player $player, int $id) :bool{
		$cosmetics = $this->getCosmeticUsing($player);
		foreach($cosmetics as $i){
			if($i >= self::BANANA and $i <= self::RAINBOW){
				if($id == $i){
				    return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * @param Player $player
	 * @return bool
	 */
	public function checkTrailPlayer(Player $player, int $id) :bool{
		$cosmetics = $this->getCosmeticUsing($player);
		foreach($cosmetics as $i){
			if($i >= self::ENDER and $i <= self::GREEN_STAR){
				if($id == $i){
				    return true;
				}
			}
		}
		return false;
	}

    /**
     * @param int $min
     * @param int $max
     * @return bool
     */
    public function convertDataCosmetic(Player $player, int $min, int $max, int $id) :bool{
        $cosmetics = $this->getCosmeticUsing($player);
        if($id >= $min and $id <= $max){
            foreach($cosmetics as $case => $i){
                if($i >= $min and $i <= $max){
                    unset($cosmetics[$case]);
                }
            }
            $cosmetics[] = $id;
            return true;
        }
        return false;
    }
	
	/**
	 * @param Player $player
	 * @param int $id
	 * @return bool
	 */
	public function setCage(Player $player, int $id) :bool{
		return $this->convertDataCosmetic($player, self::BANANA, self::RAINBOW, $id);
	}
	
	/**
	 * @param Player $player
	 * @param int $id
	 * @return bool
	 */
	public function setTrail(Player $player, int $id) :bool{
        return $this->convertDataCosmetic($player, self::ENDER, self::GREEN_STAR, $id);
	}
	
	/**
	 * @param Player $player
	 */
    public function getCage(Player $player){
		$cosmetics = $this->getCosmeticUsing($player);
		$haveCage = false;
		foreach($cosmetics as $i){
			if($i >= self::BANANA and $i <= self::RAINBOW){
				$haveCage = $i;
			}
		}
		$cage = new Normal();
		if($haveCage != false){
		    switch($haveCage){
				case self::BANANA:
				    $cage = new Banana();
				    break;
				case self::BUBBLEGUM:
				    $cage = new BubbleGum();
				    break;
				case self::CLOUD:
				    $cage = new Cloud();
				    break;
				case self::GREEN:
				    $cage = new Green();
				    break;
				case self::LIME:
				    $cage = new Lime();
				    break;
				case self::MIST:
				    $cage = new Mist();
				    break;
				case self::ORANGE:
				    $cage = new Orange();
				    break;
				case self::SKY:
				    $cage = new Sky();
				    break;
				case self::TOFFEE:
				    $cage = new Toffee();
				    break;
				case self::VOIDCAGE:
				    $cage = new VoidC();
				    break;
				case self::ANGEL:
				    $cage = new Angel();
				    break;
				case self::BLUE:
				    $cage = new Blue();
				    break;
				case self::DARK:
				    $cage = new Dark();
				    break;
				case self::PREMIUM:
				    $cage = new Premium();
				    break;
				case self::RAGE:
				    $cage = new Rage();
				    break;
				case self::ROYAL:
				    $cage = new Royal();
				    break;
				case self::FARM_HUNT:
				    $cage = new FarmHunt();
				    break;
				case self::NETHER:
				    $cage = new Nether();
				    break;
				case self::NOTCH_APPLE:
				    $cage = new NotchApple();
				    break;
				case self::REDSTONE_MASTER:
				    $cage = new RedstoneMaster();
				    break;
				case self::PLASMA:
				    $cage = new Plasma();
				    break;
				case self::FADED:
				    $cage = new Faded();
				    break;
				case self::BLAZING:
				    $cage = new Blazing();
				    break;
				case self::ICE:
				    $cage = new Ice();
				    break;
				case self::MAGIC_BOX:
				    $cage = new MagicBox();
				    break;
				case self::SLIME:
				    $cage = new Slime();
				    break;
				case self::TREE:
				    $cage = new Tree();
				    break;
				case self::NICOLAS:
				    $cage = new Nicolas();
				    break;
				case self::PRIVACY:
				    $cage = new Privacy();
				    break;
				case self::PRISON:
				    $cage = new Prison();
				    break;
				case self::DIAMOND_KING:
				    $cage = new DiamondKing();
				    break;
				case self::DOMINO:
				    $cage = new Domino();
				    break;
				case self::RAINBOW:
				    $cage = new Rainbow();
				    break;
	        }
		}
		return $cage;
	}
	
	/**
	 * @param Player $player
	 */
    public function getTrails(Player $player, Entity $entity){
		$cosmetics = $this->getCosmeticUsing($player);
		$haveTrail = false;
		foreach($cosmetics as $i){
			if($i >= self::ENDER and $i <= self::GREEN_STAR){
				$haveTrail = $i;
			}
		}
		if($haveTrail != false){
		    switch($haveTrail){
				case self::ENDER:
				    Ender::getParticle($entity);
				    break;
				case self::BLACK_SMOKE:
				    BlackSmoke::getParticle($entity);
				    break;
				case self::WHITE_SMOKE:
				    WhiteSmoke::getParticle($entity);
				    break;
				case self::BLUE_DUST:
				    BlueDust::getParticle($entity);
				    break;
				case self::RED_DUST:
				    RedDust::getParticle($entity);
				    break;
				case self::PURPLE_DUST:
				    PurpleDust::getParticle($entity);
				    break;
				case self::FIRE:
				    Fire::getParticle($entity);
				    break;
				case self::MAGIC:
				    Magic::getParticle($entity);
				    break;
				case self::ANGRY_VILLAGER:
				    AngryVillager::getParticle($entity);
				    break;
				case self::FIREWORK:
				    Firework::getParticle($entity);
				    break;
				case self::POTION:
				    Potion::getParticle($entity);
				    break;
				case self::HEARTS:
				    Hearts::getParticle($entity);
				    break;
				case self::NOTES:
				    Notes::getParticle($entity);
				    break;
				case self::GREEN_STAR:
				    GreenStar::getParticle($entity);
				    break;
		    }
		}
	}
}
