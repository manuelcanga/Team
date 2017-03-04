<?php
namespace team\db;

require_once(_TEAM_.'/classes/db/DB.php');
trait Database {

    protected function getDatabase($name_new_conection = null) {
		return \team\DB::get($name_new_conection, get_class($this));
    }

    protected function newQuery($values = null, array $sentences = [],   $name_new_conection = null) {
        return new Query($values, $this->getDatabase($name_new_conection), $sentences);
    }
    
    static function getNewQuery($values = null, array $sentences = [],   $name_new_conection = null) {
        return new Query($values, \team\DB::get($name_new_conection, static::class), $sentences);
    }
}
