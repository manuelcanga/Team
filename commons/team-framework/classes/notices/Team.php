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

/** **************************************************************************************
	Sistema de notificaciones/Avisos/Eventos/Alertas. Muy útil para devolver mensajes fácilmente al usuario
	de la web después de que este haya realizado alguna operación.
	Se basa en proceso, un proceso puede tener éxito o pudo tener un error crítico.
	A su vez, los pasos del proceso, pudieron tener avisos informátivos o avisos de errores(normales).
	También, pudo haber otro tipo de errores, ocasionados por el sistema(fallo de acceso a la bd, un archivo que no se encuentra, ... )
*************************************************************************************** */



require ( _TEAM_."/includes/exceptions/System_Error.php");

class Team
{


    /**  indice de la cola de mensajes. */
	static private $index = -1;

	/* Almacen de todos los listeners */
	private static $listeners = array();

	static public $notices = array();
	/** Avisos actuales */
	static public $current = array();
	/** Aviso anterior */
	static public $last = array();

	/** Manejo de errores del sistema */
	static public $errors = null;

	/** Inicializador de la clase */
	public static function __initialize() {     
	   ini_set('display_errors', 0);
	   
		// Report all PHP errors
		error_reporting(\team\Config::get('GENERAL_ERROR_LEVEL', E_ALL ) );

		$errors = new \team\notices\Errors();

		set_error_handler( array($errors , 'PHPError' ), \team\Config::get('GENERAL_ERROR_LEVEL', E_ALL) );

		register_shutdown_function(array( $errors , 'critical' ));

		self::$errors = $errors;

		self::up();
	}



	public static function last() {
		return self::$last;	
	}

	public static function index($index) {
		return self::$notices[$index];
	}

	public static function getCurrent() { 
		return self::$current->get(); 
	}

	public static function current() { 
		return self::$current; 
	}

	/** Empezamos la captura de avisos, dentro del bloque. */
	public static function up() {

		self::$index++;
		self::$notices[self::$index] = new \team\notices\Notice();
		self::$current =  self::$notices[self::$index];

	}

	/** Terminamos la captura de avisos, dentro del bloque en el que estamos */
	public static function down()
	{
        //Guardamos el listado de avisos del nivel actual.
      //  \Level::setNotices(self::$notices[self::$index]);
                
		self::$last = self::$current;

        //Borramos los avisos del nivel actual
        //ya que hemos hecho una copia
			//No haria falta, pero por si las moscas
        unset(self::$notices[self::$index]);

        self::$index =  max( self::$index -1 , 0 ); //Bajamos la cola
	    self::$current = self::$notices[self::$index];
	}



	/**
		Pasamos cualquier petición a esta clase al notice actual
	*/
	public static function __callStatic($name, $arguments) {
		return call_user_func_array([self::$current, $name], $arguments);
	}



	public static function critical($e = null) {
	    if(!isset(self::$errors)) {
            error_log(print_r($e, true));
        }

		self::$errors->critical($e);
	}




	/**
		Añadimos un listener a la espera de un evento 
		@param namespace $event Evento a esperar
		@param callable $listeners listener que se queda a la escucha
		@param int $order Posición en la llamada de eventos
	*/
	public static function addListener($event, $listener, $order = 65) {
		$event = rtrim($event, '\\');
		if(!is_callable($listener) ) return ;
		$order = \team\Check::id($order);

		//Si no habia listeners asociados al evento, ahora si
		self::$listeners[$event] =  isset(self::$listeners[$event])? self::$listener : [];

		//Vamos buscando un hueco libre para el trabajador a partir del orden que pidió
		for($max_order = 100; isset(self::$listeners[$event][$order]) && $order < $max_order; $order++);

		//Guardamos el listener
		self::$listeners[$event][$order] = $listener;
	}



	/**
		Aviso de evento. Es una notificación de tipo neutro.	
		Se recorre todos los listeners hasta que uno devuelva true. En ese momento se para el barrido.
		Los argumentos, si los hubiera, son pasados por referencia
		@param namespace $code es el código o namespace del evento ocurrido. 
		@param $data es un dato que se quiere transmitir con el evento.
		
		@return boolean devuelve si algún listener cancelo o no el evento( retornando true: cancela, false/null: no) 
	*/
	public static function event($code, &...$data) {
        $namespace = rtrim($code, '\\');


        if(isset(self::$listeners[$namespace])  ) {
			$data[] = $namespace;	

		    foreach(self::$listeners[$namespace] as $listener) {
		        //mandamos el trabajo al listener
		        $result =  $listener(...$data);
		        if($result) return $result;
		    }
		}

		return false;
	}



	//Procesa una excepción en el sistema. 
	public static function systemException(\Exception $exception) {

        $error_type = "SYSTEM";
        $result = \Team::event($exception->getCode(), $exception->get(), $error_type);
		if($result) {
			return $resut;
		}

		throw $exception;
	}
	

	public static function debug() {
		$backtrace = debug_backtrace();
		$file = $backtrace[0]["file"];
		$line = $backtrace[0]["line"];
		
		\team\Debug::me(self::$notices, "Notice Log", $file, $line);
	}

}
