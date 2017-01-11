<?php
/**
New Licence bsd:
Copyright (c) <2012>, Manuel Jesus Canga Muñoz
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

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
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

namespace team;


//Clase para gestionar variables de configuracion
abstract class Config{
    protected static $vars = [];
    protected static $modifiers = [];

    public static function setUp() {
        \Team::event('\team\setup', self::$vars);
    }

    public static function replace($var, $value = null) {
        self::$vars[$var] = $value;
    }

    public static function set($var, $value = null) {
        if(is_array($var)) {
            self::$vars =  $var + self::$vars;
        }else if(is_string($var)){
            self::$vars[$var] = $value;
        }
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

    public static function get(string $var_name, $default = null, $place = null) {
        return self::applyModifiers($var_name, self::$vars[$var_name]?? $default, $place );
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


    public static function debug() {
        \team\Debug::me(self::$vars);
    }


    public static function addModifier($config_var, $function, int $order = 50){

        if(!is_callable($config_var,  $syntax_only = true)) {
            \team\Debug::me('You are adding a modifier to ' . $config_var . ' which isn\'t a callback');
            return false;
        }

        self::$modifiers[$config_var] = self::$modifiers[$config_var]?? [];

        //Vamos buscando un hueco libre para el modificador a partir del orden que pidió
        for($max_order = 100; isset(self::$modifiers[$config_var][$order]) && $order < $max_order; $order++);

        //Lo almacemanos todo para luego poder usarlo
        self::$modifiers[$config_var][$order] =  $function;

        return false;
    }

    protected static function applyModifiers($config_var, $value, $place) {
        if(!isset(self::$modifiers[$config_var])  ) return $value;

        $modifiers =& self::$modifiers[$config_var];

        ksort($modifiers);

        foreach($modifiers as $modifier) {
            $value = $modifier($value, $place);
        }
        return $value;
    }
}