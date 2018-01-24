<?php

namespace Team\Predefined;

use \Team\Config;
use \Team\System\Task;

/*
  En el event start, añadimos los hooks necesarios para parsear los datos de entrada y devolverlos para poder ser usado para lanzar la acción main( ver abajo )
*/
\Team::addListener('\team\start', function() {


    if(\team\Config::get('CLI_MODE')) {
        return include(_TEAM_ . '/Start/cli.php');
    }


    //Si hemos llegado hasta aquí, queremos lanzar un MVC basados en la url
    //Suele suer la opción principal.
    include(_TEAM_ . '/Start/check_areas.php');
    include(_TEAM_ . '/Start/parse_url.php');
    include(_TEAM_ . '/Start/from_url.php');

});


/*
  Es posible crear un MVC personalizado(se lanzaría en vez de lanzar un componente main ) creando la función __main
  Esto es muy útil para hacer scripts pequeños usando las librerías de TEAM pero sin hacer uso de su modelo MVC.
  También permite crear pequeños comandos cli que no necesitan ser organizados en paquetes/componentes
*/
if(function_exists('__main') ) {
    Task::join('\team\main', function ($args) {
        $this->finish();

        $result =  __main($args);

        return $result;
    });
}



/*
  Definimos un trabajador para la tarea de lanzar la primera acción
  Es este worker el que desencadena el mvc.
 */
Task::join('\team\main', function($args) {

    \Team\Debug::trace("Instanciamos \Component para lanzar la primera response", $args);

    $component = new \Team\Builder\Component($args );

    $this->finish();

    return $component->retrieveResponse();
});

