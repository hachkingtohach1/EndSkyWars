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
 
namespace hachkingtohach1\SkyWars\form;

use pocketmine\Server;
use pocketmine\player\Player;
use Vecnavium\FormsUI\SimpleForm;

class CosmeticsForm{

    /**
     * @param Player $player
     * @return mixed
     */
    public static function getForm(Player $player){
	    $form = new SimpleForm(function(Player $player, int $data = null){
		    $result = $data;
		    if($result === null){
			    return true;
			}
		    switch($result){				
                case "0";
                    Form::getCages($player);				
			        break;
                case "1";		
                    Form::getTrails($player);				
			        break;					
                default: break;					
			}
		});
		$form->setTitle("§l§aCosmetics");
		$form->addButton("§8Cages", 0, "");
		$form->addButton("§8Trails", 0, "");
		$form->sendToPlayer($player);
		return $form;
	}
}
