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



\team\loaders\Classes::load('\team\defaults\Member', '/classes/defaults/Member.php', _TEAM_);
class User {
    /** Definimos la visibilidad */
    const
          ROOT = 3  /** Access to private area and admin area with restricted access */,
          ADMIN = 2 /** Access to private area and admin area. This also is logged */,
          USER = 1  /** Access to private area. Meaning the same: active but not admin.  */,
		  GUEST = 0 /* Only access to public area.  This cannot logged */;

	/**
		logged when a user or admin login 
	*/

    private static $current = null;


    /**
     * Preparamos el sistema de sesiones
     * y mantenemos activa la sesión si ya se había activado anteriormente.
     * Así ahorramos que se inicie sesión para un visitante que no haga falta( ej: bots )
     *
     */
    public static function __initialize() {
		if(isset(self::$current) ) return  ;

      $user_class =\team\Context::get('\team\User', '\team\defaults\Member');

	  if(isset($user_class) && class_exists($user_class )  ) {
		 self::$current  = new  $user_class();
	  }
    }

    public function  __call($func, $args) {
		return call_user_func_array([self::$current, $func],$args);
    }


    public static function  __callStatic($func, $args) {
		return call_user_func_array([self::$current, $func],$args);
    }


    public static function getCurrent() {
		if(!isset( self:: $current) ) {
				self::__initialize();
		}

		return  self::$current;
	}

    public static function setCurrent($user) {
		self::$current = $user;
	}

    //

    //Métodos obligatorios:
    //notValidUser


    /** *************** Comprobaciones de seguridad  *************** */
    public static function mustBeRoot() {
        if(!self:: $current->isRoot() ){
            self:: $current->notValidUser();
        }
    }

    public static function mustBeAdmin() {
        if(!self:: $current->isAdmin() ){
            self:: $current->notValidUser();
        }
    }


    public  static function mustBeLogged() {
        if(!self:: $current->isLogged() ){
            self:: $current->notValidUser();
        }
    }



    /** *************** getters y setters  generales   *************** */
    public static function & set($field, $value) {
        return self::$current->set($field, $value);
    }

    public static function & get($field = 'level', $default = null)
    {
        return self::$current->get($field, $default);
    }


    public static function levels() {
        return ['All the Internet', 'Users who can login', 'Admins' ];
    }

	/* ***************** Helpers útiles **************** */

	/**
		Devuelve la ip del cliente que ha hecho la petición contra team-framework
	*/
	public static function getIP() {
		static $ip = null;

		if(isset($ip) ) return $ip;

       $sources = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR' ];

		foreach($sources as $source) {
			if(isset($_SERVER[$source]) && \team\Check::ip($_SERVER[$source]) ) {
				return $ip = $_SERVER[$source];
			}
		}
	}


    /**
     * Test if the current user( or device )  has the capability to upload files.
     *
     * @return bool Whether the device is able to upload files.
     */
    public static function canUpload() {
        if ( \team\Http::checkUserAgent('desktop') )
            return true;

        $ua = $_SERVER['HTTP_USER_AGENT'];

        if ( strpos($ua, 'iPhone') !== false
            || strpos($ua, 'iPad') !== false
            || strpos($ua, 'iPod') !== false ) {
            return preg_match( '#OS ([\d_]+) like Mac OS X#', $ua, $version ) && version_compare( $version[1], '6', '>=' );
        }

        return true;
    }

    public static function debug() {
        self::$current->debug();
    }
}

