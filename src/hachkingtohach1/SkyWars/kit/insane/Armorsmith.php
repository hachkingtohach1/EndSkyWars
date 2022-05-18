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
 
namespace hachkingtohach1\SkyWars\kit\insane;

use pocketmine\player\Player;
use pocketmine\item\ItemIds;
use pocketmine\item\ItemFactory;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\data\bedrock\EnchantmentIdMap;

class Armorsmith{
	
	public function getName() :string{
		return "Armorsmith";
	}
	
	public function get(Player $player){
		$inventory = $player->getInventory();
		$inventoryArmor = $player->getArmorInventory();
		$itemFactory = new ItemFactory();
		$inventory->addItem($itemFactory->get(ItemIds::ANVIL, 0, 1, null));
		$enchantedBook = $itemFactory->get(ItemIds::ENCHANTED_BOOK, 0, 1, null);
		$enchantedBook->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(0), 3));
		$enchantedBook->addEnchantment(new EnchantmentInstance(EnchantmentIdMap::getInstance()->fromId(9), 1));
		$inventory->addItem($enchantedBook);
		$inventoryArmor->setHelmet($itemFactory->get(ItemIds::DIAMOND_HELMET, 0, 1, null));
		$inventory->addItem($itemFactory->get(ItemIds::EXPERIENCE_BOTTLE, 0, 64, null));
	}
}