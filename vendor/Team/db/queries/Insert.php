<?php

namespace Team\db\queries;

trait Insert {

    /**
    insert in a table
   	Ej:

    $query = new \Team\db\Query();
    $query->email("framework@latrasweb.net"); //dato
    $query->pass("Team"); //dato
    $query->level(3); //dato
    $query->state(1); //dato
    //asignamos un dato fijo en lastlogin
    $query->lastLogin = "NOW()"; //sentencia
    //Lanzamos la insercion sobre la tabla @Access ( @ se reemplazara por el prefijo de las bases de datos )
    $result = $query->add("@Access"); //accion

     */
    public function add($table) {
        //Asignamos valores a la consulta sql
        list($fields, $values) =  $this->getInsertValues( );


        //lanzamos la consulta
        return $this->database->insert($table,  $values, $fields, $this->values);

    }



    /**
    Asignamos a la consulta sql todos los valores recogidos
    @param String $_sql consulta sql a la que se le añadira los campos
     */
    protected function getInsertValues( ) {
        $sentences = $this->get();

        $fields = [];
        $values = [];

        //Añadimos los datos para reemplazar
        foreach($this->values as $_field => $_value) {

				if(isset($_value) ) {
					$fields[] = " {$_field} ";
             	    $values[] = ":{$_field}";
				}else {
					$fields[] = " {$_field} ";
					$values[] = " null ";
					unset($this->values[$_field]);
				}


        }
        //Añadimos los datos fijos( es responsabilidad del programador escaparlos )
        foreach($sentences as $_field => $_value) {
				$fields[] = " {$_field} ";
           		$values[] = " $_value ";
        }


        return [$fields, $values];

    }

}
