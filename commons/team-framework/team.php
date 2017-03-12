<?php
/**
 * New Licence bsd:
 * Copyright (c) <2016>, Manuel Jesus Canga Muñoz
 * All rights reserved.
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * - Redistributions of source code must retain the above copyright
 * notice, this list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright
 * notice, this list of conditions and the following disclaimer in the
 * documentation and/or other materials provided with the distribution.
 * - Neither the name of the trasweb.net nor the
 *  names of its contributors may be used to endorse or promote products
 * derived from this software without specific prior written permission.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 'AS IS' AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL Manuel Jesus Canga Muñoz BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace team;


/**
  Information about current team version
  @since 0.1
*/
define('TEAM_VERSION', '0.1');

/**
  Es el path relativo desde _SITE_ dónde se encuentra realizándose la ejecución.
  En un principio es /, cuando estamos procesando un paquete es /:paquete:
  y cuando estamos en un componente /:paquete:/:componente:
  @since 0.1
*/  
define('BASE', '/');

/** 
  Filesystem absolute path until root directory of Team Framework 
  ( always without / end )
  @since 0.1
*/
define('_TEAM_', __DIR__);


/**
	team\SITE_ID is usted as key in cookies, sessions, template cache, tokens, ...
	team\SITE_ID must be [a-Z|_ ]
	
	@since 0.1
*/
if(!defined('team\SITE_ID') ) {
	define('team\SITE_ID', basename(\_SITE_) );
}


if(!defined('team\_THEME_') ) {
    define('_THEME_', _SITE_.'/commons/theme');
}


/**
  TEAM looking for config from CONFIG_PATH 
  You can change it in order to improve your system security
  @since 0.1
*/
if(!defined('team\\CONFIG_PATH') ) {
	define('team\\CONFIG_PATH', \_SITE_);
}


/**
  Directory used to save temporary files: logs, caches, etc
   @since 0.1
*/
if(!defined('_TEMPORARY_DIRECTORY_') ) {
	define('_TEMPORARY_DIRECTORY_', \_TEAM_.'/data');

	if(!file_exists(_TEMPORARY_DIRECTORY_) ) {
        mkdir(_TEMPORARY_DIRECTORY_, 0777, true);
    }
}



/**
  Errors is handled for Debug and Team classes
*/
ini_set('display_errors', 0);


//Utilidades sobre el sistema de archivos
require(\_TEAM_.'/classes/FileSystem.php');
//Cargamos la clase Filter que se encarga de las validaciones
require(\_TEAM_.'/classes/Check.php');
//Filter, permite el filtrado de datos de modo desacoplado.
require(\_TEAM_.'/classes/hooks/Filter.php');
//Trait para las clases que manejan variables de configuración: Config y Context
require(\_TEAM_.'/includes/data/Vars.php');
//La clase que gestiona opciones de configuración
require(\_TEAM_.'/classes/Config.php');
//Classes se encarga de la autocarga y manejo de clases
require(\_TEAM_.'/classes/loaders/Classes.php');
//Manejo de configuración de locales
require(\_TEAM_.'/classes/I18N.php');
//Plantilla para la gestión fáci de datos de una clase
require(\_TEAM_.'/includes/data/Storage.php');
//La clase que gestiona caché
require(\_TEAM_.'/classes/Cache.php');
//La clase Context nos sirve para tener un control de variables de configuracion en funcion del contento
require(\_TEAM_.'/classes/Context.php'); 
//La clase Team, Notice y Erros llevan un control de las notificaciones de  avisos y errores del sistema
require(\_TEAM_.'/classes/notices/Errors.php');
require(\_TEAM_.'/classes/notices/Notice.php');
require(\_TEAM_.'/classes/notices/Team.php');
//La gran clase Data es un gestor de datos y su representación en distintos formatos
require(\_TEAM_.'/classes/Data.php');
//Para el manejo fácil de namespaces
require(\_TEAM_.'/classes/NS.php');
//Task permite la delegación de tareas
require(\_TEAM_.'/classes/hooks/Task.php');
//Cargamos la clase Debug y Log para todo ayudar al programador/maquetador en su tarea.
require(\_TEAM_.'/classes/notices/Log.php');
require(\_TEAM_.'/classes/notices/Debug.php');
//Añadimos la clase para gestionar componentes virtualmente
require(\_TEAM_.'/classes/builders/Component.php');
//Clase que sirve de clase base para los controladores
require(\_TEAM_.'/classes/controller/Controller.php');
//Clase que hace funciones de limpieza
require(\_TEAM_.'/classes/Sanitize.php');
//Clase que maneja cabeceras http
require(\_TEAM_.'/classes/Http.php');
//Clase que maneja base de datos
require(\_TEAM_.'/classes/DB.php');


//Añadimos la clase que gestiona los datos de session
\team\Classes::add('\team\User', '/classes/User.php', _TEAM_);
//Cargamos la clase Log para todo ayudar al programador/maquetador en su tarea.
\team\Classes::add('\team\Log', '/classes/notices/Log.php', _TEAM_);

try {

    /**
     * 1. Llamamos a los scripts de comienzos.
     * Estos scripts deberían de asignar filtros, eventos y tareas deseados
     */

    \team\FileSystem::load('/Start.php', _TEAM_);
    \team\FileSystem::load('/commons/config/setup.php');
    \team\FileSystem::load('/commons/config/'. \team\Config::get('ENVIROMENT').'/setup.php');


    /**
     * 2. Definimos un autoload de clases
     *
     *  Por cada clase desconocida que se instancie o se utilice sin haberse procesado, php llamara a Classes.
     *  Este método define un autoloader por defecto llamado Casses y avisa a php para que lo utilice
     */

    spl_autoload_register(\team\Config::get('\team\autoload', ['\team\Classes', 'factory'] ));


    /**
     * 3. Inicializamos el sistema de caché
     */
    \team\Cache::__initialize();

    /**
     * 4. Inicializamos el sistema de configuración
     */
    \team\Config::setUp();
    \team\I18N::setUp();


    /**
     * 5. Se inicia el proceso de gestión de errores
     */
    \Team::__initialize();

//Evitamos a toda costa que se quede congelado el sistema
}catch(\Throwable $e) { 
	\Team::critical($e);

}


function up() {

    try {

        \team\Debug::trace("Se inicializo el contexto. Ya podemos empezar a inicializar todo el framwork");

        /**
         * 6. Se levanta el sistema MVC
         */
        \Team::event('\team\start');


        /**
         * 7. Se parsea los parámetros de entrada
         */
        $REQUEST_URI = \team\Filter::apply('\team\request_uri', $_SERVER["REQUEST_URI"]);
        $args = \team\Task('\team\url', array() )->with($REQUEST_URI);


        /**
         * 8. Se llama al encargado( un componente o función __main ) de procesar el primer response o main
         */
        $result =  \team\Task('\team\main', '')->with($args);

        \team\Debug::trace("Se acabó, ya hemos realizado todas las operaciones pedidas. Bye!");


        /**
         * 9. Se acaba de procesar y se devuelve la respuesta
         */
        \Team::event('\team\end', $result);
        return $result;


        //Evitamos a toda costa que se quede congelado el sistema
    }catch(\Throwable $e) {
        \Team::critical($e);

    }
}
