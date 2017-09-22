<?php

namespace team\start;


class Configure
{

    /**
     * preConfigure the framework enviroment
     *
     */
    function preconfigure() {

        $this->preconfigureEnviroment();
        $this->preconfigureUrlConfigs();

        //Añadimos las constantes que hubiera como variables de configuración
        \team\Config::set(get_defined_constants(true)['user']);
    }

    private function preconfigureEnviroment() {
        //Añadimos la clase que gestiona los datos de session
        \team\Classes::add('\team\User', '/classes/User.php', _TEAM_);
        //Cargamos la clase Log para todo ayudar al programador/maquetador en su tarea.
        \team\Classes::add('\team\Log', '/classes/notices/Log.php', _TEAM_);


        \team\Config::set('ENVIROMENT', 'local');

        \team\Config::set('LANG', 'es_ES');
        \team\Config::set('CHARSET', 'UTF-8');
        \team\Config::set('TIMEZONE', 'Europe/Madrid');

        //Motor que se usara para procesar las vistas
        \team\Config::set('HTML_ENGINE',"TemplateEngine");


        //Es posible lanzar TEAM framework desde terminal
        //Así que comprobamos si se está haciendo
        global $argv, $argc;
        $cli_mode = true;
        if('cli' != php_sapi_name() || empty($argv) || 0 == $argc  ) {
            $cli_mode = false;
        }

        \team\Config::set('CLI_MODE',   $cli_mode );
    }


    private function preconfigureUrlConfigs() {

        /*
         * Un area se marca a traves de una url base.
         * Todas las peticiones webs que contengan esa url base formarán parte de esa zona.
         * A cada area( o zonas) se le puede asignar un target( /package/component ) que la procese.
         * El area vacía o con valor '/', se refiere al area principal. Pues todas las peticiones dependerán de ella
         *
         * Las areas más especificas( mayor path ) tienen prioridad sobre las más globales( menor path )
         */
        \team\Config::set('AREAS',  ['/' =>  '/web/welcome'] );


        $method  = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']?? $_POST['_method']?? $_SERVER["REQUEST_METHOD"];
        \team\Config::set('REQUEST_METHOD', strtoupper($method));

        $is_ssl = false;
        if ( isset($_SERVER['HTTPS']) &&  \team\Check::choice($_SERVER['HTTPS']) ) {
            $is_ssl = true;
        } elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
            $is_ssl = true;
        }

        \team\Config::set('IS_SSL', $is_ssl );
        \team\Config::set('PROTOCOL', $is_ssl? 'https://' : 'http://' );
        \team\Config::set('DOMAIN',  trim($_SERVER["SERVER_NAME"], '/') );
    }




    /**
     * Llamamos a los scripts de comienzos.
     * Estos scripts deberían de asignar filtros, eventos y tareas deseados
     */
    function launchConfigScripts() {
        \team\FileSystem::load('/Start.php', _TEAM_);
        \team\FileSystem::load('/commons/config/setup.php');
        \team\FileSystem::load('/commons/config/'. \team\Config::get('ENVIROMENT').'/setup.php');
    }



    /**
     *  Definimos un autoload de clases
     *
     *  Por cada clase desconocida que se instancie o se utilice sin haberse procesado, php llamara a Classes.
     *  Este método define un autoloader por defecto llamado Casses y avisa a php para que lo utilice
     */

    function registerAutoload() {
        spl_autoload_register(\team\Config::get('\team\autoload', ['\team\Classes', 'factory'] ));
    }

    function cachingSystem() {
        \team\Cache::__initialize();
    }


    function system() {
        \team\Config::setUp();
        \team\I18N::setUp();

        //Sistema de errores
        \Team::__initialize();
    }
}