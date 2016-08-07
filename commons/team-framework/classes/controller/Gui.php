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

        if($this->view && $this->isMain()){

            $this->addViewToPlace('main_view', $this->view, [], $isolate=false, 50);
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
     * @param $_file
     * @param $place lugar al que se quiere añadir la vista. Si empieza por \, se tomará como pipeline el lugar completo.
     * Si se añadirá a \team\place
     * @param bool $isolate
     * @param bool $remove_before_views
     * @return bool
     */
    function addViewToPlace($place, $file,  $options = [], $isolate = false, $order = 65) {
        return \team\Filter::addViewToPlace( $place,$file, $options, $isolate, $order);
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


    static function getUserAgentInfo($key = null) {
        static $user_agent;

        if(isset($user_agent) )  {
            return $key? $user_agent[$key] : $user_agent;
        }

        $http_user_agent = $_SERVER['HTTP_USER_AGENT'];

        $mobile = false;
        $computer = false;
        $tablet = false;

        if(!empty($http_user_agent) ) {
            $is_mobile = strpos($http_user_agent, 'Mobile') !== false;
            $is_android = strpos($http_user_agent, 'Android') !== false;

            //¿is tablet?
            if ( stripos($http_user_agent, 'Tablet') !== false || ($is_android && !$is_mobile)
                || strpos($http_user_agent, 'Kindle') !== false ) {
                $tablet =  true;
                $navigator = "tablet";
            }

            //¿is mobile?
            if(!$tablet && ($is_mobile || strpos($http_user_agent, 'Silk/') !== false
                    || strpos($http_user_agent, 'BlackBerry') !== false
                    || strpos($http_user_agent, 'Opera Mini') !== false
                    || strpos($http_user_agent, 'Opera Mobi') !== false ) ) {
                $mobile = true;
                $navigator = "mobile";
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

        $user_agent = ['navigator' => $navigator, 'computer' => $computer, 'mobile' => $mobile,'tablet' => $tablet, 'desktop' => ($computer || $tablet) ];

        $user_agent = \team\Filter::apply('\team\user_agent', $user_agent);


        return $key? $user_agent[$key] : $user_agent;
    }


    function getNavigator() {return  self::getUserAgentInfo('navigator'); }

    function isMobile() { return  self::getUserAgentInfo('mobile'); }

    function isDesktop() { return  self::getUserAgentInfo('desktop'); }

    function isComputer() { return  self::getUserAgentInfo('computer'); }

    function isTablet() { return  self::getUserAgentInfo('tablet'); }

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
