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
    use \team\gui\Seo;
    use \team\gui\Assets;

    const DEPENDENCIES = '/guis/';


    /* ____________ METHODS DE EVENTOS BASES DEL FRAMEWORK___________ */


    function ___load($response) {

        //Add Default template and layout
        $this->setView(\team\Context::get('RESPONSE'));

        //Por defecto, no habrá layout
        $this->noLayout();


        return parent::___load($response);
    }


    function ___unload($result, $response) {
        $result = parent::___unload($result, $response);

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
            $file = \team\Context::get('RESPONSE');

        $file = $this->getPath("views", $component, $package)."{$file}";

        if(\team\FileSystem::filename('/'.$file)) {
            return $file;
        }else if(\team\Config::get('SHOW_RESOURCES_WARNINGS', false) ) {
            \team\Debug::me("View {$file}[{$_file}] not found in {$package}/{$component}", 3);
            return null;
        }

    }

    /**
     * Asigna la vista principal

     */
    function setView($_file, $component = null, $package = null) {
        $view =  $this->getView($_file, $component, $package);
        \team\Context::set('VIEW', $view);

        return $view;
    }


    public function addClassToWrapper($class, $wrapper, $order = 50) {
        $pipeline = '\team\gui\wrappers\\'.$wrapper;

        return \team\Filter::add($pipeline,function($classes) use ($class) {
            return trim($classes.' '.$class);
        }, $order);
    }


    function noLayout() {
        $this->setLayout();
    }

    function setLayout($_file = null, $component = null, $package = null) {
        if(!isset($_file)) {
            \team\Context::set('LAYOUT', null);
        }else {
            //para layout el component por defecto siempre será commons
            $component = $component?: 'commons';

            \team\Context::set('LAYOUT', $this->getView($_file, $component, $package) );
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

    public static function render($string, $params = null, $isolate = true) {

        \team\Context::open($isolate);

        if(is_a($params, '\team\Data', false) ) {
            $data = $params;
        }else {
            $data = new \team\Data($params);
        }

        \team\Context::set('LAYOUT', 'string');
        \team\Context::set('VIEW', $string);

        $result =  $data->out("html");

        \team\Context::close();

        return $result;
    }



    /* ____________ UserAgent ___________ */

    function getNavigator() {return  \team\Http::checkUserAgent('navigator'); }

    function getDevice() {return  \team\Http::checkUserAgent('device'); }

    function isMobile() { return  \team\Http::checkUserAgent('mobile'); }

    function isDesktop() { return  \team\Http::checkUserAgent('desktop'); }

    function isComputer() { return  \team\Http::checkUserAgent('computer'); }

    function isTablet() { return  \team\Http::checkUserAgent('tablet'); }

    function addBodyClass($class = '', $overwrite = false) {
        if($overwrite) {
            \team\Context::set('BODY_CLASSES', [$class]);
        }else {
            \team\Context::push('BODY_CLASSES', $class);
        }
    }



    /* ____________ Helpers ___________ */

    /**
    El metodo tostring mostraria la web en html
     */
    public function __toString() { return $this->data->out("html");}
}