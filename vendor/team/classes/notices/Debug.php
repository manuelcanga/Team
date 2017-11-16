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

if(!class_exists('\Debug', false) ) {
	class_alias('\team\Debug', 'Debug', false);
}



/** ******************************************************************************
	Class for debugging, of course.
	@package team
	@TODO: Hacer que a la hora de visualizar los errores lo haga a traves de una GUI de team ?
	Podríamos hacerlo de dos formas, si esta el sistema levantado, lo enviamos. Sino, usamos la de por defecto.
   Es que así, podríamos cargar un css con una salida más bonita de error
******************************************************************************** */
error_reporting(E_ALL ^E_NOTICE); /** Activamos los errores. Ninguno: error_reporting(0) */



final  class Debug
{
	
	private function __construct()  { /* prohibida instanciar una clase */ }

	
	/*
		Devolvemos el archivo y la línea en el momento de realizar una llamada a esta función ( level = 1 )
		o de un nivel anteiror
		@param String $file Se almacerá el nombre del archivo ( por tanto, se sobreescribirá cualquier posible valor que tuviera )
		@param String $file Se almacerá el número de línea ( por tanto, se sobreescribirá cualquier posible valor que tuviera )
		@param Integer $level nivel hacia atrás a partir del que queremos obtener los datos(fílea y línea )
	*/
    public static function getFileLine(&$file, &$line, $level = 2) {

        if( NULL == $file && NULL == $line  ) {
            $backtrace = debug_backtrace();

            if (!\team\Config::get("SHOW_TRASWEB_ERRORS")) {
                $backtrace = array_reverse($backtrace);

                foreach ($backtrace as $trace) {
                    if (isset($trace["file"]) && strpos($trace["file"], _TEAM_) === false) {
                        $file = $trace["file"];
                        $line = $trace["line"];
                    }
                }
            }

            if( NULL == $file && NULL == $line  && isset($backtrace[$level]) ) {
                $file = $backtrace[$level]["file"];
                $line = $backtrace[$level]["line"];
            }
        }
    }

	/**
		Creamos una traza de depuracion. 
		Las trazas de depuración sirve para hacer un seguimiento del flujo del programa. 
	*/
	public static  function me($var = 'Hello, World', $label = false, $file = null, $line = null, $level=null) {

        if(!isset($level) && is_numeric($label)) {
			$level = (int) $label;
			$label = null;
		}

		self::getFileLine($file, $line, $level);

		if(!\team\Config::get('SHOW_ERRORS', false)  ) return ;

		if(\team\Context::get('CLI_MODE') ) {
			self::output($var, $label, $file, $line);
		}else if(false === \team\Config::get('SHOW_IN_NAVIGATOR', false)  ||  \team\Context::main("AJAX")  ) {
			self::log($var, $label, $file, $line);
		}else {
            self::log($var, $label, $file, $line);
            self::out($var, $label, $file, $line);
        }
	}


	/**
		Visualizamos el valor de una variable, metidos en una tabla con borde
		@param $var es la variable que se visualizará
		@param $label texto que aparecerá antes del valor de la variable.
		TODO: Hacer una visualización más bonita.
	*/
	public static  function out( $var = 'Hello, World', $label = false, $file = null, $line = null) {
		$level = 1;
		if(is_numeric($label)) {
			$level = (int) $label;
			$label = null;
		}

		self::getFileLine($file, $line, $level);

		echo self::get($var, $label, $file, $line);
	}

    public static  function stop( $var = 'bye, World', $label = false, $file = null, $line = null) {

	    $level = 1;
        if(is_numeric($label)) {
            $level = intval($label);
            $label = null;
        }

        self::getFileLine($file, $line, $level);
        self::out($var, $label, $file, $line);

        die();
    }

	static function  get( $var = 'Hello, World', $label = false, $file = null, $line = null) {

		self::getFileLine($file, $line);

		//Debería de ser dependiendo del tipo de salida. Html-> html, json->json, etc.
		return self::formatDisplay($var, $label, $file, $line);
	}

	/**
		Helper
		Transform objects from array in string
	*/
	private static function normalizeCompound($vars) {
		$vars = (array)$vars;

		$new_vars = [];
		foreach ($vars as $key => $value) {		
			$key = str_replace('*', '', $key);

			if(is_object($value) )  {
				if (!($value instanceof \team\Data ) )
					$value = "Object of ".get_class($value)." ";
				else {
					$key = "{$key} [ ".get_class($value)." ] ";
					$value = $value->get(); //it's now a array, so we'll proccess in next if
				}
			}

			if(is_array($value) ) {
			    	$value = self::normalizeCompound($value);
			}else {
				$value = self::normalizeScalar($value);
			}

			$new_vars[$key] = $value;
		}


		return $new_vars;
	}

	/**
		Helper
		Transform a scalar in a visuable value(string)
	*/
	private static function normalizeScalar($var, $out = 'html') {
			 if(is_string($var) ){
				if('html' == $out) {
                    return htmlentities($var, ENT_NOQUOTES | ENT_HTML5, \team\Context::get('CHARSET'));
                }else {
					return $var;
                }
			}else {
				return var_export($var, true);
			}

	}
	
	/** 
		Helper
		Visualiza en plan bonito una traza 
	*/
	private static function formatDisplay( $var, $label, $file, $line) {
	
        if("string" != \team\Context::get('ERRORS')  && class_exists('\team\Data', false) ) {
            return self::withData( $var, $label, $file, $line);
        }else {
            return self::withString( $var, $label, $file, $line);
        }
	}
	
   private static function withData($var, $label, $file, $line) {

		$data = new \team\Data();
		$data->file = $file;
		$data->line = $line;
		$data->label = $label;


		$is_object = is_object($var);
		$is_array = is_array($var);
			
		if($is_object || $is_array  ) {

            $data->setContext('VIEW',  \team\Config::get('\team\debug\compound_template', 'team:framework/debug/compound') );
			$data->label = $label;
			$data->sublabel = null;

			if($is_object) {
				$sublabel = 'Object of '.get_class($var);
				if($data->label) {
					$data->sublabel = $sublabel ;
				}else {
					$data->label = $sublabel;
				}
			}
		
			$data->vars = self::normalizeCompound($var);
        }else {
                $data->setContext('VIEW',  \team\Config::get('\team\debug\scalar_template', 'team:framework/debug/scalar') );

                $data->msg = self::normalizeScalar($var);

        }


		return  $data->out('html');

    }
        
    private static function withString($var, $label, $file, $line) {

		$out = '<div style="border: 2px solid #008000; background-color: #FAFFF6; color: black; padding: 15px; margin: 7px; border-radius: 7px; font-family: Verdana; font-size: 14px; ">';

		if($label)
			$out .= "<p><strong>$label</strong>:</p>";

		if(is_array($var) ) {
			$out .=  '<pre>';
			 $out .=  "<p>".highlight_string('<?php '.var_export($var, true), true)."</p>";

			$out .=  '</pre>';
		} else if( is_object($var) ) {
			$out .=  "<p><strong>Class: ".get_class($var).":</strong></p>";
            $out .=  '<pre>';
            $out .=  "<p>".print_r($var, true)."</p>";
            $out .=  '</pre>';
        }else {

            $out .=  "<p>".var_export($var, true)."</p>";
		}


		if(null != $file && null != $line) {
			$out .=  "<div align='right' style='font-size: 11px;font-weight: bold; margin: 8px 0 -8px 0;'>{$file}: {$line}</div>";
		}



		$out .=  '</div>';
		
		return $out;
    }

	/**
		Permite visualizar una traza en un archivo log
	*/
	public static  function log($var, $label = false, $file = null, $line = null) {
		self::getFileLine($file, $line);

		\team\Log::insertLog("debug", $var, "$label", $file, $line );
	}


	/**
		Info about enviroment state
		@param $label Texto etiqueta que aparecerá junto a la información
	*/
	public static function enviroment($label = 'System ') {
		$backtrace = debug_backtrace();
		
		//Visualizamos el valor de las constantes definidas por nosotros(user)
		$_constants = get_defined_constants(true);
		self::me($_constants["user"], $label.' constants', $backtrace[0]["file"],   $backtrace[0]["line"]);

		//Visualizamos las funciones definidas por nosotros(user)
		$_functrions = get_defined_functions();
		self::me($_functrions['user'], $label.' functions', $backtrace[0]["file"],   $backtrace[0]["line"]);
	}

	/**
		Visualizamos toda la información disponible sobre el sistema que estamos utilizando.
	*/
	public static  function system()
	{
		phpinfo(INFO_GENERAL);
	}

	/**
		Visualizamos los parametros $_GET,  $_POST, $fileS y $_SESSION 
	*/
	public static  function params()
	{
		self::me($_GET, 'GET VARS');
		self::me($_POST, 'POST VARS');
		self::me($_FILES, 'FILES VARS');
		self::me($_SESSION, 'SESSION VARS');
	}


	public static function server() {
		self::me($_SERVER, 'SERVER VARS');
	}
	
	/**
		Visualizamos un mensaje, mostrandonos en que fichero y línea se llamo 
		a la función/método en la que nos encontramos ( jumps = 1 ) o cual de ellos después
de $jumps saltos hacia atrás  */
	public static function origin( $jumps = 1, $_label = 'You are ') {
			$backtrace = debug_backtrace();

			if('all' != $jumps && $jumps > 1) {
                $file = $backtrace[ $jumps - 1 ]["file"]?? '';
                $line = $backtrace[ $jumps - 1 ]["line"]?? '';
				$me = $backtrace[ $jumps ];

				//adding to label function name
				$label = $_label;
				if(isset($me['function']) ) {
					$label .= 'in function "'.$me['function'].'"';
					//adding to label class name
					if(isset($me['class']) ) {
						$label .= ' and in class "'.$me['class'].'"';
					}

					$label .= ' with args';
					$me = $me['args'];
				}else {
					$label .= 'here';
				}


				self::me($me, $label, $file, $line);
			}else {
				//Firstly, removing this function
				array_shift($backtrace);

				self::me($backtrace, $_label, $file, $line);
			}
	}

	/**
		Hacemos una traza hacia atrás de nuestro script
		@param $label Texto etiqueta que aparecerá junto a la información
	*/
	public static  function trace($label = "Trace", $data = null)
	{

		if(\team\Config::get("SHOW_TRACE", false) ) {
			$backtrace = debug_backtrace();

			self::me($data, $label, $backtrace[1]["file"],   $backtrace[1]["line"]);
		}
	}



	/**
		@name time
		@param $_op =reset(pone a 0 el cronometro), =stop(pone en marcha el cronómetro ),
		=start(O empezamos el cronómetro o continuamos si ya estaba parado )
		@param $_display =true( visualiza el tiempo) =false( no lo visualiza )
		Función cronómetro para calculos de tiempos(en microsegundos )
	*/
	public static  function time($_op = "start", $_display = 0)
	{
		$backtrace = debug_backtrace();

		static $start = 0;  //Especifica el tiempo en que se empezó el conteo.
		static $time = 0; //Especifica el tiempo actual, si esta parado ( = 0 ) o en marcha( != 0 )

		switch($_op)
		{
 			//Ponemos el cronómetro a 0
			case "reset": $time = 0; $start = 0; break;
			//Parámos el cronómetro.
			case "stop":
					if($start)  $time = ($start)? microtime(true) - $start: 0;
					$start = 0;
					break;
			//O empezamos el cronómetro o continuamos(si ya estaba antes parado ).
			case "start":
			default:
					if($time != 0) {
						$start = microtime(true) - $time;
						$time = 0;
					}else {
			 				$start = microtime(true);
					}
		}

			//Toca ver el tiempo, o devolverlo. (pasándolo antes a una numeración más clara )
			if($_display)
                \team\Debug::out($time*10000, $_op, $label, $backtrace[0]["file"],   $backtrace[0]["line"]);
			else
				return $time*10000;
	}


	/**
			@TODO: Ahora mismo no funciona, pero habria que habilitarlo 
		@param $label Texto etiqueta que aparecerá junto a la información
		Visualizamos todas las variables que hemos mandado a smarty
	*/
	public static  function varSmarty($label = false)
	{
		global $web;
		
		$backtrace = debug_backtrace();
		self::me($web->get_template_vars(), $label, $backtrace[0]["file"],   $backtrace[0]["line"]);
	}

	/**
		@TODO: Ahora mismo no funciona, pero habria que habilitarlo 
		Mostramos popup de información de smarty
	*/
	public static  function infoSmarty()
	{
		global $web;

		$web->debugging = true;
	}



	/**
		Look for $_text in all file from working directory
		@param $_text es el texto a buscar en los archivos.
	*/
	public static  function grep($_text = "Team")
	{
		$backtrace = debug_backtrace();

		$label = "Grep {$_text} ";
		
		exec("grep -iR ".escapeshellarg($_text)." "._SCRIPT_, $_out);
		self::me($_out, $label, $backtrace[0]["file"],   $backtrace[0]["line"]);
	}




	/**
		Visualizamos todos los errores ocasionados del uso del cms
		@param $label Texto etiqueta que aparecerá junto a la información	
		@TODO revisar
	*/
	public static  function notices($label = "Notices")
	{
	
		$backtrace = debug_backtrace();
		self::me(\Team::get(), $label, $backtrace[0]["file"],   $backtrace[0]["line"]);
	}

	/**
		Visualizamos todos los errores, avisos, informaciones,  ocasionados del uso del cms
		@param $label Texto etiqueta que aparecerá junto a la información
		@TODO: Revisar
	*/
	public static  function allNotices($label = "All Notices" )
	{
		$backtrace = debug_backtrace();
		self::me(\Team::all(), $label, $backtrace[0]["file"],   $backtrace[0]["line"]);
	}

	/**
		Muestra información de la ultima consulta sql realizada
	*/
	public static  function sql($label = "Last sql", $file = null, $line = null, $level = 1)
	{
		self::getFileLine($file, $line,  $level);

		$query = \team\db\DB::getLastQuery();

		self::me($query, $label,  $file,  $line);

	}

    /**
         Muestra información del ultimo filtrado realizado
     */
    public static function filter($label='Last Filtered', $file = null, $line = null, $level = 1) {
        self::getFileLine($file, $line,  $level);

        $filtered = \team\Filter::getLast();

        $label .= ': '.$filtered['name'];
        unset($filtered['name']);

        self::me($filtered, $label,  $file,  $line);
    }


	/**
		Queremos saber la memoria que usa el sistema
	*/
	static function  memory($title = 'Memory', $real = true){

		 $size =  memory_get_usage($real);
		 $overall = \team\FileSystem::toUnits($size);
	   	\team\Debug::me(  $overall ,$title);
		return  $size;
	}

	/**
		Informa del espacio libre
	*/
  public static function getDiskFreeSpace(){
	if(PHP_OS=="WINNT"){
	        return disk_free_space("C:");
	} else {
	        $diskFreeSpace = disk_free_space($_SERVER['DOCUMENT_ROOT']);
	        return self::humanSize($diskFreeSpace);
	}
  }


  public static function end($text ="------end-----", $label = '') {
	  self::getFileLine($file, $line);
	  self::out($var, $label, $file, $line);
	  die();
  }
}
