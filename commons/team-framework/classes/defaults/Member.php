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
	
		$this->data = \team\Task('\team\member', function($data) {
            return new \team\types\Session($data, []);
		})->with($user_data);

	}

	
	public function hasRole($role) {
        if(isset($this->data['roles'][$role]) || in_array($role, $this->data['roles']) ) {
            return true;
        }else {
            return false;
        }
	}


    public function & get($var, $default = null) {
		if(isset($this->data[$var]) )
			return $this->data[$var];
		else
			return $default;
	}

    public function & set($field, $value) {
        return $this->data[$field] = $value;
    }

    public function id() {
        return (int) $this->get('id', 0);
    }


    public function level() {
        if($this->isAdmin() ) {
			return \team\User::ADMIN;
		}else if($this->isLogged() ) {
			return \team\User::USER;
		}

		return \team\User::GUEST;
    }

    public function isGuest() {
		 return  !$this-> isLogged();
	}


    public function isUser() {
		 return  $this->isLogged() &&  !$this->isAdmin();
	}

    public function isAdmin() {
		 return $this->isLogged() && $this->get('admin', false);
	}

    public function isLogged() {
        return $this->get('active', false);
    }

    public  function notValidUser() {
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
    public static function & __callStatic($func, $args) {
        if(!empty($args)) {
            return  self::$current->set($func, $args[0]);
        }else {
            return self::$current->get($func);
        }
    }
	

    /* *************** Operaciones relacionadas con comienzo y finalización de sessions *************** */

    public function doStart($defaults = [], $force_activation = false) {
		$this->data->activeSession($force_activation, $defaults);
	}


    /**
     *  Función que se encarga de validar el usuario contra la base de datos.
     *  @param strng $correo_electronico es el email del usuario
     *  @param string $clave	es la clave que ha introducido el usuario (sin md5).
     */
    public function doLogin($email, $passwd, $others_data)
    {

		$data = \team\Task('\team\login', function($user, $passwd = null, $others_data = []) {

		    $passwd = trim($passwd);
		    $without_passwd = empty($passwd);

		    if($without_passwd)
		        return [];


		    $user_data = \team\Filter::apply('\team\session\login',[],  $user);
		    $user_not_found = empty($user_data);

		    if($user_not_found) return [];

		    $hash_passwd = md5($passwd);
		    $right_passwd = isset($user_data['password']) &&  $user_data['password'] === $hash_passwd;
            $right_passwd = \team\Filter::apply('\team\session\right_passwd', $right_passwd, $user_data,  $passwd, $others_data );

            if(!$right_passwd) return [];


            $user_can_login = \team\Check::id($user_data['active'],0) > 0 ;
            $user_can_login = \team\Filter::apply('\team\session\user_can_login', $user_can_login, $user_data, $others_data );

            if(!$user_can_login)  return [];


		    return  $user_data;
			
		})->with($email, $passwd, $others_data);

        $this->data = new \team\Session($data);

        return !empty($this->data);
    }



    /**
            Función que cierra la sessión del usuario activo
     */
    public function doLogout()  {
        if(!empty($this->data) ) {
			$this->data->close();
        }
    }



    /* *************** ÚTILES  *************** */
    public function debug() {
       \team\Debug::me($this->data, '\team\user\Member');
    }
}
