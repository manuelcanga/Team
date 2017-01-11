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

\team\Classes::load('\team\defaults\Apcu', '/classes/defaults/Apcu.php', _TEAM_);
class Cache {
	/** Current cache system */
    private static $current = null;

    /**
     * Preparamos el sistema de caché
     */
    public  static function __initialize() {
		if(isset(self::$current) ) return  ;

		  $cache_class = \team\Config::get('\team\Cache', '\team\defaults\Apcu');


		  if(isset($cache_class) && class_exists($cache_class )  ) {
			 self::$current  = new  $cache_class();
		  }
    }

    /**
     * Va verificando uno por uno todos los ids pasados y devuelve el primero de ellos
     * que sea valido como identificador de caché.
     *
     * @param  $ids una lista de identificadores de cachés
     * @return mixed|null|string
     *
     */
    public  static function checkIds(...$ids) {
      if(empty($ids)) return null;

      foreach($ids as $cacheid) {
          if(!is_string($cacheid)) continue;

          $new_id = \team\Sanitize::identifier($cacheid);
          $new_id = trim(trim($new_id,'_'));

          if(!empty($new_id)) return $new_id;
      }

        return null;
    }

    /**
     * Comprueba que time sea una duración de tiempo valida.
     * @param $time Es la duración de tiempo del cache. Esta duración puede ser::
     * - 0: el cache estará habilitado de forma indefinida
     * - [int]: número de segundos que durará.
     * - [string]: cadena con tiempo en forma humana: 3 hours, 1 week, 10 minutes, ...
     * - null: se toma por filtro el valor por defecto de tiempo
     * @param $cacheid: Es el identificador de caché sobre el que se aplicará la duración de tiempo( $time )
     * @return array|int|string
     */
    public  static function checkTime($time, $cacheid) {
		$time = \team\Date::strToTime($time);

        if(is_null($time)){
            return \team\Filter::apply('\team\cache\default_time', \team\Date::AN_HOUR, $cacheid);
        }

        return $time;
    }


	//Borramos un elemento de la caché
	public static function delete($cacheid) {
		return self::$current->delete($cacheid);
	}

    public  static function clear() {
		return self::$current->clear();
	}

    public  static  function save($cacheid, $value, $time = 0) {
		return self::$current->save($cacheid, $value, self::checkTime($time, $cacheid));
	}

    public  static  function overwrite($cacheid, $value, $time = 0) {
		return self::$current->overwrite($cacheid, $value, self::checkTime($time, $cacheid));
	}


    public  static function exists($cacheid) {
		return self::$current->exists($cacheid);
	}

    public  static  function get($cacheid, $default = null) {
        return \team\Filter::apply('\team\cache\\'.$cacheid, self::$current->get($cacheid, $default) );
    }

    public  static function debug($msg = null) {
		self::$current->debug($msg);
	}


     function  __call($func, $args) {
		return call_user_func_array([self::$current, $func],$args);
    }


    public  static function  __callStatic($func, $args) {
		return call_user_func_array([self::$current, $func],$args);
    }

}
