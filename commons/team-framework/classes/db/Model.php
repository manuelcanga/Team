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

namespace team\db;


/**
		Simple Model skel

		@TODO en un futuro:
		Los models tendrá los campos de la entidad. Serán las clases que se pasen a los collections. Es la base para los List y los Gest
		Los collections servirán para realizar los distintos listados de elementos. Serían los antiguos List. Ej: Noticias
		Los activeRecord heredarán de algún model que ya tenga definido sus campos. servirá para insertar, modificar y borrar. Serían los antiguos Gest. Ej: GestNoticias

		La clase actual de Collection debería de separarse en dos: Collection que gestionaría las consultas y los filtros( y cuyo resultados se la pasaría a Pagination ) y pagination que hace todos los calculos de paginación a partir de los resultados de Collection y luego crea un PageIterator
	

*/
abstract class Model implements \ArrayAccess, \Iterator{
    use \team\data\Storage, \team\db\Database;


    const ID = '';
    const TABLE = '';
	protected $initializer = 'onInitialize';
	protected $listUrl = null;


	/**
		Construct a Model
		@param mixed $id :  primary key or key used in order to initialize
		@param array $data : data for initializing
		$param string|bool $initializer : method used in order to initialize data. Use false value to avoid initialization
	*/
    function __construct($id = 0, $data = [], $initializer = null) {
		$initializer = $initializer?? $this->initializer;
		$data = $data?? [];

		$this->commons($id, $data, $initializer);

		if($initializer && method_exists($this,  $initializer) ) {
			$this->initializer = $initializer;
	        $data = $this->$initializer($id, $data);
		}else {
			$this->onLoad($id, $data);
		}
		
    }



	/* ----------------- Results----------------- */
	public function pagination($_elements_for_page = 10, $data = [], $colecction='\team\gui\Pagination') {
		$collection = $colecction($_elements_for_page, $data + $this->data); 

		$collection->setModel($this);

		return $collection;
	}


	/** 
		Create a iterator for registers 
	*/
	public function newCollection($registers,  $defaults = [], $collection_class='\team\db\Collection') {
	        return new $collection_class($registers ,get_class($this), $defaults );
	}


	/* ------------------ getters and setters  ___________________ */

	protected function load($id , $data = []) {
		if(!empty($data) ) {
			$this->setData($data);
			$this->safeId = $this[static::ID]  = $id;
		}
	}

	public function getListUrl() {
		return $this->listUrl;
	}


	/* ------------------ QUERIES ___________________ */


	/**
		Count all rows in table 
	*/
	public function countAll($sentences = [], $data = [], $table = null) {
		$table = $table ??  static::TABLE;

        $query =  $this->newQuery($data, $sentences );
		return $query->getVar('total', $table, 'count('.static::ID.') as total');
	}


	/**
		Retrieve all rows from table TABLE 
		@param array $sentences list of params to query. Excepcionally, you can pass a 'order' params(ASC or DESC)
		@param array $data   list of data to query
	*/
    public function findAll(  $sentences = [], $data = [], $table = null) {
		$sentences = $sentences?? [];
		$table = $table ??  static::TABLE;

		$order = 'DESC';
		if(isset($sentences['order']) ) {
			$order = $sentences['order'];
			unset($sentences['order']);
		}

		$default = ['limit' => -1, 'order_by' =>  [static::ID  =>  $order] ]; 

		$sentences = $sentences + $default;

        $query =  $this->newQuery($data + $this->data, $sentences );

        return $this->newCollection($query->getAll($table) );
    }


	/* ----------------- EVENTS ----------------- */
	/*
		Before initializers
	*/
	protected function commons($id, $data, $initializer) {}

	/**
		Initialize by default
	*/
    protected function onInitialize($id, $data =  []) {

		if(!empty($data) ) {
	 	   $this->import($data);
		}
	}

	/** When a initializer is not selected */
	protected function onLoad($id , $data = []) {
		$this->load($id, $data);
	}



	//This function from Collection for everytime a newRecord is created
	function onNewRecord() {}

}
