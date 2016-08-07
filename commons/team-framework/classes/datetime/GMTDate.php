<?php
/**
New Licence bsd:
Copyright (c) <2014>, Manuel Jesus Canga Muñoz
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of the trasweb.net nor the
      names of its contributors may be used to endorse or promote products
      derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL Manuel Jesus Canga Muñoz BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

*/

namespace team\datetime;




class GMTDate extends \team\Date {

	/**
	* Retrieve the current GMT time based on specified format.
	*
	* The 'database' format will return the time in the format for Database DATETIME field.
	* The 'timestamp' type will return the current timestamp.
	 * Other strings will be interpreted as PHP date formats (e.g. 'Y-m-d').
	 *
	 * @param string   $format Format of time to retrieve. Accepts 'database', 'timestamp', or 
	 * PHP strftime format string (e.g. '%d/%m/%Y').
	 * @param int $offset Optional. offset gmt
	 * @return int|string Integer if $type is 'timestamp', string otherwise.
	 */
	static function current($format = 'timestamp', $offset = null) {
		if(!isset($offset) || !is_numeric($offset) ) {
			$offset = \team\Context::getOption( 'gmt_offset',0 );
		}

		 $gmt_time = time() + $offset* self::AN_HOUR;

		  switch ( $format ) {
			case 'database':
				return gmdate( 'Y-m-d H:i:s',   $gmt_time );
			case 'timestamp':
				return  $gmt_time;
			default:
				return self::convert( $gmt_time, $format );
		 }
	}


	/**
	 * Retrieve time from i18n date 
	 *
	 *
	 *
	 **/

	static function toTime( $date, $format = null) {
		$datetime = \team\Date::split($date, $format);

		if(empty($datetime) ) {
			return false;
		}

		extract($datetime, EXTR_SKIP);
		
		return gmmktime($H, $M, $S, $m, $d, $Y);
	}


	/**
     * Change $date from format $format_start to format $end
	 **/
	static function transform($date, $format_start, $format_end = null) {
		$timestamp = self::toTime($date,  $format_start);
		return self::convert($timestamp, $format_end);
	}


}
