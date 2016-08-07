<?php
namespace team\db;

use \team\data\Storage;


/**
	Simple  Iterator for Models( y ActiveRecords )
*/
class Collection implements \Iterator, \Countable{
    protected $model = null;
	protected $records = null;
	private $index = 0;
	private $defaults = [];

	/**
		@param array $records elementos sobre los que se va a iterar
		@param string|Model clase que se usará como base para los registros
		@param array $defaults valores que se usaran por defecto a los valores del Record
	*/
    function __construct(array $records = [], $model = null, array $defaults = []) {
		$this->records = $records;
		$this->defaults = $defaults;

		if(is_object($model) ) {
			$this->model = get_class($model);
		}else {
			$this->model = $model;
		}
    }

	/** Create a new Record */
	function newModel($safeId = 0, $data = []) {
	 	 $class =  $this->model;
		 $activeRecord =  new $class($safeId, $data + $this->defaults, false);
 		 $activeRecord->onNewRecord();
		 return $activeRecord;
	}

	/** Check if iterator is empty */
	function __isset($index = 0) {
		return !empty($this->records) && isset($this->records[$index]);
	}

	/** Counts of records */
    function count() {
        return count($this->records);
    }

	/** Check if iterator is empty */
	function isEmpty() {
		return !$this->__isset();
	}

	/* extract first record */
	function shift() {
		$record = array_shift($this->records);

		return $this->toModel($record);
	}

	/**
		get first record
	*/
    function first() {
        $record =  $this->records[0];
        return $this->toModel($record);
    }


	/* extract last record */
	function pop() {
		$record = array_pop($this->records);

		return $this->toModel($record);
	}

	/**
		get last record
	*/
    function last() {
        $record = end($this->records);
        return $this->toModel($record);
    }


	/***** Iterator implements ******/
	function toModel($data) {
		$class = $this->model;

		$fieldID = $class::ID;
		$safeId = 0;

		if(isset($data[$fieldID]) ) {
			$safeId = $data[$fieldID];
		}

		return $this->newModel($safeId, $data);
	}



    public function recordOf($index =  null) {
        if(!isset($index) ) {
            $index = $this->index;     
		}
       
        if(isset($this->records[$index]) ) {
            $record =  $this->records[$index];
			return $this->toModel($record);
        }

        return false;
    }


    
    public function rewind(){
        $this->index = 0;
    }
    
    public function current(){
        return $this->recordOf($this->index);
    }
    
    public function key(){        
        $current = $this->current();
        return $current->getId();
    }
    
    public function next(){
        $this->index++;
        return  $this->current();
    }
    
    public function valid(){ 
        return (bool) $this->current();
    }

} 

