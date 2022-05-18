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
 
namespace hachkingtohach1\SkyWars\world\sound;

use pocketmine\math\Vector3;
use pocketmine\world\sound\Sound;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\types\BlockPosition;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

class NoteBlockSound implements Sound{
    /*@var int*/
    private int $note;

    /**
     * @param int $note
     */
    public function __construct(int $note){
        $this->note = $note;
    }

    /**
     * @param Vector3 $vector
     * @return array|\pocketmine\network\mcpe\protocol\ClientboundPacket[]
     */
    public function encode(Vector3 $vector):array{
        $pk = new BlockEventPacket();
        $pk->blockPosition = new BlockPosition($vector->x, $vector->y, $vector->z);
        $pk->eventType = 0;
        $pk->eventData = $this->note;
        $pk2 = new LevelSoundEventPacket();
        $pk2->sound = 81;
        $pk2->position = $vector;
        $pk2->extraData = 0 | $this->note;
        return [$pk, $pk2];
    }
}
