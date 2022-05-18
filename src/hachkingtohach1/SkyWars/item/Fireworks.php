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

namespace hachkingtohach1\SkyWars\item;

use hachkingtohach1\SkyWars\entity\FireworksRocket;
use pocketmine\block\Block;
use pocketmine\entity\Location;
use pocketmine\item\Item;
use pocketmine\item\ItemUseResult;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;

class Fireworks extends Item{

    public const TYPE_SMALL_SPHERE = 0;
    public const TYPE_HUGE_SPHERE = 1;
    public const TYPE_STAR = 2;
    public const TYPE_CREEPER_HEAD = 3;
    public const TYPE_BURST = 4;

    public const COLOR_BLACK = "\x00";
    public const COLOR_RED = "\x01";
    public const COLOR_DARK_GREEN = "\x02";
    public const COLOR_BROWN = "\x03";
    public const COLOR_BLUE = "\x04";
    public const COLOR_DARK_PURPLE = "\x05";
    public const COLOR_DARK_AQUA = "\x06";
    public const COLOR_GRAY = "\x07";
    public const COLOR_DARK_GRAY = "\x08";
    public const COLOR_PINK = "\x09";
    public const COLOR_GREEN = "\x0a";
    public const COLOR_YELLOW = "\x0b";
    public const COLOR_LIGHT_AQUA = "\x0c";
    public const COLOR_DARK_PINK = "\x0d";
    public const COLOR_GOLD = "\x0e";
    public const COLOR_WHITE = "\x0f";
	
	/**
     * @return int
     */
    public function getRandomizedFlightDuration() :int{
        return ($this->getFlightDuration() + 1) * 10 + rand(0, 5) + rand(0, 6);
    }
	
	/**
     * @return int
     */
    public function getFlightDuration() :int{
        return $this->getExplosionsTag()->getByte("Flight", 1);
    }
	
	/**
     * @return CompoundTag
     */
    protected function getExplosionsTag() :CompoundTag{
        $tag = $this->getNamedTag()->getCompoundTag("Fireworks");
        if ($tag === null) {
            $this->getNamedTag()->setTag("Fireworks", $tag = CompoundTag::create());
        }
        return $tag;
    }
	
	/**
	 * @param int $duration
     * @return void
     */
    public function setFlightDuration(int $duration) :void{
        $this->getExplosionsTag()->setByte("Flight", $duration);
    }
	
	/**
	 * @param int $type
	 * @param string $color
	 * @param string $fade
	 * @param bool $flicker
	 * @param bool $trail
     * @return void
     */
    public function addExplosion(int $type, string $color, string $fade = "", bool $flicker = false, bool $trail = false) :void{
        $tag = $this->getExplosionsTag();
        $explosions = $tag->getListTag("Explosions");
        if ($explosions === null) {
            $tag->setTag("Explosions", $explosions = new ListTag());
        }
        $explosions->push(CompoundTag::create()
            ->setByte("FireworkType", $type)
            ->setByteArray("FireworkColor", $color)
            ->setByteArray("FireworkFade", $fade)
            ->setByte("FireworkFlicker", $flicker ? 1 : 0)
            ->setByte("FireworkTrail", $trail ? 1 : 0)
        );
    }

    /**
	 * @param Player $player
	 * @param Block $blockReplace
	 * @param Block $blockClicked
	 * @param int $face
	 * @param Vector3 $clickVector
     * @return ItemUseResult
     */
    public function onInteractBlock(Player $player, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector) :ItemUseResult{
        $entity = new FireworksRocket(Location::fromObject($blockReplace->getPosition()->add(0.5, 0, 0.5), $player->getWorld(), lcg_value() * 360, 90), $this);
        $this->pop();
        $entity->spawnToAll();
        return ItemUseResult::SUCCESS();
    }
}
