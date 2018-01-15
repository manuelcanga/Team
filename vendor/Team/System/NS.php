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

namespace Team\System;

/** 
 Funciones útiles para manejo de namespaces
*/
class NS {

	/**
		Convierte un path a namespace
		@param path $path cadena path que se quiere transformar a string
		@example: \Team\System\NS::toPath("/Team/news/index")  -> \Team\news\index
	*/
	public static function pathToNS($path) {
  		 $path = trim($path, '/');
		 return '\\'.str_replace('/', '\\', $path); 
	}

	/**
		Convierte un namespace en path
		@param namespace $namespace cadena namespace que se quiere pasar a path
		@example: \Team\System\NS::toPath("\Team\news\index")  -> /Team/news/index
	*/
	public static function toPath($namespace) { 
		 $path = str_replace('\\', '/', $namespace);

		 $path = \Team\Data\Sanitize::trim($path, '/');

		 return $path; 
	}

	/**
		Transforma el path relativo de un archivo a namespace
		@param $file nombre de archivo con su path
		@example: \Team\System\NS::fileToNS("/Team/news/index.html")  -> \Team\news
	*/
	public static function fileToNS($file) {
		$path = basename($file);
		return self::pathToNS($path);
	}


	/**
		Convierte una cadena(normalmente, namesapace o path) a uno más manejable/friendly
		@param string $str cadena que se quiere transformar
		@param char $separator carácter separador de elementos en $str ( \ para namespace, / para path ) 
		@example: \Team\System\NS::friendly("\Team\news\Evento") ->  "team_news_Evento"
	*/
	public static function friendly($str = '', $separator = '\\' ) {
		$str = trim($str, $separator);
	
		return str_replace($separator, '_', $str);
	}

	/**
		Bajamos un nivel del namespace/path pasado.
		@param string $str cadena a la que queremos bajar un nivel
		@param char $separator carácter separador de elementos en $str ( \ para namespace, / para path ) 
		@example NS::shift("\Team\news") -> "\Team";
	*/
	public static function shift($str, $separator = '\\', &$level = null) {
		$str = trim($str, $separator);
			
		//Dividimos el array 
		$str = explode($separator, $str);

		//Nos quitamos el último elemento del namespace
		array_pop($str);

        if($level) {
            $level = count($str);
        }

		return '\\'.implode($separator, $str);
	}

	/**
		Obtiene el último elemento de un namespace o un path ( muy util para extraer nombre de 
		clases, ideal para las Tasks )
		@param string $str cadena de la que queremos bajar el último elemento.
		@param char $separator carácter separador de elementos en $str ( \ para namespace, / para path ) 
		@example NS::basename("\Team\news") -> "news";
		@example NS::basename("/Team/news/data/Prueba.jpg") -> "prueba.jpg"
	*/
	public static function basename($str, $separator = '\\') {
		$str = trim($str,  $separator);

		//Dividimos el array 
		$str = explode($separator, $str);

		return array_pop($str);
	}
	
	/** 
		Separa el namespace en sus paquetes
		@param string $namespace cadena a la que extraeremos todos sus elementos
		@param char $separator carácter separador de elementos en $namespace ( \ para namespace, / para path ) 
		@example NS::explode("\Team\news\index") -> package="Team",component="news", response="main", others=array()
	*/
	public static function explode($namespace, $separator = '\\') {
		//Si ya es un array, aprovechamos eso, sino hay que crearlo
		if(!is_array($namespace) ) {
			$namespace = trim($namespace, $separator );	
			$namespace = explode($separator, $namespace);
		}	


	  //Nos aseguramos que todos los valores son correctos
	   $namespace = array_filter($namespace, function($value) { return \Team\Data\Check::key($value, false); });

		//Obtenemos el paquete, componente y acción del namespace
		list($package, $component) = array_pad($namespace, 3, null);

		$others = array_slice($namespace, 2);

		$name =  array_pop($others);
		if(empty($name) ) $name = null;
 

		$path = '/'.implode('/', $namespace);
		

		return array('namespace'=> $namespace, 'package' => $package, 'component' => $component,   'name' =>$name , 'others' => $others, 'path' => $path);
	}
}
