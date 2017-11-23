<?php
/**
 * Developed by Manuel Canga ( dev@trasweb.net )
 * Date: 12/01/17
 * Time: 15:46
 */

namespace team\data;


trait Vars
{



    public static function replace($var, $value = null) {
        self::$vars[$var] = $value;
    }

    public static function set($var, $value = null) {
        $old_value = null;

        if(is_array($var)) {
            self::$vars =  $var + self::$vars;
        }else if(is_string($var)){
            $old_value = self::$vars[$var]?? $old_value;

            self::$vars[$var] = $value;
        }

        return $old_value;
    }

    public static function push($var, $value = null) {
        if(!isset(self::$vars[$var]) || is_array(self::$vars[$var])) {
            self::$vars[$var][] = $value;
        }
    }

    public static function add($var, $key, $value = null) {
        if(!isset(self::$vars[$var]) || is_array(self::$vars[$var])) {
            self::$vars[$var][$key] = $value;
        }
    }

    public static function unset($var, $key = null) {
        if(isset($key)) {
            if(isset(self::$vars[$var][$key])) {
                unset(self::$vars[$var][$key]);
                return true;
            }
            return false;
        }

        if(isset(self::$vars[$var])) {
            unset(self::$vars[$var]);
            return true;
        }

        return false;
    }


    public static function get($var, $default = null){
            return self::$vars[$var]?? $default;
    }

    public static function getKey(string $key, string $var_name, $default = null, $place = null) {
        $var =  self::get($var_name, $default, $place);
        return $var[$key]?? $default;
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


    public static function debug($str = '') {
        $backtrace = debug_backtrace();
        $file = $backtrace[0]['file'];
        $line = $backtrace[0]['line'];

        \team\Debug::me(self::$vars, $str.' log:', $file, $line);
    }

}