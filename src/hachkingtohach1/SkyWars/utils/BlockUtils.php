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

use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockFactory;
use pocketmine\player\Player;

class BlockUtils{

    /**
     * @param Player $player
     * @param int $blockId
     * @param int $blockMeta
     * @return void
     */
    public static function openCage(Player $player, int $blockId = BlockLegacyIds::AIR, int $blockMeta = 0) :void{
        $world = $player->getWorld();
        $pos = $player->getPosition()->floor();
        $player->teleport($pos->add(0.5, 0, 0.5));
        $x = $pos->x;
        $y = $pos->y;
        $z = $pos->z;
        for($i = -2; $i <= 2; ++$i){
            for($k = -2; $k <= 2; ++$k){
                for($j = -1; $j <= 3; ++$j){
					$world->setblockAt($x + $i, $y + $j, $z + $k, BlockFactory::getInstance()->get($blockId, $blockMeta));
                }
            }
        }
        $world->setblockAt($x, $y + 3, $z, BlockFactory::getInstance()->get($blockId, $blockMeta));
		$world->setblockAt($x, $y - 1, $z, BlockFactory::getInstance()->get($blockId, $blockMeta));
    }

    /**
     * @param Player $player
     * @param int $blockId
     * @param int $blockMeta
     * @return void
     */
    public static function generateCage(Player $player, int $blockId = BlockLegacyIds::GLASS, int $blockMeta = 0) :void{
        $world = $player->getWorld();
        $pos = $player->getPosition()->floor();
        $player->teleport($pos->add(0.5, 0, 0.5));
        $x = $pos->x;
        $y = $pos->y;
        $z = $pos->z;
        for($i = -2; $i <= 2; ++$i){
            for($k = -2; $k <= 2; ++$k){
                for($j = -1; $j <= 3; ++$j){
					if(!in_array($j, [-1, 3])){
						$world->setblockAt($x, $y + $j, $z, BlockFactory::getInstance()->get(BlockLegacyIds::AIR, $blockMeta));
					    $world->setblockAt($x + 1, $y + $j, $z + 1, BlockFactory::getInstance()->get(BlockLegacyIds::AIR, $blockMeta));
					    $world->setblockAt($x - 1, $y + $j, $z - 1, BlockFactory::getInstance()->get(BlockLegacyIds::AIR, $blockMeta));
					    $world->setblockAt($x - 1, $y + $j, $z + 1, BlockFactory::getInstance()->get(BlockLegacyIds::AIR, $blockMeta));
					    $world->setblockAt($x + 1, $y + $j, $z - 1, BlockFactory::getInstance()->get(BlockLegacyIds::AIR, $blockMeta));
						$world->setblockAt($x, $y + $j, $z + 1, BlockFactory::getInstance()->get(BlockLegacyIds::AIR, $blockMeta));
					    $world->setblockAt($x - 1, $y + $j, $z, BlockFactory::getInstance()->get(BlockLegacyIds::AIR, $blockMeta));
					    $world->setblockAt($x + 1, $y + $j, $z, BlockFactory::getInstance()->get(BlockLegacyIds::AIR, $blockMeta));
					    $world->setblockAt($x, $y + $j, $z - 1, BlockFactory::getInstance()->get(BlockLegacyIds::AIR, $blockMeta));
					}
					$world->setblockAt($x + $i, $y + $j, $z + $k, BlockFactory::getInstance()->get($blockId, $blockMeta));
                }
            }
        }
		$world->setblockAt($x, $y + 3, $z, BlockFactory::getInstance()->get($blockId, $blockMeta));
		$world->setblockAt($x, $y - 1, $z, BlockFactory::getInstance()->get($blockId, $blockMeta));
    }
}