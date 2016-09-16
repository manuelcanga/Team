<?php
namespace team\data;



/**
Remember using: implements \ArrayAccess  in your class definition
 */

trait DataArrayAccess {



    function offsetUnset($offset){
        if(!isset($offset) ) {
            $this->data = [];
        }if(array_key_exists($offset, $this->data)  ) {
            unset($this->data[$offset]);
        }
    }

    function offsetExists($offset) {
        return  isset($this->data[$offset]);
    }


    function &  offsetGet($offset) {
        if(!isset( $this->data[$offset]) )
            $this->data[$offset] = null;


        return $this->data[$offset];
    }



    function & offsetSet($offset, $valor) {

        if(is_null($offset)) {
            return $this->data[] = $valor;
        }else {
            $this->data[$offset] = $valor;
        }

        return  $this->data[$offset];
    }
}