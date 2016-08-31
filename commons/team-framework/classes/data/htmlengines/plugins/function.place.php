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

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 'AS IS' AND
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

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     function.place.php
 * Type:     function
 * Name:     place
 * Purpose:  Evento hooks para colocar en lugares especificos. Ejemplo: {place name="pie"} {place name="pie2"} {place name="top"} esto se convierte en: \template\places\pie, \template\places\pie2, \template\places\top
	Se diferencia a filter, en que place es un evento( y los awaiters pueden generar contenido o hacer cualquier tipo de operación ) y filter es un filtro( con lo que puede ser usado para generar contenido o para asignar valores a variables )
 * -------------------------------------------------------------
 */

function smarty_function_place($params, &$engine)
{


    $content = '';
    if(isset($params['name']) && is_string($params['name'])  && !empty($params['name'] )  ) {

        $pipeline = ('\\' == $params['name'][0])?	$params['name'] : '\team\places\\'.$params["name"];

		$cache_id = null; 
		if(isset($params['_cache']) ) {
			$cache = $params['_cache'];
			if(is_bool($cache) || 'true' == $cache ) {
				$cache_id = \team\Sanitize::identifier($pipeline);
			}else {
				$cache_id = \team\Sanitize::identifier($cache);
			}

			$cache_id = trim($cache_id, '_');
			$cache = \team\Cache::get($cache_id);

			if(!empty($cache)) {
				return $cache;
			}

		}

        $content =  \team\Filter::apply($pipeline, $content, $params, $engine);


		if(isset($cache_id) ) {
			if(isset($params['_cachetime']) ) {
				$cache_time =  strtotime($params['_cachetime']);
			}else {
				$cache_time =   \team\Date::A_DAY;
			}


			\team\Cache::overwrite($cache_id, $content, $cache_time );
		}
	}

    return $content;
}
