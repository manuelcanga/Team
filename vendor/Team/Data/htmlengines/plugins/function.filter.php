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
 * File:     function.filter.php
 * Type:     function
 * Name:     filter
 * Purpose:  Filtro hooks para filtrar contenido. Ejemplos:
{filter name='\gui\breadscrumb' value=[] assign='breadscrumb'}
{filter name='\gui\menu' value=[] assign='menu'}
{filter name='\gui\title' value='Web Title' assign='title'}

	Se diferencia a place, en que place es un evento( y los awaiters pueden generar contenido o hacer cualquier tipo de operación ) y filter es un filtro( con lo que puede ser usado para generar contenido o para asignar valores a variables )
 * -------------------------------------------------------------
 */

function smarty_function_filter($params, &$smarty)
{
	if(isset($params['name']) )  {
		$name =  $params["name"];

		$value = null;
		if(isset($params['value']) ) {
			$value = $params['value'];
			unset($params['value']);
		}

		$assign = null;
		if(isset($params['assign']) ) {
			$assign = $params['assign'];
			unset($params['assign']);
		}

		$out =  \Team\Data\Filter::apply($name,$value, $params,  $smarty);

		if(isset($assign) ) {
			$smarty->assign($assign, $out);
		}else {
			return $out;
		}
	}	
	return "";
}
