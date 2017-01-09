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
require_once(\_TEAM_.'/includes/data/Vars.php');
abstract class Config{
    use \team\data\Vars;

    private static $vars = [];
    private static $databases = [];


    public static function setDatabase(array $options, $databaseid = 'main') {
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

    public static function database($conname = 'main') {
        $connection_data = self::$databases[$conname]?? [];

        return \team\Filter::apply('\team\db\\'.$conname, $connection_data, $conname );
    }


    public static function get($var, $default = null) {
        return \team\Filter::apply('\team\configs\\'.$var, self::$vars[$var]?? $default );
    }


    public static function setUp() {
        \Team::event('\team\setup', self::$vars);
        \team\I18N::setUp();
    }


    public static function setMainPackage($package) {
        if(self::get('PACKAGE')) return ;

        \team\Config::set('PACKAGE', $package);
        \team\Config::set('_PACKAGE_', _SITE_.'/'.$package);
        \team\Config::set('BASE', '/'.$package);


        //Aquí ya sabemos el package del main, así que le mandamos un Start
        //Así pueden añadir filtros o tasks dependientes del package( por ejemplo, para parseos de urls dependiendo del paquete )
        \team\FileSystem::load("/{$package}/commons/config/Start.php");
    }
}