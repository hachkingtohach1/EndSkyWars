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

use pocketmine\Server;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\entity\Location;
use pocketmine\entity\Living;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockFactory;
use pocketmine\timings\Timings;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\world\particle\AngryVillagerParticle;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\PlaySoundPacket;

class EnderDragon extends Living{
    /*@var bool $isRotating*/
    private bool $isRotating = false;
    /*@var int $rotationTicks*/
    private int $rotationTicks = 0;
    /*@var float $lastRotation*/
    private float $lastRotation = 0.0;
    /*@var int $rotationChange*/
    private int $rotationChange; // from 5 to 10 (- or +)
    /*@var int $pitchChange*/
    private int $pitchChange;
	/*@var float*/
    public $gravity = 0.0;
	/*@var int $timeSendSound*/
	private int $timeSendSound = 0;
	
    /**
     * @return string
     */
    public static function getNetworkTypeId() :string{
	    return EntityIds::ENDER_DRAGON; 
	}

    /**
     * @return EntitySizeInfo
     */
    protected function getInitialSizeInfo() :EntitySizeInfo{
	    return new EntitySizeInfo(5.0, 8.0); 
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
    public function entityBaseTick(int $tickDiff = 1) :bool{
		$return = parent::entityBaseTick($tickDiff);		
		if($this->timeSendSound == 0){
			$this->timeSendSound = microtime(true);
		}else{
			$entities = [];
			foreach($this->getWorld()->getEntities() as $entity){	
                if(($entity instanceof Player) and !($entity instanceof EnderDragon) and $entity->getPosition()->distance($this->getPosition()->asVector3()) <= 20){
					if(!$entity->isSpectator()){
					    $entities[] = $entity;
					}
				}
			}
			if(count($entities) >= 1){
				$player = $entities[array_rand($entities, 1)];
				$from = new Vector3($this->getLocation()->x, $this->getLocation()->y + $this->getEyeHeight(), $this->getLocation()->z);
				$to = new Vector3($player->getLocation()->x, $player->getLocation()->y + $player->getEyeHeight(), $player->getLocation()->z);					    
				$distance = sqrt(pow($from->x - $to->x, 2) + pow($from->y - $to->y, 2) + pow($from->z - $to->z, 2));
				$vector = $to->subtract($from->x, $from->y, $from->z)->normalize()->multiply(0.2);
				for($i = 0; $i <= $distance; $i += 0.2){
					$from = $from->add($vector->x, $vector->y, $vector->z);								
			    	$this->getWorld()->addParticle($from, new AngryVillagerParticle(), $this->getWorld()->getPlayers());
				}
			    $event = new EntityDamageByEntityEvent($this, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, 1);
                $player->attack($event);
			    $player->setMotion($this->getDirectionVector()->multiply(1)->add(0, 0.5, 0));
		    }
			if((microtime(true) - $this->timeSendSound) >= 10){
			    $pk = new PlaySoundPacket;
                $pk->soundName = "mob.enderdragon.growl";
                $pk->x = (float)$this->getLocation()->x;
                $pk->y = (float)$this->getLocation()->y;
                $pk->z = (float)$this->getLocation()->z;
                $pk->volume = 5.0;
                $pk->pitch = 1.0;
				$this->timeSendSound = 0;
				foreach($this->getWorld()->getPlayers() as $player){
                    $player->getNetworkSession()->sendDataPacket($pk);
				}
			}
		}
        $this->boundingBox->offset($this->motion->x, $this->motion->y, $this->motion->z);		
		$this->updateMovement();
		$this->boundingBox->offset($this->motion->x, $this->motion->y, $this->motion->z);
		return $return;
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
     * @param bool $canStart
     */
	public function changeRotation(bool $canStart = false){
        // checks for new rotation
        if(!$this->isRotating){
            if(!$canStart){
                return;
            }
            if(microtime(true)-$this->lastRotation < 10){
                return;
            }
            $this->rotationChange = mt_rand(5, 30);
            if(mt_rand(0, 1) === 0) {
                $this->rotationChange *= -1;
            }
            $this->pitchChange = mt_rand(-4, 4);
            $this->isRotating = true;
        }
        // checks for rotation cancel
        if($this->rotationTicks > mt_rand(5, 8)){
            $this->lastRotation = microtime(true);
            $this->isRotating = false;
            return;
        }
        $this->setRotation(($this->getLocation()->yaw + ($this->rotationChange / 3)) % 360, ($this->getPitch() + ($this->pitchChange / 10)) % 360);
        $this->rotationTicks++;
    }
	
	/**
     * @param bool $teleport
     */
    protected function broadcastMovement(bool $teleport = false) :void{
        $pk = new MoveActorAbsolutePacket();
        $pk->actorRuntimeId = $this->getId();
        $pk->position = $this->getOffsetPosition($this->getPosition()->asVector3());
        $pk->pitch = $this->getLocation()->pitch;
        $pk->headYaw = ($this->getLocation()->yaw + 180) % 360; 
        $pk->yaw = ($this->getLocation()->yaw + 180) % 360;
        if($teleport){
            $pk->flags |= MoveActorAbsolutePacket::FLAG_TELEPORT;
        }
		Server::getInstance()->broadcastPackets($this->getViewers(), [$pk]);
    }
	
	/**
     * @param Player $player
     */
    public function onCollideWithPlayer(Player $player) :void{
        $player->attack(new EntityDamageByEntityEvent($this, $player, EntityDamageEvent::CAUSE_ENTITY_ATTACK, 0.1));
        $player->setMotion($this->getDirectionVector()->multiply(1)->add(0, 0.5, 0));
		parent::onCollideWithPlayer($player);
    }
	
	protected function move(float $dx, float $dy, float $dz) : void{
		$this->blocksAround = null;
		Timings::$entityMove->startTiming();
		$wantedX = $dx;
		$wantedY = $dy;
		$wantedZ = $dz;
		if($this->keepMovement){
			$this->boundingBox->offset($dx, $dy, $dz);
		}else{
			$this->ySize *= 0.4;
			$moveBB = clone $this->boundingBox;
			assert(abs($dx) <= 20 and abs($dy) <= 20 and abs($dz) <= 20, "Movement distance is excessive: dx=$dx, dy=$dy, dz=$dz");
			$list = $this->getWorld()->getCollisionBoxes($this, $moveBB->addCoord($dx, $dy, $dz), false);
			foreach($list as $bb){
				$dy = $bb->calculateYOffset($moveBB, $dy);
				$this->getWorld()->setblockAt((int)$bb->minX, (int)$bb->minY, (int)$bb->minZ, BlockFactory::getInstance()->get(BlockLegacyIds::AIR, 0));
			}
			$moveBB->offset(0, $dy, 0);
			$fallingFlag = ($this->onGround or ($dy != $wantedY and $wantedY < 0));
			foreach($list as $bb){
				$dx = $bb->calculateXOffset($moveBB, $dx);
			}
			$moveBB->offset($dx, 0, 0);
			foreach($list as $bb){
				$dz = $bb->calculateZOffset($moveBB, $dz);
			}
			$moveBB->offset(0, 0, $dz);
			if($this->stepHeight > 0 and $fallingFlag and ($wantedX != $dx or $wantedZ != $dz)){
				$cx = $dx;
				$cy = $dy;
				$cz = $dz;
				$dx = $wantedX;
				$dy = $this->stepHeight;
				$dz = $wantedZ;
				$stepBB = clone $this->boundingBox;
				$list = $this->getWorld()->getCollisionBoxes($this, $stepBB->addCoord($dx, $dy, $dz), false);
				foreach($list as $bb){
					$dy = $bb->calculateYOffset($stepBB, $dy);
					$this->getWorld()->setblockAt((int)$bb->minX, (int)$bb->minY, (int)$bb->minZ, BlockFactory::getInstance()->get(BlockLegacyIds::AIR, 0));
				}
				$stepBB->offset(0, $dy, 0);
				foreach($list as $bb){
					$dx = $bb->calculateXOffset($stepBB, $dx);
				}
				$stepBB->offset($dx, 0, 0);
				foreach($list as $bb){
					$dz = $bb->calculateZOffset($stepBB, $dz);
				}
				$stepBB->offset(0, 0, $dz);
				$reverseDY = -$dy;
				foreach($list as $bb){
					$reverseDY = $bb->calculateYOffset($stepBB, $reverseDY);
				}
				$dy += $reverseDY;
				$stepBB->offset(0, $reverseDY, 0);
				if(($cx ** 2 + $cz ** 2) >= ($dx ** 2 + $dz ** 2)){
					$dx = $cx;
					$dy = $cy;
					$dz = $cz;
				}else{
					$moveBB = $stepBB;
					$this->ySize += $dy;
				}
			}
			$this->boundingBox = $moveBB;
		}
		$this->location = new Location(
			($this->boundingBox->minX + $this->boundingBox->maxX) / 2,
			$this->boundingBox->minY - $this->ySize,
			($this->boundingBox->minZ + $this->boundingBox->maxZ) / 2,
			$this->location->world,
			$this->location->yaw,
			$this->location->pitch
		);
		$this->getWorld()->onEntityMoved($this);
		$this->checkBlockIntersections();
		$this->checkGroundState($wantedX, $wantedY, $wantedZ, $dx, $dy, $dz);
		$postFallVerticalVelocity = $this->updateFallState($dy, $this->onGround);
		$this->motion = $this->motion->withComponents(
			$wantedX != $dx ? 0 : null,
			$postFallVerticalVelocity ?? ($wantedY != $dy ? 0 : null),
			$wantedZ != $dz ? 0 : null
		);
		//TODO: vehicle collision events (first we need to spawn them!)
		Timings::$entityMove->stopTiming();
	}
	
	/**
     * @param int $seconds
     */
    public function setOnFire(int $seconds) :void{}
	
	/**
     * @param EntityDamageEvent $source
     * @return void
     */
    public function attack(EntityDamageEvent $source) :void{
		$source->cancel();
	}
	
	/**
     * @return string 
     */
	public function getName() :string{
        return "Ender Dragon";
    }
}