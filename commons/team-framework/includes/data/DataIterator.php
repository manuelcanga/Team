<?php
namespace team\data;

trait DataIterator {
    protected $iteratorIndex = 0;
    
  
    public function keyOf($index =  null) {
        if(!isset($index) )
            $index = $this->iteratorIndex;
    
            $keys = array_keys($this->data);
            if(isset($keys[$index]) )
                return $keys[$index];
                else
                    return false;
    }
    
    public function rewind(){
        $this->iteratorIndex = 0;
    }
    
    public function current(){
        $key = $this->keyOf($this->iteratorIndex);
        return $this->get($key);
    }
    
    public function key(){
        return $this->keyOf($this->iteratorIndex);
    }
    
    public function next(){
        $this->iteratorIndex++;
    
        return $this->current();
    }
    
    public function valid(){
        return (bool) $this->key();
    }
    
}

