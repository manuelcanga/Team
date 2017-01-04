<?php 

namespace team\start\cli;

if(!defined("_SITE_") ) die("Hello,  World");



/**
 * Ahora vamos a comprobar si estamos lanzando Team en modo Cli mode
 */
\team\Task::join('\team\url', function() {
	
	global $argv;

	$command =  $args->response = extract_command($argv);

	\team\Debug::trace("Vamos a procesar los parámetros CLI", $argv);

	if(is_first_argument_an_url($argv)) {
	  $url =  array_shift($argv);
	  $args = get_store_for_args($url);
	}else {
	  $args = get_store_for_args();
   }
	 
	$args->_all = $argv; 
	$args->addData( parse_args_from_argv($argv) );

   \team\Debug::trace("Acabado el proceso de analisis CLI", $args);

    $this->finish();


	return $args;

});



/**
  First argument is name of command( ej: php ./site/index.php -> command: index )
*/
function extract_command(& $argv) {
 return \team\FileSystem::basename(array_shift($argv));
}


/**
  Checkeamos si el primer de los argumentos de CLI están pasándose en formato URL.
  Esto es así, porque es posible lanzar TEAM con este formato. Ejemplos:
  $ php ./index.php "/scripts/setName?name=fulanito
  $ php ./index.php "/noticias/listado"

*/
function is_first_argument_an_url($argv = null) {
   /* const */ $FIRST_ARG = $FIRST_CHARACTER =  0;
   
   $first_arg_exists = isset($argv[$FIRST_ARG]);
   $fist_character_is_of_url = $first_arg_exists &&  ( '/' == $argv[$FIRST_ARG][$FIRST_CHARACTER] );
   
   return $is_url = $fist_character_is_of_url;
}

/**
  Comprueba si el argumento contiene un key
*/
function has_key($arg) {
   /* const */ $FIRST_CHARACTER =  0;

  return $with_key = ( '-' == $_arg[$FIRST_CHARACTER] );
}

/**
  Extrae el key/value/type de un argumento
*/

function get_key_value_and_type($arg) {
   /* const */ $ARGUMENTS_SEPARATOR =  "=";
   /* const */ $KEY_VALUE_SEPARATOR =  "=";

	$key = trim($_arg, $ARGUMENTS_SEPARATOR);
	$value = true;
	$type = 'spaced';
	
	$with_separator_key_value = strpos($key, $SEPARATOR_KEY_VALUE) !== false;
	
	if($with_separator_key_value) {
		list($key, $value) = explode("=", $var);
		$type='not_spaced';
	}

	return [$key, $value, $type];
}

/**
  Crea un almacen para guardar los valores de cli
*/
function get_store_for_args($url = null) {

	if(isset($url)) {
	  return new \team\Data('Url',$url);   
	}else {
	  return  new \team\Data();
	}
}


/**
  Parsea argv y las variables descubiertas se guardan se devuelven

*/
function parse_args_from_argv($argv) {
	//Ponemos todo en sus valores iniciales
	reset($argv);
	$key = null;
	$value = null;
	$options =  [];
	$options['args'] =
	$type = 'spaced';
	
		/**
	  Hay tres modos de pasar argumentos:
	  - not_spaced: son de la forma:  command -arg1=value1 -arg2=value2 -arg3=value3
	  - spaced, son de la forma   :  command -arg1 value1 -arg2 value2 -arg3 value3
	  - positional, tiene importancia el orden de aparicion: command arg1 arg2 arg3
	  
	  Hay que tener en cuenta que en los tipos2, se puede omitir el valor si este es igual a 1 o true.
	  command -arg1 -arg2 -arg3 
	  
	  Así que nos tocará ir parseando todos los arguementos, descubriendo de que tipo son, 
	  para extraer por cada argumento su key/value corréctamente
	  //TODO: Usar regular expression para hace más rápida/clara la extración

	*/
	//Procesamos los options
	while($_arg = current($argv) ) {
		if(has_key($_arg)) {
			list($var, $value, $type) = get_key_value_and_type($arg);
			$options[$key] = $value;
			//Si a lo mejor era un argumento espaciado, tenemos que esperar a la siguiente vuelta para
			//saber su valor. Por tanto, necesitamos mantener el key.
			//Por contra, para un no espaciado no necestamos guardar su key, además podría confundirnos
			//si lo mantenemos
			if('not_spaced' == $type) {
			  $key = null;
			}
		}else {
			//Si existía un key es que estamos en la segunda vuelta de un argumento espaciado. 
			if(isset($key) && "spaced" == $type ) {
				$options[$key] = $value;
			}else { //Lo consideramos como un posicional
				$options['args'][] = $_arg;
			}
			//Ya no tiene sentido mantener el key, porque si era spaced ya se ha tomado su valor
			//si era posicional, no nos ipmorta para nada el key
			$key = null;
		}


		next($argv);
	}



  
  return $options ;
}
