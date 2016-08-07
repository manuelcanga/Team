<?php
/**
New Licence bsd:
Copyright (c) <2014>, Manuel Jesus Canga Muñoz
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

/**
	Clase que proporciona metodos utiles para asuntos de seguridad
*/
namespace team;

if(!class_exists('\Security', false) ) {
	class_alias('\team\Security', 'Security', false);
}


class Security {


	/**
		Devuelve una sal generada aleatoriamente
	*/
	public static function getSalt() {		
		return uniqid(mt_rand().mt_rand(), true);
	}

	/**
		Devuelve un password aleatorio de la longitud querida
		@param int $length Longitud para el nuevo password
		@example \team\Security::getPassword() => devuelve jbjltmnlp ( password al azar )

		@return devuelve el password generado
	*/
	public static function getPassword($length = 10) {
		$passwd = '';
		for($i = 0; $i< $length; $i++) {
			$index = mt_rand ( 33 , 126 );
			$passwd .= chr($index);
		}
		return $passwd;
	}


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



}
?>
