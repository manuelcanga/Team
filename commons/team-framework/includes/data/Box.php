<?php

namespace team\data;

require(__DIR__.'/DataIterator.php');
require(__DIR__.'/DataArrayAccess.php');
require(__DIR__.'/DataTools.php');


trait Box {
    use DataIterator, DataArrayAccess, DataTools;
   
	protected $data = [];

    /**** SETTER ****/
   function setRef(array & $data) { 
		$this->data = &$data;  
		return $this; 
	}

   function setData(array $_data = null) {  
		$this->data = (array) $_data;  
		return $this; 
	}

   function setBox($_data = null) {  
		$this->data = $_data;  
		return $this; 
	}

   function addData(array $values = []) {
        $this->data = (array)$values + (array)$this->data;

        return $this;
    }

    /**
     * Asignamos un valor  a la clase. 
     * Devuelve $this para poder hacer encademinamiento( aunque no esté bien hacerlo )
     * $this->set('age', 10)->set('project', 'team'); ...
     * @param unknown $name
     * @param unknown $value
     * @return \team\Storage
     */
   function set($name, $value = null) {
		 $this->__set($name, $value);
		 return $this;
	}

    //Acceso directo a todos los elementos. $data->elem1, $data->elemen2
   function  __set($name, $value = null) {
        //Si se quiere actualizar todos los datos de una vez
   		if(!isset($value) && is_array($name) )
			return $this->data = $value;

		//Ya lo que queda es asignar el valor a data.
		return $this->data[$name] = $value;
    }

    /**** GETTER ****/
   function & getData($var = null) {
		if(isset($var) ) {
			return $this->data[$var];
		}else {
		   return $this->data; 
		}
	}
   function getDataObj() { return new \team\Data($this->data); }


    function  get($name = null, $default = null) {
       if(!isset($name)) return $this->data;
      
       if(array_key_exists($name, $this->data) ) {
           return $this->offsetGet($name);
       }
       
       return $default;
    }

    public function & __get($var) {
       return  $this->offsetGet($var);
    }



    /**** ISSET ****/
    public function __isset($_name) {
        return $this->exists($_name);
    }

	public function exists($name = null) {
		if(!isset($name) ) {
			return !empty($this->data);
		}

        return $this->offsetExists($name);
	}

    /**** UNSET ****/
    public function __unset($name = null) {
        $this->offsetUnset($name);
    }
} 
