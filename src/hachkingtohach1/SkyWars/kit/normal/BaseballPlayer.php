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
 
namespace hachkingtohach1\SkyWars\kit\normal;

use pocketmine\player\Player;
use pocketmine\item\ItemIds;
use pocketmine\item\ItemFactory;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\data\bedrock\EnchantmentIdMap;

class BaseballPlayer{
	
	public function getName() :string{
		return "Baseball Player";
	}
	
	public function get(Player $player){
		$inventory = $player->getInventory();
		$inventoryArmor = $player->getArmorInventory();
		$itemFactory = new ItemFactory();
		$inventory->addItem($itemFactory->get(ItemIds::ANVIL, 0, 1, null));
		$woodenSword = $itemFactory->get(ItemIds::WOODEN_SWORD, 0, 1, null);
		$woodenSword->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(12), 1));
		$inventory->addItem($woodenSword);
		$helmet = $itemFactory->get(ItemIds::IRON_HELMET, 0, 1, null);
		$helmet->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(0), 1));
		$inventoryArmor->setHelmet($helmet);
	}
}