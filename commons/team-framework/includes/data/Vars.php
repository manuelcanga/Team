<?php
/**
 * Developed by Manuel Canga ( dev@trasweb.net )
 * Date: 5/01/17
 * Time: 13:27
 */

namespace team\data;


trait Vars {
    private static $vars = [];


    public static function set($var, $value = null) {
        if(is_array($var)) {
            self::$vars =  $var + self::$vars;
        }else if(is_string($var)){
            self::$vars[$var] = $value;
        }
    }

    public static function push($var, $value = null) {
        if(isset(self::$vars[$var]) && is_array(self::$vars[$var])) {
            self::$vars[$var][] = $value;
        }
    }

    public static function add($var, $key, $value = null) {
        if(isset(self::$vars[$var]) && is_array(self::$vars[$var])) {
            self::$vars[$var][$key] = $value;
        }
    }

    public static function get($var, $default = null) {
        return self::$vars[$var]?? $default;
    }

    public static function getVars() {
        return self::$vars;
    }

    public static function defaults($vars) {
        self::$vars +=  $vars;
    }

    public static function exists($var) {
        return array_key_exists($var, self::$vars);
    }


    public static function debug() {
        \team\Debug::me(self::$vars);
    }


}