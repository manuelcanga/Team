<?php

namespace Team\Predefined;

use \Team\Config;

if(!defined('_TEAM_')) die("Hello, World!");

/**
Errors is handled for Debug and Team classes
 */
ini_set('display_errors', 0);

/**
 * Information about current team version
 * @since 0.1
 */

define('TEAM_VERSION', '0.1');

/**
 * Es el path relativo desde _SCRIPTS_ dónde se encuentra realizándose la ejecución.
 * En un principio es /, cuando estamos procesando un paquete es /:paquete:
 * y cuando estamos en un componente /:paquete:/:componente:
 * @since 0.1
 */

define('BASE', '/');

define('Team\_SERVER_', dirname(\_SCRIPTS_));


if(!defined('_SCRIPTS_') ) {
    define('_SCRIPTS_', \Team\_SERVER_.'/public_html' );
}

if(!defined('_APPS_') ) {
    define('_APPS_', \Team\_SERVER_.'/apps' );
}

if(!defined('Team\_VENDOR_') ) {
    define('Team\_VENDOR_', \Team\_SERVER_.'/vendor');
}

/**
 * SCRIPT_ID is usted as key in cookies, sessions, template cache, tokens, ...
 * @IMPORTANT!: SCRIPT_ID must be [a-Z|_ ]
 * @since 0.1
 */

Config::addSanitizer('SCRIPT_ID',
    function($script_id) {
        return \Team\Data\Sanitize::identifier($script_id);
    }
);
Config::set('SCRIPT_ID', basename(\Team\_SERVER_)  );



/**
TEAM looking for config for path to enviroments
You can change it in order to improve your system f
@since 0.1
 */
if(!defined('team\_CONFIG_') ) {
    define('team\_CONFIG_', \Team\_SERVER_.'/config');
}


/**
Directory used to save temporary files: logs, caches, etc
@since 0.1
 */

Config::addSanitizer('_TEMPORARY_',
    function($directory) {
        if(!file_exists($directory) ) {
            mkdir($directory, 0777, true);
        }
        return $directory;
    }
);

Config::set('_TEMPORARY_',\Team\_SERVER_.'/tmp/'.Config::get('SCRIPT_ID'));


Config::set('ENVIROMENT', 'dev');
Config::set('_THEME_', \_SCRIPTS_.'/theme');
Config::set('_TESTS_', \Team\_SERVER_.'/tests');
Config::set('LANG', 'es_ES');
Config::set('CHARSET', 'UTF-8');
Config::set('TIMEZONE', 'Europe/Madrid');

//Motor que se usara para procesar las vistas
Config::set('HTML_ENGINE',"TemplateEngine");

Config::addConstructor('CLI_MODE', function() {
    //Es posible lanzar TEAM framework desde terminal
    //Así que comprobamos si se está haciendo
    global $argv, $argc;
    $cli_mode = true;
    if('cli' != php_sapi_name() || empty($argv) || 0 == $argc  ) {
        $cli_mode = false;
    }

    \Team\Debug::trace('¿Cli mode activo?', $cli_mode);

    return $cli_mode;
});

/*
 * Un area se marca a traves de una url base.
 * Todas las peticiones webs que contengan esa url base formarán parte de esa zona.
 * A cada area( o zonas) se le puede asignar un target( /package/component ) que la procese.
 * El area vacía o con valor '/', se refiere al area principal. Pues todas las peticiones dependerán de ella
 *
 * Las areas más especificas( mayor path ) tienen prioridad sobre las más globales( menor path )
 */
Config::set('AREAS',  ['/' =>  '/web/welcome'] );

Config::set('REQUEST_METHOD', strtoupper($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']?? $_POST['_method']?? $_SERVER["REQUEST_METHOD"]));

$port = $_SERVER['SERVER_PORT']?? 80;


$is_ssl = false;
if ( isset($_SERVER['HTTPS']) &&  \Team\Data\Check::choice($_SERVER['HTTPS']) ) {
    $is_ssl = true;
} elseif (  '443' == $port  ) {
    $is_ssl = true;
}

Config::set('IS_SSL', $is_ssl );
Config::set('PROTOCOL', $is_ssl? 'https://' : 'http://' );
Config::set('DOMAIN',  trim($_SERVER["SERVER_NAME"], '/') );
Config::set('PORT', $port);

Config::addModifier('WEB', function($url){
    if(!empty($url)) return $url;

    $domain = \Team\System\Context::get('DOMAIN');

    $port = \Team\System\Context::get('PORT');
    $with_port = '';
    if('80' != $port && '443' != $port) {
        $with_port = ":{$port}";
    }

    $protocol =  \Team\System\Context::get('PROTOCOL');
    $domain = rtrim($domain, '/');



    return $url = "{$protocol}{$domain}{$with_port}";
});

//Añadimos las constantes que hubiera como variables de configuración
Config::set(get_defined_constants(true)['user']);



