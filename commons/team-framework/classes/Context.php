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
abstract class Context  {
    use \team\data\Vars;

    protected static $vars =  ['LEVEL'  => 0, 'NAMESPACE' => '\\'];


	/**
     * Abrimos un contexto( es decir, se lanza un nuevo response )
     * @return array contexto nuevo
     */
	public static function open( $isolate = true) {

        if($isolate) {
            $vars = ['LEVEL'  => 0, 'NAMESPACE' => '\\'];
        }else { //reuse parent vars
            $vars = self::$vars;
        }

        $vars['BEFORE'] = self::$vars?: $vars; //Guardamos el contexto anterior( es decir, el que lanzó el response )
        $vars['LEVEL'] = $vars['BEFORE']['LEVEL'] + 1;
        $vars['LAST'] = []; //Aún no se ha lanzado un response desde el contexto actual

        self::$vars = $vars;


		return  self::$vars;
	}

	/**
     * Se cierra el contexto actual y se vuelve al que lo llamó
     * @return array se devuelve los datos del contexto que se cierra
     */
	public static function close() {

         $namespace = self::$vars['NAMESPACE'];

        //Obtenemos el namespace del contexto que se va a cerrar
        \team\Debug::trace("Context[{$namespace}]: Ending");

        if(self::getLevel()> 0) {
            $vars = self::$vars['BEFORE']; //El contexto que llamó al contexto actual pasa a ser el nuevo activo


            //Asignamos el contexto actual como el last del nuevo activo
            unset(self::$vars['BEFORE']);
            $vars['LAST'] = self::$vars;
            self::$vars = $vars;


            //Devolvemos el contexto que se ha cerrado
            return self::$vars['LAST'];
        }

        return self::$vars;
	}

	/**
	 * Para dejar todo como al principio
	 */
	public static function reset() {
        self::$vars = ['LEVEL'  => 0, 'NAMESPACE' => '\\', 'BEFORE' => [], 'LAST'=> []];
    }

	public static function isMain() {
        return 1 === self::getLevel();
    }

	/* ------------------- GETTERS  ---------------------- */
    public static function get($var, $default = null, $place = null){
        return self::$vars[$var]??  \team\Config::get($var, $default, $place);
    }
    public static function getLevel() { return self::$vars['LEVEL']; }
    public static function getIndex() { return self::getLevel(); }
    public static function & getContext() { return self::$vars; }

	/* 
		Devolvemos el valor de una variable de configuración del contexto inferior( el que empezó el actual )
		@param String $name nombre de la variable de configuración de la que queremos devolver el valor.
		@param mixed $default valor a devolver en caso de no existir la variable de $name 
	*/

	public static function before($name = null, $default = null) {
        if(!isset($name)){
            return self::$vars['BEFORE']?? [];
        }

		if(isset(self::$vars['BEFORE'][$name]) &&  array_key_exists($name, self::$vars['BEFORE'])  ) {
			return self::$vars['BEFORE'][$name];
		}
		return $default;
	}

	/* 
		Devolvemos el valor de una variable de configuración del contexto ultimo cerrado. 
		@param String $name nombre de la variable de configuración de la que queremos devolver el valor.
		@param mixed $default valor a devolver en caso de no existir la variable de $name 
	*/
	public static function last($name = null, $default = null){
        if(!isset($name)){
            return self::$vars['LAST']?? [];
        }

		if(isset(self::$vars['LAST'][$name]) &&  array_key_exists($name, self::$vars['LAST'])  ) {
			return self::$vars['LAST'][$name];
		}
		return $default;
	}

	/* 
		Devolvemos el valor de una variable de configuración existente en el contexto de la acción main. 
		@param String $name nombre de la variable de configuración de la que queremos devolver el valor.
		@param mixed $default valor a devolver en caso de no existir la variable de $name 
	*/
	public static function main($name = null, $default = null) {
        $main_level = 1;

        if(!isset($name)){
	        return self::$vars[$main_level]?? [];
        }


		if(!empty(self::$vars[$main_level]) && array_key_exists($name, self::$vars[$main_level]) ) {
			return self::$vars[$main_level][$name];
		}
		return $default;
	}




}

