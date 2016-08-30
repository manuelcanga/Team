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
		Simple ActiveRecord class for Team Framework
*/
abstract class ActiveRecord extends \team\db\Model{

	const DETAILS_URL = '';

    protected $safeId = 0;
	


	/* ----------------- Checks----------------- */

	/**
		Validamos el campo clave ID del activerecord
		@param $id es el valor a usar como campo clave
	*/
	function checkId($id) {
			return \team\Check::key($id, 0);
	}

	function isSafe() {
		return (bool) $this->safeId;
	}



	public function exists($name = null) {
		if(!isset($name) ) {
			return $this->exists(static::ID);
		}

        return isset($this->data[$name]);
	}



	/* ----------------- Geters and Setters ----------------- */


	function & getId() {
		return  $this->safeId;
	}

	public function getGeneratedUrl($data = null, &$matches = null) {
		if(!isset($data) ) $data = $this->data;

		return \team\Url::to(self::DETAILS_URL, $data, $matches);
	}


	/* ----------------- QUERIES ----------------- */


	/**
		Initialize by default
	*/
    protected function initializeIt($id, $sentences = [], $table = null, $data = []) {
		$table = $table ??  static::TABLE;

	    $query = $this->newQuery([static::ID =>  $id] + $data,  $sentences);
	    $query->where[] = [ static::ID  =>  ':'.static::ID  ];	
		$record = $query->getRow($table);

		if(!empty($record) ) {
			 $this->setData($record);
		}

		$this[static::ID] = $id;

	}


	/**
		En modo seguro sólo se actualizará un registro
	*/
	public function save($sentences = [], $secure = true) {
		if($this->safeId ) {
			return $this->updateIt($sentences, $secure);
		}else {
			return $this->insertIt($sentences);
		}
	}

	public function UpdateIt($sentences = [], $secure = true, $table = null ) {
		$table = $table ??  static::TABLE;

        $query = $this->newQuery($this->data, $sentences );

		$query->where[] = [ static::ID  =>  ':'.static::ID  ];
		$result = $query->update($table, $secure);

		if($result) {
			$initializer = $this->initializer;
			$this->$initializer($this[static::ID]);
		}


		return $result;
	}


	public function insertIt($sentences = [], $table = null) {
		$table = $table ??  static::TABLE;

		if(!isset($this[static::ID]) ) {
			$this[static::ID] = null;
		}


        $query = $this->newQuery($this->data, $sentences );

		$id =  $query->add($table);
		if(!empty($id) ) {
			$initializer = $this->initializer;
			$this->$initializer( $id );

		}
		return $id;
	}

	/**
		Realiza el borrado en la base de datos.
		Si $secure es true, no se podrá hacer un delete sin where y los delete con where estarán limitados a un elemento.
	*/
	public function removeIt($sentences = [], $secure = true, $table = null) {
		$table = $table ??  static::TABLE;

		if(!$this->safeId ) return false;

        $query = $this->newQuery($this->data, $sentences );

		$query->where[] = [ static::ID  =>  ':'.static::ID  ];

		return $query->delete($table, $secure);
	}


	/* ----------------- EVENTS ----------------- */

	protected function onInitialize($id) {
        $this->safeId = $this->checkId($id);

		if( $this->safeId) {
			$this->initializeIt($this->safeId);
		}
	}

} 
