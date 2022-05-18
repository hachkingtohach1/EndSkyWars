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
 
namespace hachkingtohach1\SkyWars\cosmetics\trails;

use pocketmine\entity\Entity;
use pocketmine\color\Color;
use pocketmine\world\particle\SmokeParticle;
use pocketmine\world\particle\DustParticle;

class WhiteSmoke{

    /**
     * @param Entity $entity
     * @return void
     */
    public static function getParticle(Entity $entity){
	    $entity->getWorld()->addParticle($entity->getLocation()->asVector3(), new SmokeParticle(), $entity->getWorld()->getPlayers());
		$entity->getWorld()->addParticle($entity->getLocation()->asVector3(), new DustParticle(new Color(255, 255, 255)), $entity->getWorld()->getPlayers());
	}
}