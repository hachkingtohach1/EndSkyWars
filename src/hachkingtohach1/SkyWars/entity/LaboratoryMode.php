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
use pocketmine\utils\TextFormat;
use pocketmine\player\Player;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use hachkingtohach1\SkyWars\SkyWars;

class LaboratoryMode extends Entity{	

    /**
     * @return string
     */
    public static function getNetworkTypeId() :string{
	    return EntityIds::CREEPER; 
	}

    /**
     * @return EntitySizeInfo
     */
    protected function getInitialSizeInfo() :EntitySizeInfo{
	    return new EntitySizeInfo(2.0, 2.0); 
	}

    /**
     * @param CompoundTag $nbt
     * @return void
     */
    protected function initEntity(CompoundTag $nbt) :void{
		parent::initEntity($nbt);
	}

    /**
     * @param int $currentTick
     * @return bool
     */
    public function onUpdate(int $currentTick) :bool{
		$countSolo = SkyWars::getInstance()->getTotalCountPlayers("laboratory");
		$countDouble = SkyWars::getInstance()->getCountDoubleArena("laboratory");
		$total = $countSolo + $countDouble;
		$this->setNameTag(
		    TextFormat::BOLD.TextFormat::YELLOW."CLICK TO PLAY\n".
			TextFormat::RESET.TextFormat::GRAY."Laboratory Mode [Solo/Double]\n".
			TextFormat::BOLD.TextFormat::GREEN. number_format($total)." Players"
		);
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
}