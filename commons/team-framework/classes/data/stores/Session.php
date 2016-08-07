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

namespace team\data\stores;

/** Seguridad antes que nada  */
ini_set('session.cookie_httponly', 1 );
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies',1);

class Session  implements \team\interfaces\data\Store 
{
    /** Identificador que se le dará a la cookie de sessión */
    const ID_COOKIE = \team\SITE_ID;

    /**
     * Mantiene los datos de la sesion
     * Entre esta variable estática y la función self::checkSession()
     * nos abstraemos totalmente de la variable de sesión de PHP
     */
    private $session = array();

	private $name = null;


	function setName($name = null) {
		if(isset($name) ) 
			$this->name = $name;
	}

    /**
     * Esta función sirve para abstraernos de la variable sesión de PHP y del
     * id distintitivo usado por Team Framework.
     * Esto lo hacemos así porque puede pasar que haya librerías de tercero que quieran
     * usar $_SESSION y así nos quitamos de que nos pisen los datos.
     *
     * @return ref array devuelve el array que usaremos para almacenar los datos de sesión
     */
    function & session($name = null, $default = []) {
		$this->setName($name);

        if( !isset($_SESSION[self::ID_COOKIE])) {
            $_SESSION[self::ID_COOKIE] = [];
        }

        if( !isset($_SESSION[self::ID_COOKIE][$this->name])) {
            $_SESSION[self::ID_COOKIE][$this->name] = $default;
        }

        return $_SESSION[self::ID_COOKIE][$this->name];
    }


    /**
     * Preparamos el sistema de sesiones
     * y mantenemos activa la sesión si ya se había activado anteriormente.
     * Así ahorramos que se inicie sesión para un visitante que no haga falta( ej: bots )
     *
     */
	 function & import( $_origin, Array $_options = [], Array $_default = []) {
         $with_previous_session = isset($_COOKIE[self::ID_COOKIE]);
         $force_activation =  isset($_options['force']) && $_options['force'];

         if ($with_previous_session || $force_activation) {
             $this->activeSession($force_activation,  $_default,  $_origin);
         }else{
             $this->session =&  self::session( $_origin, $_default) ;
         }

         return $this->session;
     }


    /**
     * Iniciamos una session ( sólo si no se había activado anteriormente  ) o se especifico forzado
     *
     * @param boolean $forzar_activacion nos permite forzar el comienzo de una nueva sesión
     *
     */
     function activeSession($force_activation = false, $default = [],  $name = null) {
        $session_not_initialized = PHP_SESSION_NONE == session_status();

		if($session_not_initialized && $force_activation) {
			$this->close();
		}

        if($session_not_initialized || $force_activation) {
            session_name(self::ID_COOKIE);
            session_start();
            $this->session = &  self::session($name, $default);
        }
    }

    function close() {
        $this->session = array();
        session_unset();
        return session_destroy();
    }

	function debuglolo() {
		\team\Debug::out($this->data);
	}


    function export($_target, Array $_data = [], Array $_options = [] ) {
        $session =& $this->session($_target);

        $session = $_data;

        return $session = $_data;
    }
}
