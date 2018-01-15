<?php

namespace Team\Data;



require(__DIR__.'/Box.php');


trait Storage {
    use Box;

    function & offsetSet($offset, $valor) {

        if(is_null($offset)) {
            return $this->data[] = $valor;
        }else {
            $this->data[$offset] = $valor;
        }

        return  $this->data[$offset];
    }


    /**** GETTER ****/

    function  get($name = null, $default = null) {
        if(!isset($name)) return $this->data;


        //¿Tiene un método asociado ?
        $method =  'get'.\team\data\Sanitize::identifier($name);
        if($method && method_exists($this, $method) ) {
            return  $this->$method();
        }

        if(array_key_exists($name, $this->data) ) {
            return $this->data[$name];
        }

        return $default;
    }

    function __get($var) {
        return $this->get($var);
    }

    function __call($_method, $arguments) {
        $method =  'get'.\team\data\Sanitize::identifier($_method);
        if(method_exists($this,$method)) {
            return call_user_func_array([$this, $method], $arguments);
        }else {
            \Team\Debug::me("Not found method $method", $level = 2);
        }

        return ;
    }


    /**** ISSET ****/
    function offsetExists($offset) {
        return isset($this->data[$offset]);
    }




}
