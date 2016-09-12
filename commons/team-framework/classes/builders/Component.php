<?php
/**
New Licence bsd:
Copyright (c) <2012>, Manuel Jesus Canga MuÃ±oz
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
DISCLAIMED. IN NO EVENT SHALL Manuel Jesus Canga MuÃ±oz BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

*/

namespace team;

require(_TEAM_.'/classes/builders/Builder.php');


/**
	Es la base para crear componentes virtuales.
	Es decir, un componente corresponderia con una clase que se crea al vuelo.
	Al llamar a un método de ese componente virtual lo que hace es llamar a un response
del controlador( Gui, Actions, Commands ) asociado. 
*/

class Component   implements \ArrayAccess{
    use \team\data\Box;

    static function call($widget_name, $params, $cache = null) {

        //A partir del nombre tenemos que obtener el paquete y el componente al que pertenece el widget
        $namespace =  \team\NS::explode($widget_name);

        if(array_key_exists('name',$namespace ) ) {
            if(isset($namespace['name'])) {
                $namespace['response'] = $namespace['name'];
            }

            unset($namespace['name']);
        }

        $params =  $namespace + $params;


        //No se ha pasado un componente correcto
        if(!\team\FileSystem::exists("/".$params['package'].'/'.$params['component']) ) {
            \Team::warning('Review widget name or change " to \' in your widget name param, please', 'WIDGET_NAME');
            return '';
        }

        $cache_id = null;
        if(isset($cache) ) {
            $cache_id =  \team\Cache::checkIds($cache, $widget_name);

            $cache = \team\Cache::get($cache_id);

            if(!empty($cache)) {
                return $cache;
            }

        }


        //No es una llamada main
        $params['is_main'] = false;
        $params['widget'] = true;

        if(!isset($params['out'])) {
            $params['out'] = 'html';
        }

        $class_name = '\\'.$params['package'].'\\'.$params['component'];

        if(!class_exists($class_name) ) {
            \Team::warning("widget class $class_name not found", 'NO_WIDGET');

            return '';
        }


        $controller = new $class_name($params);
        $widget_content = trim($controller->retrieveResponse());

        if(isset($cache_id) ) {
            $cache_time = $params['cachetime']?? null;

            \team\Cache::overwrite($cache_id, $widget_content, $cache_time );
        }

        return $widget_content;
    }

	/**
		Desde el contructor nos toca averiguar si se ha instanciado directamente Component
	    ( este es el caso para las responses main ) o bien forma parte, como padre, de un componente virtual.
		Ademśa, realizamos las tareas rutinarias de inicialización del componente( se esté abriendo como main o no ).
	*/
	function __construct($params=[]) {
		if($params instanceof \team\Data) {
			$params = $params->getData();
		}

		$this->setData($params);


		/* 
			Los controladores virtuales tienen como padre a Component, mientras que en el caso de las respuestas main
			se usara la clase Component como principal. Con lo que deducimos, que si tiene padre es un componente virtual
			y sino tiene padre es Componente tal cual.
			Este trozo de código habría que reemplazarlo si alguna vez hacemos que component herede de otra clase 
		*/		
	  
        $parent = get_parent_class($this);
        $is_component = empty($parent);
        if(!$is_component) { 
            //Queremos la clase ( si \team\users -> team\users, si \Component -> component )
            $this->namespace = trim(strtolower(get_class($this)), '/' );
            list($this->package, $this->component) = explode('\\', trim($this->namespace, '\\'));
        }else {
            $this->namespace = "\\{$this->package}\\{$this->component}";
        }

        $this->path = str_replace("\\", "/", $this->namespace);

        $this->embedded = (bool)\team\Context::getIndex();
        $this->is_main =  !$this->embedded;
        
	   //Especificamos si se está usando o no la linea de comando
        $this->terminal = (boolean)\team\Context::get("CLI_MODE");

	}

	/**
		El metodo toString llamara al método por defecto
	*/
	public function toString() { return $this->retrieveResponse(); }

	/** 
		Cualquier llamada a un método de una clase componente(virtual o no) es como una llamada
	a una response( sea stage, action o command )  */
	public function __call($response_name, $arguments = NULL) { 
		//Si ha habido argumentos, utilizamos sólo el primero. 
		if(!empty($arguments) ) $arguments = $arguments[0];
		return $this->retrieveResponse($response_name, $arguments); 
	}

	/**	
		Llamamos a una response que se hapa cargo de las necesidades del llamante
		
	*/
	final function retrieveResponse($response_name = NULL, $arguments = []) {
		\team\Context::open(); //Abrimos un contexto que encapsule la response

		$this->addData($arguments);

        $this->response = \team\Check::key($response_name, $this->response);

		//Llamamos a un contructor de response, para que se encargue de hacer todo lo necesario para que la petición
		//llegue a este la response adecuado
		$data =  $this->getDataObj();
		$response = \team\Task('\team\builders\get_builder', array( $this,"_getBuilder") )->with($data);

		$result =  $response->buildResponse();

		//Acabamos la encapsulación del contexto de response
	   \team\Context::close(); 

		return $result;
	}

	/**
		Factoría que se encargaría de obtener un constructor de respuesta
		@param $params son los parámetros de construcción de la response 
		@remember:
        //Si se lanza de fuera, el formato por defecto es html
        //Si se lanza desde otra response, el formato por defecto es array
        //Si se lanza desde plantilla, el formato por defecto es html
		//Si se lanza desde un terminal, el formato será por defecto, terminal
	*/
	public  function _getBuilder($params) {
		$builders = array(
			'command' => '\team\classes\builders\Commands',
			'html' 	  => '\team\classes\builders\Gui',
			'action' => '\team\classes\builders\Actions'
		 );

		//Filtramos por el tipo de salida
	  if(\team\Context::get('CLI_MODE')) {
       $params->out = \team\Check::key($params->out, 'command');
	  }else if($params->is_main) {
       $params->out = \team\Check::key($params->out, 'html');
     }else {
       $params->out = \team\Check::key( $params->out, 'array');
     }


		//Cogemos dependiendo del tipo de salida. Sino el predeterminado será el de acciones
		$class = isset($builders[$params->out])?  $builders[$params->out] : $builders['action'];
		\team\Context::set("out", $params->out);

		if(class_exists($class) ) {
			\team\Debug::trace("Se usará el siguiente builder para crear una respuesta con salida {$params->out} ", $class);
			return new $class($params);		
		}else {
			\Team::error("Not found builder {$class}", '\team\builders\__get_builder');
			\team\Debug::me("Not found builder {$class}", '\team\builders\__get_builder');
			return ;
		}

	}
}

