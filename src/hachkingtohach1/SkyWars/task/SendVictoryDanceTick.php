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

namespace hachkingtohach1\SkyWars\task;

use pocketmine\scheduler\Task;
use hachkingtohach1\SkyWars\SkyWars;

/**
 * This class controls repeating plugin tasks depending on ticks
 */
class SendVictoryDanceTick extends Task{
    /*@var SkyWars*/
	private ?SkyWars $plugin;
	
	/**
	 * @param SkyWars $plugin
	 */
    public function __construct(SkyWars $plugin){
		$this->plugin = $plugin;
    }
	
	/**
	 * Run all cycled tasks
	 */
    public function onRun() :void{
		foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
			if(isset($this->plugin->victoryDance[$player->getXuid()])){
				$this->plugin->victoryDance[$player->getXuid()]->getVictoryDance($player);
			}
		}
    }
}