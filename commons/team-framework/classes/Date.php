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

namespace team;




class Date {
    /* a minute in seconds */
	const A_MINUTE = 60;
    /* an hour in seconds */
    const AN_HOUR   = 60 * self::A_MINUTE;
    /* a day in seconds */
    const A_DAY    = 24 * self::AN_HOUR;
    /* a week in seconds */
    const A_WEEK   = 7 * self::A_DAY;
    /* a month in seconds */
    const A_MONTH  = 30  * self::A_DAY;
    /* a year in seconds */
    const A_YEAR   = 365 * self::A_DAY;

    /** Minutes in seconds  */
    public function minutes($minutes = 1) { return $minutes*self::A_MINUTE; }
    /** Hours in seconds  */
    public function hours($hours = 1) { return $hours*self::AN_HOUR; }
    /** Days in seconds  */
    public function days($days = 1) { return $days*self::A_DAY;}
    /** Weeks in seconds  */
    public function weeks($weeks = 1) { return $weeks*self::A_WEEK; }
    /** Months in seconds  */
    public function months($months = 1) { return $months*self::A_MONTH; }
    /** Years in seconds  */
    public function years($years = 1) { return $years*self::A_YEAR; }

	/** 
		Changing time using human words(  e.g: 3 weeks, 1 month, and son on ) to seconds 
		@param string|numeric $time_expression_human time in human language
		@return int  seconds according to $time_expression_human
	*/
	public static function strToTime($time_expresion_human) {
        if(is_numeric($time_expresion_human)) {
            return $time_expresion_human;
        }

		$time_expresion_human = '+'.ltrim($time_expresion_human, '+'); //2 hours, 1 week, ...
        $time =  strtotime($time_expresion_human) - time();

		return $time;
	}


	/**
	* Retrieve the current time based on specified format.
	*/
	public static function current($format = null) {
		return self::convert( null, $format );
	}


	public static function getUnits() {
		return \team\Filter::apply('\team\date\units', [ 
			self::A_YEAR  =>  ['a&ntilde;o', 'a&ntilde;os'], 
			self::A_MONTH => ['mes','meses'],
			self::A_WEEK => ['semana', 'semanas'], 
			self::A_DAY => ['d&iacute;a', 'd&iacute;as'], 
			self::AN_HOUR => ['hora', 'horas'],
			self::A_MINUTE => ['minuto', 'minutos'],
			1 => ['segundo', 'segundos'],
		 ] );
	}


	/**
	*	Diff from a date until now
	*/
	public static function diff($date, $from_format  = null, $with_units = true) {
		$diff = [];

        $diff['isPast'] = $diff['isFuture'] = $diff['isToday'] = $diff['isNull'] =false;

		$date_in_seconds = self::toTime($date, $from_format);

		if(0 === $date_in_seconds) {
            $diff['isNull'] = true;
        }

		$now = time();		

		$diff['diff'] = $date_in_seconds - $now;

		$seconds = abs($diff['diff']);

		//comparando la fecha con respecto al día de hoy.
		$today = self::dayStartEnd( );

		//¿ Es la fecha pasada anterior al día de hoy ?
		if($seconds < $today['start']) {
			$diff['isPast'] = true; 

		//¿ Es la fecha pasada posterior al día de hoy ?
		}else if($seconds > $today['end']) {
			$diff['isFuture'] = true; 

		//¿ Es la fecha pasada el día de hoy ?
		}else {
			$diff['isToday'] = true; 
		}

		//Si no se quería desglose por unidades, se sale
		if(!$with_units) return $diff;

		$units = self::getUnits();
		
		foreach($units as $seconds_in_unit =>  $unit) {
			if($seconds > $seconds_in_unit) {
				$count_units = (int) floor($seconds/$seconds_in_unit);
				$label = ($count_units > 1)? $unit[1] /* plural */: $unit[0] /* singular*/;
				$diff['units'][$label] = $count_units;
				$seconds =  $seconds - $count_units *$seconds_in_unit;
			}
		}


		return $diff;
	}

	/**
		Transform a day of week in leters(e.g: Miércoles in its position( 3)
	*/
	public static function getDayOfWeek($day) {
		if(empty($day) ) return false;

		return array_search(ucfirst(strtolower($day)), self::getDaysOfWeek());
	}


	/**
	* Retrieve days of week.
	* @return array with days of week
	*/
	public static function  getDaysOfWeek() {
		$days_of_week = [1=>'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo' ];

		return  \team\Filter::apply('\team\days_of_week', $days_of_week  );
	}

	/**
		Transform a month in leters(e.g: Abril in its position( 4 ) /
	*/
	public static function getMonth($month) {
			if(empty($month) ) return false;

			$months =  self::getMonths();
			if(is_numeric($month) ) {
					if(isset($months[$month]) ) 
						return $months[$month];
					else
						return null;
			}else {
		   		return array_search(ucfirst(strtolower($month)),$months);
			}
	}

	/**
	* Retrieve months.
	* @return array with months
	*/
	public static function getMonths() {
		$months = [ 1 =>'Enero', 'Febrero', 'Marzo', 'Abril','Mayo','Junio','Julio',
				'Agosto','Sepiembre', 'Octubre','Noviembre','Diciembre'];

		return  \team\Filter::apply('\team\months', $months  );
	}


	/**
	 * Get the week start and end from the datetime or date string from timestamp
	 *
	 *
	 * @param string     $timestamp   Timestamp of a day
	 * @param int|string $start_of_week Optional. Start of the week as an integer(offset from first day). Default empty string.
	 * @return array Keys are 'start' and 'end'.
	 */
	public static function weekStartEnd( $timestamp, $start_of_week = '' ) {
		$day = $timestamp;

		// The day of the week from the timestamp.
		$weekday = date( 'w', $day );

		if ( !is_numeric($start_of_week) )
			$start_of_week = \team\Context::get( 'START_OF_WEEK', 0, '\team\Date' );

		if ( $weekday < $start_of_week )
			$weekday += 7;

		$days_betweet_day_until_first_day = $weekday - $start_of_week; // Miercoles(3) - Lunes(1)
		$time_from_first_day_of_week =  self::days($days_betweet_day_until_first_day );

		// The most recent week start day on or before $day. //$day(24 abril Miercoles) - tiempo de 3 días = tiempo del día que comienza
		$start = $day - $time_from_first_day_of_week ;

		// $start + 1 week - 1 second. ej: tiempo lunes + tiempo de una semana = tiempo del domingo a las 00.00
		$end = $start + sef::A_WEEK - 1;

		return compact( 'start', 'end' );
	}

	/**
	 * Get the today start and end timestamp from current datetime  or from timestamp
	 *
	 *
	 * @param string     $timestamp   Timestamp of a day
	 * @return array Keys are 'start' and 'end'.
	 */
	public static function dayStartEnd( $timestamp  = null) {
		if(isset($timestamp) ) {
			$start = strtotime(self::convert($timestamp,'mysql') );
		}else {
			$start = strtotime('Today');
		}

		// $start + 1 day - 1 second. ej: tiempo de comienzo del día + tiempo de un día  - 1 segundo para obtener el timestamp de: 23 minutos 59 segundos 
		$end = $start + self::A_DAY - 1;

		return compact( 'start', 'end' );
	}


	/**
	 * Convert given date string into a different format.
	 *
	 * $format should be either a PHP date format string or 'timestamp' or null( for i18n format )
	 *
	 * If $translate is true then the given date and format string will
	 * be passed to date_i18n format for translation.

	 * @param string $date      Date string to convert.
	 * @param string $format    Format of the date to return.
	 * @return string|int|bool Formatted date string or Unix timestamp. False if $date is empty.
	 */
	public static function fromDatabase( $date, $new_format) {
		if ( empty( $date ) )
			return false;

		return self::transform($date, 'database', $new_format);
	}

	/**
 	 * Parses English $time( from $timestamp ) and converts to $to_format
	 * e.g. \ team\Date::get('+2 days', 'day'); //result 'wednesday' if today is 'monday'
     * e.g2:  \team\Date::get('-1 Week')
     **/
	public static function get($change, $to_format =  'timestamp', $timestamp = null) {
		$timestamp = $timestamp?: time();
		$new_date_timestamp = strtotime($change, $timestamp);

		return self::convert($new_date_timestamp, $to_format);
	}

    /**
     * Parses English $time( from $date )
     **/
    public static function change($change, $date, $format = null, $format_out = null) {
        $timestamp = self::toTime($date, $format);

        if(!isset($format_out)) {
            $format_out = $format;
        }

        return self::get($change, $format_out, $timestamp);
    }

	/**
	 * Retrieve time from $date with format $from_format
	 * Si es null devuelve 0
     * Si es erronea devuelve false
	 **/
	public static function toTime( $date, $from_format  = null) {
		$datetime = self::split($date, $from_format );

        if(self::checkIsNull($datetime)) {
            return 0;
        }

        if(empty($datetime) ) {
            return false;
        }



		extract($datetime, EXTR_SKIP);

        if(isset($Y) && ! checkdate (  $m  , $d , $Y )) return false;

		return mktime($H, $M, $S, $m, $d, $Y);
	}

	/**
	 * Comprueba si la fecha $date con formato $with_format está correcta.
	 * Si lo es se devuelve $date sino se devuelve $default. Si es null devuelve 0
	 */
	public static function check($date, $with_format = null, $default = false) {
		$datetime = self::split($date,  $with_format, $with_default = false);


		if(self::checkIsNull($datetime) ) {
		    return $default;
        }

        if(!$datetime) {
			return $default;
		}


        extract($datetime, EXTR_SKIP);
		if(isset($Y) && isset($m) && isset($d) &&! checkdate (  $m  , $d , $Y )) return $default;

		if(isset($H) && ( $H<0 || $H>23 ) ) return $default;
		if(isset($m) && ( $m<0 || $m>59 ) ) return $default;
		if(isset($S) && ( $S<0 || $S>59 ) ) return $default;


        return $date;
	}

	/**
	 *
	*/
	public static function split($date, $from_format = null, $with_defaults = true) {
		$format = self::getWithRealFormat($from_format );

		//Detect date elements
	  	$regexp= preg_replace('@\%([\w]+)@', '(?<\1>[\d]+)', $format);


		if(! preg_match("@^{$regexp}$@",trim($date), $matches, $keys_numeric = 0) ) {
			return false;
		}

        $defaults = [];
        $fields = [ 'S'  /* seconds */,'M'  /* minutes */,'H'  /* hours */, 'Y'  /* year */, 'm' , /* month */ 'd' ,  /* days */];

        if($with_defaults) {
            $defaults = array_fill_keys($fields, 0);
        }

        return array_intersect_key($matches + $defaults, array_flip($fields) );
	}

	/**
     * Check if date is all zeros. Example: 0000-00-00
     */
	private static function checkIsNull($datetime) {
	    if(!isset($datetime)) {
	        return true;
        }

        /** Removing zeros in dates. If $date_without_zero hasn't got element then time is zero.
         *  Examples with empty $date_without_zero: 0000-00-00 or 0000-00-00 00:00:00
         */
        $date_without_zero = array_filter ( $datetime, function($val) {
            if(!trim($val, '0')) {
                return false;
            }
            return true;
        });

        if($date_without_zero) {
            return false;
        }
        return true;
    }

	/**
	 * Retrieve a date convert to format from timestamp $timestamp
	 * @param float $timestamp Timestamp to convert to date
	 * @param string $format format( in PHP format ) to use if this is not null
	*/
	public static function convert($timestamp = null, $format = null) {
		$timestamp = $timestamp?: time();

        if(!is_numeric($timestamp)) return null;

		return self::getWithRealFormat($format, $timestamp);
	}

	/**
     * Change $date from format $format_start to format $end
	 **/
	public static function transform($date, $format_start, $format_end = null) {
		$timestamp = self::toTime($date,  $format_start);
		return self::convert($timestamp, $format_end);
	}

	/**
		Tranform human format to strftime format
		@see http://strftime.net/ ( very useful )
	*/
	public static function getWithRealFormat($format = null, $timestamp = null) {
		if(empty($format) ) {
			$format = \team\Context::get('DATE_FORMAT',  'i18n', '\team\Date');
		}

		switch($format) {
			case 'i18n':
				$format = self::getWithRealFormat(\team\Context::get('DATE_I18N',  'date', '\team\Date'), $timestamp);break;
			case 'long-i18n':
				$format = self::getWithRealFormat(\team\Context::get('LONG_DATE_I18N',  'datetime', '\team\Date'), $timestamp );break;
			case 'timestamp':
					$format = '%s';break;
			case 'tiempo':
			case 'time':
				$format = "%H:%M:%S";break;
			case 'short-time':
			case 'tiempo-corto':
				$format = "%H:%M";break;
			case 'database-date':
			case 'mysql-date':
					$format = "%Y-%m-%d";break;
			case 'database-datetime':
			case 'mysql-datetime':
			case 'database':
			case 'mysql':
					$format = "%Y-%m-%d %H:%M:%S";break;
			case 'date':
					$format = "%m/%d/%Y";break;
			case 'datetime':
					$format = "%m/%d/%Y %H:%M:%S";break;
			case 'date-alt':
					$format = "%m-%d-%Y";break;
			case 'fecha':
					$format = "%d/%m/%Y";break;
			case 'fecha-alt':
					$format = "%d-%m-%Y";break;
			case 'fecha-humana':
			case 'humana':
					$format = '%d %B %Y';break;
			case 'fechahora':
					$format = "%d/%m/%Y %H:%M:%S";break;
			case 'fechahora-humana':
					$format = '%d %B %Y a las %T';break;
			case 'american':
					$format = '%B %d, %Y';break;
			case 'dia':
			case 'día':
			case 'day':
					$format = '%d';break;
			case 'dayname':
			case 'nombredia':
			case 'nombredía':
					$format = '%A';break;
			case 'short-dayname':
					$format = '%a';break;
			case 'mes':
			case 'month':
					$format = '%m';break;
			case 'monthname':
			case 'nombremes':
					$format = '%B'; break;
			case 'año':
			case 'year':
					$format = '%y';break;
            case 'fullyear':
                $format = '%Y';break;
			case 'hora':
			case 'hour':
			case 'hours':
					$format = '%H';break;
			case 'minutos':
			case 'minutes':
					$format = '%M'; break;
			case 'segundos':
			case 'seconds':
					$format = '%S'; break;
            case 'cookie':
                    return date('l, d-M-Y H:i:s T', $timestamp);
                    break;
            case 'atom':
                return date('Y-m-d\TH:i:sP', $timestamp);
                break;
            case 'rss':
                return date('D, d M Y H:i:s O', $timestamp);
                break;
            case 'w3c':
                return date('Y-m-d\TH:i:sP', $timestamp);
                break;
            case 'uploads_dir':
                $format = '/%Y/%m';break;
            default:
                 $date_format =  \team\Context::get("\\team\\date_format\\{$format}", null, '\team\Date');
                 if(isset($date_format)){
                     $format = self::getWithRealFormat($date_format);
                 }else {
                     return null;
                 }
		}

        if(isset($timestamp)) {
            return strftime($format, $timestamp);
        }else {
            return $format;
        }

    }


}



