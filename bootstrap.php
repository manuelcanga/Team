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

namespace Team;


/**
Filesystem absolute path until root directory of Team Framework
( always without / end )
@since 0.1
 */
define('_TEAM_', __DIR__);

//Utilidades sobre el sistema de archivos
require(\_TEAM_ . '/System/FileSystem.php');
//Cargamos la clase Filter que se encarga de las validaciones
require(\_TEAM_ . '/Data/Check.php');
//Filter, permite el filtrado de datos de modo desacoplado.
require_once(\_TEAM_ . '/Data/Filter.php');
//Trait para las clases que manejan variables de configuración: Config y Context
require(\_TEAM_ . '/Data/Vars.php');
//La clase que gestiona opciones de configuración
require(\_TEAM_ . '/Config.php');

//Classes se encarga de la autocarga y manejo de clases
require(\_TEAM_ . '/Loader/Classes.php');
//Manejo de configuración de locales
require(\_TEAM_ . '/System/I18N.php');
//Plantilla para la gestión fáci de datos de una clase
require(\_TEAM_ . '/Data/Storage.php');
//La clase que gestiona caché
require(\_TEAM_ . '/System/Cache.php');
//La clase Context nos sirve para tener un control de variables de configuracion en funcion del contento
require(\_TEAM_ . '/System/Context.php');
//La clase Team, Notice y Erros llevan un control de las notificaciones de  avisos y errores del sistema
require(\_TEAM_ . '/Notices/Errors.php');
require(\_TEAM_ . '/Notices/Notice.php');
require(\_TEAM_ . '/Team.php');
//La gran clase Data es un gestor de datos y su representación en distintos formatos
require(\_TEAM_ . '/Data/Data.php');
//Para el manejo fácil de namespaces
require(\_TEAM_ . '/System/NS.php');
//Task permite la delegación de tareas
require(\_TEAM_ . '/System/Task.php');
//Cargamos la clase Debug y Log para todo ayudar al programador/maquetador en su tarea.
require(\_TEAM_ . '/Notices/Log.php');
require(\_TEAM_ . '/Debug.php');

if(!class_exists('Debug', false)) {
    class_alias('\team\Debug', 'Debug', false);
}

//Añadimos la clase para gestionar componentes virtualmente
require(\_TEAM_ . '/Builder/Component.php');
//Clase que sirve de clase base para los controladores
require(\_TEAM_ . '/Controller/Controller.php');
//Clase que hace funciones de limpieza
require(\_TEAM_ . '/Data/Sanitize.php');
//Clase que maneja cabeceras http
require_once(\_TEAM_ . '/Client/Http.php');
//Clase que maneja base de datos
require(\_TEAM_ . '/System/DB.php');


try {

    require \_TEAM_. '/Predefined/config.inc.php';
    require \_TEAM_. '/Predefined/tasks.inc.php';
    require \_TEAM_. '/Predefined/filters.inc.php';

    //Llamamos para que el proyecto inicie sus config, tasks, filters, ...
    \Team\System\FileSystem::load('/config/setup.php', \Team\_SERVER_);
    \Team\System\FileSystem::load('/'. \Team\Config::get('ENVIRONMENT').'/setup.php', \Team\_CONFIG_);

    require \_TEAM_. '/Predefined/system.inc.php';


//Evitamos a toda costa que se quede congelado el sistema
}catch(\Throwable $e) { 
	\Team::critical($e);

}


function up() {

   \Team\Debug::trace();
    try {

        \Team\Debug::trace("Se inicializo el contexto. Ya podemos empezar a inicializar todo el framwork");

        /**
         * 6. Se levanta el sistema MVC
         */
        \Team::event('\team\start');


        /**
         * 7. Se parsea los parámetros de entrada
         */
        $REQUEST_URI = \Team\Data\Filter::apply('\team\request_uri', $_SERVER["REQUEST_URI"]);
        $args = \Team\System\Task('\team\url', array() )->with($REQUEST_URI);


        /**
         * 8. Se llama al encargado( un componente o función __main ) de procesar el primer response o main
         */
        $result =  \Team\System\Task('\team\main', '')->with($args);

        \Team\Debug::trace("Se acabó, ya hemos realizado todas las operaciones pedidas. Bye!");


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
