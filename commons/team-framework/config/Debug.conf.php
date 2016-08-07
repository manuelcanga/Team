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


namespace config\team; 


/**
	Establece los parametros de depuracion.
	Muy util en el desarrollo
*/

class Debug extends \team\Config {

	/**
		Message en muy critical error
	*/
	protected $CRITICAL_MESSAGE = "We are in maintenance, sorry";

	/** 
		Establecemos si queremos cazar los errores 
	*/
	protected $SHOW_ERRORS = true;

	/**
		Nivel de error general a procesar
	*/
	protected $GENERAL_ERROR_LEVEL = E_ALL;

	/** 	
	Establecemos si no queremos mostrar los errores en el navegador(false)
	o en caso contrario el tipo de visualizarlo('data', 'echo')
	También hay que tener en cuenta que si la salida de la acción principal(main)
	no es html, se forzará la salida por los logs.

	*/
	protected $SHOW_IN_NAVIGATOR = 'data';

	/**
		Especifica si los logs escribirian en archivo(true) o no(false)
		exceptuando para fallos criticos donde se escribiran siempre.
	*/
	protected $LOGS_WRITE = true;

	/**
		Especifica si se escribirá en el error.log de apache(true) o 
		en los de team(false). 
		Este último no es recomendable, a no ser que no tenga acceso
		al error log de apache. 
		Si se usa, no olvidar borrarlo después por segurida
		Tambien tiene que notar que los errores en error_log no tienen formato.
	*/
	protected $ERROR_LOG = false;
	
	
	/**
		Muestra traza de los cambios de estados en Team
	*/
	protected $SHOW_LEVEL = false;

	/**
		Muestra las trazas general del recorrido de ejecución del framework. 
	*/
	protected $SHOW_TRACE = false;

	/**
		Muestra advertencias de recursos(css, js, views ) no encontrados 
	*/
	protected $SHOW_RESOURCES_WARNINGS = false;

	/**
		Muestra una traza de los eventos lanzados en Team
		Asi comos lo Awaiters que lo recogen
	*/
	protected $TRACE_EVENTS = false;

	/**
		Muestra una traza de las acciones que se cargan
	*/
	protected $TRACE_ACTIONS = false;

	/**
		Muestra una traza de la carga de archivos de configuracion
	*/
	protected $TRACE_CONFIG = false;
	

	/**
		Especifica si visualizamos la traza de cargado de clases
	*/
	protected $TRACE_AUTOLOAD_CLASS = true;
	
	/**
		Mostramos informacion de todas las consultas que se realizan
	*/
	protected $TRACE_SQL = false;
	
	/**
		Decidimos si mostramos o no la salida de las vistas
	*/
	protected $SHOW_VIEWS = true;

	/**
		Decidimos el nivel de error a mostrar en las vistas
	*/
	protected $VIEWS_ERROR_LEVEL = E_ALL ^ E_NOTICE;

}
