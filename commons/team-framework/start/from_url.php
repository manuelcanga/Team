<?php 

namespace team\start\url;

if(!defined("_SITE_") ) die("Hello,  World");



/**
	 Tratamos la url del agente de usuario para extraer todo los argumentos establecido por el usuario
*/
\team\Task::join('\team\url', function(& $url) {


		 \team\Debug::trace("Vamos a procesar la url pedida", $url );

		//Este worker acabará la tarea de url, así que ya notificamos que no queremos que se siga propagando .
		$this->finish();

        $defaults = [];

        list($package, $defaults['component'], $defaults['response'], $defaults['out']) = array_pad(explode('/',trim($this->main,'/') ), 4, null);

        $package = setUpPackage($package, $url);
        setUpWebUrl($url);

        $url = \team\Filter::apply('\team\url', $url);
        $_POST = \team\Filter::apply('\team\parse_post', $_POST);

        //Parseamos la url en busca de los parámetros de la web, los argumentos base serán los de post
		$args = new \team\Data('Url',$url, [], $_POST +((array)$this->area_params) + $defaults);
        $url = $args->base_url;

        //*** Evitamos que desde el exterior se creen parámetros propios del framework y que no se deberían de modificar directamente ***
        //El package ya se determinó
        $args->package = $package;
        $args->component = null;
        //_self_ determina la ruta url que se utilizó para llegar al response actual ( normalmente url_path_list en forma de path pero sin filtros )
        //por tanto es muy útil para que un response se refiera así mismo.
        //por ejemplo, como  en formulario
        $args->_self_= null;

        //Si se especifico una url con extension( ej: /peliculas/mi-pelicula-10.html ) y no hubo un tipo de salida explicito, se toma la extension como salida
        if(!$args->out && $args->item_ext) {
            $args->out = $args->item_ext?: $defaults['out'];
	    }

		$args->action = parse_action($args->action?:  \team\Config::get('REQUEST_METHOD') );


		//La variable de configuración MAIN, permite forzar la carga de un paquete/componente determinado
		//Por tanto, su existencia implica que no queremos el flujo normal de parseo de url
		//Es muy útil,por ejemplo, por si en el evento Start hemos detectado que el usuario está
		//tratando de entrar en una zona restringuida. Asígando un MAIN tal como: '/user/login'
		//forzaríamos al sistema a que el componente main sea login( paquete user )
		//También es útil para crear archivos que determinen dónde se gestionara su lógica.
		//por ejepmlo, podríamos crear un archivo /rss.php que tenga un define('MAIN', '/tools/rss');
		//Así al cargarse ese archivo automaticamente se iría a rss( paquete tools )
		if(\team\Config::get('MAIN') ) {
			$args->component = $defaults['component'];
			$args->response = $defaults['response'];
			$args->out =  $defaults['out'];
            $args->filters_list = [];
            $args->_self_ = \team\Sanitize::trim( implode('/', $args->url_path_list), '/');

        }else {
            //Le damos la opción al programador de que implemente su propio sistema de parseo de urls
		  $args = \team\Task('\team\parse_url', $args)->with($args, $url, $package );
		}


        //Creamos el path de sólo los filtros
        $args->filters_path = '';
        if(!empty($args->filters_list) ) {
            $args->filters_path = '/'.implode('/', $args->filters_list);
        }


        $default_response = \team\Filter::apply('\team\default_response', 'index');
        //Si no se especificó un default response se coge el del sistema( index  );
        $defaults['response'] = $defaults['response']?: $default_response;
        //Si se especifico un default component para el area, debemos de coger también el default response que se escogiera
         if(!isset($args->component) ) {
            $args->component = $args->component?: $defaults['component'];
            $args->response = $args->response?: $defaults['response'];
        }else {
            $args->response = $args->response?: $defaults['response'];
        }


        $args = \team\Filter::apply('\team\url\args', $args);

        //Reseteamos las variables superglobales porque ya la hemos procesado
		$_GET = $_POST = array();


        //_SELF_  debe empezar y terminar  / y terminar con  /
        $_SELF_ =  \team\Sanitize::trim( \team\Config::get("_AREA_").ltrim( $args->_self_, '/'), '/');

        \team\Config::set('_SELF_', $_SELF_);
        \team\Config::set('URL',  $url);
        \team\Config::set('ARGS',  $args);


        unset($args->_self_); //ya no lo necesitamos, está en context

        \team\Debug::trace("Acabado el proceso de analisis de url", $args);

        \team::event("\\team\\conponent", $package, $args->component, $url, $args );


		return $args;
	}
);



function setUpWebUrl() {
    $web = \team\Config::get('PROTOCOL').\team\Config::get('DOMAIN');
    \team\Config::set('WEB', $web);
}


function setUpPackage($package, $url) {

    $package = \team\Sanitize::identifier($package);
    $package =  \team\Filter::apply('\team\package',  $package, $url );
    \team\Config::setMainPackage( $package);

    return $package;
}


/**
Proccesamos los distintos tipos de métodos https que pueden estar mandando
y lo asignamos a uno de los argumentos para el controlador ya que puede serle util
en el caso de que sea un Actions, este será el response que se escoja como predeterminado
 */
function parse_action($request) {

    switch (strtoupper($request)) {
        case 'PUT':  $action = 'update'; break;
        case 'POST': $action = 'save'; break;
        case 'GET':  $action = 'search'; break;
        case 'HEAD': $action = 'status'; break;
        case 'DELETE':  $action = 'remove'; break;
        case 'OPTIONS': $action = 'help'; break;
        case 'TRACE': $action = 'debug'; break;
        case 'CONNECT': $action = 'login'; break;
        default: $action =  \team\Check::key($request, 'undefined'); break;
    }

    return  $action;
}

