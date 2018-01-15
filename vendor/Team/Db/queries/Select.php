<?php
/**
New Licence bsd:
Copyright (c) <2014>, Manuel Jesus Canga Muñoz
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

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 'AS IS' AND
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


namespace Team\Db\queries;

trait Select {
    /** Devuelve todos los registros para la consulta realizada
        Recupera datos de una base de datos
        Ejemplo 1:
        $query = new \Team\Db\Query();
        $result = $query->getAll("@Access");  /

        Ejemplo 2:

        query = new \Team\Db\Query();
        $query->limit = 10;
        $result = $query->getAll("@Access");

        Ejemplo 3:
        $query = new \Team\Db\Query(['idmenor' => 3);
        $query->idmayor(5); //2º forma de pasar datos
        $query->where = " idAccess > :idmenor && idAccess < :idmayor ";
        $result = $query->getAll("@Access");

        Ejemplo 3 alternativo:
        $data = new \Team\Data\Data();
        $data->idmenor = 3;
        $data->idmayor = 5;
        $result = new \Team\Db\Query($data)->getAll("@Access");


        Ejemplo 4:
        $query = new \Team\Db\Query();
        $query->select = " name ";
        $query->from = "@Access"
        $result = $query->getAll();


     */
    public function getAll($from = NULL, $select = [], $limit = -1) {
		//Si existe la sentencia from, damos prioridad a esta
		if(isset($this->from) ) {
			$from = $this->from;
		}

        if(isset($from) ) {
			if(!is_array($from) ) {
				$from = (array)$from;
			}

            $this->from = $from;

		}

		//Si existe la sentencia select, damos prioridad a esta
		if(isset($this->select) ) {
			$select = $this->select;
		}

        if($select) {
			if(!is_array($select) ) {
				$select = (array)$select;
			}
            $this->select = $select;
        }

		//Si existe la sentencia limit, damos prioridad a esta
		if(isset($this->limit) ) {
			$limit = $this->limit;
		}


		if(-1 != $limit) {
			$this->limit = $limit;
		}

		$sentences = $this->get();
		$values = $this->values;


        return $this->database->get($sentences, $values);
    }


	/**
		Es como getAll pero devuelve sólo los valores de una columna $field.
		Si se añade el campo $index. Entonces el array resultante tendrá de keys el campo especificado en $index
	*/
	public function getVars($field, $from = null, $select = [], $index = null) {
		$result = $this->getAll($from, $select);


		if(empty($result) ) { return []; }

		if(isset($index) ) {
			return array_column($result, $field, $index);
		}else {
			return array_column($result, $field);
		} 
	}

    /**
     * Es como getAll pero retorna sólo una fila
     *
     * @param null $from
     * @param array $select
     * @return mixedE
     */
    public function getRow($from = NULL, $select = [],$where = null) {

		if(isset($where) ) {
			if(is_array($this->where) ) {
				$this->where[] = $where;
			}else {
				$this->where = $where;
			}
		}
               
       $rows = $this->getAll($from, $select, 1);
        if(1 === count($rows)) {
            return $rows[0];
        }
    }

    /**
     * Es como getRow pero retorna sólo un valor de esa fila.
     *
     * @param $field campo del que se quiere obtener el valor
     * @param null $from tabla asociada 
	 * @param string $select select avanzado que se quiere( por ejemplo, para consultas con agregados )
     * @return el valor pedido o false si no se encuentra
     */
    public function getVar($field, $from = NULL, $select = null, $where = null) {
		//Esta función es un método directo para obtener un valor
		//Si se adelanto el usuario para meter un select, no tiene sentido añadirlo de nuevo
		if(!isset($this->select) )
			$select = ($select)?: $field;

		if(isset($where) ) {
			if(is_array($this->where) ) {
				$this->where[] = $where;
			}else {
				$this->where = $where;
			}
		}

        $row = $this->getRow($from, [$select]);

        if(isset($row[$field])) {
            return $row[$field];
        }else {
            return false;
        }
    }
}
