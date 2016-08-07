<?php
namespace team\db;

require_once(_TEAM_.'/classes/db/DB.php');
trait Database {

    function getDatabase($name_new_conection = '', $connection_data = [] ) {
		$DB_class = \team\Filter::apply('\team\DB', '\team\db\DB', $name_new_conection, $connection_data);

        return new $DB_class($name_new_conection,$connection_data);
    }

    protected function newQuery($values = null, array $sentences = [],   $name_new_conection = '', $connection_data = [] ) {
        return new Query($values, $this->getDatabase($name_new_conection, $connection_data), $sentences);
    }
    

}
