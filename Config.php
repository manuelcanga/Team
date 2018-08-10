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

namespace Team;



/**
 * Clase para gestionar variables de configuracion
 *
 */
abstract class Config{
    use \Team\Data\Vars;

    protected static $vars = [];
    protected static $modifiers = [];
    protected static $sanitizers = [];
    protected static $constructors = [];


    public static function setup() {
        \Team::event('\team\setup', self::$vars);
    }

    public static function get(string $var_name, $default = null, $place = null) {
        return self::applyModifiers($var_name, self::$vars[$var_name]?? self::applyConstructors($var_name, $default), $place );
    }

    public static function set($var, $value = null) {
        $old_value = null;

        if(is_array($var)) {
            self::$vars =  $var + self::$vars;
        }else if(is_string($var)){
            $old_value = self::$vars[$var]?? $old_value;

            self::$vars[$var] = static::applySanitizers($var, $value);
        }

        return $old_value;
    }

    public static function addSanitizer($config_var, $function, int $order = 50){

        self::$sanitizers[$config_var] = self::$sanitizers[$config_var]?? [];

        //Vamos buscando un hueco libre para el sanitizer a partir del orden que pidió
        for($max_order = 100; isset(self::$sanitizers[$config_var][$order]) && $order < $max_order; $order++);

        //Lo almacemanos todo para luego poder usarlo
        self::$sanitizers[$config_var][$order] =  $function;

        return false;
    }


    protected static function applySanitizers($config_var, $value) {
        if(!isset(self::$sanitizers[$config_var])  ) return $value;

        $sanitizers =& self::$sanitizers[$config_var];

        ksort($sanitizers);

        foreach($sanitizers as $sanitizer) {
            if(!is_callable($config_var,  $syntax_only = true)) {
                \Team\Debug::me('You are adding a sanitizier to ' . $config_var . ' which isn\'t a callback');
                return false;
            }else {
                $value = $sanitizer($value);
            }
        }
        return $value;
    }


    public static function addConstructor($config_var, $function, int $order = 50){

        self::$constructors[$config_var] = self::$constructors[$config_var]?? [];

        //Vamos buscando un hueco libre para el constructor a partir del orden que pidió
        for($max_order = 100; isset(self::$constructors[$config_var][$order]) && $order < $max_order; $order++);

        //Lo almacemanos todo para luego poder usarlo
        self::$constructors[$config_var][$order] =  $function;

        return false;
    }


    protected static function applyConstructors($config_var, $default) {
        if(!isset(self::$constructors[$config_var])  ) return $default;

        $constructors =& self::$constructors[$config_var];

        ksort($constructors);

        $value = $default;
        foreach($constructors as $constructor) {
            if(!is_callable($config_var,  $syntax_only = true)) {
                \Team\Debug::me('You are adding a constructor to ' . $config_var . ' which isn\'t a callback');
                return false;
            }else {
                $value = $constructor($default);
            }
        }
        return $value;
    }

    public static function addModifier($config_var, $function, int $order = 50){

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
            if(!is_callable($config_var,  $syntax_only = true)) {
                \Team\Debug::me('You are adding a modifier to ' . $config_var . ' which isn\'t a callback');
                return false;
            }else {
                $value = $modifier($value, $place);
            }
        }
        return $value;
    }


}