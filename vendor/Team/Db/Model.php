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

namespace Team\Db;


/**
    Simple Model skel

    Models are useful in order to create Queries for lists in only a table( for more table check Finds
    ActiveRecords are useful in order to create/update/remove rows in a table
    Traits are useful in order to manager fields or attributes
*/
abstract class Model implements \ArrayAccess{
    use \Team\Data\Storage, \Team\Db\Database;


    const ID = '';
    const TABLE = '';
	protected  $listUrl = null;



	/* ----------------- Results----------------- */
	public function pagination(int $_elements_for_page = 10, $current_page = 1 ,string $pagination='\Team\Gui\Pagination') {
        $pagination = new $pagination($_elements_for_page, $current_page,  $this->data);

        $pagination->setModel($this);

		if(isset($this->listUrl) )
			$pagination->setBaseUrl($this->getListUrl());

		$pagination->setFrom(static::TABLE)
  		    	   ->setOrderBy(static::ID);

		return $pagination;
	}


	/**
		Create a iterator for registers
	*/
	public function newCollection(array $registers,  array $defaults = []) {
	        return new \Team\Db\Collection($registers , get_class($this), $defaults );
	}


	/* ------------------ getters and setters  ___________________ */

	protected function loadData(array $data = []) {
		if(!empty($data) ) {
			$this->set($data);
		}
	}

	public function getListUrl() {
		return $this->listUrl;
	}


	/* ------------------ QUERIES ___________________ */


	/**
		Count all rows in table 
	*/
	public function countAll(array $sentences = [], array $data = []) {
        $query =  $this->newQuery($data, $sentences );
		return $query->getVar('total', static::TABLE, 'count('.static::ID.') as total');
	}


	/**
		Retrieve all rows from table TABLE 
		@param array $sentences list of params to query. Excepcionally, you can pass a 'order' params(ASC or DESC)
		@param array $data   list of data to query
	*/
    public function findAll( array $sentences = [], array $data = [], $result_type = null) {
		$sentences = $sentences?? [];

		$order = 'DESC';
		if(isset($sentences['order']) ) {
			$order = $sentences['order'];
			unset($sentences['order']);
		}

		$default = ['select'=> '*', 'limit' => -1, 'order_by' =>  [static::ID  =>  $order] ];

		$sentences = $sentences + $default;

        $query =  $this->newQuery($data, $sentences );

        $records = $query->getAll(static::TABLE);

        if(is_string($result_type) && "array" == $result_type) {
            return $records;
        }

        if(!isset($result_type)) {
            $result_type = get_class($this);
        }

        return new \Team\Db\Collection($records , $result_type);
    }


	/* ----------------- EVENTS ----------------- */


	//This function from Collection for everytime a newRecord is created
	function onNewRecord(array $data = []){
		$this->loadData($data);
    }

}
