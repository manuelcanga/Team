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


/** **********************************************************************************
    Esta clase sirve para asegurarse de que los datos que tenemos esten limpios y seguros
************************************************************************************* */
class Sanitize {
	private function __construct() { } //Prohibido instanciar esta clase.


    static function encoding($string, $charset = null, $fromcharset = null) {
        $charset = ($charset)?: \team\Config::get('CHARSET');

		if(!isset($fromcharset) ) {
			$fromcharset = mb_detect_encoding($string, ['ISO-8859-15', 'UTF-8', 'Windows-1251']);
		}

        return  iconv( $fromcharset, $charset, $string);
    }

    /**
     * Esta función elimina cualquier espacio en blanco que pudiera tener una cadena independientemente del lugar
     * dónde se sitúe
     *
     * @param $string cadena a limpiar
     * @return mixed cadena sin espacio en blanco en ella
     */
    static function withoutWhiteSpace($string) {
	    return preg_replace("/\s/", '', $string);
    }

	/** 
		 normalize some characters to regular Unicode codepoints, 
		@param string str string to normalize
	*/
	static function normalize($str) {
		$str = 	\team\Sanitize::encoding($str,  'UTF-8//IGNORE'); 

		$normalization_map = array(
		   "\xC2\x80" => "€",			 
		   "\xC2\x82" => "‚", 		
		   "\xC2\x91" => "'", 			
		   "\xC2\x92" => "'", 			

		);
		$from = array_keys  ($normalization_map); // but: for efficiency you should
		$to = array_values($normalization_map); // pre-calculate these two arrays

		return str_ireplace($from, $to, $str);
	}

    /**
        Se asegura de devolver un valor numerico
		  @param String $number: Cadena, supuestamente numérica
		  @return devuelve un número asociado a $number
    */
    static function id($number) {
        return (int) $number;
    }

    /**
     * Se asegura de devolver un número natural
     *
     * @param $natural cadena a limpiar
     * @return mixed número natural obtenido
     */
    static function natural($natural, $max_length = 0) {

        $natural =  preg_replace("/[^0-9]/", "", $natural);

        if($max_length) {
            return substr($natural, 0, $max_length);
        }else {
            return $natural;
        }
    }

    
    /**
        Se asegura de devolver un valor float
		  @param String $float: Cadena, supuestamente real
		  @return devuelve un número real asociado a $float
    */
    static function float($float) {
      return preg_replace("/[^0-9\-\.\+]/", "", $float);
    }


    /**
       Sanitize a boolean variable
    */
	static public function choice( $var) {
		if ( is_bool( $var ) ) {
			return $var;
		}

		if ( is_string( $var ) && 'false' === strtolower( $var ) ) {
			return false;
		}
	
		return (bool) $var;
	}

	/** 
		Se asegura de devolver un valor no numérico(textual)
		@param String $str especifica la cadena a limpiar
		@return devuelve la cadena $str pero con sólo carácteres no numéricos
	*/
	static function text($str) {
  		return preg_replace("/[0-9\-\.\+]+/", "", $str);
	}

	/**
		Transformamos ciertos carácteres, que podrían ser reutilizados, por otros más validos para url. Es decir, reemplazamos carácteres no internacional( ñ, á,é, í, ó, ú, ... ) o no adecuados
		para url(&, ' ', ':')
	*/
	static function chars($str) {

        //Replacing of Very commons transforms( the reason is thats sometime encoding can be  a problem for some characters )
        $before = ["á", "é", "í", "ó", "ú", "ä","ë", "ï", "ö", "ü", "ñ", "&", " "];
        $after = ["a", "e", "i", "o", "u", "a","e", "i", "o", "u", "n", "y", "-"];
        $str = str_replace($before, $after, strtolower($str));

        //Convertimos la cadena pasada a ASCII;
        $str =  self::encoding($str,  'ASCII//TRANSLIT');


        $before = [ "&", " ", "_",':'];
        $after = [ "y", "-", "-",'-' ];

		//Quitamos otros carácteres no adecuados para urls
		$str = str_replace($before, $after, $str);


		//Lo codeamos a la codificación de la web.
		$str =  self::encoding($str);


		return $str;
	}

	/** Se asegura de devolver un valor alfanumerico 
		@param String $str especifica la cadena a limpiar
		@return devuelve la cadena $str pero con sólo carácteres alfanuméricos
	*/
	static function key($str, $others_allowed = '\-\_\.', $replace = '') {
      return preg_replace("/[^A-Za-z0-9{$others_allowed}]+/", $replace, $str);
	}


	/** Se asegura de devolver un valor alfanumerico con espacios
		@param String $str especifica la cadena a limpiar
		@return devuelve la cadena $str pero con sólo carácteres alfanuméricos con espacios
	*/
	static function string($str, $others_allowed = '', $replace = '') {
      return preg_replace("/[^a-zA-Z0-9\-\_\.\ {$others_allowed}]+/", $replace, $str);
	}

	static function html($_string) {
			if("<br>" == $_string || "<br />" == $_string) {
				return "";
			}

        $string = str_replace("<br>", "\n\r", $_string);
        $string = str_replace("<br />", "\n\r", $string);

        $string = str_replace("&nbsp;", "", $string);

        return self::toHtml($string);

   }


	/**
		Limpiamos un string para que sea válido para ser una url
	*/
    static function urlFriendly($str, $others_allowed = '', $urlencode = true) {

        //remove query strings and extension only if dots are not allowed
		if(strpos($others_allowed,'.') === false)
	        $str = preg_replace('/[\?\&\.].*/','',$str);


        //Convertimos semiválidos a carácteres válidos
		$str  = self::chars($str);


        //Limpiamos todos los carácteres no válidos
        $str = self::key($str,$others_allowed .= '\-');

        //Quitamos todos aquellos guiones que se hayan quedado juntos. Ejemplo: mi---noticia -> mi-noticia
        $str = preg_replace("/-{2,}/","-", $str);

        //Quitamos todos aquellos guiones del principio y del final de la cadena. ej: ---prueba--- -> prueba
        $str = trim($str, '-');

        //Devolvemos la url urlcodeada por si acaso queda algo raro
        return $urlencode? urlencode($str) : $str;
    }
    

    
    /**
    * truncate function
    * Purpose:  Truncate a string to a certain length if necessary,
    *               optionally splitting in the middle of a word, and
    *               appending the $etc string or inserting $etc into the middle.
    *
    *
    * @param string  $string      input string
    * @param integer $length      length of truncated text
    * @param string  $etc         end string
    * @param boolean $break_words truncate at word boundary
    *
    * @return string truncated string
    */
    static function length($string, $length = 80, $etc = '...', $break_words = false) {
        if ($length == 0) {
            return '';
        }

        $charset = \team\Config::get('CHARSET');
        
        if (function_exists('mb_strlen') ) {
            if (mb_strlen($string, $charset) > $length) {
                $length -= min($length, mb_strlen($etc, $charset));

                if (!$break_words) {
                    $string = preg_replace('/\s+?(\S+)?$/u', '', mb_substr($string, 0, $length + 1, $charset));
                }
          
                return mb_substr($string, 0, $length, $charset) . $etc;
            }

            return $string;
        }

        // no MBString 
        if (isset($string[$length])) {
            $length -= min($length, strlen($etc));
            if (!$break_words) {
                $string = preg_replace('/\s+?(\S+)?$/', '', substr($string, 0, $length + 1));
            }
   
   
            return substr($string, 0, $length) . $etc;
        }

        return $string;
    }


	/**
		Limpia un identificador( nombre de variable, función, clase, constante ) de manera general( omite acentos y eñes, aunque sean adecuados ) 
		para que sea valido.
		@param string $identifier nombre a limpiar
		@return la cadena identifier limpiada
	*/
	static function identifier($_identifier) { return trim(self::key($_identifier, '_','_'), '_'); }

	
	/**
		Adapta una cadena para ser usada por javascript.
		@see http://www.javascripter.net/faq/accentedcharacters.htm
	*/

	static function toJs($str) {
		$replaces = [
			'À' => '&#192;', 'Á' => '&#193;', 'Â' => '&#194;', 'Ã' => '&#195;', 'Ä' => '&#196;', 'Å' => '&#197;',
			'Æ' => '&#198;', 'Ç' => '&#199;',
			'È' => '&#200;', 'É' => '&#201;', 'Ê' => '&#202;', 'Ë' => '&#203;',
			'Ì' => '&#204;', 'Í' => '&#205;', 'Î' => '&#206;', 'Ï' => '&#207;',
			'Ð' => '&#208;', 'Ñ' => '&#209;',
			'Ò' => '&#210;', 'Ó' => '&#211;', 'Ô' => '&#212;', 'Õ' => '&#213;', 'Ö' => '&#214;', 'Ø' => '&#216;',
			'Ù' => '&#217;', 'Ú' => '&#218;', 'Û' => '&#219;', 'Ü' => '&#220;',
			'Ý' => '&#221;',
			'Þ' => '&#222;',
			'ß' => '&#223;',
			'à' => '&#224;', 'á' => '&#225;', 'â' => '&#226;', 'ã' => '&#227;', 'ä' => '&#228;', 'å' => '&#229;',
			'æ' => '&#230;', 'ç' => '&#231;',
			'è' => '&#232;', 'é' => '&#233;', 'ê' => '&#234;', 'ë' => '&#235;',
			'ì' => '&#236;', 'í' => '&#237;', 'î' => '&#238;', 'ï' => '&#239;',
			'ð' => '&#240;', 'ñ' => '&#241;',
			'ò' => '&#242;', 'ó' => '&#243;', 'ô' => '&#244;', 'õ' => '&#245;', 'ö' => '&#246;', 'ø' => '&#248;',
			'ù' => '&#249;', 'ú' => '&#250;', 'û' => '&#251;', 'ü' => '&#252;',
			'ý' => '&#253;',
			'þ' => '&#254;',
			'ÿ' => '&#255;',
			'Œ' => '&#338;',
			'œ' => '&#339;',
			'Š' => '&#352;',
			'š' => '&#353;',
			'Ÿ' => '&#376;',
			'ƒ' => '&#402;' ];

		return str_replace(array_keys($replaces), array_values($replaces), $str);

	}
	

	/** Pasa una cadena a su correspondiente con carácteres htmlentities( &amp;, &iacute;, ...)
		@param $str cadena inicial que queremos convertir sus carácteres a htmlentities
	*/
	static function toHtml($str) { 
        $charset =  \team\Config::get('CHARSET');
		return htmlentities($str, ENT_QUOTES, $charset); 
	}

	/** 
		Elimina etiquetas html de la cadena pasada
	 */
	static function toText($str, $max_length = 0) {
	    $str = strip_tags($str);

	    if($max_length) {
	        return substr($str, 0, $max_length);
        }else {
	        return $str;
        }
	}

	/**
		Nos aseguramos que @url sea una url interna válida
	*/
	static function internalUrl($url) {
		$url = self::urlFriendly($url, '\/', $urlencode = false);

		$parsed_url = parse_url($url);
		$url = $parsed_url["path"];

		if($url)
			return $url;
		 else 
			return "/";
	}

    /**
     * Retrieve a canonical form of the provided charset appropriate for passing to PHP
     * functions such as htmlspecialchars() and charset html attributes.
     *
     * @param string $charset A charset name.
     * @return string The canonical form of the charset.
     */
	static function charset($charset) {
        if ( 'UTF-8' === $charset || 'utf-8' === $charset || 'utf8' === $charset ||
            'UTF8' === $charset )
            return 'UTF-8';

        if ( 'ISO-8859-1' === $charset || 'iso-8859-1' === $charset ||
            'iso8859-1' === $charset || 'ISO8859-1' === $charset )
            return 'ISO-8859-1';

        return $charset;
    }

	/**
		Nos aseguramos que un determinado string sólo tenga como máximo un caracter por delante y por detrás
	*/
	static function trim($str, $char = '/') {
		$str = trim(trim($str), $char);

		if('' === $str || is_null($str)) return $char;

		return $char.$str.$char;
	}

	/**
		Nos aseguramos que un determinado carácter no se repita por la izquierda
	*/
	static function ltrim($str, $char = '/') {
			return $char.ltrim(trim($str), $char);
	}

	/*
		Nos aseguramos que un determinado carácter no se repita por la derecha
	*/
	static function rtrim($str, $char = '/') {
			return rtrim(trim($str), $char).$char;
	}


    /** Nos aseguramos que es de tipo decimal. Util para manejo de monedas */
    static function decimal($number, $decimals = 2) {
        return  number_format(floatval($number), $decimals);
    }


    /**
        Se asegura que $number es de valor numerico de $zeros longitud. En caso
        de no tener esa longitud la rellena con ceros.
    */
    static function zerofill($number, $zeros = 12) {
        $number = \team\Check::real($number);

        return str_pad($number, $zeros, "0", STR_PAD_LEFT);
    }


    static function __callStatic($name, $arguments) {
        return \team\Filter::apply('\team\Sanitize\\'.$name, ...$arguments );
    }
} /* Fin de clase */
