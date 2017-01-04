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




require(_TEAM_.'/classes/loaders/Config.php');


/** 	
 	 Gestión de contextos de Team Framework
	 Un contexto es una colección de variables de configuración para un namespace especifico.
	 Los contextos se van abriendo por niveles de profundidad según se va cargando las acciones.
	 Cada contexto que se abre se añade a una pila. De manera, que mientras que las acciones se
	van anidando el número de contextos aumenta en esa pila. 
	Diremos que el contexto es de mayor nivel cuando más alto esté en la pila ( o más anidada esté
	la acción asociada ) y más bajo nivel cuanto más bajo esté en la pila ( o la acción esté menos profunda
	en cuanto a anidamiento )
	Los contextos sirve de substituto a las constantes y a las variables globales. 
*/
class Context implements \ArrayAccess  {
	/** Lleva el conteo de niveles. Cuanto más alto es el valor de index, más alto es el nivel */
	private static $index = 0;
	/** Pila de acciones. Elemento 0, es Team/Root */
	private static $contexts = [];
	/** Contexto actual */
	private  static $current = [];
	/** Referencia al objeto $_CONTEXT */
	private static $context = null;
	/** Cargador de archivos de configuración */
	private $configloader = null;

	public function __construct() {
		self::$context = $this;

		//Comenzamos un nuevo contexto( por root )
		self::$contexts[self::$index] = [];

		//Creamos un acceso rápido más manejable
		self::$current  = & self::$contexts[self::$index];
	}
	
	public function initialize() {
		//Creamos la instancia del loader de config(Este en su constructor inicializará team y comenzará el namespace )
		$this->configloader = new \team\loaders\Config();

		//Ahora añadimos el root( con root nos referimos a los archivos de /common del raiz del framework )
		self::setNamespace("\\");

        \Team::event('\team\start');

    }

	/**
		Baja hasta el primer contexto
	*/
	public static function restart() {
		self::$index = 0;
	}

	/**
		Abrimos un nuevo nivel de contexto bien para el namespace seleccionado
		@param String $namespace: Cadena de texto con el namespace al que se asociará el contexto
		@return devolvemos el nuevo contexto.
	*/
	public static function open($namespace = "" ) {
        //Subimos un nivel de la pila
        self::$index++;

        //Comenzamos un nuevo contexto tomando como base el contexto de root
        self::$contexts[self::$index] = self::$contexts[0];

        //Creamos un acceso rápido más manejable
        self::$current  = & self::$contexts[self::$index];

        self::$current['options'] = [];
		self::$current['before'] = [];


        if(self::$index>1) {
            self::$current['before'] =& self::$contexts[self::$index - 1];
        }

		self::$current['last'] = [];

		//Inicializamos el contexto
		self::$current['NAMESPACE'] = $namespace;

		return  self::$current;
	}


	/** 
		Cerramos el contexto actual y ,por lo tanto, bajamos un nivel en la pila
		@return devolvemos el contexto que se cierra.
	*/
	public static function close() {
		//Obtenemos el namespace del contexto que se va a cerrar
		$namespace = self::getNamespace();

        \team\Debug::trace("Context[".self::$index."][{$namespace}]Ending context");

        //Eliminamos el last del contexto actual
		unset(self::$current['last']);

		//Hacemos una copia del contexto actual
		$last =   self::$current;

		//Si aún tenemos niveles por debajo, asignamos a current el nivel nuevo ( e inferior )
		if(self::$index >= 1 ) {
			//Bajamos la pila
			self::$index--;
			self::$current = & self::$contexts[self::$index];

        }

		//Guardamos el contexto de la última acción anidada
		self::$current['last'] = $last;

		return self::$current['last'];
	}

	/* ------------------- GETTERS  ---------------------- */
	public static function getNamespace() { return self::get('NAMESPACE'); }
	public static function getPackage(){	return self::get('PACKAGE'); }
	public static function getComponent(){	return self::get('COMPONENT'); }
	public static function getResponse(){ return self::get('RESPONSE'); }

	/* 
		Devolvemos el valor de una variable de configuración existente. 
		@param String $name nombre de la variable de configuración de la que queremos devolver el valor.
		@param mixed $default valor a devolver en caso de no existir la variable de $name 
	*/
	public static function get($name, $default = null){
		$result = $default;

		if(!empty(self::$current) && array_key_exists($name, self::$current) ) {
			$result = self::$current[$name];
		}
		return $result;
	}


	public static function getOption($name, $default = null) {

		/* 
			Devolvemos el valor de una opción de la configuración existente. 
			@param String $name nombre de la opción de configuración de la que queremos devolver el valor.
			@param mixed $default valor a devolver en caso de no existir la variable de $name 
		*/
		$value = self::$current['options'][$name]?? $default;

        $value =  \team\Filter::apply('\team\options', $value, $name);

        return  \team\Filter::apply('\team\options\\'.$name, $value);
	}


	/* 
		Devolvemos el valor de una variable de configuración del contexto inferior( el que empezó el actual )
		@param String $name nombre de la variable de configuración de la que queremos devolver el valor.
		@param mixed $default valor a devolver en caso de no existir la variable de $name 
	*/

	public static function before($name, $default = null) {
		if(isset(self::$current['before'][$name]) &&  array_key_exists($name, self::$current['before'])  ) {
			return self::$current['before'][$name];
		}
		return $default;
	}

	/* 
		Devolvemos el valor de una variable de configuración del contexto ultimo cerrado. 
		@param String $name nombre de la variable de configuración de la que queremos devolver el valor.
		@param mixed $default valor a devolver en caso de no existir la variable de $name 
	*/
	public static function last($name, $default = null){

		if(isset(self::$current['last'][$name]) &&  array_key_exists($name, self::$current['last'])  ) {
			return self::$current['last'][$name];
		}
		return $default;
	}

	/* 
		Devolvemos el valor de una variable de configuración existente en el contexto de la acción main. 
		@param String $name nombre de la variable de configuración de la que queremos devolver el valor.
		@param mixed $default valor a devolver en caso de no existir la variable de $name 
	*/
	public static function main($name, $default = null) {
		if(!empty(self::$contexts[1]) && array_key_exists($name, self::$contexts[1]) ) {
			return self::$contexts[1][$name];
		}
		return $default;
	}

	/**
		Devolvemos un nivel de contexto.
		@param int $index nivel de contexto a devolver. Si no se espcifica se devuelve el actual.
	*/
	public static function getState($index = null) { 
		if(null === $index)
			return self::$current; 
		else if($index > 0)
			return self::$contexts[$index];
	
		return [];		
	}

	/**
		Deolvemos el número de nivel en el que nos encontramos.
	*/
	public static function getIndex() { return (int) self::$index; }

	/**
		Devuelve un Data que contiene todas las variables de configuración del contexto actual
		Además, como está referenciado. Permite cambiar fácilmente sus valores.
	*/
	public static function getContext() {
		$data = new \team\Data();
		$data->setRef(self::$current);

		return $data;
	}

	public static function getCurrent() {
		return new \team\Data(self::$current);
	}


	/* ------------------- SETTERS  ---------------------- */
	/**
		Añade nuevas variables de configuración al nivel actual
		@param array $vars variables nuevas a añadir.
	*/
	public static function add(array $vars) {
		$last_vars =  self::$current;
		self::$current = $vars + $last_vars;
	}

    /**
        Añade nuevas variables de configuración al nivel actual
        Se diferencia del método add en que este es un método más rápido y optimo en últimas versiones de PHP
        @param array $vars variables nuevas a añadir.
     */
    public static function setContexts(array $vars) {
        self::$current = $vars;
    }


    /**
		Asignamos un nuevo namespace al contexto actual.
		Esto también hace que el contexto se actualize según el namespace que vaya tomando
		@param $namespace Namespace nuevo para el contexto
		@nota: He quitado caché, así siempre se dispararán los setups de los configs
	*/
	public static function setNamespace($namespace){

		self::$context->configloader->load($namespace);
        self::$current['SUBPATH'] =   str_replace('\\', '/', $namespace);

		//Notificamos del nuevo evento. @AMEDIDA
		//\Team::event('\team\context\Set_Namespace', $namespace);
	}

	/** Asignamos un valor de configuración.  */
	public static function set($name, $value = NULL){
		self::$current[$name] = $value; 
	}
	
	public static function exists($key) {
		return array_key_exists($key, self::$current);
	}

	/** Asignamos un grupo de variables de configuración como contexto actual */
	public static function setState($context) { self::$current = & $context; }

	/**
		Depuración. Only Developer
	*/
	public  function debug($str = '') {
		$backtrace = debug_backtrace();
		$file = $backtrace[0]['file'];
		$line = $backtrace[0]['line'];
		
		\team\Debug::me(self::$current, 'Context Log:'.$str, $file, $line);
	}


	/* ------------------- ArrayAccess  ---------------------- */

    public function offsetUnset($offset){
        if(array_key_exists($offset, self::$current)  ) {
            unset(self::$current[$offset]);
        }
    }

    public function offsetExists($offset) {
        return  isset(self::$current[$offset]);
    }

    public function &  offsetGet($offset) {
		$result = null;

		if(is_numeric($offset) &&  isset(self::$contexts[self::$index]) ) {
			$result =& self::$contexts[self::$index];
		}elseif(!isset( self::$current[$offset]) ) {
		   self::$current[$offset] = null;
		}else {
			$result =&  self::$current[$offset];
		}

		return $result;
    }



    public function & offsetSet($offset, $valor) {
		if(is_numeric($offset) && isset(self::$contexts[self::$index]) ) {
 			self::$contexts[self::$index] = $valor;
		}else {
			self::$current[$offset] = $valor;
    	    return  self::$current[$offset];
		}
    }


}

