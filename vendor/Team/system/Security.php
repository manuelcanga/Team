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
namespace Team\system;



class Security {

	/**
		Devuelve un token generado aleatoriamente
	    incluye valores  alfanuméricos( mayúsculas y minúsculas )

	    @param int $length Longitud para la nueva sal
	*/
	public static function getToken(int $length = 10) {
		$token_values = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTVWYXZ";
		$token_values = \Team\data\Filter::apply('\team\security\token_values', $token_values);

		$min_number = 0;
		$max_number = strlen($token_values) - 1;

		$token = '';
		for($i = 0; $i< $length; $i++) {
			$index = random_int( $min_number , $max_number );
			$token .= $token_values[$index];
		}
		return $token;
	}

	/**
		Devuelve una salt aleatoria de la longitud querida
		incluye valores alfanuméricos( mayúsculas y minúsculas ) y carácteres especiales
	 	Muy útil también para generar passwords

	    @param int $length Longitud para la nueva sal
		@example \Team\system\Security::getSalt() => devuelve jbjltmnlp ( salt al azar )

		@return devuelve la salt generada del tamaño especificado
	*/
	public static function getSalt(int $length = 32) {

		$min_char = ord('!');
		$max_char = ord('}');

		$salt = '';
		for($i = 0; $i< $length; $i++) {
			$index = random_int( $min_char, $max_char);
			$salt .= chr($index);
		}
		return $salt;
	}


	/**
	 * Devuelve un password semántico
	 *
	 * @param int $length longitud de la parte variable( no perteneciente a palabras )
	 * @return string
	 */
	public static function getPassword($length = 6) {
		$words = [
			'acro',
			'anti',
			'auto',
			'cycle',
			'kinesis',
			'less',
			'counter',
			'cosmo',
			'demo',
			'dynam',
			'extra',
			'hyper',
			'mega',
			'mania',
			'maxi',
			'maxi',
			'kilo',
			'milli',
			'multi',
			'ultra',
			'scope',
			'phone',
			'wise',
			'onomy',
			'ology',
			'osis',
			'zoo' ];

		$words = \Team\data\Filter::apply('\team\security\words', $words);

		$prefix = array_rand($words);
		$postfix = array_rand($words);

		$password = $words[$prefix].self::getToken($length).$words[$postfix];

		return $password;
	}

}