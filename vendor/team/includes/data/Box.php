<?php

namespace team\data;

require(__DIR__.'/DataIterator.php');
require(__DIR__.'/DataArrayAccess.php');
require(__DIR__.'/DataTools.php');


trait Box {
    use DataIterator, DataArrayAccess, DataTools;

    protected $data = [];

    /**** SETTER ****/
    public function setRef(array & $data) {
        $this->data = &$data;
        return $this;
    }



    public function addData(array $values = []) {
        $this->data = (array)$values + (array)$this->data;

        return $this;
    }

    public function defaults(array $values = []) {
        $this->data = (array)$this->data + (array)$values;

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
    public function set($var , $value = null) {
        $this->__set($var, $value);
        return $this;
    }

    //Acceso directo para asignar todos los elementos. $data->elem1, $data->elemen2
    public function  __set($var, $value = null) {
        if(is_array($var)) {
            $this->data = $var;
        }else {
            //Ya lo que queda es asignar el valor a data.
            return $this->data[$var] = $value;
        }
    }

    /**** GETTER ****/

    public function getDataObj() { return new \team\Data($this->data); }


    public function  get($name = null, $default = null) {
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