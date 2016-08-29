<?php
/**
New Licence bsd:
Copyright (c) <2016>, Manuel Jesus Canga Muñoz
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

\team\Classes::load('\team\defaults\Apc', '/classes/defaults/Apc.php', _TEAM_);
class Cache {
	/** Current cache system */
    private static $current = null;

    /**
     * Preparamos el sistema de caché
     */
    static function __initialize() {
		if(isset(self::$current) ) return  ;

		  $cache_class = \team\Filter::apply('\team\Cache', '\team\defaults\Apc');



		  if(isset($cache_class) && class_exists($cache_class )  ) {
			 self::$current  = new  $cache_class();
		  }
    }


	//Borramos un elemento de la caché
	static function delete($key) {
		return self::$current->delete($key);
	}

	static function clear() {
		return self::$current->clear();
	}

	static  function save($key, $value, $time = 0) {
		return self::$current->save($key, $value, $time);
	}

	static  function overwrite($key, $value, $time = 0) {
		return self::$current->save($key, $value, $time);
	}


	static function exists($key) {
		return self::$current->exists($key);
	}

	static  function get($key) {	
		return self::$current->get($key);
	}

	static function debug($msg = null) {
		self::$current->debug($msg);
	}


     function  __call($func, $args) {
		return call_user_func_array([self::$current, $func],$args);
    }


    static function  __callStatic($func, $args) {
		return call_user_func_array([self::$current, $func],$args);
    }

}
