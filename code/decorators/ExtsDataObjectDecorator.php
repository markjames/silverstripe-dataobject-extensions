<?php

/**
 * A Decorator which adds additional utility methods to DataObjects
 *
 * @package		dataobject-extensions
 * @author		Mark James <mail@mark.james.name>
 * @copyright	2011 - Mark James
 * @license		New BSD License
 * @link		http://github.com/markjames/silverstripe-fieldextensions
 * 
 * Copyright (c) 2011, Mark James
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 * 
 *     * Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 * 
 *     * Redistributions in binary form must reproduce the above copyright notice,
 *       this list of conditions and the following disclaimer in the documentation
 *       and/or other materials provided with the distribution.
 * 
 *     * Neither the name of Zend Technologies USA, Inc. nor the names of its
 *       contributors may be used to endorse or promote products derived from this
 *       software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
class ExtsDataObjectDecorator extends Extension {

	/**
	* Adds first/last/start/end class to a list item.
	*
	* Sometimes you need to:
	* Lay out a load of thumbnails in a grid.
	* Style only the first or last elements in a vertical/horizontal list of items
	*
	* You can do this using nth-child pseudoselectors, but it is dangerous.
	*
	* Instead, on any list of elements:
	*
	* - add a _first_ class to the very first one
	* - add a _last_ class to the very last one
	* - add a _start_ class to the first item on a row (e.g. every 4th item, starting with the first one)
	* - add an _end_ class to the last item on a row (e.g. every 4th item, starting with the fourth one)
	*
	* @param $rowLength length of each row
	* @return string list class
	*/
	public function ListClass( $rowLength=2 ) {

		$classes = $this->owner->FirstLast();
		if ($this->owner->Modulus($rowLength, 1)) {
			$classes .= ' end';
		} else if ($this->owner->Modulus($rowLength, 0)) {
			$classes .= ' start';
		}

		return trim($classes,' ');

	}

}