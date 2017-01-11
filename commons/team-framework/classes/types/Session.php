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

namespace team\types;
/** Seguridad antes que nada  */
ini_set('session.cookie_httponly'   , 1);
ini_set('session.use_cookies'       , 1);
ini_set('session.use_only_cookies',   1);

class Session  extends Base
{
    /** Identificador que se le dará a la cookie de sessión */
    const ID_COOKIE = \team\SITE_ID;


    /**
     * Preparamos el sistema de sesiones
     * y mantenemos activa la sesión si ya se había activado anteriormente.
     * Así ahorramos que se inicie sesión para un visitante que no haga falta( ej: bots )
     *
     */
    public function  __construct( $data = null, Array $_options = []) {

        $with_previous_session = isset($_COOKIE[self::ID_COOKIE]);
        $force_activation =  isset($_options['force']) && $_options['force'];

        if ( $with_previous_session || $force_activation ) {
            $this->activeSession($force_activation,  $data);
        }else{
            //Si está activa sólo necesitamos enganchar los datos de sessiona activo con los nuevos
            //si no estaba activa pero tampoco hay interes en activarlo, lo unico que tendremos es un almacen de datos temporal
            $this->data =&  self::session($data, $_options['overwrite']?? false);
        }

    }

    /**
     * Check if session is already active
     * @return bool
     */
    protected function isActive() {
        return PHP_SESSION_NONE != session_status();
    }

    /**
     * Esta función sirve para abstraernos de la variable sesión de PHP y del
     * id distintitivo usado por Team Framework.
     * Esto lo hacemos así porque puede pasar que haya librerías de tercero que quieran
     * usar $_SESSION y así nos quitamos de que nos pisen los datos.
     *
     * @return ref array devuelve el array que usaremos para almacenar los datos de sesión
     */
    protected function & session($defaults = [], $overwrite = false) {
        if(!isset($_SESSION[self::ID_COOKIE])) {
            $_SESSION[self::ID_COOKIE] = [];
        }

        if($overwrite) {
            $_SESSION[self::ID_COOKIE] = (array)$defaults;
        }else if(!empty($defaults)) {
           $_SESSION[self::ID_COOKIE] = (array)$defaults + (array)$_SESSION[self::ID_COOKIE];
        }

        return $_SESSION[self::ID_COOKIE];
    }

    /**
     * Iniciamos una session ( sólo si no se había activado anteriormente  ) o se especifico forzado
     *
     * @param boolean $forzar_activacion nos permite forzar el comienzo de una nueva sesión
     *
     */
     public function activeSession($force_activation = false, $defaults = []) {
		if( $this->isActive() && $force_activation) {
			$this->close();
		}

        if(!$this->isActive() || $force_activation  ) {
            session_name(self::ID_COOKIE);
            session_start();
        }

         $this->data = &  self::session($defaults);
     }

    public function close() {
        $this->data = array();
        session_unset();
        return session_destroy();
    }


    public function __destruct() {
        session_commit();
    }
}
