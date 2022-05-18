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

use pocketmine\Server;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\network\mcpe\protocol\AddActorPacket;

class Lightning{

    /**
     * @param Player $player
     * @return void
     */
    public static function spawnLightning(Player $player) :void{
        $position = $player->getPosition();		
        $light = new AddActorPacket();
		$light->type = "minecraft:lightning_bolt";
		$light->actorRuntimeId = 1;
		$light->actorUniqueId = 1;
		$light->metadata = [];
		$light->motion = null;
		$light->yaw = $player->getLocation()->getYaw();
		$light->pitch = $player->getLocation()->getPitch();
		$light->position = new Vector3($position->getX(), $position->getY(), $position->getZ());
		$block = $player->getWorld()->getBlock($player->getPosition()->floor()->down());
		$particle = new BlockBreakParticle($block);
        $player->getWorld()->addParticle($position->asVector3(), $particle, $player->getWorld()->getPlayers());
		Server::getInstance()->broadcastPackets($player->getWorld()->getPlayers(), [$light]);
    }
}