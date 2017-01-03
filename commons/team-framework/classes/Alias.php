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

/** 

 	Proporciona un sistema de creación de atajos(shortcodes) y alias. 
	Lo que se busca es hacer Monkey patching ( http://en.wikipedia.org/wiki/Monkey_patch ) con funciones y clases con la finalidad de hacer otro(¿otro más?) sistema de hooks y también de abstracción al CMS: 

	@example Por ejemplo, podríamos decir que la acción "youtube" con namesapce  \plugins\videos ( o \plugins\videos\youtube )
	se pudiera lanzar simplemente con el shortcode "video". ¿ Qué conseguimos con esto ?. 
	1. Comodidad a la hora de llamar a acciones. ( video([url => "http.../"] ); )
	2. Abstracción de componentes, de manera que si el día de mañana queremos usar otro sistema de videos
	que no sea de youtube simplemente tendríamos que cambiar el componente asociado al shortcode y automáticamente todo
	nuestro código habrá cambiado. 

	@example: Otra opción es cambiar funciones  para realizar alguna tarea, en plan enganche(y adapter). Ejemplo:
		function sql_debug($sql) { \team\Debug::log($sql); return sql_debug($sql); }
		\team\Alias::swap("mysql_query", "sql_debug");
		Ahora, cada vez que se lance una consulta veremos en formato depuración la consulta realizada.
*/
namespace team;

class Alias {
	/* Array de alias realizados de funciones  */
	private static $list = array();

	private function __construct()  { /* prohibido instanciar un objeto */ }
	
	/**
		Creamos la relación entre nuestro atajo/alias y la acción/función
		@param string $alias será el atajo que queremos ( tener cuidado que no de conflicto con ningún otra función )
		@param string $original namespace completo de la acción que queremos ( el namespace completo es aquel que incluye el nombre de la acción )
		@param array $defaults valores predeterminados que queremos
		\team\Alias::assign("ver_youtube", "\\plugins\\videos\\youtube");
		echo ver_youtube(["url" => "http://urlvideo"]);
	*/
	public static function shortcode($alias, $original, array $defaults = [] ) {
		//Si ya existía, no se puede crear el shortcode
		if(is_callable($alias) || function_exists($alias) ) {
			return false;
		}

		/**
			Nos encargamos de averiguar el nombre de la acción($action) que queremos 
			lanzar con nuestro alias y también su namespace( $original );
		*/
		$namespace = \team\NS::explode($original);
		$original = "\\{$namespace["package"]}\\{$namespace["component"]}";
		$response = $namespace['name'];

		//Mezclamos unas pequeñas opciones por defecto, con las escogidas por el usuario como por defecto
		$defaults = ['out' => 'html', 'response'=> $response] +  $defaults;
		//Almacenamos los datos necesarios para lanzar el shortcode 
		self::$list[$alias] = array('original' => $original, 'defaults' => $defaults );

		/** Creamos la función alias, que no hace más que llamar a la función transform con los datos pasados */
		eval('function '.$alias.'($params = array() ) { 
				return call_user_func(array("\team\Alias", "transform"), "'.$alias.'",  $params); 	
		}');
	
		return true;
	}

	public static function exists($alias) {
		return  isset(self::$list[$alias]); 
	}

	/**
		Transformamos el shortcode a lo que es su llamada original y la lanzamos
		@param string $alias es el nombre del atajo que queremos lanzar
		$params array $params Son los parámetros pasados en la llamada al atajo
	*/
	public static function transform($alias,array $params = [] ) {
		if(empty($alias) ) return false;

		//Obtenemos los valores predefinidos
		$defaults = self::$list[$alias]['defaults'];
		//Mezclamos los parámetros pasados con los valores predefinidos
		$params +=  $defaults;
		//Obtenemos la acción a llamar
		$response = $params['response'];
		//Obtenemos el nombre de la clase ( namespace del módulo )
		$original =  self::$list[$alias]['original'];
		//instanciamos la clase y lanzamos la acción.
		$class = new $original($params);

		return $class->$response();
	}


	/**
		Creamos un alias de una función/clase
		@param $original es el nombre de la función o clase que se quiere crear un alias
		@param $alias es el nuevo nombre o alias que tendrá. 
	*/
	private static function create($original, $alias ) {

		//Estamos ante un alia de función.
		if(!class_exists($original) && function_exists($original)  ) {
			if(! function_exists($alias) ) return false;

			//Creamos la función alias con un código que hace llamar a la función original
			eval('function '.$alias.'( ) { 
					$params = func_get_args(); 
					return call_user_func_array("'.$original.'", $params); 	
			}');

			//Lo guardamos en el registro de shortcodes realizados
			self::$list[$alias] = array('original' => $original );
			
			return true;
		}

		/* Si hemos llegado hasta aquí seguramente sea un alias de clase 
			Lo que hacemos es que desde ahora se pueda referiar a $alias 
			con el identificador de $original */
		if(class_exists($original))
			return class_alias($original, $alias, false);

		return false;
	}

	/**
		Sobreescribimos la función $antigua con la función $nueva
		@param string $nueva es la función a la que queremos crear un alias sobreescribiendo una anterior
		@param string $antigua es la función que ya existia que ahora vamos a usar como alias de $nueva
		@param array $defaults son uns parámetros predefinidos en caso de que se tenga que crear la función.
	*/
	public static function overwrite($nueva, $antigua, array $defaults = array() ) {	
		//Si la función antigua que queriamos crear no existe, lo que hacemos es un simple alias.
		if(!function_exists($antigua) ) return  self::create($nueva, $antigua, $defaults);

		/** Sobreescribimos la antigua función por una que llama a la función escogida */
		if(!function_exists('override_function') || !override_function($antigua, '', '$params = func_get_args(); return call_user_func_array("'.$nueva.'", $params)') ) {
			return false;
		}

		//Lo guardamos en el registro de shortcodes realizados
		self::$list[$antigua] = array('original' => $nueva );
		return true;
	}


	/** 
		Intercambiamos el nombre de dos funciones: $funct1 y $funct2
		@example:
		function sql_debug($sql) { \team\Debug::log($sql); return sql_debug($sql); }
		\team\Alias::swap("mysql_query", "sql_debug");
	*/ 
	private static function swap($funct1, $funct2) {
		$temp = $funct1.'_team_temporal';
		rename_function($funct1, $temp);
		rename_function($funct2, $funct1);
		rename_function($temp, $funct2);
	}


}
