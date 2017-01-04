<?php
namespace team\db;

require_once(_TEAM_.'/classes/db/DB.php');
trait Database {

    protected function getDatabase($name_new_conection = '') {
		$DB_class = \team\Filter::apply('\team\DB', '\team\db\DB', $name_new_conection);

        return new $DB_class($name_new_conection);
    }

    protected function newQuery($values = null, array $sentences = [],   $name_new_conection = '', &$database = null) {
        $database = $this->getDatabase($name_new_conection);
        return new Query($values, $database, $sentences);
    }
    

}
