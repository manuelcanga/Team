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


namespace team\db;

/** 
 Capa de abstracción de base de datos
 Supuestamente soportaría toda base de datos compatible con el ANSI SQL. Sin embargo, esta enfcado para su uso con PostgreSQL
 @see Recomendable usar las clases Query => /commons/classes/db . Ya que protegen de sql-injection y son mucho mas potentes.
*/
/** 
	@TODO: Hacer efectiva la capa de abstraccion, sobre todo para postgresql
 */
class DB {
    /* nombre de la conexión actual */
    private $conname = null;
	/* Referencia a la conexión que se está utilizando en el objeto actual */
	private  $server = null;
	/** Almacena la última consulta sql lanzada. Muy útil para debugs */
	private static $lastQuery = ['sql' => '', 'data' => []] ;
	/** Filas que se devolvió */
	private  $numRows = 0;
	/** ¿ Encontró resultados(1) o no(0) la última consulta ? */
	private $result = 0;
	/** Almacena todas las conexiones a la base de datos */
	private static $connections = array();
	/** El último id insertado */
	private  $lastId = 0;
	/** Las ultimas filas devueltas */
	private $rows = array();
    /** Tipo de los resultados */
    private $typeRows = \PDO::FETCH_ASSOC;
	/** Último error */
	private static $error = array();
    /** prefijo para las tablas  */
    private $dbPrefix = '';


	/** Abre una conexion a la base de datos */
	public  function __construct($conname = null ) {
        $this->change($conname);

	}

	public static function createConnection($conname ) {
		return new DB($conname);
	}

    public function connect($conname = null) {
        $connection= \team\DB::getConfig($conname);


        extract( $connection, EXTR_SKIP);

        //Conectamos a la base de datos segun la configuracion
        //	$dsn = strtolower($dbtype).":dbname={$name};host={$host};port={$port};charset={$charset}";
        $dsn = strtolower($type).":dbname={$name};host={$host};port={$port};charset={$charset}";

        if('mysql' == $type) {
             $options = $options + [ \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.$charset, ];
        }


        try {
            self::$connections[$conname]['prefix'] = $prefix?? '';
            self::$connections[$conname]['link'] = new \PDO($dsn, $user, $password, $options);
            self::$connections[$conname]['database'] = $name;
            self::$connections[$conname]['charset'] = $charset; //No se añaden otros datos por seguridad
						
			$this->dbPrefix = self::$connections[$conname]['prefix'];
         //   self::$connections[$conname]['link']->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            $this->conname = $conname;

            return  self::$connections[$conname]['link'];

        }catch(\Exception $e) {
            \Team::system("ERROR: Problem with connection to the database. {$e->getMessage()}", '\team\system\database\Connection');
        }
        return null;
    }

	public static function connectionExists($conname = null) {
		return isset(self::$connections[$conname]);
	}

	/**
		Seleccionamos entre una de las bases de datos en las que hayamos abierto una conexion
	*/
	public  function change($conname = null) {
        $conname =  $conname?: $this->conname;

        if(self::connectionExists($conname)) {
            $this->server = self::$connections[$conname]['link'];
	        $this->dbPrefix = self::$connections[$conname]['prefix'];
        }else {
            $this->server = $this->connect($conname);
        }

        if($this->server )
            $this->conname =  $conname;

	}

    public function close($conname = null) {
        $conname =  $conname?: $this->conname;

        if(self::connectionExists($conname)) {
           self::$connections[$conname]['link']->close();
        }

        //¿Se trata de la conexión actual ?.
        if($conname == $this->conname) {
            $this->conname = '';
            $this->server = null;

            //Mientras que haya objeto tiene que haber una conexión
            //Así que cambiamos o nos conectamos a la principal
            $this->change();
        }
    }


	/**
		Comprobamos si hay alguna conexión activa
	*/
	public  function test() { return isset($this->server); }

	public static function debug() {
		\team\Debug::me(self::$connections, 'Database connections' );
	}
	
	public function getDatabase() {

        if(self::connectionExists($this->conname)) {
            return self::$connections[$this->conname]['database'];
        }else {
            return null;
        }
	}
	
	
    /**
        Retrieve tables in current database
    */
    function getTables($like = null) {
        if(isset($like) ) {
            $like = "AND table_name LIKE '".$like."'";
        }
        
        $database = $this->getDatabase();
        if(empty($database) ) return null;
    
        $result = $this->getAll("SELECT table_name FROM information_schema.tables WHERE table_type = 'BASE TABLE' AND table_schema='{$database}' $like");
        
        /* Hay alguno SGBD que devuelve cada tabla en un subarray dependiente de un key
            [  
                0 	=>  'table_name' =>  mod_imagenes ]
                1   =>	'table_name' =>	 mod_imagenes_tmp ]
            ]
        
            No creo que sea usable por el programador cliente y por eso, quitamos esas keys.
        */
        if(!empty($result) ) {
            $result = array_map(function($value) { return current($value); }, $result);
        }
        
        
        return $result;
    }

	/**
		Entrecomilla correctamente una cadena de caracteres
	*/
	public function quote($_string) { return $this->server->quote($_string); }

    public function setTypeRows($type) {
        $this->typeRows = $type;
    }

    public function setDbPrefix($newDbPrefix){
        $this->dbPrefix = $newDbPrefix;
    }

    /**
    Remplazamos el "@" por el prefijo actual para las tablas
     */
    public function prefix($str) {
        return str_replace("@", $this->dbPrefix, $str);
    }


	public function prepare() {}

	protected function registerSQL($_sql, $_values) {

		$with_error =  $this->checkError();

		/** Guardamos el sql por si luego lo quisieramos visualizar */
		self::$lastQuery = ['sql' => $_sql, 'full_sql' => $this->reverseSQL($_sql, $_values), 'data' => $_values, 'rows' => $this->rows, 'numRows' => $this->numRows, 'lastId' => $this->lastId, 'result' => $this->result, 'errors' => self::$error, 'error' => $with_error];



		if(\team\Config::get("TRACE_SQL") || $with_error  ) {
			\Debug::sql();
		}

	}


	/**
	 * Replaces any parameter placeholders in a query with the value of that
	 * parameter. Useful for debugging. Assumes anonymous parameters from 
	 * $params are are in the same order as specified in $query
	 *
	 * @param string $query The sql query with parameter placeholders
	 * @param array $params The array of substitution parameters
	 * @return string The interpolated query
	 * @link http://stackoverflow.com/questions/210564/getting-raw-sql-query-string-from-pdo-prepared-statements
	 */
	public  function reverseSQL($query, $params) {
		$keys = array();

		# build a regular expression for each parameter
		 if(!empty($params) ) {
			 foreach ($params as $key => $value) {
				 if (is_string($key)) {
				     $keys[] = '/:'.$key.'/';
				 } else {
				     $keys[] = '/[?]/';
				 }
			 }
		}

		$query = preg_replace($keys, $params, $query, 1, $count);

		return $query;
	}


	/**
		Lanza la consulta especificada
		@param String $_sql cadena sql que representa la consulta
		@param Array $_values  valores de reemplazo para la consulta
	*/
	public function query($_sql,Array $_values = []) {
		$conection = $this->server;


		//Preparamos y optimizamos la consulta
		$query = $conection->prepare($_sql);

		//Lanzamos la consulta
		if(!empty($_values) ) {
			$this->result = $query->execute($_values);
		}else {
            $this->result  = $query->execute();
		}
		self::$error = $query->errorInfo();

        $this->rows = $query->fetchAll($this->getTypeRows());
		//Guardamos el numero de elementos afectados3
		$this->numRows = \team\Check::id($query->rowCount(),0);
		//Guardamos el id, por si fuera una insercion
		$this->lastId = \team\Check::id( $conection->lastInsertId(), 0 );

		$this->registerSQL($_sql, $_values);
	
		//Cerramos el cursor
		$query->closeCursor();

		return $this->result;
	}



	/**
		Hace una consulta y devuelve todos los resultados
	*/
	public  function getAll($_sql, Array $_values = NULL) {

		$conection = $this->server;

		//Preparamos y optimizamos la consulta
		$query = $conection->prepare($_sql);

		//Lanzamos la consulta
		if(!empty($_values) ) {
			$this->result  = $query->execute($_values);
		}else {
            $this->result  = $query->execute();
		}

		//Si todo salio mal...
		self::$error = $query->errorInfo();

		//Guardamos el numero de registros afectados
		$this->numRows =  \team\Check::id($query->rowCount(),0);
		//
		$this->rows = $query->fetchAll($this->getTypeRows());

		$this->registerSQL($_sql, $_values);

		$query->closeCursor();

		return $this->rows;
	}


	/**
		Devuelve todos los elementos asociados a una consulta
	*/
	public  function get( $elements, Array $_values = NULL) {
		$default = [ 'from' => null, 'where' => null, 'group_by' => null, 'order_by' => null, 'having' => null, 'limit' => null, 'offset' => null, 'select' => '*' ];
		
		if(!is_array($elements) ) {
			$params = $elements;
			parse_str($params, $elements);
		}

		extract( ($elements + $default) );


        //Select tiene la opción de pasarse como array
        if(is_array($select)) {
            $select = implode(',', $select);
        }
		$sql =  " SELECT {$select}";


        if(is_array($from)) {
            $from = array_map([$this, 'prefix'], $from);
            $sql .= ' FROM '.implode(',', $from);
        }else {
            $sql .= ' FROM '.$this->prefix($from);
        }



		$sql .= $this->whereProccesor($where);

		if(isset($group_by)  && !empty($group_by) ) {
		    if(is_array($group_by)) {
		        $group_by = array_map([$this, 'prefix'], $group_by);
		        $sql .= ' GROUP BY '.implode(',', $group_by);
		    }else {
		        $sql .= ' GROUP BY '.$this->prefix($group_by);
		    }
		}

		if(isset($having)  && !empty($having)  ) {
		     $sql .= ' HAVING'.$having;
		}


		if(isset($order_by) ) {
            if(is_array($order_by)) {
                $order_by_to_convert = $order_by;
                $order_by = [];
                foreach($order_by_to_convert as $field => $value) {
					//ej: $order_by[] = [ id => 'asc' ];
					if(is_array($field)) {
						list($field, $value ) = each($field);
					}
                    if(is_numeric($field)){
                        $order_by[] = $value; //ej: $order_by[] = 'id desc';
                    }else {
                        $order_by[] = $field.' '.$value;
                    }
                }
                $order_by = implode(',', $order_by);
            }

			$sql .= " ORDER BY {$order_by}";
		}

		if(isset($limit) ) {
			if(is_numeric($limit) && $limit > 0)
				$sql .= " LIMIT {$limit}  ";
		}

		if(isset($offset) ) {
            if(is_array($offset)) {
                $offset = implode(',', $offset);
            }
			$sql .= "OFFSET $offset";
		}


	//\team\Debug::out($sql);

//\team\Debug::out($_values);


		return $this->getAll($sql, $_values);

	}


	/** Devuelve sólo el primer registro de la base de datos
		 @param $_sql es la consulta sql a partir de la cual se estraerá el registro en cuestion */
	public  function getRow($_sql) {

		/** Lanzamos la consulta */
		$result = $this->getAll($_sql);

		/** Devolvemos el registro */
		if(!empty($result) ) {
			return $this->lastRow = $result[0];
		}else {
			return $this->lastRow = array();
		}
	}

	public function getVar($_sql, $_var, $connection = null) {
		/** Lanzamos la consulta */
     	$row  = $this->getRow($_sql, $connection);

		/** Devolvemos el registro */
		if(!empty($row) && array_key_exists($_var, $row) ) {
			return $row[$_var];
		}else {
			return null;
		}
	}

	/** 
		Lanza una consulta de actualizacion
		En modo seguro sólo se actualizará un registro
	*/
	public function update($_table, $set, Array $where, $_values = NULL, $secure = true) {
		$table = $this->prefix($_table);


		if(is_array($set) && !empty($set) ) {
			$_set = '';
			foreach($set as $field => $newValue) {
				$_set .= $field.' = '.$newValue.',';
			}

			$_set = rtrim($_set, ',');
		}else {
			$_set = $set;
		}


		$sql = 'UPDATE '.$table.' SET '.$_set;
		$sql .=  $this->whereProccesor($where);

		if($secure)  $sql .= ' LIMIT 1';

		return  $this->query($sql, $_values);

	}

	/**
		Lanza una consulta de insercion
	*/
	public  function insert($_table, $_values, $_fields = "", $data = []) {

        $table = $this->prefix($_table);

		if(is_array($_values) ) {
	        $_values = implode(',', $_values);
		}

		if(is_array($_fields) ) {
			$_fields = implode(',', $_fields);
		}

		if(!empty($_fields) ) {
			$sql = 'INSERT INTO '.$table.'('.$_fields.') VALUES ( '.$_values.' ) ';
		}else {
			$sql = 'INSERT INTO '.$table.' VALUES ( '.$_values.' ) ';
		}

		$this->result = $this->query($sql, $data);

		//Si todo salio ok, devolvemos el ultimo id que se realizo
		if($this->result) {
            $this->lastId = \team\Check::id( $this->server->lastInsertId(), 0 );
            return $this->getLastId();
        }
		
		return $this->lastId = 0;
	}		

	/**
		Lanza una consulta de borrado
		En modo seguro no se podrá hacer delete sin where y los deletes con where estarán limitados a 1 elemento
	*/
	public  function delete($_table, $_where , $data, $secure = true) {

		$table = $this->prefix($_table);

		if(empty($_where) ) {
			if($secure) return false;
			$sql = 'DELETE FROM '.$table;
		}else {
			$sql = 'DELETE FROM '.$table;
			$sql .=  $this->whereProccesor($_where);

			if($secure)  $sql .= ' LIMIT 1';
		}
		return $this->query($sql, $data);
	}




	/** 
		Devuelve la ultima consulta que se hizo
	*/
	public static function getLastQuery() { return self::$lastQuery; }
	/**
		Devuelve el numero de registros devueltos
	*/
	public  function getNumRows() { return $this->numRows; }
	/**
		Devuelve si la ultima consulta tuvo exito o no
	*/
	public  function getResult() { return $this->result; }
	/**
		Devuelve el id de la ultima insercion realizada
	*/
	public  function getLastId() { return $this->lastId; }
	/**
		Devolvemos los ultimas filas
	*/
	public  function getRows() { return $this->rows; }
    /**
         Develovemos el tipo de las filas
     */
    public function getTypeRows() { return $this->typeRows; }
	/**
		Devolvemos el ultimo error
	*/
	public  function error() { return self::$error; }

	/**
		Check if last query had errors
	*/
	public function checkError() { return  isset(self::$error[0]) && '0000'!= self::$error[0]; }


	/** _____________ HELPERS ________________________ */
	protected function whereProccesor($where) {
		$sql = '';
		if(isset($where) ) {
            if(is_array($where)) {


				//$this->where = ['id' => 3]   <transform to>   $this->where[0] = ['id' => 3];
				if(!isset($where[0]) and 1 == count($where) ) {
	               $where_to_convert[0] = $where;					
				}else {
	               $where_to_convert = $where;
				}

               $where = [];
				//hay dos modos de pasar datos: literalmente o como array
               foreach($where_to_convert as $field => $value  ) {
					//ej: $where[] = [ id => 10 ];

					if(is_numeric($field) ) {
						if(!is_array($value) ){
		                    $where[] = $value; 	//ej: $where[] = 'id = 10';
							$value = null;
		                }else {
							list($field, $value ) = each($value);  //ej: $where[] = ['id' => 10]; <transform> $where['id'] = 10;
						}
					}

					if(isset($value) ){
                        $where[] = $field.' = '.$value; //ej: $where['id'] = 10;
					}
               }

                $where = implode(' AND ', $where);
            }

			$sql .= " WHERE {$where}";
		}

		return $sql;

	}

} // Fin class
