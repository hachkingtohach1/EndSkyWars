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

namespace hachkingtohach1\SkyWars\math;

class CaculateTime{
    
	/**
	 * @param int $time
	 * @return array
	 */
	public static function remainingTime(int $time) :bool{
		$timeNow = time();
		if($time > $timeNow){	
			$timeRemaining = $time - $timeNow;
			$day = floor($timeRemaining / 86400);
			$hourSeconds = $timeRemaining % 86400;
			$hour = floor($hourSeconds / 3600);
			$minuteSec = $hourSeconds % 3600;
			$minute = floor($minuteSec / 60);
			$secW = $minuteSec % 60;
			$second = ceil($secW);
			if($day >= 1){
				$expiration = true;				
			}else{
				if($hour >= 1){
					$expiration = true;			
				}else{
					if($minute >= 1){
						$expiration = true;			
					}elseif($second >= 1){
						$expiration = true;			
					}else{
					    $expiration = false;
					}
				}
			} 
            return $expiration;			
		}
		return false;
	}
}