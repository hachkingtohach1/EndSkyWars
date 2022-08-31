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

use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\EntityDataHelper;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockFactory;
use pocketmine\world\World;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\network\mcpe\protocol\types\entity\EntityIds;
use hachkingtohach1\SkyWars\task\ArenaTick;
use hachkingtohach1\SkyWars\task\ProjectileTick;
use hachkingtohach1\SkyWars\task\UpdateMovement;
use hachkingtohach1\SkyWars\task\SendVictoryDanceTick;
use hachkingtohach1\SkyWars\entity\ChestTile;
use hachkingtohach1\SkyWars\entity\EnderDragon;
use hachkingtohach1\SkyWars\entity\SoloMode;
use hachkingtohach1\SkyWars\entity\DoubleMode;
use hachkingtohach1\SkyWars\entity\RankedMode;
use hachkingtohach1\SkyWars\entity\MegaMode;
use hachkingtohach1\SkyWars\entity\LaboratoryMode;
use hachkingtohach1\SkyWars\entity\TopKills;
use hachkingtohach1\SkyWars\entity\TopDeaths;
use hachkingtohach1\SkyWars\entity\TopWins;
use hachkingtohach1\SkyWars\entity\TopLevel;
use hachkingtohach1\SkyWars\entity\SoulWell;
use hachkingtohach1\SkyWars\entity\QuestMaster;
use hachkingtohach1\SkyWars\entity\FireworksRocket;
use hachkingtohach1\SkyWars\item\Fireworks;
use hachkingtohach1\SkyWars\provider\DataBase;
use hachkingtohach1\SkyWars\provider\sql\SQL;
use hachkingtohach1\SkyWars\cosmetics\Cosmetics;
use hachkingtohach1\SkyWars\form\Form;
use hachkingtohach1\SkyWars\soulwell\SoulWellTick;
use hachkingtohach1\SkyWars\ranking\UpdateRankTick;
use hachkingtohach1\SkyWars\economy\Economy;
use hachkingtohach1\SkyWars\ranking\Ranking;
use hachkingtohach1\SkyWars\math\Vector3;
use hachkingtohach1\SkyWars\quest\QuestTick;

/**
 * Base plugin class, contains onEnable and onDisable methods,
 * specific SkyWars commands like /lobby, /sw, /swadmin
 */
class SkyWars extends PluginBase{
	/*@var array*/
	public array $setup = [];
	/*@var array*/
	public array $arenas = [];
	/*@var array*/
	public array $players = [];
	/*@var array*/
	public array $party = [];
	/*@var array*/
	public array $partyChat = [];
	/*@var array*/
	public array $invite = [];
	/*@var array*/
	public array $rollSoulWell = [];
	/*@var array*/
	public array $victoryDance = [];
    /*@var bool*/
	private bool $testMode = false;	
	/*@var static*/
	private static $instance;
	/*@var static*/
	private static $dataBase;
	/*@var messages config file*/
	private $messages;
	/*@var quests config file*/
	public $quests;
	
	const MODE_NORMAL = "normal";
	const MODE_INSANE = "insane";
	const MODE_RANKED = "ranked";
	const MODE_MEGA = "mega";
	const MODE_LABORATORY = "laboratory";

	public const PREFIX = TextFormat::DARK_PURPLE."";

    /**
	 * @return void
	 */
	public function onLoad() :void{
        self::$instance = $this;
	}
	
	/**
	 * @return SkyWars
	 */
    public static function getInstance(): ?SkyWars{
        return self::$instance;
    }
	
	/**
     * @return DataBase
	 */
	public function getDatabase(): Database{
        return self::$dataBase;
	}
	
	/**
     * @return string
	 */
	public function getFile(): string{
        return parent::getFile();
    }
	
	/**
     * @return array
	 */
	public function getMessages(): array{
		$data = [];
		if($this->messages instanceof Config){
			$data = $this->messages->getAll();
		}
        return $data;
    }

    /**
	 * calls when plugin enable
	 */
	public function onEnable() :void{
		//SQL
		self::$dataBase = new SQL("mysql");          
		//save default config from resource file 
		@mkdir($this->getDataFolder());
		@mkdir($this->getDataFolder()."arenas/");
		@mkdir($this->getDataFolder()."saves/");
		$this->saveDefaultConfig();
		$this->saveResource("messages.yml");
		$this->saveResource("quests.yml");
		//register entity
		$entityfactory = EntityFactory::getInstance();
		$entityfactory->register(ChestTile::class, function(World $world, CompoundTag $nbt) :ChestTile{
			return new ChestTile(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['ChestTile']);
		$entityfactory->register(EnderDragon::class, function(World $world, CompoundTag $nbt) :EnderDragon{
			return new EnderDragon(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['EnderDragon']);
		$entityfactory->register(SoloMode::class, function(World $world, CompoundTag $nbt) :SoloMode{
			return new SoloMode(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['SoloMode']);
		$entityfactory->register(DoubleMode::class, function(World $world, CompoundTag $nbt) :DoubleMode{
			return new DoubleMode(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['DoubleMode']);
		$entityfactory->register(RankedMode::class, function(World $world, CompoundTag $nbt) :RankedMode{
			return new RankedMode(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['RankedMode']);
	        $entityfactory->register(MegaMode::class, function(World $world, CompoundTag $nbt) :MegaMode{
			return new MegaMode(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['MegaMode']);
		$entityfactory->register(LaboratoryMode::class, function(World $world, CompoundTag $nbt) :LaboratoryMode{
			return new LaboratoryMode(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['LaboratoryMode']);
		$entityfactory->register(TopKills::class, function(World $world, CompoundTag $nbt) :TopKills{
			return new TopKills(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['TopKills']);
		$entityfactory->register(TopDeaths::class, function(World $world, CompoundTag $nbt) :TopDeaths{
			return new TopDeaths(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['TopDeaths']);
		$entityfactory->register(TopWins::class, function(World $world, CompoundTag $nbt) :TopWins{
			return new TopWins(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['TopWins']);
		$entityfactory->register(TopLevel::class, function(World $world, CompoundTag $nbt) :TopLevel{
			return new TopLevel(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['TopLevel']);
		$entityfactory->register(SoulWell::class, function(World $world, CompoundTag $nbt) :SoulWell{
			return new SoulWell(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['SoulWell']);
        $entityfactory->register(QuestMaster::class, function(World $world, CompoundTag $nbt) :QuestMaster{
			return new QuestMaster(EntityDataHelper::parseLocation($nbt, $world), null, $nbt);
		}, ['QuestMaster']);		
        //firework		
        $item = new Fireworks(new ItemIdentifier(ItemIds::FIREWORKS, 0), "Fireworks");
		$entityfactory->register(FireworksRocket::class, static function (World $world, CompoundTag $nbt) use ($item): FireworksRocket {
            return new FireworksRocket(EntityDataHelper::parseLocation($nbt, $world), $item);
        }, ['FireworksRocket', EntityIds::FIREWORKS_ROCKET], EntityLegacyIds::FIREWORKS_ROCKET);
        ItemFactory::getInstance()->register($item, true);
		//upload listener
		$listener = new EventListener($this);
		$this->getServer()->getPluginManager()->registerEvents($listener, $this);
		//setup all arenas
		$this->getLogger()->info("Setting up all arenas...");		
		$files = [];
		foreach(glob($this->getDataFolder()."arenas". DIRECTORY_SEPARATOR ."*.yml") as $file){
            $config = new Config($file, Config::YAML);
			$files[] = $config->getAll(\false);
		}
		foreach($files as $data){
			if(!$this->getServer()->getWorldManager()->isWorldLoaded($data["world"])){
                $this->getServer()->getWorldManager()->loadWorld($data["world"]);
			}
			if(!$this->getServer()->getWorldManager()->isWorldLoaded($data["lobbywaiting"]["world"])){
                $this->getServer()->getWorldManager()->loadWorld($data["lobbywaiting"]["world"]);
			}
			$this->arenas[$data["world"]] = new Arenas($this, $data["world"], $data["name-map"], $data["world"], $data["spawns"], $data["spawn-dragon"], $data["lobbywaiting"], $data["chests-mid"], $data["max-player-inteam-count"], $data["count-teams"], $data["mode"]);
		}
		//messages file
		$this->messages = new Config($this->getDataFolder()."messages.yml", Config::YAML);
		//quests file
		$this->quests = new Config($this->getDataFolder()."quests.yml", Config::YAML);
		//register task
		$this->getScheduler()->scheduleRepeatingTask(new ArenaTick($this), 20);		
		$this->getScheduler()->scheduleRepeatingTask(new ProjectileTick($this), 1);
		$this->getScheduler()->scheduleRepeatingTask(new UpdateMovement($this), 10);
		$this->getScheduler()->scheduleRepeatingTask(new SendVictoryDanceTick($this), 20);
		$this->getScheduler()->scheduleRepeatingTask(new SoulWellTick($this), 5);
		$this->getScheduler()->scheduleRepeatingTask(new UpdateRankTick($this), 20);
		$this->getScheduler()->scheduleRepeatingTask(new QuestTick($this), 20);
		//set name server
		$this->getServer()->getNetwork()->setName($this->getConfig()->get("name-server"));
	}

    /**
     * @param CommandSender $sender
     * @param Command $command
     * @param string $label
     * @param array $args
     * @return bool
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) :bool{
		if($command->getName() == "swp"){
			if(!$sender instanceof Player){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis is command for in-game!");
				return false;
			}
			if(!isset($args[0])){
				$sender->sendMessage(self::PREFIX.TextFormat::GREEN."§l§6» §r§a/swp <invite,kick,accept,dispand,chat>");
				return false;
			}
			switch($args[0]){
				case "invite":
				    if(!isset($args[1])){
				        $sender->sendMessage(self::PREFIX.TextFormat::GREEN."§l§6» §r§a/swp invite <player>");
				        break;
					}
					$target = $this->getServer()->getPlayerByPrefix($args[1]);
				    if(!$target instanceof Player){
						$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cPlayer doesn't exist!");
						break;
					}
					if(!isset($this->party[$sender->getXuid()])){
						$this->party[$sender->getXuid()] = [
						    "owner" => $sender,
							"members" => []
						];						
					}
					if(!isset($this->invite[$target->getXuid()])){
						$this->invite[$target->getXuid()] = [];
					}
					if(isset($this->invite[$target->getXuid()][$sender->getName()])){
						$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cYou already have invited this player");
						break;
					}
					$this->invite[$target->getXuid()][$sender->getName()] = ["player" => $sender];
					$sender->sendMessage(self::PREFIX.TextFormat::AQUA."§l§6» §r§aYou have successfully invited this player!");
					$target->sendMessage(self::PREFIX.TextFormat::GREEN."§l§6» §r§c".TextFormat::YELLOW.$sender->getName().TextFormat::AQUA." sent you an invitation to their party. Usage: /swp accept ".$sender->getName());
				    break;
				case "kick":
				    if(isset($this->party[$sender->getXuid()])){
						if(!isset($args[1])){
				            $sender->sendMessage(self::PREFIX.TextFormat::GOLD."§l§6» §r§6/swp kick <player>");
				            break;
						}
						$target = $this->getServer()->getPlayerByPrefix($args[1]);
				        if(!$target instanceof Player){
						    $sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cPlayer doesn't exist!");
						    break;
						}
						if(!isset($this->party[$sender->getXuid()]["members"][$target->getXuid()])){
							$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cPlayer is not a member of your party!");
						    break;
						}
                        unset($this->party[$sender->getXuid()]["members"][$target->getXuid()]);						
						foreach($this->party[$sender->getXuid()]["members"] as $member){
							$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§c".TextFormat::RED.$target->getName()." was kicked out of the party!");
						}
						$target->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§c".TextFormat::YELLOW.$sender->getName().TextFormat::AQUA." kicked you out of the party!");
					}else{
						$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cYou are not currently in a party!");
					}
				    break;
				case "accept":
				    if(!isset($args[1])){
				        $sender->sendMessage(self::PREFIX.TextFormat::GOLD."§l§6» §r§g/swp accept <player>");
				        break;
					}
					if(!isset($this->invite[$sender->getXuid()][$args[1]])){
						$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis invitation does not exist!");
						break;
					}
					$target = $this->getServer()->getPlayerByPrefix($args[1]);
					if(!$target instanceof Player){
						$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cPlayer doesn't exist!");
						break;
					}
					if(!isset($this->party[$target->getXuid()])){
						$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis invitation does not exist!");
						break;
					}
					if($this->inParty($sender)){
				        $sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cYou are in a party!");
				        break;
					}
					$owner = $this->party[$target->getXuid()]["owner"];
					$owner->sendMessage(self::PREFIX.TextFormat::GREEN."§l§6» §r§c".TextFormat::GREEN.$sender->getName()." has joined the party!");
					foreach($this->party[$target->getXuid()]["members"] as $member){
						$sender->sendMessage(self::PREFIX.TextFormat::GREEN."§l§6» §r§c".TextFormat::GREEN.$sender->getName()." has joined the party!");
					}
					$this->party[$target->getXuid()]["members"][$sender->getXuid()] = ["player" => $sender];
					$sender->sendMessage(self::PREFIX.TextFormat::GREEN."§l§6» §r§aYou have joined the party!");
					break;
				case "dispand":
				    if(!isset($args[1])){
				        $sender->sendMessage(self::PREFIX.TextFormat::GOLD."§l§6» §r§g/swp dispand");
				        break;
					}
					if(!isset($this->party[$sender->getXuid()])){
						$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cYou aren't in a party!");
						break;
					}
					foreach($this->party[$sender->getXuid()]["members"] as $member){
						$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThe party has been disbanded, you are no longer in a party!");
					}
					unset($this->party[$sender->getXuid()]);
					break;
				case "chat":
					if(!isset($this->party[$sender->getXuid()])){
						$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cYou aren't in a party!");
						break;
					}
				    if(!isset($this->partyChat[$sender->getXuid()])){
						$this->partyChat[$sender->getXuid()] = $sender;
						$sender->sendMessage(self::PREFIX.TextFormat::GREEN."§l§6» §r§aYou have joined party chat!");
						break;
					}	
                    unset($this->partyChat[$sender->getXuid()]);
					$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cYou have left party chat!");					
				    break;
			}
			return true;
		}
		if($command->getName() == "swbuild"){
			if(!$sender instanceof Player){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for in-game only!");
				return false;
			}
			if(!$this->getServer()->isOp($sender->getName())){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for staff only!");
				return false;
			}
			if(!$this->getConfig()->get("builder-mode")){
				$this->getConfig()->set("builder-mode", true);
				$this->getConfig()->save();
				$sender->sendMessage(self::PREFIX.TextFormat::YELLOW."Builder mode: ".TextFormat::GREEN." ENABLED");
			}else{
				$this->getConfig()->set("builder-mode", false);
				$this->getConfig()->save();
				$sender->sendMessage(self::PREFIX.TextFormat::YELLOW."Builder mode: ".TextFormat::RED." DISABLED");
			}
			return true;
		}
		if($command->getName() == "createsw"){
			if(!$sender instanceof Player){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for in-game only!");
				return false;
			}
			if(!$this->getServer()->isOp($sender->getName())){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for staff only!");
				return false;
			}
			if(!isset($args[0])){
				$sender->sendMessage(self::PREFIX.TextFormat::GREEN."/createsw <name-arena>");
				return false;
			}
			if(!isset($this->setup[$sender->getXuid()])){
				$this->setup[$sender->getXuid()] = [
				    "mode" => "normal",
                    "name-map" => false,
                    "world" => $args[0],
                    "lobbywaiting" => [
                        "spawn" => false,
                        "world" => false
					],
					"spawns" => [],
                    "spawn-dragon" => false,
                    "max-player-inteam-count" => 1,
					"count-teams" => 12,
                    "chests-mid" => []
				];
				$sender->sendMessage(self::PREFIX.TextFormat::RED."Arena created!");
				$mode = [self::MODE_NORMAL, self::MODE_INSANE, self::MODE_RANKED, self::MODE_MEGA, self::MODE_LABORATORY];
				$sender->sendMessage(self::PREFIX.TextFormat::GREEN."/swmode <".implode("/", $mode).">");
			}else{
				$sender->sendMessage(self::PREFIX.TextFormat::RED."You are in mode setup!");
				return false;
			}
			return true;
		}
		if($command->getName() == "swmode"){
			if(!$sender instanceof Player){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for in-game only!");
				return false;
			}
			if(!$this->getServer()->isOp($sender->getName())){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for staff only!");
				return false;
			}
			$mode = [self::MODE_NORMAL, self::MODE_INSANE, self::MODE_RANKED, self::MODE_MEGA, self::MODE_LABORATORY];
			if(!isset($args[0])){
				$sender->sendMessage(self::PREFIX.TextFormat::GREEN."/swmode <".implode("/", $mode).">");
				return false;
			}
			if(!in_array($args[0], $mode)){
				$sender->sendMessage(self::PREFIX.TextFormat::GREEN."/swmode <".implode("/", $mode).">");
				return false;
			}
			if(isset($this->setup[$sender->getXuid()])){
				$this->setup[$sender->getXuid()]["mode"] = $args[0];
				$sender->sendMessage(self::PREFIX.TextFormat::GREEN."Data has been saved!");
				$sender->sendMessage(self::PREFIX.TextFormat::GOLD."/swname <name>");
			}else{
				$sender->sendMessage(self::PREFIX.TextFormat::RED."You need in mode setup!");
				return false;
			}
			return true;
		}
		if($command->getName() == "swname"){
			if(!$sender instanceof Player){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for in-game only!");
				return false;
			}
			if(!$this->getServer()->isOp($sender->getName())){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for staff only!");
				return false;
			}
			if(!isset($args[0])){
				$sender->sendMessage(self::PREFIX.TextFormat::GREEN."/swname <name>");
				return false;
			}
			if(isset($this->setup[$sender->getXuid()])){
				$this->setup[$sender->getXuid()]["name-map"] = $args[0];
				$sender->sendMessage(self::PREFIX.TextFormat::GREEN."Data has been saved!");
				$sender->sendMessage(self::PREFIX.TextFormat::GOLD."/swlobbywaiting");
			}else{
				$sender->sendMessage(self::PREFIX.TextFormat::RED."You need in mode setup!");
				return false;
			}
			return true;
		}
		if($command->getName() == "swlobbywaiting"){
			if(!$sender instanceof Player){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for in-game only!");
				return false;
			}
			if(!$this->getServer()->isOp($sender->getName())){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for staff only!");
				return false;
			}
			if(isset($this->setup[$sender->getXuid()])){
				$this->setup[$sender->getXuid()]["lobbywaiting"]["world"] = $sender->getWorld()->getFolderName();
				$this->setup[$sender->getXuid()]["lobbywaiting"]["spawn"] = (new Vector3($sender->getPosition()->x, $sender->getPosition()->y, $sender->getPosition()->z))->toString();
			    $sender->sendMessage(self::PREFIX.TextFormat::GREEN."Data has been saved!");
				$sender->sendMessage(self::PREFIX.TextFormat::GOLD."/swspawndragon");
			}else{
				$sender->sendMessage(self::PREFIX.TextFormat::RED."You need in mode setup!");
				return false;
			}
			return true;
		}
		if($command->getName() == "swspawndragon"){
			if(!$sender instanceof Player){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for in-game only!");
				return false;
			}
			if(!$this->getServer()->isOp($sender->getName())){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for staff only!");
				return false;
			}
			if(isset($this->setup[$sender->getXuid()])){
				$this->setup[$sender->getXuid()]["spawn-dragon"] = (new Vector3($sender->getPosition()->x, $sender->getPosition()->y, $sender->getPosition()->z))->toString();
			    $sender->sendMessage(self::PREFIX.TextFormat::GREEN."Data has been saved!");
				$sender->sendMessage(self::PREFIX.TextFormat::GREEN."/swmaxplayerinteam <count> - Set max player in team");
			}else{
				$sender->sendMessage(self::PREFIX.TextFormat::RED."You need in mode setup!");
				return false;
			}
			return true;
		}
		if($command->getName() == "swmaxplayerinteam"){
			if(!$sender instanceof Player){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for in-game only!");
				return false;
			}
			if(!$this->getServer()->isOp($sender->getName())){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for admins only!");
				return false;
			}
			if(isset($this->setup[$sender->getXuid()])){
				if(!isset($args[0])){
					$sender->sendMessage(self::PREFIX.TextFormat::GREEN."/swmaxplayerinteam <count>");
				    return false;
				}
				$this->setup[$sender->getXuid()]["max-player-inteam-count"] = (int)$args[0];
			    $sender->sendMessage(self::PREFIX.TextFormat::GREEN."Data has been saved!");
				$sender->sendMessage(self::PREFIX.TextFormat::GOLD."/swmaxteam <count(max: 24)> - Set number team");
			}else{
				$sender->sendMessage(self::PREFIX.TextFormat::RED."You need in mode setup!");
				return false;
			}
			return true;
		}
		if($command->getName() == "swmaxteam"){
			if(!$sender instanceof Player){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for in-game only!");
				return false;
			}
			if(!$this->getServer()->isOp($sender->getName())){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for staff only!");
				return false;
			}
			if(isset($this->setup[$sender->getXuid()])){
				if(!isset($args[0])){
					$sender->sendMessage(self::PREFIX.TextFormat::GREEN."/swmaxteam <count>");
				    return false;
				}
				$this->setup[$sender->getXuid()]["count-teams"] = (int)$args[0];
			    $sender->sendMessage(self::PREFIX.TextFormat::GREEN."Data has been saved!");		
			    $sender->sendMessage(self::PREFIX.TextFormat::GOLD."/swspawn <slot> - Set as much as you want!");
				$sender->sendMessage(self::PREFIX.TextFormat::GOLD."/swchestmid - Set as much as you want!");
				$sender->sendMessage(self::PREFIX.TextFormat::GOLD."/swsave - Final save arena");
			}else{
				$sender->sendMessage(self::PREFIX.TextFormat::RED."You need in mode setup!");
				return false;
			}
			return true;
		}
		if($command->getName() == "swspawn"){
			if(!$sender instanceof Player){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for in-game only!");
				return false;
			}
			if(!$this->getServer()->isOp($sender->getName())){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for staff only!");
				return false;
			}
			if(!isset($args[0])){
				$sender->sendMessage(self::PREFIX.TextFormat::GREEN."/swspawn <slot>");
				return false;
			}
			if(isset($this->setup[$sender->getXuid()])){
				$this->setup[$sender->getXuid()]["spawns"][(int)$args[0]] = (new Vector3($sender->getPosition()->x, $sender->getPosition()->y + 7, $sender->getPosition()->z))->toString();
			    $sender->getWorld()->setblockAt($sender->getPosition()->x, $sender->getPosition()->y + 7, $sender->getPosition()->z, BlockFactory::getInstance()->get(BlockLegacyIds::BEACON, 0));
				$sender->sendMessage(self::PREFIX.TextFormat::GREEN."Spawn ".$args[0]." saved!");
			}else{
				$sender->sendMessage(self::PREFIX.TextFormat::RED."You need in mode setup!");
				return false;
			}
			return true;
		}
		if($command->getName() == "swchestmid"){
			if(!$sender instanceof Player){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for in-game only!");
				return false;
			}
			if(!$this->getServer()->isOp($sender->getName())){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for staff only!");
				return false;
			}
			if(isset($this->setup[$sender->getXuid()])){
				$position = $sender->getPosition()->asVector3()->add(0, -1, 0);
				$this->setup[$sender->getXuid()]["chests-mid"][(new Vector3($position->x, $position->y, $position->z))->toString()] = true;
			    $sender->sendMessage(self::PREFIX.TextFormat::GREEN."Data has been saved!");
			}else{
				$sender->sendMessage(self::PREFIX.TextFormat::RED."You need in mode setup!");
				return false;
			}
			return true;
		}
		if($command->getName() == "swsave"){
			if(!$sender instanceof Player){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for in-game only!");
				return false;
			}
			if(!$this->getServer()->isOp($sender->getName())){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for staff only!");
				return false;
			}
			if(isset($this->setup[$sender->getXuid()])){
				$config = new Config($this->getDataFolder()."arenas". DIRECTORY_SEPARATOR .$this->setup[$sender->getXuid()]["world"].".yml", Config::YAML);
				$config->setAll($this->setup[$sender->getXuid()]);
				$config->save();
				$sender->sendMessage(self::PREFIX.TextFormat::GOLD."Now create file /nameworld.zip/ in folder saves!");
				unset($this->setup[$sender->getXuid()]);
			}else{
				$sender->sendMessage(self::PREFIX.TextFormat::RED."You need in mode setup!");
				return false;
			}
			return true;
		}
		if($command->getName() == "swgive"){
			if(!$sender instanceof Player){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for in-game only!");
				return false;
			}
			if(!$this->getServer()->isOp($sender->getName())){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for staff only!");
				return false;
			}
			if(!isset($args[0]) or !isset($args[1]) or !isset($args[2])){
				$sender->sendMessage(self::PREFIX.TextFormat::GREEN."/swgive <xp,coins,souls,tokens> <player> <amount>");
				return false;
			}
			$target = $this->getServer()->getPlayerByPrefix($args[1]);
			if($target == null){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."Player don't exist!");
				return false;
			}
			switch($args[0]){
				case "xp":
				    Ranking::addXp($target, (int)$args[2]);
				    break;
				case "coins":
				    Economy::addCoins($target, (int)$args[2]);
				    break;
				case "souls":
				    Economy::addSouls($target, (int)$args[2]);
				    break;
				case "tokens":
				    Economy::addTokens($target, (int)$args[2]);
				    break;
			}
			return true;
		}
		if($command->getName() == "lobby"){
			if(!$sender instanceof Player){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for in-game only!");
				return false;
			}
			$sender->setGamemode($this->getServer()->getGamemode());	
			$dataPlayer = $this->getPlayer($sender);
			if($dataPlayer->isInGame()){
				$dataArena = $this->arenas[$dataPlayer->getNameArena()];
				if($dataArena instanceof Arenas){	
				    $dataArena->removePlayer($sender, false, true);
				}
			}
			$sender->teleport($this->getServer()->getWorldManager()->getDefaultWorld()->getSpawnLocation());
			$sender->sendMessage(TextFormat::GRAY."§l§6» §r§aSending you to the lobby...");
			return true;
		}
		if($command->getName() == "npc"){
			if(!$sender instanceof Player){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for in-game only!");
				return false;
			}
			if(!$this->getServer()->isOp($sender->getName())){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for staff only!");
				return false;
			}
			if(!isset($args[0])){
				$sender->sendMessage(self::PREFIX.TextFormat::GREEN."§l§6» §r§a/npc <solo,double,ranked,mega,laboratory,triple,topkills,topdeaths,topwins,toplevel,soulwell,questmaster,clearalltop,clearallnpc,clearallsoulwell>");
				return false;
			}
			switch($args[0]){
				case "solo":
				    $entity = new SoloMode($sender->getLocation());
		            $entity->spawnToAll(); 				
				    break;
				case "double":
				    $entity = new DoubleMode($sender->getLocation());
		            $entity->spawnToAll();
				    break;
				case "ranked":
				    $entity = new RankedMode($sender->getLocation());
		            $entity->spawnToAll();
				    break;
				case "mega":
				    $entity = new MegaMode($sender->getLocation());
		            $entity->spawnToAll();
				    break;
				case "laboratory":
				    $entity = new LaboratoryMode($sender->getLocation());
		            $entity->spawnToAll();
				    break;
				case "topkills":
				    $entity = new TopKills($sender->getLocation());
		            $entity->spawnToAll();
				    break;
				case "topdeaths":
				    $entity = new TopDeaths($sender->getLocation());
		            $entity->spawnToAll();
				    break;
				case "topwins":
				    $entity = new TopWins($sender->getLocation());
		            $entity->spawnToAll();
				    break;
				case "toplevel":
				    $entity = new TopLevel($sender->getLocation());
		            $entity->spawnToAll();
				    break;
				case "soulwell":
				    $entity = new SoulWell($sender->getLocation());
		            $entity->spawnToAll();
				    break;
				case "questmaster":
				    $entity = new QuestMaster($sender->getLocation());
		            $entity->spawnToAll();
				    break;
				case "clearalltop":
				    foreach($this->getServer()->getWorldManager()->getWorlds() as $world){
						foreach($world->getEntities() as $entity){
			                if(
							    $entity instanceof TopKills or
								$entity instanceof TopDeaths or
								$entity instanceof TopWins or
								$entity instanceof TopLevel
							){
				                $entity->close();
			                }
					    }
					}
					$sender->sendMessage(self::PREFIX.TextFormat::GREEN."§l§6» §r§aAll entities have cleared!");
				    break;
				case "clearallnpc":
				    foreach($this->getServer()->getWorldManager()->getWorlds() as $world){
						foreach($world->getEntities() as $entity){
			                if(
							    $entity instanceof SoloMode or
								$entity instanceof DoubleMode or
								$entity instanceof RankedMode or
								$entity instanceof MegaMode or
								$entity instanceof LaboratoryMode or
								$entity instanceof SoulWell
							){
				                $entity->close();
			                }
					    }
					}
					$sender->sendMessage(self::PREFIX.TextFormat::GREEN."§l§6» §r§aAll entities have cleared!");
				    break;
				case "clearallsoulwell":
				    foreach($this->getServer()->getWorldManager()->getWorlds() as $world){
						foreach($world->getEntities() as $entity){
			                if($entity instanceof SoulWell){
				                $entity->close();
			                }
					    }
					}
					$sender->sendMessage(self::PREFIX.TextFormat::GREEN."All entities has cleared!");
				    break;
			}
			return true;
		}
		if($command->getName() == "sw"){
			if(!$sender instanceof Player){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for in-game only!");
				return false;
			}
			if(!isset($args[0]) or !isset($args[1])){
				$sender->sendMessage(self::PREFIX.TextFormat::GREEN."/sw <solo,double,triple> <normal,insane,ranked>");
				return false;
			}
			$mode = [self::MODE_NORMAL, self::MODE_INSANE, self::MODE_RANKED, self::MODE_MEGA, self::MODE_LABORATORY];
			if(!in_array($args[0], ["solo", "double", "triple", "normal"]) or !in_array($args[1], $mode)){
				$sender->sendMessage(self::PREFIX.TextFormat::GREEN."/sw <help,solo,double,triple,normal> <normal,insane,ranked,mega,laboratory>");
				return false;
			}
			$mode = null;
			switch($args[1]){
				case "normal":
				    $mode = self::MODE_NORMAL;
				    break;
				case "insane":
				    $mode = self::MODE_INSANE;
				    break;
				case "ranked":				    
				    $mode = self::MODE_RANKED;
				    break;
				case "mega":
				    $mode = self::MODE_MEGA;
				    break;
				case "laboratory":
				    $mode = self::MODE_LABORATORY;
				    break;
			}
			if($args[1] == self::MODE_RANKED){
				$require = $this->getConfig()->get("level-required-for-rating");
			    if(Ranking::getLevel($sender) < $require){
				    $sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cYou need to reach level§e ".$require." §cto join ranked mode! Keep grinding!");
					return false;
				}
			}
			switch($args[0]){
				case "solo":
				    $this->findSoloArenas($sender, $mode);
				    break;
				case "double":
				    $this->findTeamArenas($sender, 2, $mode);
				    break;
				case "triple":
				    $this->findTeamArenas($sender, 3, $mode);
				    break;
				case "normal":
				    if($args[1] == self::MODE_MEGA){
				        $this->findTeamArenas($sender, 5, $mode);
					}else{
						$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis mode is for mega only!");
					}
				    break;
			}			
			return true;
		}
		if($command->getName() == "menu"){
			if(!$sender instanceof Player){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cThis command is for in-game only!");
				return false;
			}
			$dataPlayer = $this->getPlayer($sender);
			if($dataPlayer->isInGame()){
				$sender->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cYou are in-game!");
				return false;
			}
			Form::getMenu($sender);
			return true;
		}
		return false;
	}
	
	/**
	 * @return Cosmetics
	 */
	public function getCosmetics(){
		return new Cosmetics();
	}
	
	/**
	 * @return bool
	 */
	public function isTesting() :bool{
		return $this->testMode;
	}	
	
	/**
	 * @param Player $player
	 * @return bool
	 */
	public function inParty(Player $player) :bool{
		foreach($this->party as $xuid => $data){
			$owner = $data["owner"];
			$members = $data["members"];
			if($owner->getName() == $player->getName()){
				return true;
			}
			foreach($members as $member){
			    $check = $member["player"];
				if($check->getName() == $player->getName()){
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * @param Player $playerA
	 * @param Player $playerB
	 * @return bool
	 */
	public function inPartyByPlayer(Player $playerA, Player $playerB) :bool{
		$partyId = $this->getIdPartyByPlayer($playerA);
		if($this->inParty($playerA)){
			if(isset($this->party[$partyId]["members"][$playerB->getXuid()])){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * @param Player $player
	 * @return int
	 */
	public function getIdPartyByPlayer(Player $player) :int{
		$partyId = null;
		foreach($this->party as $xuid => $data){
			$owner = $data["owner"];
			$members = $data["members"];
			if($owner->getName() == $player->getName()){
				$partyId = $xuid;
			}
			foreach($members as $member){
			    $check = $member["player"];
				if($check->getName() == $player->getName()){
					$partyId = $xuid;
				}
			}
		}
		return $partyId;
	}

	/**
	 * @param Player $player
	 * @return bool
	 */
	public function inPartyChat(Player $player) :bool{
		if(isset($this->partyChat[$player->getXuid()])){
			return true;
		}
		return false;
	}
	
	/**
	 * @param Player $player
	 * @return bool
	 */
	public function isOwnerParty(Player $player) :bool{
		foreach($this->party as $xuid => $data){
			$owner = $data["owner"];
			if($owner->getName() == $player->getName()){
				return true;
			}
		}
		return false;
	}
	
	/**
     * @param Player $player
	 * @return array
	 */
	public function getPlayer(Player $player){
		$playerData = null;
		if(isset($this->players[$player->getXuid()])){
			$playerData = $this->players[$player->getXuid()];
		}
		return $playerData;
	}

	/**
	 * @return int
	 */
	public function getTotalCountMegaPlayers() :int{
	    $count = 0;
		foreach($this->arenas as $t => $k){
			if($k instanceof Arenas){
				if($k->getMaxInTeamCount() > 2 and $k->getMode() == self::MODE_MEGA){
				    $count += $k->getPlayerCount();
				}
			}
		}
		return $count;
	}
	
	/**
	 * @return int
	 */
	public function getCountDoubleArena(string $mode) :int{
	    $count = 0;
		foreach($this->arenas as $t => $k){
			if($k instanceof Arenas){
				if($k->getMaxInTeamCount() == 2 and $k->getMode() == $mode){
				    $count += $k->getPlayerCount();
				}
			}
		}
		return $count;
	}
	
	/**
	 * @return int
	 */
	public function getCountTripleArena(string $mode) :int{
	    $count = 0;
		foreach($this->arenas as $t => $k){
			if($k instanceof Arenas){
				if($k->getMaxInTeamCount() == 3 and $k->getMode() == $mode){
				    $count += $k->getPlayerCount();
				}
			}
		}
		return $count;
	}
	
	/**
	 * @return int
	 */
	public function getTotalCountPlayers(string $mode) :int{
	    $count = 0;
		foreach($this->arenas as $t => $k){
			if($k instanceof Arenas){
				if($k->getMaxInTeamCount() == 1 and $k->getMode() == $mode){
				    $count += $k->getPlayerCount();
				}
			}
		}
		return $count;
	}
	
	/**
	 * @return int
	 */
	public function getCountPlayersInParty(Player $player) :int{
        $count = 0;	    
		if($this->inParty($player)){
			$count += 1; //this is owner
			$count += count($this->party[$player->getXuid()]["members"]);			
		}		
		return $count;
	}
	
	/**
	 * @param Player $player
	 * @return bool
	 */
	public function findRandomArenas(Player $player) :bool{
		if($this->inParty($player)){
			if(!$this->isOwnerParty($player)){
			    $player->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cYou are not the party owner!");
				return false;
			}			
		}
		foreach($this->arenas as $arena){
			if($arena instanceof Arenas){
				if(!$arena->isStarted()){
					$inParty = ($arena->getMaxInTeamCount() * (count($arena->teams))) - $this->getCountPlayersInParty($player);
					if($arena->getPlayerCount() >= 1 and $arena->getPlayerCount() < $inParty){
						$arena->addPlayer($player);
						if($this->inParty($player) and $this->isOwnerParty($player)){
						    foreach($this->party[$this->getIdPartyByPlayer($player)]["members"] as $member){
							    $check = $member["player"];
							    $arena->addPlayer($check);
							}
						}
						return true;
					}
				}				
			}
		}		
		$arena = $this->arenas[array_rand($this->arenas, 1)];
		if($arena instanceof Arenas){
			if(!$arena->isStarted()){
				$arena->addPlayer($player);
				if($this->inParty($player) and $this->isOwnerParty($player)){
					foreach($this->party[$this->getIdPartyByPlayer($player)]["members"] as $member){
						$check = $member["player"];
						$arena->addPlayer($check);
					}
				}
				return true;
			}				
		}
		return false;
	}
	
	/**
	 * @param Player $player
	 * @param string $mode
	 * @return bool
	 */
	public function findSoloArenas(Player $player, string $mode) :bool{
		if($this->inParty($player)){
			if(!$this->isOwnerParty($player)){
			    $player->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cYou are not the party owner!");
				return false;
			}			
		}
		foreach($this->arenas as $arena){
			if($arena instanceof Arenas){
				if(!$arena->isStarted() and $arena->getMode() == $mode){
					$inParty = ($arena->getMaxInTeamCount() * (count($arena->teams))) - $this->getCountPlayersInParty($player);
					if(!$arena->isTeamMode() and $arena->getPlayerCount() >= 1 and $arena->getPlayerCount() < $inParty){
						$arena->addPlayer($player);
						if($this->inParty($player) and $this->isOwnerParty($player)){
						    foreach($this->party[$this->getIdPartyByPlayer($player)]["members"] as $member){
							    $check = $member["player"];
							    $arena->addPlayer($check);
							}
						}
						return true;
					}
				}				
			}
		}
		$arenas = [];
		foreach($this->arenas as $name => $data){
			if($data instanceof Arenas){
				if(!$data->isTeamMode() and !$data->isStarted() and $data->getMode() == $mode){
					$arenas[$name] = $data;					
				}				
			}
		}
		if(count($arenas) >= 1){			
		    $arena = $arenas[array_rand($arenas, 1)];
			$arena->addPlayer($player);
			if($this->inParty($player) and $this->isOwnerParty($player)){
				foreach($this->party[$this->getIdPartyByPlayer($player)]["members"] as $member){
					$check = $member["player"];
					$arena->addPlayer($check);
				}
			}
			return true;
		}
		return false;
	}
	
	/**
	 * @param Player $player
	 * @param int $maxInTeamCount
	 * @param string $mode
	 * @return bool
	 */
	public function findTeamArenas(Player $player, int $maxInTeamCount, string $mode) :bool{
		if($this->inParty($player)){
			if(!$this->isOwnerParty($player)){
			    $player->sendMessage(self::PREFIX.TextFormat::RED."§l§6» §r§cYou are not the party owner!");
				return false;
			}			
		}
		foreach($this->arenas as $arena){
			if($arena instanceof Arenas){
				if(!$arena->isStarted() and $arena->getMode() == $mode){
					$inParty = ($arena->getMaxInTeamCount() * (count($arena->teams))) - $this->getCountPlayersInParty($player);
					if($arena->getMaxInTeamCount() == $maxInTeamCount){
					    if($arena->getPlayerCount() >= 1 and $arena->getPlayerCount() < $inParty){
						    $arena->addPlayer($player);
							if($this->inParty($player) and $this->isOwnerParty($player)){
						        foreach($this->party[$this->getIdPartyByPlayer($player)]["members"] as $member){
							        $check = $member["player"];
							        $arena->addPlayer($check);
								}
							}
						    return true;
						}
					}
				}				
			}
		}
		$arenas = [];
		foreach($this->arenas as $name => $data){
			if($data instanceof Arenas){
				if($data->isTeamMode() and !$data->isStarted() and $data->getMode() == $mode){
					if($data->getMaxInTeamCount() == $maxInTeamCount){
					    $arenas[$name] = $data;		
					}						
				}				
			}
		}
		if(count($arenas) >= 1){			
		    $arena = $arenas[array_rand($arenas, 1)];
			$arena->addPlayer($player);
			if($this->inParty($player) and $this->isOwnerParty($player)){
				foreach($this->party[$this->getIdPartyByPlayer($player)]["members"] as $member){
					$check = $member["player"];
					$arena->addPlayer($check);
				}
			}
			return true;
		}
		return false;
	}
	
	/**
	 * @return array
	 */
	public function getTopKills() :array{
		$getAll = $this->getDataBase()->getAll();
		$top = [];
		foreach($getAll as $data){
			$top[$data["name"]] = $data["kills"];
		}
		arsort($top);
		return $top;
	}
	
	/**
	 * @return array
	 */
	public function getTopDeaths() :array{
		$getAll = $this->getDataBase()->getAll();
		$top = [];
		foreach($getAll as $data){
			$top[$data["name"]] = $data["deaths"];
		}
		arsort($top);
		return $top;
	}
	
	/**
	 * @return array
	 */
	public function getTopWins() :array{
		$getAll = $this->getDataBase()->getAll();
		$top = [];
		foreach($getAll as $data){
			$top[$data["name"]] = $data["wins"];
		}
		arsort($top);
		return $top;
	}
	
	/**
	 * @return array
	 */
	public function getTopLevel() :array{
		$getAll = $this->getDataBase()->getAll();
		$top = [];
		foreach($getAll as $data){
			$top[$data["name"]] = $data["level"];
		}
		arsort($top);
		return $top;
	}
}
