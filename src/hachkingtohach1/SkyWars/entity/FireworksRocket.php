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

use hachkingtohach1\SkyWars\entity\animation\FireworkParticleAnimation;
use hachkingtohach1\SkyWars\item\Fireworks;
use pocketmine\entity\Entity;
use pocketmine\entity\EntitySizeInfo;
use pocketmine\entity\Location;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityMetadataCollection;
use pocketmine\network\mcpe\protocol\types\LevelSoundEvent;

class FireworksRocket extends Entity{    
	/*@var int*/
    protected int $lifeTime = 0;
	/*@var Fireworks*/
    protected Fireworks $fireworks;
	
	public const DATA_FIREWORK_ITEM = 16; 
	
	/**
     * @return string
     */
	public static function getNetworkTypeId() :string{
        return EntityIds::FIREWORKS_ROCKET;
    }
	
	/**
     * @return EntitySizeInfo
     */
	protected function getInitialSizeInfo() :EntitySizeInfo{
        return new EntitySizeInfo(0.25, 0.25);
    } 
	
	/**
     * @param Location $location
	 * @param Fireworks $fireworks
	 * @param int|null $lifeTime
     */
    public function __construct(Location $location, Fireworks $fireworks, ?int $lifeTime = null){
        $this->fireworks = $fireworks;
        parent::__construct($location, $fireworks->getNamedTag());
        $this->setMotion(new Vector3(0.001, 0.05, 0.001));
        if($fireworks->getNamedTag()->getCompoundTag("Fireworks") !== null){
            $this->setLifeTime($lifeTime ?? $fireworks->getRandomizedFlightDuration());
        }
        $location->getWorld()->broadcastPacketToViewers($location, LevelSoundEventPacket::create(LevelSoundEvent::LAUNCH, $location, -1, ":", false, false));
    }
	
	/**
     * @param int $life
	 * @return void
     */
    public function setLifeTime(int $life) :void{
        $this->lifeTime = $life;
    }

    /**
     * @param int $tickDiff
	 * @return bool
     */
    public function entityBaseTick(int $tickDiff = 1) :bool{
        if($this->closed){
            return false;
        }
        $hasUpdate = parent::entityBaseTick($tickDiff);
        if($this->doLifeTimeTick()){
            $hasUpdate = true;
        }
        return $hasUpdate;
    }
	
	/**
	 * @return bool
     */
    protected function doLifeTimeTick() :bool{
        if(--$this->lifeTime < 0 && !$this->isFlaggedForDespawn()){
            $this->doExplosionAnimation();
            $this->playSounds();
            $this->flagForDespawn();
            return true;
        }
        return false;
    }
	
	/**
	 * @return void
     */
    protected function doExplosionAnimation() :void{
        $this->broadcastAnimation(new FireworkParticleAnimation($this), $this->getViewers());
    }
	
	/**
	 * @return void
     */
    public function playSounds() :void{
        $fireworksTag = $this->fireworks->getNamedTag()->getCompoundTag("Fireworks");
        if(!isset($fireworksTag)){
            return;
        }
        $explosionsTag = $fireworksTag->getListTag("Explosions");
        if($explosionsTag === null){
            return;
        }
        foreach($explosionsTag->getValue() as $info){
            if($info instanceof CompoundTag){
                if($info->getByte("FireworkType", 0) === Fireworks::TYPE_HUGE_SPHERE) {
                    $this->getWorld()->broadcastPacketToViewers($this->location, LevelSoundEventPacket::create(LevelSoundEvent::LARGE_BLAST, $this->location, -1, ":", false, false));
                }else{
                    $this->getWorld()->broadcastPacketToViewers($this->location, LevelSoundEventPacket::create(LevelSoundEvent::BLAST, $this->location, -1, ":", false, false));
                }
                if($info->getByte("FireworkFlicker", 0) === 1){
                    $this->getWorld()->broadcastPacketToViewers($this->location, LevelSoundEventPacket::create(LevelSoundEvent::TWINKLE, $this->location, -1, ":", false, false));
                }
            }
        }
    }
	
	/**
	 * @param EntityMetadataCollection $properties
	 * @return void
     */
    public function syncNetworkData(EntityMetadataCollection $properties) :void{
        parent::syncNetworkData($properties);
        $properties->setCompoundTag(self::DATA_FIREWORK_ITEM, new CacheableNbt($this->fireworks->getNamedTag()));
    }
	
	/**
	 * @return void
     */
    protected function tryChangeMovement() :void{
        $this->motion->x *= 1.15;
        $this->motion->y += 0.04;
        $this->motion->z *= 1.15;
    }
}