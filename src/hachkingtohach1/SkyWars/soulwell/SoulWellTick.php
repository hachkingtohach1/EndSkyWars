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

namespace hachkingtohach1\SkyWars\soulwell;

use pocketmine\utils\TextFormat;
use pocketmine\Server;
use pocketmine\player\Player;
use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\world\sound\XpLevelUpSound;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use hachkingtohach1\SkyWars\SkyWars;
use hachkingtohach1\SkyWars\economy\Economy;
use hachkingtohach1\SkyWars\kit\KitManager;
use hachkingtohach1\SkyWars\cosmetics\Cosmetics;
use hachkingtohach1\SkyWars\world\sound\NoteBlockSound;

/**
 * This class controls repeating plugin tasks depending on ticks
 */
class SoulWellTick extends Task{
    /*@var SkyWars*/
	private ?SkyWars $plugin;
	
	/**
	 * @param SkyWars $plugin
	 */
    public function __construct(SkyWars $plugin){
		$this->plugin = $plugin;
    }
	
	/**
	 * @param Player $player
	 * @return bool
	 */
	public function registerQueue(Player $player) :bool{
		if(!isset($this->plugin->rollSoulWell[$player->getXuid()])){
			$this->plugin->rollSoulWell[$player->getXuid()] = [
			    "name" => $player->getName(),
				"note" => 11,
				"last-time" => microtime(true)
			];
			return true;
		}else{
			$player->sendMessage(TextFormat::RED."You are in-queue!");
		}
		return false;
	}
	
	/**
	 * Run all cycled tasks
	 */
    public function onRun() :void{		
		foreach($this->plugin->rollSoulWell as $xuid => $data){
			$name = $data["name"];
			$note = $data["note"];
			$lastTime = $data["last-time"];
			$player = $this->plugin->getServer()->getPlayerByPrefix($name);
			if($player != null and $player instanceof Player){
				if($note == 11 or $note == 12){
                	$player->getWorld()->addSound($player->getPosition(), new NoteBlockSound(18));
                	if($note == 11){
                    	$this->plugin->rollSoulWell[$xuid]["note"] = 0;
					}elseif($note == 12){
                    	$this->plugin->rollSoulWell[$xuid]["note"] = 1;
					}
				}elseif($note == 0){
                	$player->getWorld()->addSound($player->getPosition(), new NoteBlockSound(20));
                	$this->plugin->rollSoulWell[$xuid]["note"] = 12;
				}elseif($note == 1){
                	$player->getWorld()->addSound($player->getPosition(), new NoteBlockSound(16));
                	$this->plugin->rollSoulWell[$xuid]["note"] = 11;
				}
			}
			$timeDiff = microtime(true) - $lastTime;
			if($timeDiff >= 7){
				//lightning
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
				Server::getInstance()->broadcastPackets([$player], [$light]);
				//result
				$reward = SoulWell::getReward();
				switch($reward["type"]){
					case SoulWell::CAGES:
						$permission = Cosmetics::PERMISSION_CAGES.".".strtolower($reward["result"]);
						if(!$player->hasPermission($permission)){
							$api = Server::getInstance()->getPluginManager()->getPlugin("PurePerms");						
							$api->getUserDataMgr()->setPermission($player, $permission, null);
					    	$player->sendMessage(str_replace("#reward", $reward["result"]." (Cage)", SoulWell::MESSAGE_FOUND_REWARD));
					    }else{
							$coins = rand(1000, 5000);
							Economy::addCoins($player, $coins);
					        $player->sendMessage(str_replace(["#reward", "#coins"], [$reward["result"]." (Cage)", $coins." Coins"], SoulWell::MESSAGE_ALREADY_HAVE_REWARD));
						}
					    break;
					case SoulWell::TRAILS:
						$permission = Cosmetics::PERMISSION_TRAILS.".".strtolower($reward["result"]);
						if(!$player->hasPermission($permission)){
							$api = Server::getInstance()->getPluginManager()->getPlugin("PurePerms");						
							$api->getUserDataMgr()->setPermission($player, $permission, null);
					    	$player->sendMessage(str_replace("#reward", $reward["result"]." (Trail)", SoulWell::MESSAGE_FOUND_REWARD));
					    }else{
							$coins = rand(1000, 5000);
							Economy::addCoins($player, $coins);
					        $player->sendMessage(str_replace(["#reward", "#coins"], [$reward["result"]." (Trail)", $coins." Coins"], SoulWell::MESSAGE_ALREADY_HAVE_REWARD));
						}
					    break;
					case SoulWell::KITS_NORMAL:
					    $kit = explode("Normal", $reward["result"])[0];
						$permission = KitManager::PERMISSION_KIT.".".strtolower($reward["result"]);
						if(!$player->hasPermission($permission)){
							$api = Server::getInstance()->getPluginManager()->getPlugin("PurePerms");						
							$api->getUserDataMgr()->setPermission($player, $permission, null);
					    	$player->sendMessage(str_replace("#reward", $kit." (Normal)", SoulWell::MESSAGE_FOUND_REWARD));
					    }else{
							$coins = rand(1000, 5000);
							Economy::addCoins($player, $coins);
					        $player->sendMessage(str_replace(["#reward", "#coins"], [$kit." (Normal)", $coins." Coins"], SoulWell::MESSAGE_ALREADY_HAVE_REWARD));
						}
						break;
					case SoulWell::KITS_INSANE:
					    $kit = explode("Insane", $reward["result"])[0];
						$permission = KitManager::PERMISSION_KIT.".".strtolower($reward["result"]);
						if(!$player->hasPermission($permission)){
							$api = Server::getInstance()->getPluginManager()->getPlugin("PurePerms");						
							$api->getUserDataMgr()->setPermission($player, $permission, null);
					    	$player->sendMessage(str_replace("#reward", $kit." (Insane)", SoulWell::MESSAGE_FOUND_REWARD));
					    }else{
							$coins = rand(1000, 5000);
							Economy::addCoins($player, $coins);
					        $player->sendMessage(str_replace(["#reward", "#coins"], [$kit." (Insane)", $coins." Coins"], SoulWell::MESSAGE_ALREADY_HAVE_REWARD));
						}
						break;
					case SoulWell::KITS_RANKED:
					    $kit = explode("Ranked", $reward["result"])[0];
						$permission = KitManager::PERMISSION_KIT.".".strtolower($reward["result"]);
						if(!$player->hasPermission($permission)){
							$api = Server::getInstance()->getPluginManager()->getPlugin("PurePerms");						
							$api->getUserDataMgr()->setPermission($player, $permission, null);
					    	$player->sendMessage(str_replace("#reward", $kit." (Ranked)", SoulWell::MESSAGE_FOUND_REWARD));
					    }else{
							$coins = rand(1000, 5000);
							Economy::addCoins($player, $coins);
					        $player->sendMessage(str_replace(["#reward", "#coins"], [$kit." (Ranked)", $coins." Coins"], SoulWell::MESSAGE_ALREADY_HAVE_REWARD));
						}
						break;
					case SoulWell::KITS_MEGA:
					    $kit = explode("Mega", $reward["result"])[0];
						$permission = KitManager::PERMISSION_KIT.".".strtolower($reward["result"]);
						if(!$player->hasPermission($permission)){
							$api = Server::getInstance()->getPluginManager()->getPlugin("PurePerms");						
							$api->getUserDataMgr()->setPermission($player, $permission, null);
					    	$player->sendMessage(str_replace("#reward", $kit." (Mega)", SoulWell::MESSAGE_FOUND_REWARD));
					    }else{
							$coins = rand(1000, 5000);
							Economy::addCoins($player, $coins);
					        $player->sendMessage(str_replace(["#reward", "#coins"], [$kit." (Mega)", $coins." Coins"], SoulWell::MESSAGE_ALREADY_HAVE_REWARD));
						}
						break;
					case SoulWell::COINS:
					    Economy::addCoins($player, $reward["result"]);
					    $player->sendMessage(str_replace("#reward", $reward["result"]." Coins", SoulWell::MESSAGE_FOUND_REWARD));
					    break;
				}
				//sound
				$player->getWorld()->addSound($player->getLocation()->asVector3(), new XpLevelUpSound(100), [$player]);
				unset($this->plugin->rollSoulWell[$xuid]);
			}
		}
    }
}