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

Classes::add('team\data\stores\Store', "/classes/data/stores/Store.php", _TEAM_);
Classes::add('team\data\formats\Format', "/classes/data/formats/Format.php", _TEAM_);
Classes::add('team\interfaces\data\Store', "/includes/interfaces/data/Store.php", _TEAM_);
Classes::add('team\interfaces\data\Format', "/includes/interfaces/data/Format.php", _TEAM_);
Classes::add('team\interfaces\data\HtmlEngine', "/includes/interfaces/data/HtmlEngine.php", _TEAM_);

class Data implements \ArrayAccess
{
    use \team\data\Box;

    protected $store;

    public function setStore($store, $_origin = NULL, $_options = [], $_store_defaults = []) {
        $this->store = \team\data\stores\Store::get($store);
		if(isset($this->store) ) {
			if(isset($_origin)) {
				 $this->data =& $this->store->import($_origin, (array) $_options, $_store_defaults);
			}else if(method_exists($this->store, 'setData') ){
				$this->store->setData($this->data);
			}
		}
		return $this->store;
    }

	/** Debería de ser al reves */
	function __construct($store = NULL, $_origin = NULL, $_options = [], $_store_defaults = []) {

		//Check if implements Box instead
		if($store instanceof \team\Data ) {
            return $this->data = $store->getData();
        }else if(is_array($store) ) {
			return $this->data = $store;
		}else if(isset($store)  ) {
			$this->setStore($store,$_origin, $_options, $_store_defaults);
		}
	
	}

    function export($_target = NULL, $_options = [], $store = null) {

        if(empty($this->data) ) {
            $this->data = array();
        }

        //Si no se ha especificado un store ni tampoco tenemos ninguno definido
		//hacemos que el export lo que haga es un out
        if(!isset($store) && !isset($this->store)) {
	            return $this->out();
		}


        if(isset($store)) {
            $this->setStore($store);
		}


        if(is_object($this->store) ) {
            return $this->store->export($_target, $this->data, $_options);
		}

    }

    function __call($_method, $arguments) {
		$result = false;
        $method =  \team\Sanitize::identifier($_method);

        if($this->store && method_exists($this->store, $method)) {
           $result =  call_user_func_array([$this->store, $method], $arguments );
        }else {
			\team\Debug::me("Not found method  $method");
		}

        return $result;
    }


    /**** FORMATS ****/
	public function __toString() { 

		if(!isset($this->data['view']) && !isset($this->data['layout'])) {
			$_class = 'Object of '.get_class( $this );

			$file = null;
			$line = null;
			\team\Debug::getFileLine($file, $line);
	
			return \team\Debug::get($this->data, $_class, $file, $line);
		}else {
			return self::out('Html');
		}
	}

	public final function out($_type = NULL, $options = [], $defaults = []) {
		$format_class = new \team\data\formats\Format();

		$type = $_type?? $format_class->filter($_type);

		if( !isset($type) &&  isset($this->out) ) {
            $type = \team\Check::key($this->get("out"), "Array");

			 unset($this->out);
		}

		//Factory de vistas
		$obj = $format_class->get($type);



		if(!isset($obj) ) {
			\Team::system("Not found Data format  for {$type}", '\team\Dataformat_Not_Found');
		}

		return $obj->renderer($this->data + $defaults, $options);
	 }

	
	final function debug() {
		$_class = 'Object of '.get_class( $this );

		$file = null;
		$line = null;
		\team\Debug::getFileLine($file, $line);
		
		 \team\Debug::me($this->data, $_class, $file, $line);
	}
}
