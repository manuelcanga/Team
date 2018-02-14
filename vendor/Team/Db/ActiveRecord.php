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
Simple ActiveRecord class for Team Framework
 */
abstract class ActiveRecord  implements \ArrayAccess{
    use \Team\Data\Storage, \Team\Db\Database;

    const DETAILS_URL = '';
    const ID = '';
    const TABLE = '';

    protected $safeId = 0;

    /**
    Construct a ActiveRecord
    @param mixed $id :  primary key or key used in order to initialize
    @param array $data : data for initializing
     */
    function __construct($id = 0,  array $data = []) {
        $this->setSafeId($id);

        if( $this->safeId) {
            $this->initializeIt($this->safeId);
        }

        $this->onImport($data, $id);
    }


    /* ----------------- Checks----------------- */

    /**
    Validamos el campo clave ID del activerecord
    @param $id es el valor a usar como campo clave
     */
    function checkId($id, $default = 0) {
        return \Team\Data\Check::key($id, $default);
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

    function setSafeId($newId) {
        $this->safeId = $this->checkId($newId, 0);
    }

    public function getGeneratedUrl($data = null, &$matches = null) {
        if(!isset($data) ) $data = $this->data;

        return \Team\Client\Url::to(self::DETAILS_URL, $data, $matches);
    }

    protected function loadData(array $data = []) {
        if(!empty($data) ) {
            $this->set($data);
            $this->safeId = $this[static::ID];
        }

        $this->onUnserialize();
    }

    /* ----------------- Results----------------- */
    public function pagination(int $elements_for_page = 10, $current_page = 1 ,string $base_url = null) {
        $pagination = new \Team\Gui\Pagination($elements_for_page, $current_page,  $this->data);

        $pagination->setModel($this);

        if(isset($base_url) ) {
            $pagination->setBaseUrl($base_url);
        }

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


    /* ----------------- QUERIES ----------------- */


    /**
    Initialize by default
     */
    protected function initializeIt($id) {

        $query = $this->newQuery([static::ID =>  $id]);
        $query->where[] = [ static::ID  =>  ':'.static::ID  ];
        $record = $query->getRow(static::TABLE);

        $this->onInitialize($id, (array) $record);
    }


    public function save( ) {

        if($this->safeId ) {
            $result =  $this->updateIt($secure = true);
        }else {
            $result = $this->insertIt();
        }

        return $result;
    }

    public function updateIt($secure = true) {
        $this->onSerialize('update');
        $this->commons('update');

        $this->data[static::ID] = $this->safeId;

        $query = $this->newQuery($this->data);
        $query->where[] = [ static::ID  =>  ':'.static::ID  ];

        $result =  $query->update(static::TABLE, $secure);

        if($result) {
            $this->custom("update");
        }

        return $result;
    }


    public function insertIt() {
        $this->onSerialize('insert');
        $this->commons('insert');

        if(!isset($this[static::ID]) ) {
            $this[static::ID] = null;
        }

        $query = $this->newQuery($this->data );
        $newId = $query->add(static::TABLE);

        if($newId) {
            $this->setSafeId($newId);

            $this->custom('insert');
        }

        return $newId;
    }

    public function serializeIt($field) {
        $this[$field] = '';

        if(is_array($this->$field)) {
            $this[$field] = json_encode($this->$field);
        }
    }


    public function unSerializeIt($field) {
        $this->$field = [];

        if(is_string($this[$field])) {

            $array_with_values = json_decode($this[$field], $assoc = true);

            if(json_last_error() == JSON_ERROR_NONE){
                $this->$field  = $array_with_values;
            }
        }
    }

    /**
    Realiza el borrado en la base de datos.
    Si $secure es true, no se podrá hacer un delete sin where y los delete con where estarán limitados a un elemento.
     */
    public function removeIt($secure = true) {
        if(!$this->safeId ) return false;

        $query = $this->newQuery([static::ID => $this->safeId] );

        $query->where[] = [ static::ID  =>  ':'.static::ID  ];

        $result =  $query->delete(static::TABLE, $secure);

        if($result){
            $this->custom('remove');
        }

        return $result;
    }

    /**
     * This function changes a value of database field in current record.
     * Be careful, this function does a eval with arguments
     *
     * @example  $this->changeIt('counter','+', 1)
     * This example add + 1 in counter field for current record
     *
     * @param $field
     * @param string $operation
     * @param int $amount
     * @return mixed
     */
    function changeIt($field, $operation = '+', $amount = 1) {
        if(! $this->safeId) return false;

        $query = $this->newQuery([static::ID => $this->safeId]);

        $query->$field  = "{$field} {$operation} {$amount}";

        $query->where = [ static::ID  =>  ':'.static::ID  ];

        $query_result =  $query->update(static::TABLE);

        if($query_result){
            $initial_amount = $this->$field;
            $result = 0;
            eval('$result  = '.$initial_amount.' '.$operation.' '.$amount.';');
            $this->$field = $result;
        }


        return $query_result;
    }



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
    public static function findAll( array $sentences = [], array $data = [], $result_type = null) {
        $sentences = $sentences?? [];

        $order = 'DESC';
        if(isset($sentences['order']) ) {
            $order = $sentences['order'];
            unset($sentences['order']);
        }

        $default = ['select'=> '*', 'limit' => -1, 'order_by' =>  [static::ID  =>  $order] ];

        $sentences = $sentences + $default;

        $query =  self::getNewQuery($data, $sentences );

        $records = $query->getAll(static::TABLE);

        if(is_string($result_type) && "array" == $result_type) {
            return $records;
        }

        if(!isset($result_type)) {
            $result_type = static::CLASS;
        }

        return new \Team\Db\Collection($records , $result_type);
    }


    /* ----------------- EVENTS ----------------- */
    protected function onInitialize($id, $data = []){
        $this->loadData($data);
    }


    //Before updating, creating register
    protected function commons($operation) { }

    //After updating, creating or removing  register
    protected function custom($operation){}

    protected function onSerialize($operation) { }
    protected function onUnserialize(){}

    /**
    Initialize by default
     */
    protected function onImport($data){
        if(is_array($data)) {
             $this->import($data);
        }
    }

    //This function from Collection for everytime a newRecord is created
    function onNewRecord(array $data = []){
        $this->loadData($data);
    }

} 
