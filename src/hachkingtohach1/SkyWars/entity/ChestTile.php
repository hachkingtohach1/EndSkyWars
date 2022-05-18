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

namespace hachkingtohach1\SkyWars\entity;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;

class ChestTile extends Entity{	
	/*@var float*/
	protected $baseOffset = 0.125;
	/*@var float*/
	protected $drag = 0.0;
    /*@var bool*/
	public $canCollide = false;

    /**
     * @return string
     */
    public static function getNetworkTypeId() :string{
	    return EntityIds::ARMOR_STAND; 
	}

    /**
     * @return EntitySizeInfo
     */
    protected function getInitialSizeInfo() :EntitySizeInfo{
	    return new EntitySizeInfo(0.1, 0.1); 
	}

    /**
     * @param CompoundTag $nbt
     * @return void
     */
    protected function initEntity(CompoundTag $nbt) :void{
		$this->setScale(0.01);
		$this->setMaxHealth(50);
		parent::initEntity($nbt);
	}

    /**
     * @param int $currentTick
     * @return bool
     */
    public function onUpdate(int $currentTick) :bool{
		$this->setScale(0.01);
		$this->setHealth(50);
		$this->setImmobile(true);
		$this->setNameTagVisible(true);
        $this->setNameTagAlwaysVisible(true);	
        $this->motion->x = 0;
		$this->motion->y = 0;
		$this->motion->z = 0;	
		$this->updateMovement();		
		return parent::onUpdate($currentTick);
	}

    /**
     * @return void
     */
    protected function tryChangeMovement() :void{
		$this->checkObstruction($this->getLocation()->x, $this->getLocation()->y, $this->getLocation()->z);
		parent::tryChangeMovement();
	}

    /**
     * @return CompoundTag
     */
    public function saveNBT() :CompoundTag{
		$nbt = parent::saveNBT();
		return $nbt;
	}

    /**
     * @param Player $player
     * @return void
     */
    protected function sendSpawnPacket(Player $player) :void{
		parent::sendSpawnPacket($player);
    }

    /**
     * @param EntityDamageEvent $source
     * @return void
     */
    public function attack(EntityDamageEvent $source) :void{
		$source->cancel();
	}
}