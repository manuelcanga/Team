<?php
namespace team\data;

/**
	Utilidades para la importación de datos rápidamente a un objeto
*/
trait DataTools {

	/**
		Importa los datos de data en el objeto que implemente DataTools
		En caso de pasarse param fields, sólo se importarán los keys que se encuentren en field.
	*/
    public function import(Array $_data, $fields = [], $prefix = 'put') {
        if(empty($_data) || !is_array($_data)) return false;

    
        return self::sendTo($this, $fields, $_data, $prefix);
    }
    
    public function sendTo($object,$fields = [],  $data = [], $prefix = 'put') {
        if(empty($data)) $data = $this->data; 
    
		//Si no se ha pasado keys para filtrar se supondrá que
		//queremos todos los keys que haya en datos
		if(empty($fields) ) {
            $fields = array_keys($data);
		}

        $noImported = array_intersect_key($data, array_flip($fields));

        //Si hay datos los procesamos
        if(!empty($data) ){
            foreach ($fields as $_name) {
                $name = \team\Check::key($_name);
                if(empty($name) ) continue;
    
                $method = $prefix. str_replace('_','',$name);

                if ( method_exists($object, $method) && array_key_exists($_name, $data) ) {
					$_value = $data[$_name];
		
					//Mandamos el valor a la clase
                    $object->$method($_value);
		
					//Como se ha importado correctamente, lo eliminamos de no importados
					unset($noImported[$_name]);
                }
            }
        }


       return $noImported;
    }
    

	/** Por si necesitamos depurar datos */
    public function debug() {
        $_class = 'Object of '.get_class( $this );

        $file = null;
        $line = null;
        \team\Debug::getFileLine($file, $line);

        \team\Debug::me($this->data, $_class, $file, $line);
    }
}

