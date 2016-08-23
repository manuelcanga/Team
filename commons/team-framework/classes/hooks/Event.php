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
	Manda avisos del sistema. 
*/

namespace team;


class Event {
	
	/* Almacen de todos los awaiters */
	private static $awaiters = array();

	/**
		Añadimos un awaiter a la espera de un evento 
		@param namespace $event Evento a esperar
		@param callable $awaiters Awaiter que se queda a la escucha
		@param int $order Posición en la llamada de eventos
	*/
	public static function listen($event, $awaiter, $order = 65) {
		$event = rtrim($event, '\\');
		if(!is_callable($awaiter) ) return ;
		$order = \team\Check::id($order);

		//Si no habia awaiters asociados al evento, ahora si
		self::$awaiters[$event] =  isset(self::$awaiters[$event])? self::$awaiter : [];

		//Vamos buscando un hueco libre para el trabajador a partir del orden que pidió
		for($max_order = 100; isset(self::$awaiters[$event][$order]) && $order < $max_order; $order++);

		//Guardamos el awaiter
		self::$awaiters[$event][$order] = $awaiter;
	}


	/**
		Aviso de evento. Es una notificación de tipo neutro.	
		Se recorre todos los awaiters hasta que uno devuelva true. En ese momento se para el barrido.
		@param namespace $code es el código o namespace del evento ocurrido. 
		@param $data es un dato que se quiere transmitir con el evento.
		
		@return boolean devuelve si algún awaiter cancelo o no el evento( retornando true: cancela, false/null: no) 
	*/
	public static function send($code, ...$data) {
        $namespace = rtrim($code, '\\');


        if(isset(self::$awaiters[$namespace])  ) {
			$data[] = $namespace;	

		    foreach(self::$awaiters[$namespace] as $awaiter) {
		        //mandamos el trabajo al awaiter
		        $result =  $awaiter(...$data);
		        if($result) return $result;
		    }
		}

		return false;
	}



}
