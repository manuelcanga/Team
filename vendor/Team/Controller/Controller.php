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

namespace Team\Controller;

/**

Los controlers( Gui, Actions, Commands, ... ) agroupan las distintas respuestas/responses
Normalmente depende de la salida.
Gui -> html
Actions -> todo aquello que no sea html
Commands -> respuestas en formato terminal/consola.
...y en el futuro...
Printers -> Generan un pdf
....
 */
abstract class Controller  implements \ArrayAccess{
    use \Team\Data\Box;

    const TYPE = 'Controller';

    protected $params;
    //parent controller
    private $parent = null;

    function __construct($params, $response, $parent = null ) {

        \Team\System\Context::set('CONTROLLER',  $this );
        $this->parent = $parent;

        if($params instanceof \Team\Data) {
            $this->params = $params;
        }else{
            $this->params = new \Team\Data\Data($params);
        }


        //Contamos las veces que se ha creado
        \Team\System\Context::set('TIMES',  static::controllerInitialized($response) );
    }



    function getParams() {
        return $this->params->get();
    }


    /**
    Comprueba si se ejecutó por primera vez este Controller.
    @return boolean true si fue la primera vez,
    false si hubo más veces
     */
    function isFirstTime() {
        return (1 === \Team\System\Context::get('TIMES')['commons'] );
    }


    /**
    Comprueba si el response fue pedido directamente desde el exterior del framwork
    @return boolean true si se pidió desde el exterior
    false si se pidió deesde otro controlador
     */
    function isMain() {
        return (bool) $this->params->is_main;
    }

    /*
        Devuelve el nombre del componente al que pertenece este controlador
    */
    function getComponent() {
        return \Team\System\Context::get('COMPONENT');
    }

    /*
        Devuelve el nombre del paquete al que pertenece este controlador
    */
    function getPackage() {
        return \Team\System\Context::get('PACKAGE');
    }


    /* ____________ METHODS DE EVENTOS PARA EL PROGRAMADOR___________ */
    //Se lanza sólo la primera vez que se instancia un Controller
    protected function onInitialize(){}
    //Se lanza cada vez que se instancia un Controller. Se llama antes de a los traits de iniciación
    protected function onLoad(){}
    /** Tareas comunes para todos los responses. Se llama después de a los traits de iniciación */
    protected function commons() {	}
    //Se lanza una vez que se ha lanzado el response y antes de los trailts de fianlización. No cambia el contenido
    protected function custom(){ }
    //Se lanza cada vez que se finaliza el response( después de custom ) y después de los traits de finalización.
    //Permite modificar el contenido
    protected function onUnload($result, $response){return $result; }
    //public static function onError($SE, $result) se lanza cuando hay un error de sistema o crítico


    /* ____________ METHODS DE EVENTOS BASES DEL FRAMEWORK___________ */

    /**
    Evento de configuración del Controller
     */
    function ___load($response) {

        //El "evento" initialize se lanza sólo cuando un controlador se instancia por primera vez
        if($this->isFirstTime() ) {
            $this->onInitialize($response);
        }

        //El "evento" load se lanza por cada vez que se instancia el controlaador
        $this->onLoad($response);

        //Llamamos a los inicializadores de traits
        $this->callTemplates($response);

        //Llamamos a las tareas comunes
        $this->commons($response);

    }


    /**
    Evento de configuración del Controller, antes de que se finalize por fin.
    @param mixed $result es el valor devuelto por un controlador
    @param string $response es el nombre del response pedido

     */
    function ___unload($result, $response) {

        //El evento custom se utiliza para personalizar las tareas de los response
        //pero no su salida
        $this->custom($response);

        //Igual que los trait tienen su método de inicialización( con nombre igual que el trait )
        //También tienen su método de finalización: end + nombre de trait.
        $this->callTemplates($response, 'end', '', $result);

        return $this->onUnload($result, $response);
    }


    /**
    Devuelve los nombres de los traits usados por el controlador
     */
    function using($name, $component = null) {
        $trait = '';

        $trait .= $this->getPackage();
        if(isset($component) ) {
            $trait .= '\\'.$component;
        }

        $trait .= '\\'.ucfirst($name);

        $list_traits = class_uses($this, $autoload= false);

        if(isset($list_traits[$trait]) ) {
            return true;
        }else  {
            return false;
        }

    }


    /* ____________ METHODS UTILES  PARA EL PROGRAMADOR CLIENTE___________ */


    function debug($var_name) {
        \team\Debug::me($this->params[$var_name], $var_name);
    }


    /**
    Manda un código de estado al exterior.
    @param int $code código de salida a mandar al exterior
    Los códigos más comunes son 404(no encontrado ) o 200(ok)
    En caso de que no sea main, se asigna como variable de salida.
    $this->statusCode(200);
     */
    function statusCode($code) {
        if($this->isMain() ) {
            http_response_code($code);
        }else {
            $this->status_code = $code;
        }
    }

    function notFound($msg = 'Not found', $code = 'not_found', $data = null) {
        $this->statusCode(404);
        \Team::system($msg, $code, $data);
    }




    /**
     * Delegamos el tratamiento del response actual
     *
     * @param array $data datos a pasar al nuevo response si se lanza
     * @param string nombre del nuevo response a lanzar en el nuevo controlador
     * @return mixed devuelve la respuesta del response
     */
    function delegate(array $params = []) {

        $params['ref'] = $this->params->id;
        $params['ref_item'] = $this->params->item_id;
        $params['ref_item_ext'] = $this->params->item_ext;
        $params['ref_item_ext'] = $this->params->item_ext;


        $new_response = 'index';
        //El nuevo response será el siguiente param alfanumérico en la url
        if(isset( $this->params->url_path_list[0])) {
            $new_response = array_shift($this->params->url_path_list);
        }

        //Se toma como id el primer filtro si lo hubiera
        $params['id'] =  $params['id']?? null;
        if ( !$params['id'] && isset($this->params->filters_list[0])) {
            $params['id'] = array_shift($this->params->filters_list);
        }


        return $this->newController($new_response, $params);
    }


    /**
     * Creamos un nuevo controlador de apoyo al actual response.
     *
     * @param $name nombre del archivo( y clase ) del nuevo controlador
     * @param null $_response response que se lanzará en el nuevo controlador(sino se lanzará uno del mismo nombre al actual )
     * @param array $data datos a pasar al nuevo response si se lanza
     * @return mixed devuelve la respuesta del response
     */
    function newController( $new_response = null, $data = [],&$new_controller = null) {
        $old_response = \Team\System\Context::get('RESPONSE');


        $classname = \Team\System\Context::get('NAMESPACE').'\\'.ucfirst($old_response).'\\'.static::TYPE;
        $response = $data['response'] = \Team\Data\Sanitize::identifier($new_response?:  $old_response );
        $result = null;


        $new_controller = $this->getNewController($classname, $response,  $data);

        if($new_controller && isset($response) && method_exists($new_controller, $response) ) {
            \Team\System\Context::set('CHILD_BASE_URL', \Team\System\Context::get('BASE_URL').'\\'.$response );

            $new_controller->___load($response);


            $result = $new_controller->$response($response);

            $result = $new_controller->___unload($result, $response);

            \Team\System\Context::set('CONTROLLER',  $this );
            \Team\System\Context::set('CHILD_BASE_URL', null);

        }


        return $result;
    }



    /**
     * Devolvemos un nuevo controlador de apoyo al actual response.
     *
     * @param $name nombre del archivo( y clase ) del nuevo controlador
     * @param $path la ruta en el sistema de archivos en el que se encuentra el controlador
     * @param array $data datos a pasar al nuevo response si se lanza
     * @return mixed devuelve el objeto del controlador
     */
    function getNewController($class, $response,  $data = []) {

        $data += $this->params->get();

        $new_controller = new $class($data, $response, $this);

        $new_controller->setRef($this->data);

        return $new_controller;
    }


    /* ____________ METHOD HELPERS PARA TRASWEB FRAMEWORK___________ */

    /**
    Llamamos a los traits para que se inicialicen/finalizarán la clase a su manera
    Para ello obtenemos los nombres de traits y llamamos a los métodos
    que se llamen igual. Para la finalización se usa el prefijo: 'end'
    Usamos los traits como patrón template
     */
    protected function callTemplates($response, $prefix = '', $postfix = '', $result='') {
        $result = '';

        $skels = class_uses($this);

        //Sólo nos importa el nombrebase de cada trait
        $skels = array_map(['\Team\System\NS', 'basename'], $skels);


        if(!empty($skels) ) {
            foreach($skels as  $trait => $name) {

                $name = $prefix.$name.$postfix;

                if(is_callable([$this, $name]) ) {
                    $result_skel = $this->$name($response, $result);
                    if($result_skel)
                        $result = $result_skel;
                }
            }
        }

        return $result;
    }

    /**
     * Return parent controller
     * @return null
     */
    protected function parent() {
        return $this->parent;
    }

    /**
     * Alias of parent() method
     * @return null
     */
    protected function getParent() {
        return $this->parent;
    }



    /**
    Método auxiliar de __load que permite saber cuantas veces se lanzó cada response y  se inicializó el Controller
     */
    private static function controllerInitialized($response) {
        static $initialized = ['commons' => 0];

        if(!isset($initialized[$response]) ) {
            $initialized[$response] = 0;
        }

        ++$initialized[$response];
        ++$initialized['commons'];

        return $initialized;
    }

}
