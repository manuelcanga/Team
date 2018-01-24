<?php

namespace Team\Predefined;

use \Team\Config;

if(!defined('_TEAM_')) die("Hello, World!");


/**
 *  Definimos un autoload de clases
 *
 *  Por cada clase desconocida que se instancie o se utilice sin haberse procesado, php llamara a Classes.
 *  Este método define un autoloader por defecto llamado Casses y avisa a php para que lo utilice
 */

spl_autoload_register(Config::get('\team\autoload', ['\Team\Loader\Classes', 'factory'] ));


\Team\System\Cache::__initialize();

//Sistema de errores
\Team::__initialize();

\Team\System\I18N::setTimezone();
\Team\System\I18N::setLocale();


//Añadimos la clase que gestiona los datos de session
\Team\Loader\Classes::load('\team\client\User', '/Client/User.php', _TEAM_);

