<?php

namespace team\start\setup;

if(!defined("_SITE_") ) die("Hello,  World");

\team\Config::set('ENVIROMENT', 'local');


/** ___________________ URL _____________________  */

/** Especificamos si queremos areas asociados a paquetes y componentes o no
 *  El AREA '' o '/' se refiere al area principal (  )
 * A cada subdominio se le puede asignar un target( /package/component )
 * y zonas( suburls asociadas a package y componentes )
 *
 */
\team\Config::set('AREAS',  ['/' =>  '/package/welcome'] );
\team\Config::set('DOMAIN',  trim($_SERVER["SERVER_NAME"], '/') );


$is_ssl = false;
if ( isset($_SERVER['HTTPS']) ) {
    if ( 'on' == strtolower($_SERVER['HTTPS']) )
        $is_ssl = true;
    if ( '1' == $_SERVER['HTTPS']  )
        $is_ssl = true;
} elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
    $is_ssl = true;
}

\team\Config::set('IS_SSL', $is_ssl );
\team\Config::set('PROTOCOL', $is_ssl? 'https://' : 'http://' );


$method  = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']?? $_POST['_method']?? $_SERVER["REQUEST_METHOD"];
\team\Config::set('REQUEST_METHOD', strtoupper($method) );



/** ___________________ LOCALES _____________________  */

\team\Config::set('LANG', 'es_ES');
\team\Config::set('CHARSET', 'UTF-8');
\team\Config::set('TIMEZONE', 'Europe/Madrid');


/*  _________________________  TRANSFORM ________________________ */
//Motor que se usara para procesar las vistas
\team\Config::set('HTML_ENGINE',"TemplateEngine");


/*  _________________________  CONSTANTS ________________________ */

//Añadimos las constantes que hubiera como variables de configuración
\team\Config::set(get_defined_constants(true)['user']);