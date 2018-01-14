<?php
/**
New Licence bsd:
Copyright (c) <2012>, Manuel Jesus Canga Muñoz
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


/**

Herramienta de escritura de logs con soporte a formato.

Tags availables: 
- Utils: <vars>, <functions>,<constants>, <file>, <pathfile>, <line>, <function>, <object>, <type>, <args>, <date>, <time>, <post>, <get>, <files>,<session>, <classes>
- Colors: <color>, <reset>, <bright>,<underscore>, <blink>, <black>, <red>, <green>, <yellow>, <blue>, <magenta>, <cyan>, <white>, <bblack>, <bred>, <bgreen>, <byellow>, <bblue>, <bmagenta>, <bcyan>, <bwhite>
@TODO: date format and time format segun formato pasados,
*/
class Log {
    const actived = true;
    static $_backtrace = array();
	static $_files = array();
	
	/**
		Todos los colores posibles de usar
	*/
   	static $_colors = array(
	  "reset" 		=> "\x1b[0;0;0m",
	  "bright"		=> "\x1b[1m",
	  "underscore" 		=> "\x1b[4m",
	  "blink" 		=> "\x1b[5m",
	  "black" 		=> "\x1b[30m",
	  "red" 		=> "\x1b[31m",
	  "green"		=> "\x1b[32m",
	  "yellow"		=> "\x1b[33m",
	  "blue"		=> "\x1b[34m",
	  "magenta"		=> "\x1b[35m",
	  "cyan"		=> "\x1b[36m",
	  "white"		=> "\x1b[37m",
	  "bblack"		=> "\x1b[40m",
	  "bred"		=> "\x1b[41m",
	  "bgreen"		=> "\x1b[42m",
	  "byellow"		=> "\x1b[43m",
	  "bblue"		=> "\x1b[44m",
	  "bmagenta"		=> "\x1b[45m",
	  "bcyan"		=> "\x1b[46m",
	  "bwhite"		=> "\x1b[47m"
      );

    /**
     * ETIQUETAS de LOGS:  <{string}> -> _option_{string>_()
     */
    static function _option_vars() { $_vars = get_defined_vars(); return $_vars["user"]; }
    static function _option_functions() { $_funcs = get_defined_functions(); return $_funcs["user"]; }
    static function _option_constants() { $_const = get_defined_constants(true); return $_const["user"]; }
    static function _option_file() { return  preg_replace("/.+\//i", "", self::$_backtrace[1]['file']); }
    static function _option_pathfile() { return self::$_backtrace[1]['file']; }
    static function _option_line() { return self::$_backtrace[1]['line']; }
    static function _option_function() { return self::$_backtrace[1]['function']; }
    static function _option_object() { return self::$_backtrace[1]['object']; }
    static function _option_type() { return self::$_backtrace[1]['type']; }
    static function _option_args() { return self::$_backtrace[1]['args']; }
    static function _option_date($_format="Y-m-d") { return date($_format); }
    static function _option_time($_format="H:i:s") { return date($_format); }
    static function _option_get() { return  print_r($_GET, true); }
    static function _option_post() { return  print_r($_POST, true); }
    static function _option_files() { return  print_r($_FILES, true); }
    static function _option_session() { return  print_r($_SESSION, true); }
    static function _option_classes() { return  print_r(get_declared_classes(), true); }
	static function _option_color($_color="reset") { return \team\Log::$_colors[$_color]; }
	static function _option_break($_num = 1) { return str_pad(" ", $_num+1, "\n\t\r");  }
	static function _option_split($_char = "*") { return "\n\t\r".str_pad(" ", 80, $_char)."\n\t\r"; }
	 
	 
	/**
		Inserta un mensaje de log 
		@param String $_file_log: Fichero donde se guardara
		@param String $_var  lo que se quiere visualizar con etiquetas incluidas
		@param String $_label etiqueta que se va a usar
		@param String $_file fichero donde ocurrio el incidente
		@param String $_line linea donde ocurrio el incidente
	*/
	public static function insertLog($_file_log, $_var,  $_label, $_file, $_line) {
		self::$_backtrace = debug_backtrace();
		$label = "";
		if(!empty($_label) ) {
			$label = $_label. " ==> ";
		}


		$var = self::valuate($_var);
        $msg = "<reset>[<date>|<time>]: <green> {$label} <reset>";
        $msg .= " {$var} <reset>on <{$_file}>:<{$_line}> <break>";

        $msg =  self::replaceTags($msg);

		
		self::saveLog($_file_log, $msg);

	}

	/**
		Recibe la peticion de registro en log
		@param String $_file fichero donde se va a guardar 
		@param Array $_args contiene toda las informaciones que queremos insertar en el log
	*/
    public static function __callStatic($_file, $_args) {
		if(!self::actived) return "";

		self::$_backtrace = debug_backtrace();

		$args = func_get_args();
		//$file = $args[0];
		$msg = self::parseArgs($args[1]);

		$msg = self::putTail($msg);
		$msg = self::replaceTags($msg);
		self::saveLog($_file, $msg);


      return $msg;
   }
   
   /**
		Vamos a arreglar la visualizacion de la informacion que se quiere insertar
	*/
   public static function parseArgs($_args) {
		if(!empty($_args) )  {
			$max = count($_args);
			for($i = 0; $i<$max; $i++) {
				$args[$i] = " ".self::valuate($_args[$i]);
			}
			return implode("<reset> |",$args);
		}else {
			return ;
		}
   
   }
   
   /**
		Por cada etiqueta encontrada, llamamos a su metodo especifico
		@param String $_msg  Mensaje que vamos a parsear(y que se insertara en el log)
	*/
   public static function replaceTags($_msg) {

		return preg_replace_callback(
			"/<((\w+?)(\ [\"\'](.+?)[\"\'])?)>/",
			function($tag) {


			$attr = (count($tag)>4)? $tag[4] : null;
			$func = "_option_".$tag[2];


				if(isset(self::$_colors[$tag[2]]) ) {
					return self::$_colors[$tag[2]];
				}elseif( method_exists(self::CLASS,  $func) && null != $attr )
                   return self::$func( $attr );
				else if ( method_exists(self::CLASS, $func)  )
                    return self::$func(  );
				else
                    return $tag[1];
			},
			$_msg
		);
   
   }
   
   /**
		Ponemos un pie fijo en el mensaje a insertar en el log
	*/
   public static function putTail($_msg) {
   		$msg = "<reset>[<date>|<time>]: ".$_msg;
		$msg .= " <reset>on <file>:<line> [<function>] <break>";
		
		return $msg;
   }
   
   /** 
		Escribimos en el log
		@param String $_file fichero donde se escribira
		@param String $_msg texto que se grabara
	*/
   public static function saveLog($_file, $msg) {

		if( "apache" == $_file || \team\system\Context::get("ERROR_LOG") ) {
			error_log($msg);
		}else {

			if(!file_exists(_TEMPORARY_."/logs") ) {
				mkdir(_TEMPORARY_."/logs");
            }

			$file = _TEMPORARY_."/logs/{$_file}.log";

            file_put_contents($file, $msg, FILE_APPEND | LOCK_EX);
		}
   }
   

	/**
		"normalizamos" la cadena a insertar en los logs
	*/
	static function valuate($val) {

		if(null === $val) {
			return "NULL";
		}

		if(is_array($val) ) {
			if(!empty($val) ) {
				return print_r($val,true);
			}else {
				return " array() ";
			}
		}
	
		if(is_bool($val) ) {
			if(true === $val) {
				return "TRUE";
			}else {
				return "FALSE";
			}
		}


		if("" === $val) {
			return " \"\" ";
		}

		if(is_object($val) ) {
			return "OBJECT OF ".get_class($val);
		}

		return $val;
	}

	/**
		Creamos el directorio de logs sino estuviera creado
	*/
	public static function __initialize() {
		if(!\team\system\FileSystem::exists("/logs/", _TEMPORARY_) ) {
			mkdir( _TEMPORARY_."/logs/", 0777, true);
		}
    }

}
