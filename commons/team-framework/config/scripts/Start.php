<?php if(!defined("_SITE_") ) die("Hello,  World");


/*
  En el event start, añadimos los hooks necesarios para parsear los datos de entrada y devolverlos para poder ser usado para lanzar la acción main( ver abajo )
*/
\Team::addListener('\team\start', function() {
    global $argv, $argc, $_CONTEXT;

	//Es posible lanzar TEAM framework desde terminal
	//Así que comprobamos si se está haciendo
	$cli_mode = true;
	if('cli' != php_sapi_name() || empty($argv) || 0 == $argc  ) {
		$cli_mode = false;
	}

	\team\Debug::trace('¿Cli mode activo?', $cli_mode);
	$_CONTEXT['CLI_MODE'] =  $cli_mode;

	if($cli_mode) {
	  return include(__DIR__.'/start/cli.php');
	}
    
 
	//Si hemos llegado hasta aquí, queremos lanzar un MVC basados en la url
	//Suele suer la opción principal. 
    include(__DIR__.'/start/check_areas.php');
    include(__DIR__.'/start/parse_url.php');
    include(__DIR__.'/start/from_url.php');

});

	
/* 
  Es posible crear un MVC personalizado(se lanzaría en vez de lanzar un componente main ) creando la función __main 
  Esto es muy útil para hacer scripts pequeños usando las librerías de TEAM pero sin hacer uso de su modelo MVC.
  También permite crear pequeños comandos cli que no necesitan ser organizados en paquetes/componentes 
*/
if(function_exists('__main') ) {
	\team\Task::join('\team\main', function ($args, $_CONTEXT) {
		$this->finish();

		\team\Context::open('\\');
		$result =  __main($args, $_CONTEXT);
		\team\Context::close();

		return $result;
	});
 }



/*
  Definimos un trabajador para la tarea de lanzar la primera acción
  Es este worker el que desencadena el mvc.
 */
\team\Task::join('\team\main', function($args) {
    \team\Debug::trace("Instanciamos \Component para lanzar la primera response", $args);
    
    $component = new \team\Component($args );
    
    $this->finish();
    
    return $component->retrieveResponse();
});
