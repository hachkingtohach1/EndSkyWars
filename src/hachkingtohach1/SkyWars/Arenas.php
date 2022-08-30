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

use pocketmine\item\Item;
use pocketmine\item\ItemIds;
use pocketmine\item\ItemFactory;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\block\tile\Chest;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockFactory;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\utils\TextFormat;
use pocketmine\player\Player;
use pocketmine\player\GameMode;
use pocketmine\entity\Location;
use pocketmine\world\Position;
use pocketmine\world\World;
use pocketmine\world\sound\ClickSound;
use pocketmine\world\sound\ChestOpenSound;
use pocketmine\world\sound\XpLevelUpSound;
use hachkingtohach1\SkyWars\player\SWPlayer;
use hachkingtohach1\SkyWars\math\Vector3;
use hachkingtohach1\SkyWars\data\PluginData;
use hachkingtohach1\SkyWars\utils\ScoreBoardAPI;
use hachkingtohach1\SkyWars\utils\BlockUtils;
use hachkingtohach1\SkyWars\entity\ChestTile;
use hachkingtohach1\SkyWars\entity\EnderDragon;
use hachkingtohach1\SkyWars\world\sound\NoteSound;
use hachkingtohach1\SkyWars\kit\KitManager;
use hachkingtohach1\SkyWars\quest\Quest;
use hachkingtohach1\SkyWars\ranking\Ranking;
use hachkingtohach1\SkyWars\cosmetics\victory\FireWork;

class Arenas{
	/*@var SkyWars*/
	private ?SkyWars $plugin;
	/*@var bool*/
	private bool $started = false;
	/*@var bool*/
	private bool $restarted = false;
	/*@var bool*/
	private bool $invincible = true;
	/*@var bool*/
	private bool $opencage = false;
    /*@var bool*/
	private bool $refilled = false;
	/*@var bool*/
	private bool $doom = false;
    /*@var bool*/
	private bool $endtime = false;		
	/*@var bool*/
	private bool $teamMode = false;	
	/*@var World*/
	private ?World $world;
	/*@var string - name of current arena*/
	private string $nameArena;
	/*@var string - name map of current arena*/
	private string $nameMap;
	/*@var array - save data for spawn and world for lobby waiting*/
	public array $lobbywaiting = [];
	/*@var array*/
	private array $chestMid = [];
	/*@var int*/
	private int $maxInTeamCount;
	/*@var int*/
	private int $countTeams;
	/*@var array*/
	private array $kills = [];
	/*@var array*/
	private array $assists = [];
	/*@var array*/
	private array $players = [];
	/*@var array*/
	private array $spectators = [];
	/*@var array*/
	public array $teams = [];
	/*@var array - spawns for all slots player*/
	private array $spawns = [];
	/*@var array*/
	private array $registerSpawn = [];
	/*@var string*/
	private string $spawnDragon;
	/*@var int*/
	private int $humanReadableTime;	
	/*@var string*/
	private string $mode;	
	/*@var array*/
	private array $openedChests = [];
	/*@var array*/
	private array $registerTagChest = [];
	/*@var array*/
	private array $defaultNameTag = [];
	/*@var array*/
	private array $dragons = [];
	/*@var array*/
	private array $specialMode = [];
	/*@var array*/
	private static $teamColors = [
		"a",
		"b",
		"c",
		"d",
		"e",
		"f",
		"g",
		"h",
		"i",
		"j",
		"k",
		"l",
		"m",
		"n",
		"o",
		"p",
		"q",
		"r",
		"s",
		"t"
	];
	/*@var array*/
	private static $dragonFamily = [
		"Albi",
		"Cousin of Albi",
		"Mother of Albi",
		"Daddy of Albi"
	];
	/*@var array*/ 
	private array $modesLaboratory = [
		["TNT Madness", TextFormat::RED],
		//["Lucky Blocks", TextFormat::LIGHT_PURPLE],
		["Slime", TextFormat::GREEN],
		["Rush", TextFormat::DARK_GREEN]
	];
	
	const MIN_PLAYER_COUNT = 3;
	const MIN_TEAM_COUNT = 2;
	const MATCH_START_IN_SECONDS = 15;
	const MATCH_OPEN_CAGE_IN_SECONDS = 25;
	const MATCH_OFF_INVICIBLE_IN_SECONDS = 27;
	const MATCH_REFILL_CHEST_IN_SECONDS = 225;
	const MATCH_DOOM_IN_SECONDS = 600;
	const MATCH_FINAL_IN_SECONDS = 825;
	const MATCH_RESTART_IN_SECONDS = 10;
	
	const MODE_NORMAL = "normal";
	const MODE_INSANE = "insane";
	const MODE_RANKED = "ranked";
	const MODE_MEGA = "mega";
	const MODE_LABORATORY = "laboratory";
	
	const CHOOSE_KIT_ITEM = TextFormat::BOLD.TextFormat::GREEN."Kit Selector ".TextFormat::RESET.TextFormat::GRAY."(Right Click)";
	const INSANE_OPTIONS_ITEM = TextFormat::BOLD.TextFormat::GOLD."Insane Options ".TextFormat::RESET.TextFormat::GRAY."(Right Click)";
	const LEAVE_GAME_ITEM = TextFormat::BOLD.TextFormat::RED."Return to Lobby ".TextFormat::RESET.TextFormat::GRAY."(Right Click)";
	const PLAY_AGAIN_ITEM = TextFormat::BOLD.TextFormat::AQUA."Play Again ".TextFormat::RESET.TextFormat::GRAY."(Right Click)";
	const TELEPORTER_ITEM = TextFormat::BOLD.TextFormat::GREEN."Teleporter ".TextFormat::RESET.TextFormat::GRAY."(Right Click)";
	
    /**
	 * construct of SkyWars class, 
	 * creates arena for it
	 * 
     * @param SkyWars $plugin
     * @param string $nameArena
     * @param string $nameMap
     * @param string $world
     * @param array $spawns
     * @param string $spawnDragon
     * @param array $lobbywaiting
     * @param array $chestMid
     * @param int $maxInTeamCount
	 * @param int $countTeams
     * @param string $mode
     */
    public function __construct(SkyWars $plugin, string $nameArena, string $nameMap, string $world, array $spawns, string $spawnDragon, array $lobbywaiting, array $chestMid, int $maxInTeamCount, int $countTeams, string $mode){
		$this->plugin = $plugin;
		$this->nameArena = $nameArena;
		$this->nameMap = $nameMap;
		$this->world = $this->plugin->getServer()->getWorldManager()->getWorldByName($nameArena);
        if($maxInTeamCount > 1){
			$this->teamMode = true;
		}		
		$this->spawns = $spawns;
		//spawn dragon will like mid spawn
		$this->spawnDragon = $spawnDragon;		
		$this->lobbywaiting = $lobbywaiting;
		$this->chestMid = $chestMid;
        $this->maxInTeamCount = $maxInTeamCount;
        $this->countTeams = $countTeams;				
        $this->mode = $mode;		
		$this->humanReadableTime = (int)microtime(true);
		$this->setDefaultTeams();
		$this->updateMapData();
	}
	
	/**
	 * @return bool
	 */
	public function isStarted() :bool{
		return $this->started;
	}
	
	/**
	 * @return bool
	 */
	public function isRestarted() :bool{
		return $this->restarted;
	}
	
	/**
	 * @return bool
	 */
	public function isInvincible() {
		return $this->invincible;
	}
	
	/**
	 * @return bool
	 */
	public function isOpenCage() :bool{
		return $this->opencage;
	}
	
	/**
	 * @return bool
	 */
	public function isDoom() :bool{
		return $this->doom;
	}
	
	/**
	 * @return bool
	 */
	public function isRefilled() :bool{
		return $this->refilled;
	}
	
	/**
	 * @return bool
	 */
	public function isEndTime() :bool{
		return $this->endtime;
	}
	
	/**
	 * @return bool
	 */
	public function isTeamMode() :bool{
		return $this->teamMode;
	}

	/**
	 * @return int
	 */
	public function getPlayerCount() :int{
		return count($this->players);
	}
	
	/**
	 * @return array
	 */
	public function getPlayers() :array{
		return $this->players;
	}
    
    /**
	 * @return array
	 */
	public function getSpectators() :array{
		return $this->spectators;
	}
	
	/**
	 * @param Player $player
	 * @return bool
	 */
	public function isSpectator(Player $player) :bool{
		if(isset($this->spectators[$player->getXuid()])){
			return true;
		}
		return false;
	}
	
	/**
	 * @return World
	 */
	public function getWorld() :?World{
		return $this->world;
	}
	
	/**
	 * @return string
	 */
	public function getNameArena() :string{
		return $this->nameArena;
	}
	
	/**
	 * @return string
	 */
	public function getNameMap() :string{
		return $this->nameMap;
	}
	
	/**
	 * @return int
	 */
	public function getMaxInTeamCount() :int{
		return $this->maxInTeamCount;
	}
	
	/**
	 * create default teams array
	 */
	private function setDefaultTeams(){
		$i = 1;
		$this->teams = [];
		foreach(self::$teamColors as $color){
			if($i <= $this->countTeams){
			    $this->teams[$color] = [];
				$i++;
			}
		}
	}
	
	/**
	 * @return array
	 */
	public function getTeams() :array{
		return $this->teams;
	}
	
	/**
	 * @return array
	 */
	public function getRegisterSpawn() :array{
		return $this->registerSpawn;
	}
	
	/**
	 * @return string
	 */
	public function getSpawnDragon() :string{
		return $this->spawnDragon;
	}

	/**
	 * @return int
	 */
	public function getHumanReadableTime() :int{
		return $this->humanReadableTime;
	}
	
	/**
	 * @return string
	 */
	public function getMode() :string{
		return $this->mode;
	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function getSpecialMode() :array{
		return $this->specialMode;
	}
	
	/**
	 * @return int
	 */
	public function getCountTeams() :int{
		$i = 0;
		foreach($this->teams as $teamName => $teamPlayers){
			if(count($teamPlayers) >= 1){
				$i++;
			}
		}
		return $i;
	}
	
	/**
	 * @param string $mode
	 * @return string
	 */
	public function colorMode(string $mode) :string{
		$color = "";
		switch($mode){
			case self::MODE_NORMAL:
			    $color = TextFormat::GREEN."";
			    break;
			case self::MODE_INSANE:
			    $color = TextFormat::RED."";				
			    break;
			case self::MODE_RANKED:
			    $color = TextFormat::AQUA."";
			    break;
			case self::MODE_MEGA:
			    $color = TextFormat::GREEN."";
			    break;
			case self::MODE_LABORATORY:
			    $color = TextFormat::LIGHT_PURPLE."";
			    break;
		}
		return $color;
	}
	
	/**
	 * unload and load map saved
	 */
	public function updateMapData() :?World{	
		$folderName = $this->getWorld()->getFolderName();				
        if(!file_exists($this->plugin->getServer()->getDataPath()."worlds". DIRECTORY_SEPARATOR .$folderName)){
			$this->plugin->getServer()->getLogger()->error("Could not reload map ($folderName). File wasn't found, try save world in setup mode.");
            return null;  
		}
		$zipPath = $this->plugin->getDataFolder()."saves" . DIRECTORY_SEPARATOR . $folderName.".zip";
		//get world manager
		$worldManager = $this->plugin->getServer()->getWorldManager();		
		//check world is generated
        if(!$worldManager->isWorldGenerated($folderName)) return null;
        //unload world  
        $worldManager->getWorldByName($folderName)->setTime(1);
		$worldManager->getWorldByName($folderName)->stopTime();
        if($worldManager->isWorldLoaded($folderName)){
            $worldManager->unloadWorld($worldManager->getWorldByName($folderName));
        }		
		//extract file world
		$zipArchive = new \ZipArchive();
        $zipArchive->open($zipPath);
        $zipArchive->extractTo($this->plugin->getServer()->getDataPath()."worlds");
        $zipArchive->close();

		//load world
		$worldManager->loadWorld($folderName);
        $worldManager->getWorldByName($folderName)->setAutoSave(false);	
		//return world
        return $worldManager->getWorldByName($folderName);
    }
	
	/**
	 * calls when arena restart, removes all players and chunks,
	 * clear arena data
	 */
	private function restart(){
		$this->refilled = true;
		$this->doom = true;
		$this->endtime = true;
		$this->restarted = true;
		$this->invincible = true;
		$this->humanReadableTime = microtime(true);
		$this->dragons = [];
		//remove all entities
		$world = $this->plugin->getServer()->getWorldManager()->getWorldByName($this->getWorld()->getFolderName());
		foreach($world->getEntities() as $entity){
			if(!$entity instanceof Player and ($entity instanceof EnderDragon or $entity instanceof ChestTile)){
				$entity->close();
			}
		}
		//messages top killer for all players
		$this->almostTopKillerNotify();
	}
	
	/**
	 * teleport players to lobby, 
	 * remove them from arena object
	 */
	private function pushPlayersToLobby(){
		foreach($this->players as $player){
			if(isset($this->plugin->victoryDance[$player->getXuid()])){
				unset($this->plugin->victoryDance[$player->getXuid()]);
			}
			$this->setDataPlayer($player);
			$player->teleport($this->plugin->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
			$this->removePlayer($player);
		}
		foreach($this->spectators as $spectator){
			$this->setDataPlayer($spectator);
			$spectator->teleport($this->plugin->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
			$this->removePlayer($spectator);
		}
	}
	
	/**
	 * setup index for player when join game
	 * @param Player $player
	 */
	private function setIndexJoin(Player $player){
		$player->getInventory()->setItem(0, $this->getItem(261, 0, 1)->setCustomName(self::CHOOSE_KIT_ITEM));
	    $player->getInventory()->setItem(8, $this->getItem(355, 14, 1)->setCustomName(self::LEAVE_GAME_ITEM));
	}
	
	/**
	 * setup index for player when in spectator mode
	 * @param Player $player
	 */
	private function setIndexSpectator(Player $player){
		$player->getInventory()->setItem(0, $this->getItem(345, 0, 1)->setCustomName(self::TELEPORTER_ITEM));
		//$player->getInventory()->setItem(4, $this->getItem(355, 14, 1)->setCustomName(self::LEAVE_GAME_ITEM));
	    $player->getInventory()->setItem(7, $this->getItem(339, 0, 1)->setCustomName(self::PLAY_AGAIN_ITEM));
		$player->getInventory()->setItem(8, $this->getItem(355, 14, 1)->setCustomName(self::LEAVE_GAME_ITEM));
	}
	
	/**
	 * reset all arena data on restart
	 */
	private function setDefaultArenaData(){
		$this->started = false;
		$this->restarted = false;
		$this->opencage = false;
		$this->doom = false;
		$this->refilled = false;
		$this->endtime = false;
		$this->invincible = true;
		$this->humanReadableTime = (int)microtime(true);
		$this->players = [];
		$this->spectators = [];
		$this->kills = [];	
		$this->assists = [];	
		$this->openedChests = [];
        $this->registerTagChest	= [];	
		$this->dragons = [];
		$this->defaultNameTag = [];
		$this->specialMode = [];
		$this->setDefaultTeams();
	}
	
	/**
	 * Calls when countdown is finished and fighting start
	 * update player's data, send start messages, update arena data
	 *
	 * @return bool
	 */
	private function start() :bool{
		//check count player enought 3
		if(!$this->plugin->isTesting() and count($this->players) < self::MIN_PLAYER_COUNT){
			$this->humanReadableTime = (int)microtime(true);
			$this->broadcastMessageLocalized("NOT_ENOUGHT_PLAYER", [], []);
			return false;
		}
		//check data count player in team enought 2
		if($this->isTeamMode()){
		    if(!$this->plugin->isTesting() and $this->getCountTeams() < self::MIN_TEAM_COUNT){
			    $this->humanReadableTime = (int)microtime(true);
			    $this->broadcastMessageLocalized("NOT_ENOUGHT_PLAYER", [], []);
			    return false;
			}
	    }
		//check arena is mode laboratory
		if($this->getMode() == self::MODE_LABORATORY){
			$this->specialMode = $this->modesLaboratory[array_rand($this->modesLaboratory, 1)];
			$this->sendTitle($this->specialMode[0], $this->specialMode[1]);
		}
		//remove all entities
		$world = $this->plugin->getServer()->getWorldManager()->getWorldByName($this->getWorld()->getFolderName());
		foreach($world->getEntities() as $entity){
			if(!$entity instanceof Player){
				$entity->close();
			}
		}
		foreach($this->players as $player){
			//teleport
		    if(isset($this->registerSpawn[$player->getXuid()])){
				$position = Vector3::fromString($this->spawns[$this->registerSpawn[$player->getXuid()]]);
			    $player->teleport(Position::fromObject($position, $world));
			}   
            $player->setImmobile(true); 			
		}		
		$this->generateCage();
		$this->sendStartInfo();
		$this->started = true;
		if($this->plugin->isTesting()){
			$this->won();//also check if we have a winner
		}
		return true;
	}
	
	/**
	 * Send message about arena start, invincibility
	 */
	private function sendStartInfo(){
		$this->broadcastMessageLocalized("STARTED", [], []);
	}
	
	/**
	 * check tick per second(s)
	 */
	public function tick(){
		//set time for arena
		$world = $this->plugin->getServer()->getWorldManager()->getWorldByName($this->getWorld()->getFolderName());
        $world->setTime(1); 
		//send scoreboard
		if(!$this->isStarted()){	    
			$this->sendScoreBoardWaiting();
		}else{
		    $this->sendScoreBoard();
		}
		//check count player enought 3
		if(!$this->isStarted() and !$this->plugin->isTesting() and count($this->players) < self::MIN_PLAYER_COUNT){
			$this->humanReadableTime = (int)microtime(true);
			return;
		}
		if(!$this->isStarted() and $this->isTeamMode()){
		    //check count player in team enought 2
		    if(!$this->plugin->isTesting() and $this->getCountTeams() < self::MIN_TEAM_COUNT){
			    $this->humanReadableTime = (int)microtime(true);
			    return;
			}
	    }
		//check tick to restart arena
        $restartTick = (int)microtime(true) - $this->getHumanReadableTime();
		if($this->isRestarted()){
			if($restartTick >= self::MATCH_RESTART_IN_SECONDS){
				$this->pushPlayersToLobby();
		        $this->setDefaultArenaData();
		        $this->updateMapData();	
			}
			return;
		}
		//check tick
        $tick = (int)microtime(true) - $this->getHumanReadableTime();
		//update tag for chest
		$this->updateTagChest();
		//update name tag for player when is team mode
		$this->updateTagPlayer();
		//debug tick (anti server lag)
		if($tick > self::MATCH_START_IN_SECONDS){
			if(!$this->isStarted()){
				$this->start();
			}
		}
		if($tick > (self::MATCH_OPEN_CAGE_IN_SECONDS - 10) and $tick < self::MATCH_OPEN_CAGE_IN_SECONDS){
			foreach($this->players as $player){
				switch($this->getMode()){
			        case self::MODE_NORMAL:
			            $kitPlayer = str_replace(["Normal", "Insane", "Ranked", "Mega", "Laboratory"], ["", ""], SkyWars::getInstance()->getDataBase()->getKitNormal($player));
			            $player->sendPopup($this->getMessageLocalized("KIT_SELTECED_POPUP", ["#kit"], [$kitPlayer]));
						break;
			        case self::MODE_INSANE:
			            $kitPlayer = str_replace(["Normal", "Insane", "Ranked", "Mega", "Laboratory"], ["", ""], SkyWars::getInstance()->getDataBase()->getKitInsane($player));
			            $player->sendPopup($this->getMessageLocalized("KIT_SELTECED_POPUP", ["#kit"], [$kitPlayer]));
						break;
					case self::MODE_RANKED:
			            $kitPlayer = str_replace(["Normal", "Insane", "Ranked", "Mega", "Laboratory"], ["", ""], SkyWars::getInstance()->getDataBase()->getKitRanked($player));
			            $player->sendPopup($this->getMessageLocalized("KIT_SELTECED_POPUP", ["#kit"], [$kitPlayer]));
						break;
					case self::MODE_MEGA:
						$kitPlayer = str_replace(["Normal", "Insane", "Ranked", "Mega", "Laboratory"], ["", ""], SkyWars::getInstance()->getDataBase()->getKitMega($player));
						$player->sendPopup($this->getMessageLocalized("KIT_SELTECED_POPUP", ["#kit"], [$kitPlayer]));
						break;
					case self::MODE_LABORATORY:
						$player->sendPopup($this->getMessageLocalized("KITS_PERKS_LABORATORY", [], []));
						break;
				}
			}
		}
		if($tick > self::MATCH_OPEN_CAGE_IN_SECONDS){
			if(!$this->isOpenCage()){
				$this->openCage();
			}
		}
		if($tick > self::MATCH_REFILL_CHEST_IN_SECONDS){
			if(!$this->isRefilled()){
				$this->startFillChest();
			}
		}
		if($tick > self::MATCH_DOOM_IN_SECONDS){
			if($this->isDoom()){
			    $this->summonDragon();
			}else{
				$this->startDoom();
			}
		}
		if(!$this->isRestarted() and $tick > self::MATCH_OFF_INVICIBLE_IN_SECONDS){
		    if($this->isInvincible()){
			    $this->invincible = false;
			}
		}
		switch($tick){
			case self::MATCH_START_IN_SECONDS - 10:
			    $this->almostStartNotifyA();
			    break;
			case self::MATCH_START_IN_SECONDS - 5:
			case self::MATCH_START_IN_SECONDS - 4:
			case self::MATCH_START_IN_SECONDS - 3:
			case self::MATCH_START_IN_SECONDS - 2:
			case self::MATCH_START_IN_SECONDS - 1:
				$this->almostStartNotifyB();
				break;
			case self::MATCH_START_IN_SECONDS:
				$this->start();
				break;
		    case self::MATCH_OPEN_CAGE_IN_SECONDS - 10:
			case self::MATCH_OPEN_CAGE_IN_SECONDS - 9:
			case self::MATCH_OPEN_CAGE_IN_SECONDS - 8:
			case self::MATCH_OPEN_CAGE_IN_SECONDS - 7:
			case self::MATCH_OPEN_CAGE_IN_SECONDS - 6:
			case self::MATCH_OPEN_CAGE_IN_SECONDS - 5:
			case self::MATCH_OPEN_CAGE_IN_SECONDS - 4:
			case self::MATCH_OPEN_CAGE_IN_SECONDS - 3:
			case self::MATCH_OPEN_CAGE_IN_SECONDS - 2:
			case self::MATCH_OPEN_CAGE_IN_SECONDS - 1:
			    $this->almostCageOpenNotify();
			    break;
			case self::MATCH_OPEN_CAGE_IN_SECONDS:
				$this->openCage();
				break;
            case self::MATCH_REFILL_CHEST_IN_SECONDS:
				$this->startFillChest();
				break;	 
            case self::MATCH_DOOM_IN_SECONDS:
				if($this->isDoom()){
			    	$this->summonDragon();
				}else{
					$this->startDoom();
				}
				break;
			case self::MATCH_FINAL_IN_SECONDS:
				$this->restart();
				break;
		}		
	}
	
	/**
	 * @return string
	 */
	private function getStatus() :string{
		if(!$this->isStarted()){
			$time = self::MATCH_START_IN_SECONDS - ((int)microtime(true) - $this->getHumanReadableTime());
			return $this->getMessageLocalized("STARTING_FORMAT", ["#time"], [$time]);
		}
		if(!$this->isOpenCage()){
			$time = self::MATCH_OPEN_CAGE_IN_SECONDS - ((int)microtime(true) - $this->getHumanReadableTime());
			return $this->getMessageLocalized("OPENCAGE_FORMAT", ["#time"], [gmdate("i:s", $time)]);
		}
		if(!$this->isRefilled()){
			$time = self::MATCH_REFILL_CHEST_IN_SECONDS - ((int)microtime(true) - $this->getHumanReadableTime());
			return $this->getMessageLocalized("REFILL_FORMAT", ["#time"], [gmdate("i:s", $time)]);
		}
		if(!$this->isDoom()){
			$time = self::MATCH_DOOM_IN_SECONDS - ((int)microtime(true) - $this->getHumanReadableTime());
			return $this->getMessageLocalized("DOOM_FORMAT", ["#time"], [gmdate("i:s", $time)]);
		}
		if(!$this->isEndTime()){
			$time = self::MATCH_FINAL_IN_SECONDS - ((int)microtime(true) - $this->getHumanReadableTime());
			return $this->getMessageLocalized("ENDGAME_FORMAT", ["#time"], [gmdate("i:s", $time)]);
		}else{
			return $this->getMessageLocalized("GAME_ENDED_FORMAT", [], []);
		}
        //debug
        return TextFormat::RED."Not found status!";
	}

	/**
	 * calls in 10 seconds to start, send messages and sounds to players
	 */
	private function almostStartNotifyA(){
		foreach($this->players as $player){
			$player->getWorld()->addSound($player->getLocation()->asVector3(), new ClickSound(), [$player]);
		}
		$tick = (int)microtime(true) - $this->getHumanReadableTime();
		$timeLeft = (int)(self::MATCH_START_IN_SECONDS - $tick);
		$this->sendTitle($this->getMessageLocalized("STARTING_TITLE_A", ["#time"], [$timeLeft]), $this->getMessageLocalized("CHOOSE_KIT_TITLE", [], []));		
		$this->broadcastMessageLocalized("STARTING_IN_A", ["#time"], [$timeLeft]);
	}
	
	/**
	 * calls in 5 seconds to start, send messages and sounds to players
	 */
	private function almostStartNotifyB(){
		foreach($this->players as $player){
			$player->getWorld()->addSound($player->getLocation()->asVector3(), new ClickSound(), [$player]);
		}
		$tick = (int)microtime(true) - $this->getHumanReadableTime();
		$timeLeft = (int)(self::MATCH_START_IN_SECONDS - $tick);
		$this->sendTitle($this->getMessageLocalized("STARTING_TITLE_B", ["#time"], [$timeLeft]), $this->getMessageLocalized("PREPARE_TO_FIGHT", [], []));		
		$this->broadcastMessageLocalized("STARTING_IN_B", ["#time"], [$timeLeft]);
	}
	
	/**
	 * calls in 10 seconds to open cage, send messages and sounds to players
	 */
	private function almostCageOpenNotify(){
		foreach($this->players as $player){
			$player->setImmobile(false);
			$player->getWorld()->addSound($player->getLocation()->asVector3(), new ClickSound(), [$player]);
		}
		$tick = (int)microtime(true) - $this->getHumanReadableTime();
		$timeLeft = (int)(self::MATCH_OPEN_CAGE_IN_SECONDS - $tick);
		$this->broadcastMessageLocalized("OPEN_CAGE_IN", ["#time"], [$timeLeft]);
	}
	
	/**
	 * calls when end game
	 */
	private function almostTopKillerNotify(){
		$winners = [];
		foreach($this->kills as $data){
			$player = $data["player"];
			if(isset($this->players[$player->getXuid()])){
				$winners[] = $this->defaultNameTag[$player->getXuid()];
			}					
		}
		$winners = implode(",", $winners);
		$this->broadcastMessage("§l§a▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃");
		$this->broadcastMessage("§l§f      SkyWars         ");
		$this->broadcastMessage("§l§a");
		if($this->isTeamMode()){
		    $this->broadcastMessageLocalized("WINNERS_FORMAT", ["#players"], [$winners]);
		}else{
			$this->broadcastMessageLocalized("WINNER_FORMAT", ["#player"], [$winners]);
		}
		$this->broadcastMessage("§l§a");
        $i = 1;
		$newData = $this->kills;
		rsort($newData);
		if(count($newData) >= 1){
			foreach($newData as $case => $data){
				$player = $data["player"];
				if($i <= 3){
                    switch($i){
						case 1:
					        $this->broadcastMessageLocalized("1ST_KILLER_FORMAT", ["#player", "#counter"], [$this->defaultNameTag[$player->getXuid()], $data["kills"]]);
					        break;
						case 2:
					        $this->broadcastMessageLocalized("2ST_KILLER_FORMAT", ["#player", "#counter"], [$this->defaultNameTag[$player->getXuid()], $data["kills"]]);
					        break;
						case 3:
					        $this->broadcastMessageLocalized("3ST_KILLER_FORMAT", ["#player", "#counter"], [$this->defaultNameTag[$player->getXuid()], $data["kills"]]);
					        break;
					}
				}
				$i++;
			}
		}
		$this->broadcastMessage("§l§a");
		$this->broadcastMessage("§l§a▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃▃");
	}
	
	/**
	 * Calls on adding player to arena, some checks before allow player
	 * 
	 * @param Player $player
	 */
	public function addPlayer(Player $player) :bool{
		//can't add player - game was started
		if($this->isStarted()){
			return false;
		}
		//full arena
		if(count($this->players) >= (count($this->teams)*$this->maxInTeamCount)){
			return false;
		}
		//in-game
		$dataPlayer = $this->plugin->getPlayer($player);
	    if($dataPlayer->isInGame()){
			return false;
		}
		//accept player
		$this->acceptPlayer($player);
        return true;
	}
	
	/**
	 * Calls on adding player to spectator mode
	 * 
	 * @param Player $player
	 */
    public function setSpectator(Player $player){
		$world = $this->plugin->getServer()->getWorldManager()->getWorldByName($this->getWorld()->getFolderName());
        $player->teleport(Position::fromObject(Vector3::fromString($this->spawnDragon), $world));		
		$this->removePlayer($player);
		$this->setIndexSpectator($player);
        $player->setGamemode(GameMode::SPECTATOR());
        if(!isset($this->spectators[$player->getXuid()])){
			$this->spectators[$player->getXuid()] = $player;
		}	
		$player->sendTitle($this->getMessageLocalized("YOU_DIED", [], []), $this->getMessageLocalized("SPECTATOR", [], []));		
	}
	
	/**
	 * remove player from arena, 
	 * unset him from arena data,
	 * clear player's data
	 * 
	 * @param Player $player
	 * @param bool $fromWon
	 * @param bool $leftGame
	 */
	public function removePlayer(Player $player, bool $fromWon = false, bool $leftGame = false){
		$dataPlayer = $this->plugin->getPlayer($player);
		if(isset($this->defaultNameTag[$player->getXuid()])){
			$player->setNameTag($this->defaultNameTag[$player->getXuid()]);
		}
		if(isset($this->players[$player->getXuid()])){
			unset($this->players[$player->getXuid()]);
			//clear inventory
			$player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
		    $player->setHealth($player->getMaxHealth());
		    $player->getXpManager()->setXpAndProgress(0, 0.0);
		    $player->getEffects()->clear();
		    $player->getInventory()->clearAll();
		    $player->getArmorInventory()->clearAll();
		    $player->getCursorInventory()->clearAll();
			$this->removeFromTeam($player);
			$this->removeDataPlayer($player);
			//set winner if needs
			if(!$fromWon && $this->started){
				$this->won();
			}
			if($leftGame){
				$dataPlayer->setTeam("");
				$this->broadcastMessageLocalized("LEFT_GAME", ["#player"], [$player->getName()]);
			}
		}
		if(isset($this->registerSpawn[$player->getXuid()])){
			unset($this->registerSpawn[$player->getXuid()]);
		}
		if(isset($this->spectators[$player->getXuid()])){
			unset($this->spectators[$player->getXuid()]);
			$this->setDataPlayer($player);
			$dataPlayer->setTeam("");			
		}
	}

	/**
	 * Remove player info from team on current arena
	 * 
	 * @param Player $player
	 */
	private function removeFromTeam(Player $player){
		if($this->isTeamMode()){
			foreach($this->teams as $teamName => $teamPlayers){
				if(isset($teamPlayers[$player->getXuid()])){
					unset($this->teams[$teamName][$player->getXuid()]);
					return;
				}
			}
		}
	}
	
	/**
	 * Update data for player
	 * 
	 * @param Player $player
	 */
	private function removeDataPlayer(Player $player){
		$this->plugin->getPlayer($player)->setInGame(false);
	}
	
	/**
	 * Update data for player
	 * 
	 * @param Player $player
	 */
	private function addDataPlayer(Player $player){
		$dataPlayer = $this->plugin->getPlayer($player);
		$player->setGamemode(GameMode::SURVIVAL());
		$player->getHungerManager()->setEnabled(false);	
		$player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
		$player->setHealth($player->getMaxHealth());
		$player->getXpManager()->setXpAndProgress(0, 0.0);
		$player->getEffects()->clear();
		$player->getInventory()->clearAll();
		$player->getArmorInventory()->clearAll();
		$player->getCursorInventory()->clearAll();
		$this->saveNameTag($player);
		$dataPlayer->setInGame(true);
		$dataPlayer->setNameArena($this->getNameArena());
	}

	/**
	 * Update kills counter for player
	 * 
	 * @param Player $player
	 */
	public function updateKillsCounter(Player $player){
		if(isset($this->kills[$player->getXuid()])){
			$this->kills[$player->getXuid()]["kills"] += 1;
			$this->plugin->getDataBase()->addKills($player, 1);
		}
	}
	
	/**
	 * Update assists counter for player
	 * 
	 * @param Player $player
	 */
	public function updateAssistsCounter(Player $player){
		if(isset($this->assists[$player->getXuid()])){
			$this->assists[$player->getXuid()]["assists"] += 1;
			$this->plugin->getDataBase()->addAssists($player, 1);
		}
	}
	
	/**
	 * Save name tag for player when him join the game
	 * 
	 * @param Player $player
	 */
	private function saveNameTag(Player $player){
		if(!isset($this->defaultNameTag[$player->getXuid()])){
			$this->defaultNameTag[$player->getXuid()] = $player->getNameTag();
		}
	}
	
	/**
	 * Open cage for all players
	 */
	private function generateCage(){
		foreach($this->players as $player){
			if($this->isTeamMode()){
				BlockUtils::generateCage($player);
			}else{
			    $this->plugin->getCosmetics()->getCage($player)->generateCage($player);
			}
		}
	}
	
	/**
	 * Open cage for all players
	 */
	private function openCage(){
		//fill chest
		$this->fillChest();
		foreach($this->players as $player){
			//options or debug
			$player->getHungerManager()->setEnabled(true);	
            $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
		    $player->setHealth($player->getMaxHealth());
		    $player->getXpManager()->setXpAndProgress(0, 0.0);
		    $player->getEffects()->clear();
		    $player->getInventory()->clearAll();
		    $player->getArmorInventory()->clearAll();
		    $player->getCursorInventory()->clearAll();	
			//check mode and open cage
		    if($this->isTeamMode()){
				BlockUtils::openCage($player);
			}else{
			    $this->plugin->getCosmetics()->getCage($player)->openCage($player);
			}
			//get kit
			KitManager::getKit($player, $this->getMode());
			$player->getWorld()->addSound($player->getLocation()->asVector3(), new NoteSound(), [$player]);
		} 
		//remove blocks
		$world = $this->plugin->getServer()->getWorldManager()->getWorldByName($this->getWorld()->getFolderName());
		foreach($this->spawns as $data){
			$vector3 = Vector3::fromString($data);
			$world->loadChunk($vector3->getFloorX(), $vector3->getFloorZ());
			$world->setblockAt((int)$vector3->x, (int)$vector3->y, (int)$vector3->z, BlockFactory::getInstance()->get(BlockLegacyIds::AIR, 0));
		}
		$this->sendTitle($this->getMessageLocalized("MODE_TITLE", ["#mode"], [TextFormat::BOLD.$this->colorMode($this->getMode()). strtoupper($this->getMode())]));
		$this->broadcastMessageLocalized("OPENCAGE", [], []);
		$this->opencage = true;
	}
	
	/**
	 * Refill chest for arena
	 */
	private function startFillChest(){
		//setup data
		$this->fillChest();
		$this->refilled = true;
		$this->sendTitle(TextFormat::BOLD.TextFormat::RED."", $this->getMessageLocalized("REFILL_CHEST", [], []));
		$this->openedChests = [];
		$this->registerTagChest = [];
		//send sound
		foreach($this->players as $player){
			$player->getWorld()->addSound($player->getLocation()->asVector3(), new ChestOpenSound(), [$player]);
		}
		//remove all ChestTiles
		$world = $this->plugin->getServer()->getWorldManager()->getWorldByName($this->getWorld()->getFolderName());
		foreach($world->getEntities() as $entity){
			if($entity instanceof ChestTile){
				$entity->close();
			}
		}
	}
	
	/**
	 * Refill all chests in arena
	 */
	public function fillChest(){
		$world = $this->plugin->getServer()->getWorldManager()->getWorldByName($this->getWorld()->getFolderName());		
        foreach($world->getLoadedChunks() as $chunk){
            foreach($chunk->getTiles() as $tile){
                if($tile instanceof Chest){
		            $positionBlock = $tile->getInventory()->getHolder();
		            $convertString = (int)$positionBlock->x.",".(int)$positionBlock->y.",".(int)$positionBlock->z;
			        $chestInventory = $tile->getInventory();
			        $chestInventory->clearAll();
					//convert data chest mid 
			        $chests = [];
					//Laboratory
					if($this->getMode() == self::MODE_LABORATORY){	
						if($this->specialMode[0] == "Lucky Blocks"){									
							$world->setBlockAt((int)$positionBlock->x, (int)$positionBlock->y, (int)$positionBlock->z, BlockFactory::getInstance()->get(BlockLegacyIds::NETHER_REACTOR, 0));
							return; 
						}
					}
					foreach($this->chestMid as $chest => $enable){
						$x = (int)explode(",", $chest)[0];
						$y = (int)explode(",", $chest)[1];
						$z = (int)explode(",", $chest)[2];
						$chests[$x.",".$y.",".$z] = $enable;
					}
			        $refill = (new PluginData())->getRefill();
			        $typeChests = $refill[$this->getMode()];
			        if(isset($chests[$convertString])){
				        $type = $typeChests["mid"];
				        $chooseChest = $type[array_rand($type, 1)];
				        foreach($chooseChest as $item => $enchant){
					        $convertData = explode(",", $item);
					        if(count($convertData) >= 4){
					            $id = $convertData[0];
					            $meta = $convertData[1];
					            $count = $convertData[2];
					            $slotIndex = $convertData[3];					
						        if(count(explode("-", $slotIndex)) > 1){
							        $data = explode("-", $slotIndex);
							        $slotIndex = rand($data[0], $data[1]);
						        }
								//when refill chest will have enderpearl
								if(!$this->refilled and $id == ItemIds::ENDER_PEARL){
									$id = ItemIds::AIR;									
								}
					            $result = $this->getItem($id, $meta, $count);
					            if(count($enchant) >= 1){						
						            foreach($enchant as $id => $level){
						                $result->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId($id), $level));
						            }
					            }
					            $chestInventory->setItem($slotIndex, $result);
					        }else{
						        //debug
						        $chestInventory->addItem($this->getItem(17, 0, 64));
					        }
							$random = rand(1, 10);
							//Laboratory
							if($this->getMode() == self::MODE_LABORATORY){
								if(count($this->specialMode) >= 2){
									switch($this->specialMode[0]){
										case "TNT Madness":
											if(in_array($random, [5, 7])){
												$chestInventory->addItem($this->getItem(46, 0, rand(1, 7))->setCustomName(TextFormat::GOLD.TextFormat::RED."Insta Boom TNT"));		
											}
											break;
										case "Slime":
											if($random == 5){
												$chestInventory->addItem($this->getItem(346, 0, 1)->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(12), 5)));
											}
											break;
										case "rush":
											if(in_array($random, [5, 7])){
												$chestInventory->addItem($this->getItem(368, 0, 1));
											}
											break;
									}
								}
							}
				        }
			        }else{
				        $type = $typeChests["island"];
				        $chooseChest = $type[array_rand($type, 1)];
				        foreach($chooseChest as $item => $enchant){
					        $convertData = explode(",", $item);
					        if(count($convertData) >= 4){
					            $id = $convertData[0];
					            $meta = $convertData[1];
					            $count = $convertData[2];
					            $slotIndex = $convertData[3];
								if(count(explode("-", $slotIndex)) > 1){
							        $data = explode("-", $slotIndex);
							        $slotIndex = rand($data[0], $data[1]);
						        }
								//when refill chest will have enderpearl
								if(!$this->refilled and $id == ItemIds::ENDER_PEARL){
									$id = ItemIds::AIR;									
								}
					            $result = $this->getItem($id, $meta, $count);
					            if(count($enchant) >= 1){						
						            foreach($enchant as $id => $level){
						                $result->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId($id), $level));
							        }
						        }
						        $chestInventory->setItem($slotIndex, $result);
					        }else{
						        //debug
						        $chestInventory->addItem($this->getItem(1, 0, 15));								
							}
							$random = rand(1, 10);
							//Laboratory
							if($this->getMode() == self::MODE_LABORATORY){
								if(count($this->specialMode) >= 2){
									switch($this->specialMode[0]){
										case "TNT Madness":
											if(in_array($random, [5, 7])){
												$chestInventory->addItem($this->getItem(46, 0, rand(1, 7))->setCustomName(TextFormat::GOLD.TextFormat::RED."Insta Boom TNT"));		
											}
											break;
										case "Slime":
											if($random == 4){
												$chestInventory->addItem($this->getItem(165, 0, rand(1, 3)));
											}
											if($random == 5){
												$chestInventory->addItem($this->getItem(341, 0, 1)->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(12), 5)));
											}
											if($random == 7){
												$chestInventory->addItem($this->getItem(373, 9, 1));
											}
											break;
										case "rush":
											if(in_array($random, [5, 7])){
												$chestInventory->addItem($this->getItem(368, 0, 1));
											}
											break;
									}
								}
							}
						}
					}
				}
			}
		}
	}
	
	/**
	 * Register tag for chest when opened
	 */
	public function registerChestTile($chest){
		$positionBlock = $chest->getInventory()->getHolder();
		$convertString = (int)$positionBlock->x.",".(int)$positionBlock->y.",".(int)$positionBlock->z;
		if(!isset($this->openedChests[$convertString])){
		    $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($this->getWorld()->getFolderName());
		    $x = (int)($positionBlock->x);
		    $y = (int)($positionBlock->y + 1);
		    $z = (int)($positionBlock->z);
		    $location = new Location((float)$x + 0.5, (float)$y, (float)$z + 0.5, $world, 0, 0);
		    $entity = new ChestTile($location);
		    $entity->spawnToAll(); 
		    $this->registerTagChest[$entity->getId()] = [
		        "entity" => $entity,
			    "position" => $positionBlock
		    ];
			$this->openedChests[$convertString] = true;
		}
	}
	
	/**
	 * Update tag for all chests has opened
	 */
	private function updateTagChest(){
		$world = $this->plugin->getServer()->getWorldManager()->getWorldByName($this->getWorld()->getFolderName());
		foreach($this->registerTagChest as $entityId => $data){
			$entity = $data["entity"];
			$position = $data["position"];
			$x = (int)($position->x);
		    $y = (int)($position->y + 1);
		    $z = (int)($position->z);
			$convertString = (int)$position->x.",".(int)$position->y.",".(int)$position->z;
			$chest = $position->getWorld()->getTile($position);
			if($chest instanceof Chest){
				if(!$this->isRefilled()){
					$time = self::MATCH_REFILL_CHEST_IN_SECONDS - ((int)microtime(true) - $this->getHumanReadableTime());
					if(count($chest->getInventory()->getContents()) >= 1){
					    $entity->setNameTag(TextFormat::GREEN. gmdate("i:s", $time)."\n".TextFormat::GREEN."Opened!");
					}else{
						$entity->setNameTag(TextFormat::GREEN. gmdate("i:s", $time)."\n".TextFormat::RED."Empty!");
					}
				}else{
					if(count($chest->getInventory()->getContents()) >= 1){
					    $entity->setNameTag(TextFormat::GREEN. "Opened!");
					}else{
						$entity->setNameTag(TextFormat::RED."Empty!");
					}
				}
			}else{	
				$entity->close();	
				unset($this->registerTagChest[$entityId]);
				unset($this->openedChests[$convertString]);
			}
		}
    }
	
	/**
	 * Update tag for player together team
	 */
	private function updateTagPlayer(){
		if($this->isTeamMode()){
			foreach($this->players as $player){
				$dataPlayer = $this->getDataPlayer($player);
				foreach($player->getWorld()->getNearbyEntities($player->getBoundingBox()->expandedCopy(7, 7, 7) , $player) as $entity){
			        if($entity instanceof Player){
						$dataEntity = $this->getDataPlayer($entity);
						if(
						    $dataEntity->isInGame() and
							$dataPlayer->getTeam() == $dataEntity->getTeam()
						){
							$entity->setNameTag(TextFormat::GREEN.$entity->getName());
						}
						if(
						    $dataEntity->isInGame() and
							$dataPlayer->getTeam() != $dataEntity->getTeam()
						){
							$entity->setNameTag(TextFormat::RED.$entity->getName());
						}
					}
				} 
			}
		}
	}
	
	/**
	 * Start doom for arena
	 */
	private function startDoom(){
		$this->doom = true;
		$this->sendTitle(TextFormat::BOLD.TextFormat::RED."", $this->getMessageLocalized("SUDDEN_DEATH", [], []));
	}
	
	/**
	 * Summon dragon
	 */
	private function summonDragon(){
		if(count($this->dragons) < count(self::$dragonFamily)){
		    $this->broadcastMessageLocalized("DRAGON_SPAWNED", ["#dragon"], [self::$dragonFamily[count($this->dragons)]]);
			$position = Vector3::fromString($this->spawnDragon);
		    $world = $this->plugin->getServer()->getWorldManager()->getWorldByName($this->getWorld()->getFolderName());
		    $x = (int)($position->x);
		    $y = (int)($position->y + 1);
		    $z = (int)($position->z);
		    $location = new Location((float)$x + 0.5, (float)$y, (float)$z + 0.5, $world, 0, 0);
		    $entity = new EnderDragon($location);
		    $entity->spawnToAll(); 
            $point = $this->getPointsInArena();		
		    $this->dragons[$entity->getId()] = [
		        "entity" => $entity,
			    "point" => $point[array_rand($point, 1)],
			    "time-change-point" => microtime(true)
		    ];
		}
	}
	
	/**
	 * Summon for all dragons in arena
	 * @return void
	 */
	public function updateMovementDragons(){
		foreach($this->dragons as $id => $data){
			$entity = $data["entity"];
			if($entity->isClosed()){
				unset($this->dragons[$id]);
				return;
			}
			if((microtime(true) - $data["time-change-point"]) >= 7){
				$point = $this->getPointsInArena();
				$this->dragons[$id]["point"] = $point[array_rand($point, 1)];
				$this->dragons[$id]["time-change-point"] = microtime(true);
			}else{
				if($entity->getPosition()->distance(Vector3::fromString($data["point"])->add(0, rand(-2, 1), 0)) >= 50 or $entity->getLocation()->y < 4 or $entity->getLocation()->y > 250){
			        $entity->boundingBox->offset($entity->getMotion()->x, $entity->getMotion()->y, $entity->getMotion()->z);
			        $entity->lookAt(Vector3::fromString($data["point"])->add(0, rand(-2, 1), 0));
                    $entity->setMotion($entity->getDirectionVector());  
					$entity->boundingBox->offset($entity->getMotion()->x, $entity->getMotion()->y, $entity->getMotion()->z);
				}else{
					$entity->boundingBox->offset($entity->getMotion()->x, $entity->getMotion()->y, $entity->getMotion()->z);
		            $entity->changeRotation();
                    $entity->setMotion($entity->getDirectionVector());
                    $entity->boundingBox->offset($entity->getMotion()->x, $entity->getMotion()->y, $entity->getMotion()->z);					
				}
			}
		}
	}
	
	/**
	 * @return array
	 */
	private function getPointsInArena() :array{
		$points = [];
		foreach($this->spawns as $case => $data){
			$points[] = $data;
		}
		$chests = $this->chestMid;
		foreach($chests as $spawn => $bool){
			$points[] = $spawn;
		}
		return $points;
	}
	
	/**
	 * Send scoreboard for all players in arena when in starting mode
	 */
	private function sendScoreBoardWaiting(){
		$status = $this->getStatus();
		foreach($this->players as $player){
			ScoreBoardAPI::setScore($player, TextFormat::BOLD.TextFormat::YELLOW."§l§4Sky§cWars");
		    ScoreBoardAPI::setScoreLine($player, 1, TextFormat::YELLOW."");
			ScoreBoardAPI::setScoreLine($player, 2, TextFormat::WHITE."Players: ".TextFormat::GREEN. count($this->players)."/".(count($this->teams) * $this->maxInTeamCount));
			ScoreBoardAPI::setScoreLine($player, 3, TextFormat::WHITE."");
			ScoreBoardAPI::setScoreLine($player, 4, $status);
			ScoreBoardAPI::setScoreLine($player, 5, TextFormat::RED."");
			ScoreBoardAPI::setScoreLine($player, 6, TextFormat::WHITE."Server: ".TextFormat::GREEN."§5Blossom");
			ScoreBoardAPI::setScoreLine($player, 7, TextFormat::BOLD."");
			ScoreBoardAPI::setScoreLine($player, 8, $this->getMessageLocalized("IP_SERVER", [], []));
		}
	}
	
	/**
	 * Send scoreboard for all players in arena
	 */
	private function sendScoreBoard(){
		$status = $this->getStatus();
		foreach($this->players as $player){
			if(!$player->isOnline()){
				unset($this->players[$player->getXuid()]);
				return;
			}
			ScoreBoardAPI::setScore($player, TextFormat::BOLD.TextFormat::YELLOW."§l§4Sky§cWars");
			ScoreBoardAPI::setScoreLine($player, 1, TextFormat::GRAY."");
			ScoreBoardAPI::setScoreLine($player, 2, TextFormat::WHITE."Next Event");
			ScoreBoardAPI::setScoreLine($player, 3, $status);
			ScoreBoardAPI::setScoreLine($player, 4, TextFormat::BLUE."");
			if(!$this->isTeamMode()){
				ScoreBoardAPI::setScoreLine($player, 5, TextFormat::WHITE."Players Remaining: ".TextFormat::GREEN. count($this->players));
			    ScoreBoardAPI::setScoreLine($player, 6, TextFormat::WHITE."");
			    ScoreBoardAPI::setScoreLine($player, 7, TextFormat::WHITE."Kills: ".TextFormat::GREEN.$this->kills[$player->getXuid()]["kills"]);
			    ScoreBoardAPI::setScoreLine($player, 8, TextFormat::GREEN."");
		        ScoreBoardAPI::setScoreLine($player, 9, TextFormat::WHITE."Map: ".TextFormat::GREEN.$this->getNameMap());
				if($this->getMode() == self::MODE_LABORATORY){
					ScoreBoardAPI::setScoreLine($player, 10, TextFormat::LIGHT_PURPLE."Lab: ".$this->specialMode[1].$this->specialMode[0]);
				}else{			
					ScoreBoardAPI::setScoreLine($player, 10, TextFormat::WHITE."Mode: ".$this->colorMode($this->getMode()). ucfirst($this->getMode()));
				}
				ScoreBoardAPI::setScoreLine($player, 11, TextFormat::RED."");
			    ScoreBoardAPI::setScoreLine($player, 12, $this->getMessageLocalized("IP_SERVER", [], []));
			}else{
				ScoreBoardAPI::setScoreLine($player, 5, TextFormat::WHITE."Players left: ".TextFormat::GREEN. count($this->players));
				ScoreBoardAPI::setScoreLine($player, 6, TextFormat::WHITE."Teams left: ".TextFormat::GREEN. count($this->teams[$this->getDataPlayer($player)->getTeam()]));
			    ScoreBoardAPI::setScoreLine($player, 7, TextFormat::WHITE."");
			    ScoreBoardAPI::setScoreLine($player,8, TextFormat::WHITE."Kills: ".TextFormat::GREEN.$this->kills[$player->getXuid()]["kills"]);
				ScoreBoardAPI::setScoreLine($player, 9, TextFormat::WHITE."Assists: ".TextFormat::GREEN.$this->assists[$player->getXuid()]["assists"]);
				ScoreBoardAPI::setScoreLine($player, 10, TextFormat::GREEN."");
		        ScoreBoardAPI::setScoreLine($player, 11, TextFormat::WHITE."Map: ".TextFormat::GREEN.$this->getNameMap());
				if($this->getMode() == self::MODE_LABORATORY){
					ScoreBoardAPI::setScoreLine($player, 12, TextFormat::LIGHT_PURPLE."Lab: ".$this->specialMode[1].$this->specialMode[0]);
				}else{			
					ScoreBoardAPI::setScoreLine($player, 12, TextFormat::WHITE."Mode: ".$this->colorMode($this->getMode()). ucfirst($this->getMode()));
	                                ScoreBoardAPI::setScoreLine($player, 13, TextFormat::RED."");
		            }
			    ScoreBoardAPI::setScoreLine($player, 14, $this->getMessageLocalized("IP_SERVER", [], []));
			}
		}
		foreach($this->spectators as $spectator){
			if(!$spectator->isOnline()){
				unset($this->spectators[$spectator->getXuid()]);
				return;
			}
			ScoreBoardAPI::setScore($spectator, TextFormat::BOLD.TextFormat::YELLOW."§l§4Sky§cWars");
			ScoreBoardAPI::setScoreLine($spectator, 1, TextFormat::GRAY."");
			ScoreBoardAPI::setScoreLine($spectator, 2, TextFormat::WHITE."Next Event");
			ScoreBoardAPI::setScoreLine($spectator, 3, $status);
			ScoreBoardAPI::setScoreLine($spectator, 4, TextFormat::BLUE."");
			if(!$this->isTeamMode()){
				ScoreBoardAPI::setScoreLine($spectator, 5, TextFormat::WHITE."Players left: ".TextFormat::GREEN. count($this->players));
			    ScoreBoardAPI::setScoreLine($spectator, 6, TextFormat::WHITE."");
			    ScoreBoardAPI::setScoreLine($spectator, 7, TextFormat::WHITE."Kills: ".TextFormat::GREEN.$this->kills[$spectator->getXuid()]["kills"]);
			    ScoreBoardAPI::setScoreLine($spectator, 8, TextFormat::GREEN."");
		        ScoreBoardAPI::setScoreLine($spectator, 9, TextFormat::WHITE."Map: ".TextFormat::GREEN.$this->getNameMap());
				if($this->getMode() == self::MODE_LABORATORY){
					ScoreBoardAPI::setScoreLine($player, 10, TextFormat::LIGHT_PURPLE."Lab: ".$this->specialMode[1].$this->specialMode[0]);
				}else{			
					ScoreBoardAPI::setScoreLine($player, 10, TextFormat::WHITE."Mode: ".$this->colorMode($this->getMode()). ucfirst($this->getMode()));
				}
			    ScoreBoardAPI::setScoreLine($spectator, 11, TextFormat::RED."");
			    ScoreBoardAPI::setScoreLine($spectator, 12, $this->getMessageLocalized("IP_SERVER", [], []));
			}else{
				ScoreBoardAPI::setScoreLine($spectator, 5, TextFormat::WHITE."Players left: ".TextFormat::GREEN. count($this->players));
				ScoreBoardAPI::setScoreLine($spectator, 6, TextFormat::WHITE."Teams left: ".TextFormat::GREEN. count($this->teams[$this->getDataPlayer($spectator)->getTeam()]));
			    ScoreBoardAPI::setScoreLine($spectator, 7, TextFormat::WHITE."");
			    ScoreBoardAPI::setScoreLine($spectator, 8, TextFormat::WHITE."Kills: ".TextFormat::GREEN.$this->kills[$spectator->getXuid()]["kills"]);
				ScoreBoardAPI::setScoreLine($spectator, 9, TextFormat::WHITE."Assists: ".TextFormat::GREEN.$this->assists[$spectator->getXuid()]["assists"]);
				ScoreBoardAPI::setScoreLine($spectator, 9, TextFormat::GREEN."");
		        ScoreBoardAPI::setScoreLine($spectator, 10, TextFormat::WHITE."Map: ".TextFormat::GREEN.$this->getNameMap());
				if($this->getMode() == self::MODE_LABORATORY){
					ScoreBoardAPI::setScoreLine($player, 11, TextFormat::LIGHT_PURPLE."Lab: ".$this->specialMode[1].$this->specialMode[0]);
				}else{			
					ScoreBoardAPI::setScoreLine($player, 11, TextFormat::WHITE."Mode: ".$this->colorMode($this->getMode()). ucfirst($this->getMode()));
				}
			    ScoreBoardAPI::setScoreLine($spectator, 12, TextFormat::RED."");
			    ScoreBoardAPI::setScoreLine($spectator, 13, $this->getMessageLocalized("IP_SERVER", [], []));
			}
		}
	}

    /**
     * @param string $subject
     * @param array $search
     * @param array $replace
     * @return string
     */
    public function getMessageLocalized(string $subject, array $search, array $replace) :string{
        $message = explode("{array}", (new PluginData())->getMessage($subject));
		$result = $message[array_rand($message, 1)];
        return str_replace($search, $replace, $result);
    }
	
	/**
     * @param Player $player
	 * @param string $subject
	 * @param string $search
	 * @param string $replace
	 */
	private function sendMessageLocalized(Player $player, string $subject, array $search, array $replace){
        $player->sendMessage($this->getMessageLocalized($subject, $search, $replace));
    }
	
	/**
	 * send title without translate for all players in arena
	 * 
	 * @param string $text
	 */
	public function sendTitle(string $title = "", string $subtitle = ""){
		foreach($this->players as $player){
			$player->sendTitle($title, $subtitle);
		}
	}
	
	/**
	 * broadcast message without translate for all players in arena
	 * 
	 * @param string $text
	 */
	public function broadcastMessage(string $text){
		foreach($this->players as $player){
			$player->sendMessage($text);
		}
		foreach($this->spectators as $spectator){
			$spectator->sendMessage($text);
		}
	}
	
	/**
	 * @param string $subject
	 * @param array $search
	 * @param array $replace
	 */
	public function broadcastMessageLocalized(string $subject, array $search, array $replace){
		foreach($this->players as $player){
		    $player->sendMessage($this->getMessageLocalized($subject, $search, $replace));
		}
		foreach($this->spectators as $spectator){
		    $spectator->sendMessage($this->getMessageLocalized($subject, $search, $replace));
		}
	}
	
	/**
	 * @param string $subject
	 * @param array $search
	 * @param array $replace
	 */
	public function sendPopupLocalized(string $subject, array $search, array $replace){
		foreach($this->players as $player){
		    $player->sendPopup($this->getMessageLocalized($subject, $search, $replace));
		}
		foreach($this->spectators as $spectator){
		    $spectator->sendPopup($this->getMessageLocalized($subject, $search, $replace));
		}
	}

	/**
	 * calls when player join game:
	 * setup his inventory, teleport to pedestal, update info
	 * broadcast message inside arena, update arena data
	 * 
	 * @param Player $player
	 */
	private function acceptPlayer(Player $player){
		//check count players enought 3
		if(count($this->players) < self::MIN_PLAYER_COUNT){
			$this->humanReadableTime = (int)microtime(true);
		}		
		//teleport player to waiting lobby
		$world = $this->plugin->getServer()->getWorldManager()->getWorldByName($this->lobbywaiting["world"]);
		$position = Vector3::fromString($this->lobbywaiting["spawn"]);
		$player->teleport(Position::fromObject($position, $world));
		//operations with player
		$this->addPlayerToTeam($player);
		$this->addDataPlayer($player);	
		$this->setIndexJoin($player);
		//register spawn for player
		if(!$this->isTeamMode()){
			$registered = [];
			foreach($this->registerSpawn as $playerd => $slot){
				$registered[$slot] = $playerd;
			}
			$unregister = [];
			foreach($this->spawns as $slot => $spawn){
				if(!isset($registered[$slot])){
					$unregister[$slot] = $slot;
				}
			}
			$this->registerSpawn[$player->getXuid()] = $unregister[array_rand($unregister, 1)];
		}else{
			$i = 1;
			$dataSpawn = [];
			foreach(self::$teamColors as $color){
				$dataSpawn[$color] = $i;
				$i++;
			}
			$teamPlayer = $this->getDataPlayer($player)->getTeam();
			$this->registerSpawn[$player->getXuid()] = $dataSpawn[$teamPlayer];
		}
		$this->broadcastMessageLocalized("JOINED_GAME", ["#player"], [$player->getName()]);
		//update arena info
		$this->players[$player->getXuid()] = $player;
		$this->kills[$player->getXuid()] = [
			"kills" => 0,
			"player" => $player
		];
		$this->assists[$player->getXuid()] = [
			"assists" => 0,
			"player" => $player
		];
	}
	
	/**
	 * calls when need for data player
	 * 
	 * @param Player $player
	 * @return SWPlayer
	 */
	public function getDataPlayer(Player $player) :?SWPlayer{
		return $this->plugin->players[$player->getXuid()];
	}
	
	/**
	 * calls when need for data player
	 * 
	 * @param Player $player
	 */
	public function setDataPlayer(Player $player){
		$player->setGamemode($this->plugin->getServer()->getGamemode());	
		$player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
		$player->setHealth($player->getMaxHealth());
		$player->getXpManager()->setXpAndProgress(0, 0.0);
		$player->getEffects()->clear();
		$player->getInventory()->clearAll();
		$player->getArmorInventory()->clearAll();
		$player->getCursorInventory()->clearAll();
	}

	/**
	 * Look for team with less count of members,
	 * check if player has already have team 
	 * save player in arena teams list and save team name in player's data
	 * 
	 * @param Player $player
	 */
	private function addPlayerToTeam(Player $player){
		if($this->isTeamMode()){
			$minArray = [];
			$targetTeamPlayersCount = PHP_INT_MAX;
			foreach($this->teams as $teamName => $teamPlayers){
				if(SkyWars::getInstance()->inParty($player)){
					if($this->plugin->inPartyByPlayer($player, $teamPlayers)){
						$this->teams[$teamName][$player->getXuid()] = $player;
			            $this->plugin->getPlayer($player)->setTeam($teamName);
						return;
					}
				}
				$playersCount = count($teamPlayers);
				if($playersCount < $targetTeamPlayersCount and $playersCount < $this->maxInTeamCount){
					$minArray[$teamName] = $teamName;
				}
			}
			$targetTeamName = array_rand($minArray);
			$this->teams[$targetTeamName][$player->getXuid()] = $player;
			$this->plugin->getPlayer($player)->setTeam($targetTeamName);
		}
	}
	
	/**
	 * Calls when /player or team/ win arena
	 * $candidate is a color of won team
	 */
	private function won(){
		if(count($this->players) == 0){
			//restart
			$this->restart();
			return;
		}
		$winners = [];
		$candidate = false;
		//look for winner player or team
		if($this->isTeamMode()){
			foreach($this->teams as $color => $team){				
				if(count($team) > 0){
					if($candidate){
						return;
					}else{
						$candidate = $color;
					}
				}
			}
			if($candidate){
				$winners = $this->teams[$candidate];
			}
		}else{
			if(count($this->players) === 1){
				foreach($this->players as $player){
					$candidate = $player->getName();
				}
				$winners = $this->players;
			}
		}
		if($candidate){			
			$winnername = ucfirst($candidate);
			foreach($winners as $winner){
				if($winner instanceof Player){
					//up rating for player when in mode ranked
					if($this->getMode() == self::MODE_RANKED){
						Ranking::addRating($winner, rand(20, 50));
					}
					Quest::checkQuestPlayer($winner, $this->maxInTeamCount, "win");
					$this->plugin->victoryDance[$winner->getXuid()] = new FireWork();
					$this->plugin->getDataBase()->addWins($winner, 1);
					Ranking::addXp($winner, 10);
					$winer->sendMessage("You Won so You Earned 10 XP");
					$winner->sendTitle($this->getMessageLocalized("VICTORY", [], []), $this->getMessageLocalized("VICTORY_SUB", [], []));
				    $winner->getWorld()->addSound($winner->getLocation()->asVector3(), new XpLevelUpSound(100), [$winner]);
				}
			}
			$this->restart();
		}
	}

    /**
     * @param int $id
     * @param int $meta
     * @param int $count
     * @param $tags
     * @return Item
     */
    private function getItem(int $id, int $meta = 0, int $count = 1, $tags = null) : Item{
		return ItemFactory::getInstance()->get($id, $meta, $count, $tags);
	}
}
