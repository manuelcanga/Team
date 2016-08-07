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

if(!class_exists('\Url', false) ) {
	class_alias('\team\Url', 'Url', false);
}


class Url {
	
	/**
		Lanza un patrón de url contra una url y devuelve true(y los parámetros derivados del patrón ) o false en caso de no encajar.
		@param string $_url_to_check es la url( haystack ) sobre la que se hará el checkeo
		@param string $_url_pattern es un patrón de url(needle ) que se quiere comprobar 
		@params array $matches los smatches si se han encontados( los valores que se hayan pasado se conservan y se usarán como valor por defecto con el resultado )
        @params array $others Optional. Otras key/value que queremos que se añadan a los de match si esta última función tuviera exito.
		@return false|true  retorna false si no se encontró el patrón o array con los parametros resultantes de la comprobación
		@see https://trasweb.net/blog/desarrollo-a-medida/simple-routeador-para-php

		@example with url_pattern: '/~/[i:id][/i:pagina][/i:fecha]'
			url_to_check: /destacados/editar-categoria/peliculas/estrenos/imagenes/6
			>[ id => 6 ]
			url_to_check: /destacados/editar-categoria/peliculas/estrenos/imagenes/6/10
			> [ id => 6, 'pagina => 10 ]
			url_to_check: /destacados/editar-categoria/peliculas/estrenos/imagenes/6/10/2012
			> [ id => 6, 'pagina => 10, fecha => 2012 ]
	*/
	static function match($_url_to_check, $_url_pattern, array &$matches = null, array $others = []) {
	 	 $defaults = $matches?: [];

	 	 $url = '/'.trim(rawurldecode(parse_url($_url_to_check, PHP_URL_PATH)), '/');
	     $url_pattern = '/'.trim(rawurldecode(parse_url( $_url_pattern, PHP_URL_PATH)), '/');

		//Para un número ilimitado de elementos. Ejemplo /noticias/~/:pagina  calzaría con /noticias/listado/destacados/10 y daría como resulado ['pagina' => 10]
  		 $url_pattern = preg_replace('@~@', '(.*?)',  $url_pattern);

		//Elementos opcionales /[:prueba]/  o /noticias/[list]/ o /noticias[-listado]/
 	     $url_pattern = preg_replace('@:?\\[(.*)\\]@U', '($1)?', $url_pattern);

		//Validamos que sea un parámetro de tipo i-nt
 	  	 $url_pattern = preg_replace('@i:([\w\-]+)@', '(?<\1>\d+)',  $url_pattern);

		//Validamos que sea un parámetro de tipo t-textual(not numeric) and '_'
 	  	 $url_pattern = preg_replace('@t:([\w\-]+)@', '(?<\1>[a-zA-Z\_]+)',  $url_pattern);

		//Parámetros   /noticias/:response/:id
 	  	 $reg_expresion = preg_replace('@:([\w]+)@', '(?<\1>[a-zA-Z0-9\-\_\.\~]+)',  $url_pattern);

		 $matches = [];

		 if( !preg_match("@^{$reg_expresion}$@", $url, $matches) ) {
			$matches += $defaults;
			return false;
		}else {
			$matches =  array_filter($matches, function($value, $key) {return !is_int($key);}, ARRAY_FILTER_USE_BOTH);
			$matches  = $matches + $others +  $defaults;

			return true;
		}
	}

	/**
		Hace un reemplazo de variables en un patrón de url por sus correspondientes valores.
		@param string url sobre la que se hará los reemplazos
		@param string $_params son los reemplazos para las patrones de variables en la url
		@param matches son los reemplazos realizado ( muy útil porque no siempre se reeplazan todos los valores de $_params. Así sabemos cuales se usaron-$matches- y cuales no )
		@return deveuelve la url con todos los reemplazos realizados.
		@see https://trasweb.net/blog/desarrollo-a-medida/simple-routeador-inverso
		Ej: Los parámetros se reemplazan por las variables( en caso de que tengan valor )
			url= /blog/:anio/:mes/:dia/[:slug[-:id].html]
			con ['anio': 2016, 'mes':6, 'dia':20, 'slug': 'post-ejemplo' ]
			resultado sería: /blog/2016/6/20/post-ejemplo.html

		Ej2: Si dentro de un corchete hay alguna variable directa(no anidada) que no tenga valor, se reemplaza el corchete por cadena vacía
			url= /blog/:anio/:mes/:dia/[:slug[-:id].html]
			con ['anio': 2016, 'mes':6, 'dia':20 ]
			resultado sería: /blog/2016/6/20/

		Ej2: Si antes de un corchete se pone dos puntos es un corchete restrictivo: Si dentro ese corchete hay una variable directa sin valor, 
			el contenido del corchete es vacío pero el inmediatamente superior también.
			url= /blog/:anio/:mes/:dia/[:slug:[-:id].html]
			con ['anio': 2016, 'mes':6, 'dia':20,  'slug': 'post-ejemplo' ]
			resultado sería: /blog/2016/6/20/
			No se muestra slug porque id está dentro de un corchete restrictivo.
	*/

	static function to($_url, $_params = [], array &$matches =null) {
		$matches = $matches?: [];

		//Procesamos las variables
        $buscar_vars='@[it]?:([a-zA-Z0-9]+)@';
		
		$url = preg_replace_callback($buscar_vars, function($_matches) use($_params, &$matches) {
			//matches[0] con puntos
			//matches[1] sin puntos;
			if(isset($_matches[1]) ) {
				$var = $_matches[1];
			    $hay_valor_para_var = isset($_params[$var]);
				if($hay_valor_para_var) {
					$matches[] = $var;
					return $_params[$var];
				}else {
					return '@'; //not value for this var
				}
			}
			
		}, $_url);
		
		//Eliminamos todos los elementos corchetes
		$busca_corchetes='%:?\\[(?:([^\\[\\]]*)|\[(?R)\])\\]%';
		do {
			$url = preg_replace_callback($busca_corchetes, function($matches) {
				//matches[0] con corchetes
				//matches[1] sin corchetes;		


                $hubo_dentro_corchetes_parametro_sin_valor = strpos($matches[1], '@') !== false;
				$es_un_corchete_restrictivo =  (':' == $matches[0][0]);
				if( $hubo_dentro_corchetes_parametro_sin_valor ) {
					if($es_un_corchete_restrictivo) {
						return '@'; //Si manda la restrinción al nivel superior.
					}else {
						return ''; //Si lleva arroba es porque un elemento se quedo cojo, por tanto, no devolvemos nada.
					}
				}else {	
					return $matches[1]; //Devolvemos todo tal cual( sin los corchetes )
				}

			}, $url, -1, $count);

		}while( $count != 0);

		//Quitamos el rastro de @ en el string
		return str_replace('@', '', $url);

	} 

	

}
