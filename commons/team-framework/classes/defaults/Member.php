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

namespace team\defaults;



class Member {
	use \team\data\Box;

	public function __construct() {
		$user_data = ['active' => 0, 'level' => \team\User::GUEST];
	
		$this->data = \team\Task('\team\member', function($data, $options) {
			   	$this->data = new \team\Data('Session', 'User', $options , $data); 
		})->with($user_data);
	}

	
	public function hasRole($role) {
        if(isset($this->data['roles'][$role]) || in_array($role, $this->data['roles']) ) {
            return true;
        }else {
            return false;
        }
	}


	function & get($var, $default = null) {
		if(isset($this->data[$var]) )
			return $var;
		else
			return $default;
	}

    function & set($field, $value) {
        return $this->data[$field] = $value;
    }

    function id() {
        return (int) $this->get('id', 0);
    }


    function level() {
        if($this->isAdmin() ) {
			return \team\User::ADMIN;
		}else if($this->isLogged() ) {
			return \team\User::USER;
		}

		return \team\User::GUEST;
    }

	function isGuest() {
		 return  !$this-> isLogged();
	}


	function isUser($strict = true) {
		 return  $this->isLogged() &&  !$this->isAdmin();
	}

	function isAdmin() {
		 return $this->isLogged() && $this->get('admin', false);
	}

    function isLogged() {
        return $this->get('active', false);
    }

    function notValidUser() {
		\Team::system('User not valid', '\team\user\notValid');
        exit();
    }



    /**
     * Permitimos que las variables de la sesión se puedan obtener o asignar como si fueran
     * métodos estáticos de esta clase. ej: Usuario::level() o Usuario::nombre('Manuel Canga');
     *
     * @param string $campo se refiere al nombre de la función llamada( o la variable de sessión a tratar )
     * @param array $argumentos ( el argumento 0 en caso de existir, será el valor de la variable de sesión
     * Si no existe es que sólo queremos obtener el valor de la variable.
     * @return mixed Retornamos el valor de la variable de sesión
     */
    static function & __callStatic($func, $args) {
        if(!empty($args)) {
            return  $this->set($func, $args[0]);
        }else {
            return $this->get($func);
        }
    }
	

    /* *************** Operaciones relacionadas con comienzo y finalización de sessions *************** */

	public function start($default = [], $force_activation = false, $name = null) {
		$this->activeSession($force_activation, $default, $name);
	}


    /**
     *  Función que se encarga de validar el usuario contra la base de datos.
     *  @param strng $correo_electronico es el email del usuario
     *  @param string $clave	es la clave que ha introducido el usuario (sin md5).
     */
     function login($email, $passwd, $others_data)
    {

		$this->data = \team\Task('\team\login', function($email, $passwd, $others_data) {

		    $passwd = trim($passwd);
		    $without_passwd = empty($passwd);

		    if($without_passwd)
		        return [];


		    $datos_usuario = \team\Filter::apply('\team\session\login', $email);
		    $no_encontrado_usuario = empty($datos_usuario);

		    if($no_encontrado_usuario) return [];

		    $clave_hasheada = md5($passwd);
		    $clave_valida = $datos_usuario['password'] === $clave_hasheada;
		    $usuario_puede_loguearse = \team\Check::id($datos_usuario['active'],0) > 0 ;

		    if(!$clave_valida || !$usuario_puede_loguearse)  return [];


		    return  $datos_usuario;
			
		})->with($email, $passwd, $others_data);


        return !empty($this->data);
    }



    /**
            Función que cierra la sessión del usuario activo
     */
    function logout()
    {
        if(!empty($this->data) ) {
			$this->data->close();
        }
    }



    /* *************** ÚTILES  *************** */
    function debug() {
        \team\team\Debug::me($this->data, '\team\user\Member');
    }
}
