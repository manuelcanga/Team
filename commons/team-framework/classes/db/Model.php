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
    function __construct($id = 0,  $initializer = null) {
		$initializer = $initializer?? $this->initializer;

		if($initializer && method_exists($this,  $initializer) ) {
			$this->initializer = $initializer;
	       $this->$initializer($id);
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
	public function newCollection($registers, $activerecord_class= null,  $defaults = []) {
	        return new \team\db\Collection($registers ,$activerecord_class?? get_class($this), $defaults );
	}


	/* ------------------ getters and setters  ___________________ */

	protected function load($id , $data = []) {
		if(!empty($data) ) {
			$this->setData($data);
			$this[static::ID] = $this->safeId;
		}
	}

	public function getListUrl() {
		return $this->listUrl;
	}


	/* ------------------ QUERIES ___________________ */


	/**
		Count all rows in table 
	*/
	public function countAll($sentences = [], $data = []) {
        $query =  $this->newQuery($data, $sentences );
		return $query->getVar('total', static::TABLE, 'count('.static::ID.') as total');
	}


	/**
		Retrieve all rows from table TABLE 
		@param array $sentences list of params to query. Excepcionally, you can pass a 'order' params(ASC or DESC)
		@param array $data   list of data to query
	*/
    public function findAll(  $sentences = [], $data = [],  $activerecord_class= null) {
		$sentences = $sentences?? [];

		$order = 'DESC';
		if(isset($sentences['order']) ) {
			$order = $sentences['order'];
			unset($sentences['order']);
		}

		$default = ['limit' => -1, 'order_by' =>  [static::ID  =>  $order] ]; 

		$sentences = $sentences + $default;

        $query =  $this->newQuery($data + $this->data, $sentences );

        return $this->newCollection($query->getAll(static::TABLE), $activerecord_class );
    }


	/* ----------------- EVENTS ----------------- */

	/**
		Initialize by default
	*/
    protected function onInitialize($id){}


	//This function from Collection for everytime a newRecord is created
	function onNewRecord($id, $data) {
		$this->load($id, $data); 
	}

}
