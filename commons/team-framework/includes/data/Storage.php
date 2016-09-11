<?php

namespace team\data;



require(__DIR__.'/Box.php');


trait Storage {
    use Box;


    //Acceso directo a todos los elementos. $data->elem1, $data->elemen2
   function  __set($name, $value = null) {          
        //Si se quiere actualizar todos los datos de una vez
        //ej: $this->set(['name' => 'Manuel Canga'];
   		if(!isset($value) && is_array($name) ) {
			 return $this->data = $name;
		
   		}
		
		//Ya lo que queda es asignar el valor a data.
		return $this->data[$name] = $value;
    }

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
		$method =  'get'.\team\Sanitize::identifier($name);		
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
        $method =  'get'.\team\Sanitize::identifier($_method);
        if(method_exists($this,$method)) {
            return call_user_func_array([$this, $method], $arguments);
        }else {
			\team\Debug::me("Not found method $method");
		}
        
        return ;
    }


    /**** ISSET ****/
    function offsetExists($offset) {
        return isset($this->data[$offset]);
    }



  
} 
