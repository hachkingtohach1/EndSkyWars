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

namespace hachkingtohach1\SkyWars\provider\sql;

use mysqli;
use pocketmine\player\Player;
use hachkingtohach1\SkyWars\SkyWars;
use hachkingtohach1\SkyWars\ranking\Ranking;
use hachkingtohach1\SkyWars\provider\DataBase;

class SQL implements DataBase{
    /*@var mysqli*/
    private ?mysqli $db;
	/*@var SkyWars*/
    private ?SkyWars $plugin;
	/*@var string*/
    public string $dbName;

    /**
     * @param string $dbName
     */
    public function __construct(string $dbName){
	    $this->plugin = SkyWars::getInstance();
        $this->dbName = $dbName;
        $config = $this->plugin->getConfig()->getNested("SkyWars-SQL");
        $this->db = new mysqli(
			["Host"] ?? "127.0.0.1",
			["User"] ?? "BlossomCo",
			["Password"] ?? "",
			["Database"] ?? "skywars",
			["Port"] ?? 3306
		);			
		if($this->db->connect_error){
			$this->plugin->getLogger()->critical("Could not connect to MySQL server: ".$this->db->connect_error);
			return;
		}
		
		if(!$this->db->query("CREATE TABLE if NOT EXISTS user_profile(
			    xuid VARCHAR(50) PRIMARY KEY,
				username VARCHAR(50),
				deaths FLOAT,
				kills FLOAT,
				assists FLOAT,
				wins FLOAT,
				level FLOAT,
				xp FLOAT,
				tokens FLOAT,
				coins FLOAT,
				souls FLOAT,
				now VARCHAR(500),
				kitnormal VARCHAR(500),
				kitinsane VARCHAR(500),
				kitranked VARCHAR(500),
				kitmega VARCHAR(500),
				dailyquest VARCHAR(500),
				weeklyquest VARCHAR(500),
				rating FLOAT
		    );"
		)){
		    $this->plugin->getLogger()->critical("Error creating table: " . $this->db->error);
		    return;
		}		
    }

    /**
     * @return string
     */
    public function getDatabaseName(): string{ return $this->dbName; }

    /**
     * @return void
     */
    public function close(): void{}

    /**
     * @return void
     */
    public function reset(): void{}

    /**
     * @param string $name
     * @return bool
     */
    public function accountExists(string $name){
		$result = $this->db->query("SELECT * FROM user_profile WHERE xuid='".$this->db->real_escape_string($name)."'");
		return $result->num_rows > 0 ? true:false;
	}

    /**
     * @param Player $player
     * @return bool
     */
    public function createProfile(Player $player) :bool{
		if($player instanceof Player){
			$xuid = $player->getXuid();
		}
		$xuid = strtolower($xuid);
		$namePlayer = $player->getName();
		if(!$this->accountExists($xuid)){
			$this->db->query("INSERT INTO user_profile (
			    xuid,
				username,
				deaths,
				kills,
				assists,
				wins,
				level,
				xp, 
				tokens,
				coins,
				souls,
				now, 
				kitnormal,
				kitinsane,
				kitranked,
				kitmega,
				dailyquest,
				weeklyquest,
				rating
			)
			VALUES ('".$this->db->real_escape_string($xuid)."', 
			    '$namePlayer',
			    0.0,
			    0.0,
				0.0,
			    0.0,
				0.0,
				0.0,
				0.0,
				0.0,
				0.0,
				'cosmetics',
				'First',
				'First',
				'First',
				'First',
				'none',
				'none',
				1000.0
			);");
			return true;
		}
		return false;
	}

    /**
     * @param Player $player
     * @return bool
     */
    public function removeProfile(Player $player) :bool{
		if($player instanceof Player){
			$player = $player->getXuid();
		}
		$player = strtolower($player);
		if($this->db->query("DELETE FROM user_profile WHERE xuid='".$this->db->real_escape_string($player)."'") === true) return true;
		return false;
	}

    /**
     * @param Player $player
     * @return float
     */
    public function getDeaths(Player $player) :float{
		if($player instanceof Player){
			$player = $player->getXuid();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT deaths FROM user_profile WHERE xuid='".$this->db->real_escape_string($player)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
	}
	
	/**
     * @param Player $player
	 * @param float $amount
     * @return float
     */
	public function addDeaths(Player $player, float $amount) :float{
		$calculate = $this->getDeaths($player) + $amount;
		if($player instanceof Player){
			$player = strtolower($player->getXuid());
		}
		return $this->db->query("UPDATE user_profile SET deaths = $calculate WHERE xuid='".$this->db->real_escape_string($player)."'");
	}

    /**
     * @param Player $player
     * @return float
     */
    public function getKills(Player $player) :float{
		if($player instanceof Player){
			$player = $player->getXuid();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT kills FROM user_profile WHERE xuid='".$this->db->real_escape_string($player)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
	}
	
	/**
     * @param Player $player
	 * @param float $amount
     * @return float
     */
	public function addKills(Player $player, float $amount) :float{
		$calculate = $this->getKills($player) + $amount;
		if($player instanceof Player){
			$player = strtolower($player->getXuid());
		}
		return $this->db->query("UPDATE user_profile SET kills = $calculate WHERE xuid='".$this->db->real_escape_string($player)."'");
	}
	
	/**
     * @param Player $player
     * @return float
     */
    public function getAssists(Player $player) :float{
		if($player instanceof Player){
			$player = $player->getXuid();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT assists FROM user_profile WHERE xuid='".$this->db->real_escape_string($player)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
	}
	
	/**
     * @param Player $player
	 * @param float $amount
     * @return float
     */
	public function addAssists(Player $player, float $amount) :float{
		$calculate = $this->getAssists($player) + $amount;
		if($player instanceof Player){
			$player = strtolower($player->getXuid());
		}
		return $this->db->query("UPDATE user_profile SET assists = $calculate WHERE xuid='".$this->db->real_escape_string($player)."'");
	}
	
	/**
     * @param Player $player
     * @return float
     */
    public function getWins(Player $player) :float{
		if($player instanceof Player){
			$player = $player->getXuid();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT wins FROM user_profile WHERE xuid='".$this->db->real_escape_string($player)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
	}
	
	/**
     * @param Player $player
	 * @param float $amount
     * @return float
     */
	public function addWins(Player $player, float $amount) :float{
		$calculate = $this->getWins($player) + $amount;
		if($player instanceof Player){
			$player = strtolower($player->getXuid());
		}
		return $this->db->query("UPDATE user_profile SET wins = $calculate WHERE xuid='".$this->db->real_escape_string($player)."'");
	}
	
	/**
     * @param Player $player
     * @return float
     */
    public function getLevel(Player $player) :float{
		if($player instanceof Player){
			$player = $player->getXuid();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT level FROM user_profile WHERE xuid='".$this->db->real_escape_string($player)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
	}
	
	/**
     * @param Player $player
	 * @param float $amount
     * @return float
     */
	public function setLevel(Player $player, float $amount) :float{
		if($player instanceof Player){
			$player = strtolower($player->getXuid());
		}
		return $this->db->query("UPDATE user_profile SET level = $amount WHERE xuid='".$this->db->real_escape_string($player)."'");
	}
	
	/**
     * @param Player $player
     * @return float
     */
    public function getXp(Player $player) :float{
		if($player instanceof Player){
			$player = $player->getXuid();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT xp FROM user_profile WHERE xuid='".$this->db->real_escape_string($player)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
	}
	
	/**
     * @param Player $player
	 * @param float $amount
     * @return float
     */
	public function setXp(Player $player, float $amount) :float{
		if($player instanceof Player){
			$player = strtolower($player->getXuid());
		}
		return $this->db->query("UPDATE user_profile SET xp = $amount WHERE xuid='".$this->db->real_escape_string($player)."'");
	}
	
	/**
     * @param Player $player
     * @return float
     */
    public function getTokens(Player $player) :float{
		if($player instanceof Player){
			$player = $player->getXuid();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT tokens FROM user_profile WHERE xuid='".$this->db->real_escape_string($player)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
	}
	
	/**
     * @param Player $player
	 * @param float $amount
     * @return float
     */
	public function setTokens(Player $player, float $amount) :float{
		if($player instanceof Player){
			$player = strtolower($player->getXuid());
		}
		return $this->db->query("UPDATE user_profile SET tokens = $amount WHERE xuid='".$this->db->real_escape_string($player)."'");
	}
	
	/**
     * @param Player $player
     * @return float
     */
    public function getCoins(Player $player) :float{
		if($player instanceof Player){
			$player = $player->getXuid();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT coins FROM user_profile WHERE xuid='".$this->db->real_escape_string($player)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
	}
	
	/**
     * @param Player $player
	 * @param float $amount
     * @return float
     */
	public function setCoins(Player $player, float $amount) :float{
		if($player instanceof Player){
			$player = strtolower($player->getXuid());
		}
		return $this->db->query("UPDATE user_profile SET coins = $amount WHERE xuid='".$this->db->real_escape_string($player)."'");
	}
	
	/**
     * @param Player $player
     * @return float
     */
    public function getSouls(Player $player) :float{
		if($player instanceof Player){
			$player = $player->getXuid();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT souls FROM user_profile WHERE xuid='".$this->db->real_escape_string($player)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
	}
	
	/**
     * @param Player $player
	 * @param float $amount
     * @return float
     */
	public function setSouls(Player $player, float $amount) :float{
		if($player instanceof Player){
			$player = strtolower($player->getXuid());
		}
		return $this->db->query("UPDATE user_profile SET souls = $amount WHERE xuid='".$this->db->real_escape_string($player)."'");
	}
	
	/**
     * @param Player $player
     * @return string
     */
    public function getUsing(Player $player) :string{
		if($player instanceof Player){
			$player = $player->getXuid();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT now FROM user_profile WHERE xuid='".$this->db->real_escape_string($player)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
	}
	
	/**
     * @param Player $player
	 * @param string $data
     * @return string
     */
	public function setUsing(Player $player, string $data){
		if($player instanceof Player){
			$player = strtolower($player->getXuid());
		}
		return $this->db->query("UPDATE user_profile SET now = '$data' WHERE xuid='".$this->db->real_escape_string($player)."'");
	}
	
	/**
     * @param Player $player
     * @return string
     */
    public function getKitNormal(Player $player) :string{
		if($player instanceof Player){
			$player = $player->getXuid();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT kitnormal FROM user_profile WHERE xuid='".$this->db->real_escape_string($player)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
	}
	
	/**
     * @param Player $player
	 * @param string $data
     * @return string
     */
	public function setKitNormal(Player $player, string $data){
		if($player instanceof Player){
			$player = strtolower($player->getXuid());
		}
		return $this->db->query("UPDATE user_profile SET kitnormal = '$data' WHERE xuid='".$this->db->real_escape_string($player)."'");
	}
	
	/**
     * @param Player $player
     * @return string
     */
    public function getKitInsane(Player $player) :string{
		if($player instanceof Player){
			$player = $player->getXuid();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT kitinsane FROM user_profile WHERE xuid='".$this->db->real_escape_string($player)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
	}
	
	/**
     * @param Player $player
	 * @param string $data
     * @return string
     */
	public function setKitInsane(Player $player, string $data){
		if($player instanceof Player){
			$player = strtolower($player->getXuid());
		}
		return $this->db->query("UPDATE user_profile SET kitinsane = '$data' WHERE xuid='".$this->db->real_escape_string($player)."'");
	}
	
	/**
     * @param Player $player
     * @return string
     */
    public function getKitRanked(Player $player) :string{
		if($player instanceof Player){
			$player = $player->getXuid();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT kitranked FROM user_profile WHERE xuid='".$this->db->real_escape_string($player)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
	}
	
	/**
     * @param Player $player
	 * @param string $data
     * @return string
     */
	public function setKitRanked(Player $player, string $data){
		if($player instanceof Player){
			$player = strtolower($player->getXuid());
		}
		return $this->db->query("UPDATE user_profile SET kitranked = '$data' WHERE xuid='".$this->db->real_escape_string($player)."'");
	}
	
	/**
     * @param Player $player
     * @return string
     */
    public function getKitMega(Player $player) :string{
		if($player instanceof Player){
			$player = $player->getXuid();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT kitmega FROM user_profile WHERE xuid='".$this->db->real_escape_string($player)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
	}
	
	/**
     * @param Player $player
	 * @param string $data
     * @return string
     */
	public function setKitMega(Player $player, string $data){
		if($player instanceof Player){
			$player = strtolower($player->getXuid());
		}
		return $this->db->query("UPDATE user_profile SET kitmega = '$data' WHERE xuid='".$this->db->real_escape_string($player)."'");
	}
	
	/**
     * @param Player $player
     * @return string
     */
    public function getDailyQuest(Player $player) :string{
		if($player instanceof Player){
			$player = $player->getXuid();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT dailyquest FROM user_profile WHERE xuid='".$this->db->real_escape_string($player)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
	}
	
	/**
     * @param Player $player
	 * @param string $data
     * @return string
     */
	public function setDailyQuest(Player $player, string $data){
		if($player instanceof Player){
			$player = strtolower($player->getXuid());
		}
		return $this->db->query("UPDATE user_profile SET dailyquest = '$data' WHERE xuid='".$this->db->real_escape_string($player)."'");
	}
	
	/**
     * @param Player $player
     * @return string
     */
    public function getWeeklyQuest(Player $player) :string{
		if($player instanceof Player){
			$player = $player->getXuid();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT weeklyquest FROM user_profile WHERE xuid='".$this->db->real_escape_string($player)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
	}
	
	/**
     * @param Player $player
	 * @param string $data
     * @return string
     */
	public function setWeeklyQuest(Player $player, string $data){
		if($player instanceof Player){
			$player = strtolower($player->getXuid());
		}
		return $this->db->query("UPDATE user_profile SET weeklyquest = '$data' WHERE xuid='".$this->db->real_escape_string($player)."'");
	}
	
	/**
     * @param Player $player
     * @return string
     */
    public function getRating(Player $player) :float{
		if($player instanceof Player){
			$player = $player->getXuid();
		}
		$player = strtolower($player);
		$res = $this->db->query("SELECT rating FROM user_profile WHERE xuid='".$this->db->real_escape_string($player)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
	}
	
	/**
     * @param Player $player
	 * @param int $amount
     * @return float
     */
	public function setRating(Player $player, int $amount){
		if($player instanceof Player){
			$player = strtolower($player->getXuid());
		}
		return $this->db->query("UPDATE user_profile SET rating = $amount WHERE xuid='".$this->db->real_escape_string($player)."'");
	}
	
	/**
	 * @param string $data
     */
	public function setDailyQuestAll(string $data){
		$res = $this->db->query("SELECT * FROM user_profile");
		foreach($res->fetch_all() as $val){
			$this->db->query("UPDATE user_profile SET dailyquest = '$data' WHERE xuid='".$val[0]."'");
		}
	}
	
	/**
	 * @param string $data
     */
	public function setWeeklyQuestAll(string $data){
		$res = $this->db->query("SELECT * FROM user_profile");
		foreach($res->fetch_all() as $val){
			$this->db->query("UPDATE user_profile SET weeklyquest = '$data' WHERE xuid='".$val[0]."'");
		}
	}
	
	/**
	 * @param string $data
     */
	public function resetRatingAll(){
		$res = $this->db->query("SELECT * FROM user_profile");
		foreach($res->fetch_all() as $val){
			$rating = Ranking::getDownRanking($val[17]);
			$this->db->query("UPDATE user_profile SET rating = $rating WHERE xuid='".$val[0]."'");
		}
	}
	
	/**
	 * @return array
	 */
	public function getAll(){
		$res = $this->db->query("SELECT * FROM user_profile");
		$ret = [];
		foreach($res->fetch_all() as $val){
			$ret[] = [
			    "xuid" => $val[0],
				"name" => $val[1],
				"deaths" => $val[2],
				"kills" => $val[3],
				"assists" => $val[4],
				"wins" => $val[5],
				"level" => $val[6],
				"xp" => $val[7],
				"tokens" => $val[8],
				"coins" => $val[9],
				"souls" => $val[10],
				"now" => $val[11],
				"kitnormal" => $val[12],
				"kitinsane" => $val[13],
				"kitranked" => $val[14],
				"kitmega" => $val[15],
				"dailyquest" => $val[16],
				"weeklyquest" => $val[17],
				"rating" => $val[18]
			];
		}
		$res->free();
		return $ret;
	}
}
