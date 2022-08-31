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

namespace hachkingtohach1\SkyWars;

use pocketmine\utils\TextFormat;
use pocketmine\player\Player;
use pocketmine\event\Listener;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\world\Position;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\block\inventory\ChestInventory;
use pocketmine\block\Chest;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockFactory;
use pocketmine\item\ItemIds;
use pocketmine\entity\projectile\Arrow;
use pocketmine\entity\projectile\EnderPearl;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\StringToEffectParser;
use pocketmine\world\sound\AnvilFallSound;
use pocketmine\world\sound\XpLevelUpSound;
use pocketmine\world\sound\BlockBreakSound;
use pocketmine\world\sound\ExplodeSound;
use pocketmine\world\sound\BlazeShootSound;
use pocketmine\world\particle\BlockBreakParticle;
use pocketmine\world\particle\HugeExplodeParticle;
use hachkingtohach1\SkyWars\player\SWPlayer;
use hachkingtohach1\SkyWars\math\Vector3;
use hachkingtohach1\SkyWars\utils\Lightning;
use hachkingtohach1\SkyWars\entity\SoloMode;
use hachkingtohach1\SkyWars\entity\DoubleMode;
use hachkingtohach1\SkyWars\entity\RankedMode;
use hachkingtohach1\SkyWars\entity\LaboratoryMode;
use hachkingtohach1\SkyWars\entity\SoulWell;
use hachkingtohach1\SkyWars\entity\QuestMaster;
use hachkingtohach1\SkyWars\form\Form;
use hachkingtohach1\SkyWars\economy\Economy;
use hachkingtohach1\SkyWars\ranking\Ranking;
use hachkingtohach1\SkyWars\quest\Quest;

/**
 * base plugin EventListener, holding events like onPlayerLogin etc
 * 
 */
class EventListener implements Listener{	
	/*@var SkyWars*/
	private ?SkyWars $plugin;
	
	const TIME_LAST_DAMAGE = 7;
	const KNOCKBACK_SKYWARS = 0.2;
	const MAX_BORDER = 200;
	
	const CHOOSE_KIT_ITEM = TextFormat::BOLD.TextFormat::GREEN."Kit Selector ".TextFormat::RESET.TextFormat::GRAY."(Right Click)";
	const INSANE_OPTIONS_ITEM = TextFormat::BOLD.TextFormat::GOLD."Insane Options ".TextFormat::RESET.TextFormat::GRAY."(Right Click)";
	const LEAVE_GAME_ITEM = TextFormat::BOLD.TextFormat::RED."Return to Lobby ".TextFormat::RESET.TextFormat::GRAY."(Right Click)";
	const PLAY_AGAIN_ITEM = TextFormat::BOLD.TextFormat::AQUA."Play Again ".TextFormat::RESET.TextFormat::GRAY."(Right Click)";
	const TELEPORTER_ITEM = TextFormat::BOLD.TextFormat::GREEN."Teleporter ".TextFormat::RESET.TextFormat::GRAY."(Right Click)";
	
	/**
	 * base class constructor
	 * 
	 * @param SkyWars $plugin
	 */
	public function __construct(SkyWars $plugin){
		$this->plugin = $plugin;
	}

    /**
     * @param Player $player
     * @return bool
     */
	private function isPlayer(Player $player) :bool{
		if(isset($this->plugin->players[$player->getXuid()])){
			return true;
		}
		return false;
	}

    /**
     * @param Player $player
     * @return SWPlayer|null
     */
	private function getDataPlayer(Player $player) :?SWPlayer{
		return $this->plugin->players[$player->getXuid()];
	}

	/**
	 * Calls when player comes into game
	 * 
	 * @param PlayerLoginEvent $event
	 * @return void
	 */
	public function onPlayerLogin(PlayerLoginEvent $event) :void{
		$player = $event->getPlayer();	
		//check and create database for player when login
		$this->plugin->getDatabase()->createProfile($player);
		$this->plugin->players[$player->getXuid()] = new SWPlayer($player);
	}

    /**
     * Calls when player comes into game
     * check to can send message when player death in arena
     *
     * @param string $attacker
     * @param int $lastAttack
     * @return bool
     */
	private function canSendDeathMessage(string $attacker, int $lastAttack) :bool{
		if($lastAttack != 0 or $attacker != ""){
			$timeDiff = microtime(true) - $lastAttack;
			if($timeDiff <= self::TIME_LAST_DAMAGE){
				return true;
			}
		}	
        return false;		
	}

    /**
     * @param Player $entity
     * @param Player $attacker
     * @return void
     */
    private function updateInfoPlayer(Player $entity, Player $attacker) :void{
		$dataEntity = $this->getDataPlayer($entity);
		$dataAttacker = $this->getDataPlayer($attacker);
		$dataArenaEntity = $this->plugin->arenas[$dataEntity->getNameArena()];
		$dataArenaAttacker = $this->plugin->arenas[$dataAttacker->getNameArena()];
		//send sound
		$entity->getWorld()->addSound($entity->getLocation()->asVector3(), new AnvilFallSound(), [$entity]);
		$attacker->getWorld()->addSound($attacker->getLocation()->asVector3(), new XpLevelUpSound(10), [$attacker]);
		if($dataArenaEntity->isTeamMode() and $dataArenaAttacker->isTeamMode()){
			if($dataEntity->getTeam() != $dataAttacker->getTeam()){
				if($dataEntity->getHasTargetTime() == 0){
					$dataEntity->setHasTargetTime(microtime(true));
					$dataEntity->setHasTarget($attacker->getName());
                }else{
					//update assists counter
					$timeDiff = microtime(true) - $dataEntity->getHasTargetTime();			
					if($timeDiff <= self::TIME_LAST_DAMAGE){
						$subject = $this->plugin->getServer()->getPlayerByPrefix($dataEntity->getHasTarget());
						if($subject instanceof Player){
							$dataSubject = $this->getDataPlayer($subject);
				    		if(
					    		$dataAttacker->getTeam() == $dataSubject->getTeam() and
								$attacker->getName() != $subject->getName()
							){
					    		$dataArenaAttacker->updateKillsCounter($subject);
								$dataArenaAttacker->updateAssistsCounter($attacker);
								//update coins, souls, xp for attacker
				                $randomCoins = rand(100, 150);
				                Economy::addCoins($subject, $randomCoins);
				                Economy::addSouls($subject, 1);
								Ranking::addXp($subject, 2);
								//update quests
								Quest::checkQuestPlayer($subject, $dataArenaAttacker->getMaxInTeamCount(), "kill");								
								$subject->sendTip(TextFormat::GOLD."+".$randomCoins." coins, ".TextFormat::LIGHT_PURPLE."+1 XP, ".TextFormat::AQUA." +1 souls");
								return;
							}					
						}
					}else{
						$dataEntity->setHasTargetTime(0);
					}						
                }
                $dataArenaAttacker->updateKillsCounter($attacker);				
				//update coins, souls, xp for attacker
				$randomCoins = rand(100, 150);
				Economy::addCoins($attacker, $randomCoins);
				Economy::addSouls($attacker, 1);
				Ranking::addXp($attacker, 2);
				//update quests
				Quest::checkQuestPlayer($attacker, $dataArenaAttacker->getMaxInTeamCount(), "kill");
				$attacker->sendTip(TextFormat::GOLD."+".$randomCoins." coins, ".TextFormat::LIGHT_PURPLE."+1 XP, ".TextFormat::AQUA." +1 souls");
            }
		}else{
			if(!$attacker->isSpectator()){				
			    $dataArenaAttacker->updateKillsCounter($attacker);
				//update coins, souls, xp for attacker
				$randomCoins = rand(100, 150);
				Economy::addCoins($attacker, $randomCoins);
				Economy::addSouls($attacker, 1);
				Ranking::addXp($attacker, 2);
				//update quests
				Quest::checkQuestPlayer($attacker, $dataArenaAttacker->getMaxInTeamCount(), "kill");
				$attacker->sendTip(TextFormat::GOLD."+".$randomCoins." coins, ".TextFormat::LIGHT_PURPLE."+1 XP, ".TextFormat::AQUA." +1 souls");
			}
		}
    }
	
	/**
	 * Calls when player join game
	 * 
	 * @param PlayerJoinEvent $event
	 * @return void
	 */
	public function onPlayerJoin(PlayerJoinEvent $event) :void{
		$player = $event->getPlayer();		
		//check and set data quest for player
		$dailyQuests = [];
		$weeklyQuests = [];
		foreach(Quest::getDailyQuests() as $case => $data){
			$dailyQuests[$case] = $case.",0";
		}
		foreach(Quest::getWeeklyQuests() as $case => $data){
			$weeklyQuests[$case] = $case.",0";
		}
		$dataA = explode("|", $this->plugin->getDataBase()->getDailyQuest($player));
		$dataB = explode("|", $this->plugin->getDataBase()->getWeeklyQuest($player));
		if(count($dataA) <= 1){
			$this->plugin->getDataBase()->setDailyQuest($player, implode("|", $dailyQuests));
		}
		if(count($dataB) <= 1){
			$this->plugin->getDataBase()->setWeeklyQuest($player, implode("|", $weeklyQuests));
		}
		//send message when player join
	    $event->setJoinMessage(TextFormat::GREEN."[+] ".TextFormat::GRAY.$player->getName());
	}
	
	/**
	 * Calls when player quit game
	 * Unset data for player when in arena
	 * 
	 * @param PlayerQuitEvent $event
	 * @return void
	 */
	public function onPlayerQuit(PlayerQuitEvent $event) :void{
		$player = $event->getPlayer();
		if($this->isPlayer($player)){			
			$dataPlayer = $this->getDataPlayer($player);
			if($dataPlayer->isInGame()){
			    $dataArena = $this->plugin->arenas[$dataPlayer->getNameArena()];
			    if($dataArena instanceof Arenas){	
				    $dataArena->removePlayer($player, false, true);
				}
			}
		}
		//remove death message from pocketmine
		$event->setQuitMessage("");
	}
	
	/**
	 * Calls when player cause damage
	 * 
	 * @param EntityDamageEvent $event
	 * @return void
	 */
	public function onEntityDamage(EntityDamageEvent $event) :void{		
		$cause = $event->getCause();	
		$entity = $event->getEntity();		
		if(!$event->isCancelled() and $entity instanceof Player){			
			if($this->isPlayer($entity)){
				if($event instanceof EntityDamageByChildEntityEvent){
                    $child = $event->getChild();
                    $damager = $child->getOwningEntity();
                    if($damager instanceof Player && $child instanceof Arrow){
				        $damager->sendMessage(TextFormat::GRAY.$entity->getName().TextFormat::YELLOW." is on ".TextFormat::RED. round($entity->getHealth(), 1).TextFormat::YELLOW." HP!");
						$damager->getWorld()->addSound($damager->getPosition()->asVector3(), new XpLevelUpSound(10), [$damager]);
					}
				}
				$dataPlayer = $this->getDataPlayer($entity);
				if($dataPlayer->isInGame()){
					//maybe this is custom kb for skywars
					//$event->setAttackCooldown(self::KNOCKBACK_SKYWARS);
					$dataArena = $this->plugin->arenas[$dataPlayer->getNameArena()];                   						
					if($dataArena->isStarted()){
						if($dataArena->isInvincible()){	
							$event->cancel();			
							return;
						}
                        $attackerClass = null;
						$attacker = $dataPlayer->getAttacker();
						$lastAttack = $dataPlayer->getLastAttack();
						if($attacker != "" and $attacker != $entity->getName()){
							$attackerClass = $this->plugin->getServer()->getPlayerByPrefix($attacker);
                        }
						if($cause == $event::CAUSE_VOID){
							$event->cancel();
							if($this->canSendDeathMessage($attacker, $lastAttack)){
								$dataArena->broadcastMessageLocalized("CAUSE_ENTITY_ATTACK", ["#player1", "#player2"], [$entity->getName(), $attacker]);
							}else{
								$dataArena->broadcastMessageLocalized("CAUSE_VOID", ["#player"], [$entity->getName()]);
							}
							if($attackerClass instanceof Player and $this->canSendDeathMessage($attacker, $lastAttack) and $attacker != "" and $attacker != $entity->getName()){
								$this->updateInfoPlayer($entity, $attackerClass);
							}
							Lightning::spawnLightning($entity);
							$dataArena->setSpectator($entity);
						}
						if($event->getFinalDamage() >= $entity->getHealth()){
						    $event->cancel();						
							switch($cause){
								case $event::CAUSE_CONTACT:
								    if($this->canSendDeathMessage($attacker, $lastAttack)){
									    $dataArena->broadcastMessageLocalized("CAUSE_CONTACT_PLAYER", ["#player1", "#player2"], [$entity->getName(), $attacker]);
									}else{
										$dataArena->broadcastMessageLocalized("CAUSE_CONTACT", ["#player"], [$entity->getName()]);
									}
							    	break;
						    	case $event::CAUSE_ENTITY_ATTACK:
								    if($this->canSendDeathMessage($attacker, $lastAttack)){
									    $dataArena->broadcastMessageLocalized("CAUSE_ENTITY_ATTACK", ["#player1", "#player2"], [$entity->getName(), $attacker]);
									}
							    	break;
								case $event::CAUSE_PROJECTILE:
								    if($this->canSendDeathMessage($attacker, $lastAttack)){
									    $dataArena->broadcastMessageLocalized("CAUSE_PROJECTILE_PLAYER", ["#player1", "#player2"], [$entity->getName(), $attacker]);
									}else{
										$dataArena->broadcastMessageLocalized("CAUSE_PROJECTILE", ["#player"], [$entity->getName()]);
									}
							    	break;
								case $event::CAUSE_SUFFOCATION:
								    if($this->canSendDeathMessage($attacker, $lastAttack)){
									    $dataArena->broadcastMessageLocalized("CAUSE_SUFFOCATION_PLAYER", ["#player1", "#player2"], [$entity->getName(), $attacker]);
									}else{
										$dataArena->broadcastMessageLocalized("CAUSE_SUFFOCATION", ["#player"], [$entity->getName()]);
									}
							   		break;
								case $event::CAUSE_FALL:
								    if($this->canSendDeathMessage($attacker, $lastAttack)){
									    $dataArena->broadcastMessageLocalized("CAUSE_FALL_PLAYER", ["#player1", "#player2"], [$entity->getName(), $attacker]);
									}else{
										$dataArena->broadcastMessageLocalized("CAUSE_FALL", ["#player"], [$entity->getName()]);
									}
							    	break;
                                case $event::CAUSE_FIRE_TICK:
                                case $event::CAUSE_FIRE:
								    if($this->canSendDeathMessage($attacker, $lastAttack)){
									    $dataArena->broadcastMessageLocalized("CAUSE_FIRE_PLAYER", ["#player1", "#player2"], [$entity->getName(), $attacker]);
									}else{
										$dataArena->broadcastMessageLocalized("CAUSE_FIRE", ["#player"], [$entity->getName()]);
									}
							    	break;
                                case $event::CAUSE_LAVA:
									$dataArena->broadcastMessageLocalized("CAUSE_LAVA", ["#player"], [$entity->getName()]);
							    	break;
								case $event::CAUSE_BLOCK_EXPLOSION:
								    $dataArena->broadcastMessageLocalized("CAUSE_BLOCK_EXPLOSION", ["#player"], [$entity->getName()]);
							    	break;
								case $event::CAUSE_ENTITY_EXPLOSION:
								    $dataArena->broadcastMessageLocalized("CAUSE_ENTITY_EXPLOSION", ["#player"], [$entity->getName()]);
							    	break;
								case $event::CAUSE_SUICIDE:
								    $dataArena->broadcastMessageLocalized("CAUSE_SUICIDE", ["#player"], [$entity->getName()]);
							    	break;
								case $event::CAUSE_MAGIC:
								    $dataArena->broadcastMessageLocalized("CAUSE_MAGIC", ["#player"], [$entity->getName()]);
							    	break;
							}
							if($attackerClass instanceof Player and $this->canSendDeathMessage($attacker, $lastAttack) and $attacker != "" and $attacker != $entity->getName()){
								$this->updateInfoPlayer($entity, $attackerClass);
							}
							Lightning::spawnLightning($entity);
							$dataArena->setSpectator($entity);
							$dataArena->sendPopupLocalized("COUNT_PLAYERS_BROADCAST", ["#count"], [$dataArena->getPlayerCount()]);		
						}
					}else{
						$event->cancel();
						if($cause == EntityDamageEvent::CAUSE_VOID){
							$world = $this->plugin->getServer()->getWorldManager()->getWorldByName($dataArena->lobbywaiting["world"]);
		                    $position = Vector3::fromString($dataArena->lobbywaiting["spawn"]);
		                    $entity->teleport(Position::fromObject($position, $world));
						}
					}
				}
			}
		}
	}
	
	/**
	 * Calls when player attack other player in game
	 * Check player in team and check when invincible
	 * 
	 * @param EntityDamageByEntityEvent $event
	 * @return void
	 */
	public function onEntityDamageByEntity(EntityDamageByEntityEvent $event) :void{
		$entity = $event->getEntity();
		$damager = $event->getDamager();
		if($entity instanceof SoloMode and $damager instanceof Player){
			$event->cancel();
			Form::getMenuSoloMode($damager);
		}
		if($entity instanceof DoubleMode and $damager instanceof Player){
			$event->cancel();
			Form::getMenuDoubleMode($damager);
		}
		if($entity instanceof RankedMode and $damager instanceof Player){
			$event->cancel();
			Form::getMenuRankedMode($damager);			
		}
		if($entity instanceof MegaMode and $damager instanceof Player){
			$event->cancel();
			Form::getMenuMegaMode($damager);
		}
		if($entity instanceof LaboratoryMode and $damager instanceof Player){
			$event->cancel();
			Form::getMenuLaboratoryMode($damager);
		}
		if($entity instanceof SoulWell and $damager instanceof Player){
			$event->cancel();
			Form::getSoulWellForm($damager);
		}
		if($entity instanceof QuestMaster and $damager instanceof Player){
			$event->cancel();
			Form::getQuestsForm($damager);
		}
		if(!$event->isCancelled() and $entity instanceof Player and $damager instanceof Player){
			if($this->isPlayer($entity) and $this->isPlayer($damager) and !$entity->isSpectator() and !$damager->isSpectator()){
				$dataEntity = $this->getDataPlayer($entity);
				$dataDamager = $this->getDataPlayer($damager);
				if($dataEntity->isInGame() and $dataDamager->isInGame()){
                    $dataArenaEntity = $this->plugin->arenas[$dataEntity->getNameArena()];
                    $dataArenaDamager = $this->plugin->arenas[$dataDamager->getNameArena()];					
				    if($dataEntity->getNameArena() == $dataDamager->getNameArena()){   
						//set time for last attack and rewrite name attacker in data player
						$lastAttack = microtime(true);
						$dataEntity->setAttacker($damager->getName());
						$dataEntity->setLastAttack($lastAttack);
						//check event
						if($dataArenaEntity->isTeamMode()){
						    if($dataArenaEntity->isStarted() and $dataArenaDamager->isStarted() and $dataEntity->getTeam() == $dataDamager->getTeam()){
					            $event->cancel();
							}
						}
						if(!$dataArenaEntity->isStarted() and !$dataArenaDamager->isStarted()){
							$event->cancel();
						}
					}
				}
			}
		}
	}
	
	/**
	 * Calls when player interact any block
	 * 
	 * @param PlayerInteractEvent $event
	 * @return void
	 */
	public function onPlayerInteract(PlayerInteractEvent $event) :void{
		$player = $event->getPlayer();
		$block = $event->getBlock();
		if(!$event->isCancelled() and $this->isPlayer($player)){
			$dataPlayer = $this->getDataPlayer($player);			
			if($dataPlayer->isInGame()){
				$dataArena = $this->plugin->arenas[$dataPlayer->getNameArena()];
				if(!$dataArena->isStarted() or !$dataArena->isOpenCage()){
					$event->cancel();
				}
			}else{
				if(!$this->plugin->getConfig()->get("builder-mode")){
					if($player->getWorld()->getFolderName() == $this->plugin->getServer()->getWorldManager()->getDefaultWorld()->getFolderName()){
						$event->cancel();
					}
				}
				if(isset($this->plugin->setup[$player->getXuid()])){
					if($player->getInventory()->getItemInHand()->getId() == ItemIds::DIAMOND_HOE){
						$event->cancel();
						$count = count($this->plugin->setup[$player->getXuid()]["spawns"]);
						$compare = $this->plugin->setup[$player->getXuid()]["count-teams"] * $this->plugin->setup[$player->getXuid()]["max-player-inteam-count"];
						if($count < $compare){
							$this->plugin->setup[$player->getXuid()]["spawns"][($count + 1)] = (new Vector3($block->getPosition()->x, $block->getPosition()->y + 7, $block->getPosition()->z))->toString();
			            	$player->getWorld()->setblockAt($block->getPosition()->x, $block->getPosition()->y + 7, $block->getPosition()->z, BlockFactory::getInstance()->get(BlockLegacyIds::BEACON, 0));
							$player->sendMessage(SkyWars::PREFIX.TextFormat::GREEN."Spawn ".($count + 1)." saved!");
						}
					}
					if($player->getInventory()->getItemInHand()->getId() == ItemIds::DIAMOND_AXE){
				        $event->cancel();
						$this->plugin->setup[$player->getXuid()]["chests-mid"][(new Vector3($block->getPosition()->x, $block->getPosition()->y, $block->getPosition()->z))->toString()] = true;
			            $player->sendMessage(SkyWars::PREFIX.TextFormat::GREEN."Data has been saved!");
					}					
				}
			}
		}		
	}
	
	/**
	 * Calls when player place block
	 * 
	 * @param BlockPlaceEvent $event
	 * @return void
	 */
	public function onBlockPlace(BlockPlaceEvent $event) :void{
		$block = $event->getBlock();
		$player = $event->getPlayer();
		if(!$event->isCancelled() and $this->isPlayer($player)){
			$dataPlayer = $this->getDataPlayer($player);			
			if($dataPlayer->isInGame()){
				$dataArena = $this->plugin->arenas[$dataPlayer->getNameArena()];
				if(!$dataArena->isStarted() or !$dataArena->isOpenCage()){
					$event->cancel();
				}
				if($dataArena->isStarted() or $dataArena->isOpenCage()){
					if($block instanceof Chest){
					    $event->cancel();
					}
					$spawnDragon = Vector3::fromString($dataArena->getSpawnDragon());
					if($block->getPosition()->y >= $spawnDragon->y){
						$player->sendMessage($dataArena->getMessageLocalized("CAN_NOT_PLACE", [], []));
						$event->cancel();
					}
					$xs = (int)$spawnDragon->x + self::MAX_BORDER;
                    $zs = (int)$spawnDragon->z + self::MAX_BORDER;		
                    /*player's current XZ*/
                    $xp = $block->getPosition()->getFloorX();
                    $zp = $block->getPosition()->getFloorZ();			
                    /*the magic*/
                    $x1 = abs($xp);
                    $z1 = abs($zp);
                    $x2 = abs($xs);
                    $z2 = abs($zs);	
                    /*checking if player XZ is greater than spawn XZ+(custom size) XZ*/
                    if($x1 >= $x2){
                       $player->sendMessage($dataArena->getMessageLocalized("CAN_NOT_PLACE", [], []));
						$event->cancel();
		            }
		            if($z1 >= $z2){
                       $player->sendMessage($dataArena->getMessageLocalized("CAN_NOT_PLACE", [], []));
						$event->cancel();
		            }
				}
			}else{
				if(!$this->plugin->getConfig()->get("builder-mode")){
					if($player->getWorld()->getFolderName() == $this->plugin->getServer()->getWorldManager()->getDefaultWorld()->getFolderName()){
						$event->cancel();
					}
				}
			}
		}
		$itemInHand = $player->getInventory()->getItemInHand();
		if($itemInHand->getCustomName() == TextFormat::GOLD.TextFormat::RED."Insta Boom TNT"){
			$player->getWorld()->addParticle($player->getLocation()->asVector3(), new HugeExplodeParticle(), $player->getWorld()->getPlayers());
			$player->getWorld()->addSound($player->getPosition(), new ExplodeSound(), $player->getViewers());
			$direction = $player->getDirectionPlane()->normalize()->multiply(1);
			$player->setMotion(new Vector3($direction->getX(), 1, $direction->getY()));
			$player->getInventory()->setItemInHand($itemInHand->setCount($itemInHand->getCount() - 1));
		}
	}
	
	/**
	 * Calls when player break block
	 * 
	 * @param BlockBreakEvent $event
	 * @return void
	 */
	public function onBlockBreak(BlockBreakEvent $event) :void{
		$player = $event->getPlayer();
		$block = $event->getBlock();
		if(!$event->isCancelled() and $this->isPlayer($player)){
			$dataPlayer = $this->getDataPlayer($player);			
			if($dataPlayer->isInGame()){
				$dataArena = $this->plugin->arenas[$dataPlayer->getNameArena()];
				if(!$dataArena->isStarted() or !$dataArena->isOpenCage()){										
					$player->getWorld()->addParticle($block->getPosition()->asVector3(), new BlockBreakParticle($block), $player->getWorld()->getPlayers());
					$player->getWorld()->addSound($block->getPosition()->asVector3(), new BlockBreakSound($block), $player->getWorld()->getPlayers());
				    $event->cancel();		
				}
			}else{
				if(!$this->plugin->getConfig()->get("builder-mode")){
					if($player->getWorld()->getFolderName() == $this->plugin->getServer()->getWorldManager()->getDefaultWorld()->getFolderName()){
						$player->getWorld()->addParticle($block->getPosition()->asVector3(), new BlockBreakParticle($block), $player->getWorld()->getPlayers());
					    $player->getWorld()->addSound($block->getPosition()->asVector3(), new BlockBreakSound($block), $player->getWorld()->getPlayers());
						$event->cancel();
					}
				}
			}
		}
	}
	
	/**
	 * Calls when player open chest
	 * 
	 * @param InventoryOpenEvent $event
	 * @return void
	 */
	public function onInventoryOpen(InventoryOpenEvent $event) :void{
		$inventory = $event->getInventory();	
		if(!$event->isCancelled() and $inventory instanceof ChestInventory){
			$player = $event->getPlayer();
			if($this->isPlayer($player)){
				$dataPlayer = $this->getDataPlayer($player);
			    if($dataPlayer->isInGame()){
				    $dataArena = $this->plugin->arenas[$dataPlayer->getNameArena()];
				    if($dataArena->isStarted() and $dataArena->isOpenCage()){
				        //get tile for chest
				        $dataArena->registerChestTile($event);
					}else{						
				        $event->cancel();
					}
				}
			}else{
				if(!$this->plugin->getConfig()->get("builder-mode")){
					if($player->getWorld()->getFolderName() == $this->plugin->getServer()->getWorldManager()->getDefaultWorld()->getFolderName()){
						$event->cancel();
					}
				}
			}
		}
	}
	
	/**
	 * Calls when player use item in arena
	 * 
	 * @param PlayerItemHeldEvent $event
	 * @return void
	 */
	public function onPlayerItemHeld(PlayerItemHeldEvent $event) :void{
		$player = $event->getPlayer();
		$item = $event->getItem();
		if($this->isPlayer($player)){
			$dataPlayer = $this->getDataPlayer($player);
			if($dataPlayer->getNameArena() != ""){
				$dataArena = $this->plugin->arenas[$dataPlayer->getNameArena()];
				if($dataPlayer->isInGame()){
		        	switch($item->getCustomName()){
						case self::CHOOSE_KIT_ITEM:
						    switch($dataArena->getMode()){
								case "normal":
								    Form::getKitsNormalForm($player);
								    break;
								case "insane":
								    Form::getKitsInsaneForm($player);
								    break;
								case "ranked":
									Form::getKitsRankedForm($player);
									break;
								case "mega":
									Form::getKitsMegaForm($player);
									break;
							}
					    	break;
						case self::INSANE_OPTIONS_ITEM:
					    	break;
					}
				}
				switch($item->getCustomName()){
					case self::PLAY_AGAIN_ITEM:
						Form::getMenuMode($player);
						break;
					case self::TELEPORTER_ITEM:
						Form::getTeleporter($player);
						break;
					case self::LEAVE_GAME_ITEM:
					    if($dataArena->isSpectator($player)){
							$dataArena->removePlayer($player);
						}else{
							$dataArena->removePlayer($player, false, true);
						}
						$dataArena->setDataPlayer($player);
			            $player->teleport($this->plugin->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
					    break;
				}
			}
		}
	}

	/**
	 * Calls when player use item
	 * 
	 * @param PlayerItemUseEvent $event
	 * @return void
	 */
	public function onPlayerItemUse(PlayerItemUseEvent $event) :void{
		$player = $event->getPlayer();
		$item = $event->getItem();
		if($item->getnamedTag()->getTag("Corruptedpearl", IntTag::class) != null){
			$instance = new EffectInstance(StringToEffectParser::getInstance()->parse("slowness"), 200, 0);
		    $player->getEffects()->add($instance);
		}
	}
	
	/**
	 * Calls when player drop item
	 * 
	 * @param PlayerDropItemEvent $event
	 * @return void
	 */
	public function onPlayerDropItem(PlayerDropItemEvent $event) :void{
		$player = $event->getPlayer();
		if($this->isPlayer($player)){
			$dataPlayer = $this->getDataPlayer($player);
			if($dataPlayer->isInGame()){
				$dataArena = $this->plugin->arenas[$dataPlayer->getNameArena()];
				if($dataArena->isSpectator($player)){
					$event->cancel();
				}
				if(!$dataArena->isStarted() or !$dataArena->isOpenCage()){
					$event->cancel();
				}
			}else{
				if(!$this->plugin->getConfig()->get("builder-mode")){
					if($player->getWorld()->getFolderName() == $this->plugin->getServer()->getWorldManager()->getDefaultWorld()->getFolderName()){
						$event->cancel();
					}
				}
			}
		}
	}
	
	/**
	 * Undocumented function
	 *
	 * @param PlayerMoveEvent $event
	 * @return void
	 */
	public function onMovement(PlayerMoveEvent $event) :void{
		$player = $event->getPlayer();
		$world = $player->getWorld();
        $x = $player->getPosition()->x;
        $y = $player->getPosition()->y;
        $z = $player->getPosition()->z;
        $block = $player->getWorld()->getBlock(new Vector3($x, $y - 0.5, $z));
		if($this->isPlayer($player)){
			$dataPlayer = $this->getDataPlayer($player);
			if($dataPlayer->getNameArena() != ""){
				$dataArena = $this->plugin->arenas[$dataPlayer->getNameArena()];
				if($dataPlayer->isInGame() and $dataArena->getMode() == SkyWars::MODE_LABORATORY and $dataArena->getSpecialMode() == "TNT Madness"){
			        if($block->getId() === 165){
						$world->addSound($player->getPosition(), new BlazeShootSound(), [$player]);
						$direction = $player->getDirectionPlane()->normalize()->multiply(1);
						$player->setMotion(new Vector3($direction->getX(), 1, $direction->getY()));
					}
				}
			}
		}
	}
	
	/**
	 * Calls when player chat
	 * 
	 * @param PlayerChatEvent $event
	 * @return void
	 */
	public function onPlayerChat(PlayerChatEvent $event) :void{
		$player = $event->getPlayer();
		if($this->plugin->inParty($player) and $this->plugin->inPartyChat($player)){
			$idParty = $this->plugin->getIdPartyByPlayer($player);
			$data = [];
			$owner = $this->plugin->party[$idParty]["owner"];
			$data[$owner->getName()] = $owner;
			foreach($this->plugin->party[$idParty]["members"] as $member){
				$check = $member["player"];
				$data[$check->getName()] = $check;
			}
			foreach($data as $member){
				if($this->plugin->isOwnerParty($player)){
				    $member->sendMessage(TextFormat::DARK_PURPLE."§8[§4S§cW§5Party§8] ".TextFormat::GREEN."§8[§dParty§cOwner§8] ".$player->getName()." §l§8»§r ".$event->getMessage());
				}else{
					$member->sendMessage(TextFormat::DARK_PURPLE."§8[§4S§cW§5Party§8] ".$player->getName()." §l§8»§r ".$event->getMessage());
				}
			}
			$event->cancel();
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param ProjectileHitEvent $event
	 * @return void
	 */
	public function onProjectileHit(ProjectileHitEvent $event) :void{
		$projectile = $event->getEntity();
        $owner = $projectile->getOwningEntity();
		if($owner instanceof Player){
			if($this->isPlayer($owner)){
				$dataPlayer = $this->getDataPlayer($owner);
				if($dataPlayer->getNameArena() != ""){
					$dataArena = $this->plugin->arenas[$dataPlayer->getNameArena()];
					if($dataPlayer->isInGame() and $dataArena->getMode() == SkyWars::MODE_LABORATORY and $dataArena->getSpecialMode() == "Rush"){
						if ($projectile instanceof EnderPearl){
							$vector = $event->getRayTraceResult()->getHitVector();
							(function() use($vector) : void{ //HACK : Closure bind hack to access inaccessible members
								$this->setPosition($vector);
							})->call($owner);
							$location = $owner->getLocation();
							$owner->getNetworkSession()->syncMovement($location, $location->yaw, $location->pitch);
							$projectile->setOwningEntity(null);
						}
					}
				}
			}
		}
	}
}
