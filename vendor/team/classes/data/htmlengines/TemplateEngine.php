<?php
/**
New Licence bsd:
Copyright (c) <2012>, Manuel Jesus Canga Muñoz
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of the trasweb.net nor the
      names of its contributors may be used to endorse or promote products
      derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL Manuel Jesus Canga Muñoz BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

*/
namespace team\data\htmlengines;



require_once(__DIR__."/helpers/Mirror.php");
require_once(__DIR__."/helpers/Config.php");

/** TODO: Optimized */
//ini_set('zlib.output_compression', '1');

/**
Notas:
En archivo: lib/data/htmlengines/Smarty/sysplugins/smarty_internal_templatecompilerbase.php
Método: getPluginFromDefaultHandler
Se lanza el callback: registerDefaultPluginHandler
Para la busqueda de tags que no han sido definidos. 

*/
class TemplateEngine implements \team\interfaces\data\HtmlEngine{
	private static $functions_user_cache = array();

    private $gui = null;

	static function __initialize() {
//		\Classes::addLoader("smartyAutoload");

		//Todo: filter o Task si no hay, por defecto devolverá un ' '.
		//Podría ser util para añadir cosas a la cabecera, al pie, etc. 
       // class_alias('Smarty_Internal_Resource_File', 'Smarty_Resource_Insert', true);

		if(!defined('SMARTY_RESOURCE_CHAR_SET') ) {
			define('SMARTY_RESOURCE_CHAR_SET', \team\Config::get('CHARSET') );
		}


        require_once(\team\_VENDOR_."/Smarty/Smarty.class.php");

        //Resources propioos
        class_alias('Smarty_Internal_Resource_File', 'Smarty_Resource_Component', true);
        class_alias('Smarty_Internal_Resource_File', 'Smarty_Resource_Commons', true);
        class_alias('Smarty_Internal_Resource_File', 'Smarty_Resource_Package', true);
        class_alias('Smarty_Internal_Resource_File', 'Smarty_Resource_Root', true);
        class_alias('Smarty_Internal_Resource_File', 'Smarty_Resource_Theme', true);
        class_alias('Smarty_Internal_Resource_File', 'Smarty_Resource_Custom', true);
        class_alias('Smarty_Internal_Resource_File', 'Smarty_Resource_Team', true);


        //Creamos un directorio temporal que evite las posibles colisiones de temporales smarty entre sitios
        if(!file_exists(_TEMPORARY_DIRECTORY_."/smarty/compile") ) {
            mkdir(_TEMPORARY_DIRECTORY_."/smarty/compile", 0777, true);
        }

        //Si no existe, habra que crearlo
        if(!file_exists(_TEMPORARY_DIRECTORY_."/smarty/cache") ) {
            mkdir(_TEMPORARY_DIRECTORY_."/smarty/cache", 0777, true);
        }

    }

    function __construct() {
		//Le añadimos una referencia a la GUI Actual.
        $this->gui = \team\Context::get("CONTROLLER");

    }

	public function transform(Array $_data) {	

			$_data['_']["USER"] = \team\User::getCurrent();
			$_data['_']['notices'] = \Team::getCurrent();
            $_data['USER_AGENT'] =  \team\Http::checkUserAgent();


        //Lanzamos un evento de inicio de transformacion de plantilla
		//	$event = \Event('Transform', '\team\view')->ocurred($data);

            $_data = \team\Filter::apply('\team\template\data', $_data);

            $engine = new \Smarty();

            //Obtenemos la plantilla que vamos a procesar
			$template= $this->getView($_data );

            $this->initializeEngine($engine, $_data, $template);

			//\team\Debug::out($_data);
			//Transformamos los datos que tenemos a datos utilizables por smarty
			$engine_data = $this->transformToEngineData($_data);


			$is_main = \team\Context::isMain();
			\Team::event('\team\view\fetching', $template, $engine, $engine_data, $is_main);

			$result =  $engine->fetch($template, $engine_data);


   		    if(!$this->gui || \team\Context::get("SHOW_VIEWS", true) ) {
				return $result;
			}else {
				return "";
			}

	}

	/**
		@TODO: En un futuro se debería de poder especificar con cada elemento si se cachea o no 
		Esta función lo que hace es localizar elementos que no se hayan definido previamente.
		Aunque lo suyo es que se hubieran definido en los directorios de plugins ( ver método de "initializeSmarty" 
			más abajo )	
		)
	*/
	function customElements($name, $type, $template, &$callback, &$script, &$cacheable) {
		$cacheable = false;

			//Por algún motivo que desconozco, no puedo mandar el callback con un ojecto
			//Así que con el apaño de un mirror solucionamos el problema
			//Todo esto para buscar si existe un evento
		switch($type) {
			case \Smarty::PLUGIN_FUNCTION: 
			case \Smarty::PLUGIN_BLOCK:
			case "modifier":

				/** Si hay una función de usuario creada con el mismo nombre, la llamamos
					con TEAM no hay necesidad de funciones, si la hay es por algo */
				if(in_array($name,  self::$functions_user_cache ) ) {
					$callback = $name;
					return true;
				}

				$callback = array( '\team\data\htmlengines\Mirror',  'mirror_'.$name);
				return true;

			case \Smarty::PLUGIN_COMPILER: 
	    	//	$callback = $name;
				return false;
			case "class": 

	    	//	$callback = $name;
				return false;
	
		}


		return false;
	}

    public function default_template_handler_func($type, $name, &$content, &$modified, \Smarty $smarty) {
        $name = str_replace('.tpl', '', $name).'.tpl';

  	   $component = \team\Context::get('COMPONENT');
       $package = '/'.\team\Context::get('PACKAGE');

        $template = $name;
		$found_type = false;
        switch($type) {
            case 'team':
              $template =  _TEAM_."/views/{$name}";
              $found_type =  true;
            break;
            case 'theme':
                $template =  \team\Context::get('_THEME_')."/{$name}";
                $found_type =  true;
                break;
            case 'custom':
                $template =  \team\Context::get('_THEME_')."/{$package}/{$component}/views/{$name}";
                $found_type =  true;
                break;
            case 'commons':
            case 'package':
 				 $found_type =  true;
              	  $template =  _SCRIPT_."/{$package}/commons/views/{$name}";
				break;
            case 'root':
  				 $found_type =  true;
              	  $template =  _SCRIPT_."/commons/views/{$name}";
                break;
         	case 'component':
  				  $found_type =  true;
	               $template =  _SCRIPT_."/{$package}/{$component}/views/{$name}";
            break;

        }

	if($found_type && !file_exists($template) ) {
		if(\team\Context::get('SHOW_RESOURCES_WARNINGS', false) ) {
			\Debug::me("Not found view {$template} of type {$type} and name {$name}");
		}

		$template = _TEAM_."/views/layouts/void.tpl";
	} 


        return $template;
    }
	
	/**
		Inicializamos el objeto de smarty con la configuracion propia de team
		@param Smarty $_engine: objeto de smarty que inicializaremos.
	*/
	function initializeEngine(\Smarty  $_engine, $_data, $_template) {

		$package = \team\Context::get('PACKAGE');
		$component =  \team\Context::get('COMPONENT');
		$response = \team\Context::get('RESPONSE');

		$_engine->addPluginsDir(_SCRIPT_.'/'.$package.'/'.$component.'/views/plugins');
		$_engine->addPluginsDir(_SCRIPT_.'/'.$package.'/commons/views/plugins');
		$_engine->addPluginsDir(_SCRIPT_.'/commons/views/plugins');
		$_engine->addPluginsDir(_TEAM_.'/classes/data/htmlengines/plugins');

		//Si aún asi no se encuentran los elementos, se añade una funcion buscadora de elementos
		$_engine->registerDefaultPluginHandler(array($this, "customElements") );
        //Permitimos varios wrappers en la plantilla
        $_engine->default_template_handler_func =  [$this,'default_template_handler_func' ];

		/**
			@see https://github.com/smarty-php/smarty/blob/master/INHERITANCE_RELEASE_NOTES.txt
		*/
        $_engine->inheritance_merge_compiled_includes = false;
        //Para poder encontrar  shortcode, necesitamos buscar entre las funciones del usuario. Cacheamos estas
		$functions = get_defined_functions();
		 self::$functions_user_cache = $functions["user"];

		 $view_cache =  \team\Context::get('VIEW_CACHE', false);

		 $_engine->compile_check = !$view_cache;
		 $_engine->caching = $view_cache;
		 $compile_id = $package.\team\Context::get('AREA').\team\Context::get('LAYOUT');
		 $_engine->compile_id =  \team\Filter::apply('\team\smarty\compile_id',$compile_id );

		
		//Un componente sólo vería sus cosas, aún así puede usar root: package: etc
		$_engine->template_dir = _SCRIPT_.'/';
		$_engine->setCompileDir(_TEMPORARY_DIRECTORY_."/smarty/compile");
		$_engine->setCacheDir(_TEMPORARY_DIRECTORY_."/smarty/cache");

			/** Usamos un filtro para que no haya espacio en blanco  */
        if((bool)\team\Context::get('MINIMIZE_VIEW', true)) {
 		  $_engine->loadFilter('output', 'trimwhitespace');
		}


		\Team::event('\team\smarty\initializing', $_engine, $_data, $_template);


			/** Activar el debug(para administradores):  http://MIDOMAIN.es?debug  */
		//$smarty->debugging = true;
		//$smarty->smarty_debug_id = "debug";
		// self::$engine ->debugging_ctrl =  ( \team\User::level() >= \team\User::Manager  ) ? 'URL' : 'NONE';

	}
	
	
	/**
		Selecciona la plantilla por la que se comenzara el parseo de vistas
		@param Array $data: Datos pasados a la plantilla
	*/
	function getView(Array $_data = null) {

	    $layout = \team\Context::get('LAYOUT');
        $view = \team\Context::get('VIEW');

        if(isset($view) ) {
            $view = \team\FileSystem::stripExtension($view);
        }

        if(isset($layout) ) {
            $layout = \team\FileSystem::stripExtension($layout);
        }

        $is_string = "string" === $layout;
		$is_layout = !$is_string && !empty($layout);
		$view_exists = !empty($view);


		//Si es un layout que usa el  "default_template_handler_func" de smarty(team:framework/debug.tpl) la devolvemos ta cual
		if( $is_layout  && strpos($layout, ':') ) return $layout;
		//lo mismo que el anterior pero para vistas
		if(!$is_string &&  $view_exists && strpos($view, ':') ) return $view;


		$template = '';
		if($is_string) {
			 $template = "eval:".$view;
		}else if($is_layout) {
			$template = _SCRIPT_."/".$layout.".tpl";
            if(!file_exists($template)) {
                \Team::system("Not found layout in {$template}",  "\\team\\gui\\ViewNotFound");
                $template = '';
            }
		}else {
            $is_view =  $view_exists &&  \team\FileSystem::exists("/".$view.".tpl");

            if (!$is_layout && $is_view ) {
                $template = _SCRIPT_."/".$view.".tpl";
                if(!file_exists($template)) {
                    \Team::system("Not found view in {$template}",  "\\team\\gui\\ViewNotFound");
                    $template = '';
                }
            }else if (isset($_data["response"])  )  {
                \Team::system("Not assign view for [{$_data["package"]}, {$_data["component"]}, {$_data["response"]}]", "\\team\\gui\\ViewNotFound");
            }else {
                \Team::system("Not assign, or not found, either view or layout",  "\\team\\gui\\ViewNotFound");
            }
        }


		return $template;
	}

	/**
		Transformamos los datos que tenemos a datos utilizables por smarty
		@param Array $_data: los datos que vamos a utilizar
		@return \Smarty_Data : retornamos un objeto de datos smarty
	*/
	function transformToEngineData(Array $_data) {

		//Definimos las que seran las constantes de configuracion de smarty
		$data = new \Smarty_Data();
		//Añadimos a la plantilla todas las constantes de configuracion
		$data->config_vars = new Config();
		if(\team\Context::get("TRACE_CONFIG") ) {
			\team\Debug::me($data->config_vars, "Variables de configuracion smarty");
		}

		$data->assign($_data);
	
		return  $data;
	}
	

	

	
    function export($_target, Array $_data = [], Array $_options = [] ) {

    }
}

