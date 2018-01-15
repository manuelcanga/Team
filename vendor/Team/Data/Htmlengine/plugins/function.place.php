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
 * Purpose:  Evento hooks para colocar en lugares especificos. Ejemplo: {place name="pie"} {place name="pie2"} {place name="top"}
 * A los place se le puede incrustar vistas, widgets, contenidos, ... e incluso envolverlo en un wrapper.
 * Sólo usable desde las vistas pero se puede añadir elementos desde cualquier sitio a través de la clase \Team\Gui\Place
 * -------------------------------------------------------------
 */

function smarty_function_place($params, &$engine)
{


    $content = '';
    if(isset($params['name']) && is_string($params['name'])  && !empty($params['name'] )  ) {
        $place = $params['name'];
        unset($params['name']);

        //¿ Está el contenido de este place disponible desde la caché ?
		$cache_id = null; 
		if(isset($params['cache']) ) {
            $cache_id =  \Team\System\Cache::checkIds($params['cache'], $place);

            $cache = \Team\System\Cache::get($cache_id);

			if(!empty($cache)) {
				return $cache;
			}

		}


		$items = \Team\Gui\Place::getItems($place);

		if(!empty($items)) {
            foreach($items as $order => $target ) {
                $func = $target['item'];
                $content = $func( $content, $params, $engine );
            }
        }


        //Guardamos el contendio del place en la caché para otra vez
		if(isset($cache_id) ) {
            $cache_time = $params['cachetime']?? null;


			\Team\System\Cache::overwrite($cache_id, $content, $cache_time );
		}
	}

    return $content;
}
