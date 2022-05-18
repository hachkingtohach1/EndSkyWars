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
 
namespace hachkingtohach1\SkyWars\cosmetics\cages;

use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockFactory;
use pocketmine\player\Player;

class Plasma{
	
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
     * @return void
     */
    public static function generateCage(Player $player) :void{
        $world = $player->getWorld();
        $pos = $player->getPosition()->floor();
        $player->teleport($pos->add(0.5, 0, 0.5));
        $x = $pos->x;
        $y = $pos->y;
        $z = $pos->z;
        $glassA = [10, 2];
		$glassB = [10, 2, 0, 6, 1];
		for($i = -1; $i <= 1; ++$i){
            for($k = -1; $k <= 1; ++$k){
                for($j = -1; $j <= 3; ++$j){
					$world->setblockAt($x, $y + $j, $z, BlockFactory::getInstance()->get(BlockLegacyIds::AIR, 0));
					if($j == 3){
						$world->setblockAt($x + $i, $y + $j, $z + $k, BlockFactory::getInstance()->get(BlockLegacyIds::STAINED_GLASS, $glassA[array_rand($glassA, 1)]));
					}
					if($j == 2){
						$world->setblockAt($x + $i, $y + $j, $z + $k, BlockFactory::getInstance()->get(BlockLegacyIds::STAINED_GLASS_PANE, $glassB[array_rand($glassB, 1)]));
					}
					if($j == 1){
						$world->setblockAt($x + $i, $y + $j, $z + $k, BlockFactory::getInstance()->get(BlockLegacyIds::STAINED_GLASS_PANE, $glassB[array_rand($glassB, 1)]));
					}
					if($j == 0){
						$world->setblockAt($x + $i, $y + $j, $z + $k, BlockFactory::getInstance()->get(BlockLegacyIds::STAINED_GLASS_PANE, $glassB[array_rand($glassB, 1)]));
					}
					if($j == -1){
						$world->setblockAt($x + $i, $y + $j, $z + $k, BlockFactory::getInstance()->get(BlockLegacyIds::STAINED_GLASS, $glassA[array_rand($glassA, 1)]));
					}
				}
            }
        }
		$world->setblockAt($x, $y + 3, $z, BlockFactory::getInstance()->get(BlockLegacyIds::STAINED_GLASS, $glassA[array_rand($glassA, 1)]));
		$world->setblockAt($x, $y - 1, $z, BlockFactory::getInstance()->get(BlockLegacyIds::STAINED_GLASS, $glassA[array_rand($glassA, 1)]));
    }
}