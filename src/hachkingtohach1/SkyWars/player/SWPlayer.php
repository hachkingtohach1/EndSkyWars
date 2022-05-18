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

namespace hachkingtohach1\SkyWars\player;

use pocketmine\player\Player;

/**
 * Custom plugin player class, contains:
 * 1. Info about player's arena and team
 * 2. Sets default options on spawn
 */
class SWPlayer{
	/*@var Player*/
	private ?Player $player;
	/*@var bool*/
	private bool $inGame = false;
	/*@var string*/
	private string $nameArena = "";
	/*@var string*/
	private string $team;
	/*@var string*/
	private string $attacker = "";
	/*@var string*/
	private string $hasTarget = "";
	/*@var int*/
	private int $lastAttack = 0;
	/*@var int*/
	private int $hasTargetTime = 0;
	
	/**
	 * construct of SkyWars class, 
	 * creates arena and sign for it
	 *
	 * @param Player $player
	 */
    public function __construct(Player $player){
		$this->player = $player;
    }
	
	/**
	 * @return Player
	 */
	public function getPlayer() :Player{
		return $this->player;
	}
	
	/**
	 * @return bool
	 */
	public function isInGame() :bool{
		return $this->inGame;
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
	public function getTeam() :string{
		return $this->team;
	}
	
	/**
	 * @return string
	 */
	public function getAttacker() :string{
		return $this->attacker;
	}
	
	/**
	 * @return string
	 */
	public function getHasTarget() :string{
		return $this->hasTarget;
	}
	
	/**
	 * @return int
	 */
	public function getLastAttack() :int{
		return $this->lastAttack;
	}
	
	/**
	 * @return int
	 */
	public function getHasTargetTime() :int{
		return $this->hasTargetTime;
	}
	
	/**
	 * @param bool $value
	 */
	public function setInGame(bool $value = false){
		$this->inGame = $value;
	}
	
	/**
	 * @param string $name
	 */
	public function setNameArena(string $name){
		$this->nameArena = $name;
	}
	
	/**
	 * @param string $team
	 */
	public function setTeam(string $team){
		$this->team = $team;
	}
	
	/**
	 * @param string $name
	 */
	public function setAttacker(string $name){
		$this->attacker = $name;
	}
	
	/**
	 * @param string $name
	 */
	public function setHasTarget(string $name){
		$this->hasTarget = $name;
	}
	
	/**
	 * @param int $time
	 */
	public function setLastAttack(int $time){
		$this->lastAttack = $time;
	}
	
	/**
	 * @param int $time
	 */
	public function setHasTargetTime(int $time){
		$this->hasTargetTime = $time;
	}
}