<?php
/**
 * Developed by Manuel Canga ( dev@trasweb.net )
 * Date: 10/01/17
 * Time: 14:27
 */

namespace team;

/**
 *
 * Class DB
 * @package team
 */
class DB
{
    protected static $databases = [];

    protected static $main = 'main';


    public static function getConnection($conname =  null, $place = null) {
        return \team\Filter::apply('\team\db\conname', $conname?? self::$main, $place );

    }

    public static function get($new_conection_name = null, $place = null){
        $DB_class = \team\Filter::apply('\team\DB', '\team\db\DB', self::getConnection($new_conection_name, $place) );

        /** Las clases de base de datos también gestionan las conexiones. Así para  */
        return new $DB_class($new_conection_name);
    }


    public static function set(array $options) {
        self::add(self::getConnection(), $options);
    }

    public static function add($databaseid, array $options) {
        $defaults = [
            'user'      => 'my_user',
            'password'  => 'my_password',
            'name'      => 'my_db',
            'host'      => 'localhost',
            'port'      => '5432',
            'prefix'    => '',
            'charset'   => 'UTF8',
            'type'      => 'mysql',
            'options'   =>  [],
        ];


        self::$databases[$databaseid] = $options + $defaults;
    }

    public static function getConfig($conname = null) {
        $conname = self::getConnection($conname);

        $connection_data = self::$databases[$conname]?? [];

        return \team\Filter::apply('\team\db\\'.$conname, $connection_data, $conname );
    }
}