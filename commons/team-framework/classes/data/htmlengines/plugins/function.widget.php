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
 * File:     function.widget.php
 * Type:     function
 * Name:     widget
 * Purpose:  output content of an response ( if 'assign' exists then put content in it )
 * -------------------------------------------------------------
 * IMPORTANMTE:Ojo, usar comillas simples en la plantilla para el name. Sino podría haber colisión con las secuencias de escape. Ejemplo:
 * {widget name="\widgets\news"} -> se interpretan \w y \n como secuencias de escape, mejor poner:  {widget name='\widgets\news'}
 */

function smarty_function_widget($params = [], &$smarty)
{
	//Si no existe name, salimos.
	if(!isset($params['name']) ) {
		return '';
	}


	$widget_name = $params['name'];
	$namespace =  \team\NS::explode($params['name']);


	if(isset($namespace['name']) ) {
		$namespace['response'] = $namespace['name'];
		unset($namespace['name']);
	}
	$params =  $namespace + $params;


	//No se ha pasado un componente correcto
	if(!\team\FileSystem::exists("/".$params['package'].'/'.$params['component']) ) { 
		\team\Debug::me('Change " to \' in your widget name, please');
		return ;
	}


	$cache_id = null; 
	if(isset($params['_cache']) ) {
		$cache = $params['_cache'];
		if(is_bool($cache) || 'true' == $cache ) {
			$cache_id = \team\Sanitize::identifier($widget_name);
		}else {
			$cache_id = \team\Sanitize::identifier($cache);
		}

		$cache_id = trim($cache_id, '_');


		$cache = \team\Cache::get($cache_id);
		if(!empty($cache)) {
			return $cache;
		}
	}


	//Es una llamada incrustada en la vista
	$params['embedded'] = true;
	//Para más información le indicamos que lo hacemos desde la vista.
	$params['widget'] = true;
	//No es una llamada main
	$params['is_main'] = false;
	
	if(!isset($params['out'])) {
		$params['out'] = 'html';
	}

	$class_name = '\\'.$params['package'].'\\'.$params['component'];



	if(!class_exists($class_name) ) {
		return '';
	}

	$controller = new $class_name($params);
	//team\Debug::out($params);

	$result = trim($controller->retrieveResponse());

	if(isset($cache_id) ) {
		$cache_time =  $namespace['_cache_time']?? \team\Date::A_DAY;
		\team\Cache::save($cache_id, $result, $cache_time );
	}


	//Si se paso un parametro assign, se le asigna el resultado ahi
	if(isset($params['assign']) && !empty($params['assign']) ) {
		$var = $params['assign'];
		$smarty->assign($var, $result);
		return '';
	}else {
		return $result;
	}

}
