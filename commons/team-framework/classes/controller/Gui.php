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

namespace team;


/**
Representa los datos que llegaran a utilizarse en la vista para formar la web
Es la base para las acciones tipo GUI
 */
class Gui extends Controller {

    const DEPENDENCIES = '/guis/';

    /* ____________ METHODS DE EVENTOS BASES DEL FRAMEWORK___________ */


    function ___load($response) {

        //Add Default template and layout
        $this->setView($this->_CONTEXT['RESPONSE']);

        //Por defecto, no habrá layout
        $this->noLayout();

        return parent::___load($response);
    }


    function ___unload($result, $response) {
        $result = parent::___unload($result, $response);

        if( $this->isMain() ) {
            if($this->view){

                $this->addViewToPlace($this->view,'main_view',  [], $isolate=false, 50);
            }
        }

        return $result;
    }



    /* ____________ Views / Templates___________ */
    function getView($_file, $component = null, $package = null ) {

        //Eliminamos la extensión( ya que eso depende del sistema de render escogido )
        $file = \team\FileSystem::stripExtension($_file);


        //Es un resource
        if(strpos($_file, ':')) {
            return $file;
        }

        if(empty($file) )
            $file = $this->_CONTEXT['RESPONSE'];

        $file = $this->getPath("views", $component, $package)."{$file}";

        if(\team\FileSystem::filename('/'.$file)) {
            return $file;
        }else if(\team\Context::get('SHOW_RESOURCES_WARNINGS') ) {
            \team\Debug::me("View {$file}[{$_file}] not found in {$package}/{$component}", 3);
            return null;
        }

    }

    /**
     * Asigna la vista principal

     */
    function setView($_file, $component = null, $package = null) {
        return $this->view = $this->getView($_file, $component, $package);
    }

    /**
     * @param $view vista que se incluirá en el lugar
     * @param $place punto de anclaje en el que queremos incluir la vista. Si empieza por \, se tomará como pipeline el lugar completo.
     * Sino se añadirá a \team\place
     * @param bool $isolate determinada si la plantilla heredará el entorno de la plantilla padre( isolate = false ) o será independiente( isolate = true )
     * @param bool $order  orden de colocación de la vista respecto a otra en el mismo lugar. 
     */
    function addViewToPlace($view, $place,  $_options = [], $isolate = false, $order = 65) {

		$view =  \team\FileSystem::stripExtension($view);
		$idView = \team\Sanitize::identifier($view);
        $pipeline = ('\\' == $place[0])? $place : '\team\places\\'.$place;
		$options =  $_options;

		//Comprobamos si se quiere caché o no
		$cache_id = null;
		if(isset($_options['cache']) ) {
            $cache_id =  \team\Cache::checkIds($_options['cache'], $idView);
		}


        \team\Filter::add($pipeline,function($content, $params, $engine) use ($view, $options, $isolate, $idView, $cache_id) {

			//Comprobamos si ya estaba la plantilla cacheada
			if(isset($cache_id) ) {
				$cache = \team\Cache::get($cache_id);
				if(!empty($cache)) {
					return $content.$cache;
				}
			}

            //    \Debug::out(get_class_methods($engine) );
            //Si se quiere con todas las variables del padre
            if($isolate) { //aislado, sólo se quiere las variables que se le pasen		
                $engine->assign($params);
                $engine->assign($options);
                $view_content = $engine->fetch($view.'.tpl');
            }else {
                $father = $engine;
                $template = $engine->createTemplate($view.'.tpl', $idView, $idView, $father);
                $template->assign($params);
                $template->assign($options);
                $view_content = $template->fetch();
            }

			//Si se ha pedido sistema de caché, lo guardamos
			if(isset($cache_id) ) {
                $cache_time = $options['cachetime']?? null;


				\team\Cache::overwrite($cache_id,  $view_content, $cache_time );
			}

            return $content. $view_content;
        }, $order, $idView);

    }

    function addWidgetToPlace($widget_name, $place, $_options = [], $order = 65) {
        $idwidget = \team\Sanitize::identifier($widget_name);

        //Puede haber ocasiones que un widget requiera de colocar información en otras partes del html
        //es por ello, que le damos la oportunidad de que carguen la información que necesiten ya
        //para ello, cargaremos el script /events/placed.php
        //y llamaremos al evento \team\widget\{id_widget}
        $namespace =  \team\NS::explode($widget_name);

        \team\FileSystem::ping("/{$namespace['package']}/{$namespace['component']}/events/placed.php");
        \Team::event('\team\placed\\'.$idwidget, $place, $_options, $order, $this);


        $pipeline = ('\\' == $place[0])? $place : '\team\places\\'.$place;


        //Comprobamos si se quiere caché o no
        $cache_id = null;
        if(isset($_options['cache']) ) {
            $cache_id =  \team\Cache::checkIds($_options['cache'], $idwidget);
            unset($_options['cache']);
        }

        $options = $_options;
        \team\Filter::add($pipeline,function($content, $params, $engine) use ($widget_name, $options,  $cache_id) {

            $params = $params + $options;
            $params['engine'] = $engine;
            $params['placed'] = true;

            $widget_content =  \team\Component::call($widget_name, $params,  $cache_id);

            return $content.$widget_content;
        }, $order, $idwidget);

        return true;
    }

    function noLayout() {
        $this->setLayout();
    }

    function setLayout($_file = null, $component = null, $package = null) {
        if(!isset($_file)) {
            $this->layout = null;
        }else {
            //para layout el component por defecto siempre será commons
            $component = $component?: 'commons';

            $this->layout =  $this->getView($_file, $component, $package);
        }
    }

    /**
    Renderizamos una cadena de texto
    @string Cadena a renderizar
    @param array||\team\Data Variables de transformación que se usarán en la transformación
    ej: $this->parseString('Hola {$nombre}', ["nombre" => "mundo"]);
    Se podría usar también filtros, shortcodes, etc.
    También se importa las variables de contexto actual
    OJO: Esto es independiente de la acción
     */

    public static function render($string, $params = null) {

        if(is_a($params, '\trrasweb\Data', false) ) {
            $data = $params;
        }else {
            $data = new \team\Data($params);
        }

        $data->layout = "string";
        $data->view = $string;

        return $data->out("html");
    }






    /* ____________ UserAgent ___________ */


    static function checkUserAgent($key = null) {
        static $user_agent;

        if(isset($user_agent) )  {
            return $key? $user_agent[$key] : $user_agent;
        }

        $http_user_agent = $_SERVER['HTTP_USER_AGENT'];

        $mobile = false;
        $computer = false;
        $tablet = false;
        $device = 'computer';
        $navigator = 'explorer';

        if(!empty($http_user_agent) ) {
            $is_mobile = strpos($http_user_agent, 'Mobile') !== false;
            $is_android = strpos($http_user_agent, 'Android') !== false;

            //¿is tablet?
            if ( stripos($http_user_agent, 'Tablet') !== false || ($is_android && !$is_mobile)
                || strpos($http_user_agent, 'Kindle') !== false ) {
               $tablet =  true;
                $device = $navigator = "tablet";
            }

            //¿is mobile?
            if(!$tablet && ($is_mobile || strpos($http_user_agent, 'Silk/') !== false
                    || strpos($http_user_agent, 'BlackBerry') !== false
                    || strpos($http_user_agent, 'Opera Mini') !== false
                    || strpos($http_user_agent, 'Opera Mobi') !== false ) ) {
                $mobile = true;
                $device = $navigator = "mobile";
            }

            //¿is desktop?
            if(!$mobile && !$tablet) {
                $computer = true;
                if (strpos($http_user_agent, 'Chrome') !== false) {
                    $navigator = "chrome";
                }else if (strpos($http_user_agent, 'Firefox') !== false) {
                    $navigator = "firefox";
                }else {
                    $navigator = "explorer";
                }
            }

        }


        $user_agent = ['navigator' => $navigator, 'device' => $device, 'computer' => $computer, 'mobile' => $mobile,'tablet' => $tablet, 'desktop' => ($computer || $tablet) ];

        $user_agent = \team\Filter::apply('\team\user_agent', $user_agent);


        return $key? $user_agent[$key] : $user_agent;
    }


    function getNavigator() {return  self::checkUserAgent('navigator'); }

    function getDevice() {return  self::checkUserAgent('device'); }

    function isMobile() { return  self::checkUserAgent('mobile'); }

    function isDesktop() { return  self::checkUserAgent('desktop'); }

    function isComputer() { return  self::checkUserAgent('computer'); }

    function isTablet() { return  self::checkUserAgent('tablet'); }

    function getBodyClasses($classes = '') {

        if($this->isMobile()) {
            $classes  .= " movil ";
        }else {
            $classes .= " desktop ";
        }

        $classes .= $this->getNavigator();

        return \team\Filter::apply('\team\body_classes', trim($classes));
    }


    /* ____________ HTTP ___________ */

    function redirect($redirect, $code = 301, $protocol = 'http://',  $domain = null) {
        $redirect = \team\Sanitize::internalUrl($redirect);

        if(!$domain) {
            $domain = \team\Context::get('DOMAIN');
        }
        

        $domain = str_replace($protocol, '',$domain);

        $domain = rtrim($domain, '/');

        header("Location: {$protocol}{$domain}{$redirect}", true, $code);
        exit();
    }


    /* ____________ Helpers ___________ */



    /**
    El metodo tostring mostraria la web en html
     */
    public function __toString() { return $this->data->out("html");}
}
