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


namespace team;

/*  **********************************************************************************
Sistema de modificación en cascada
Se diferencia de los awaiters, en que los awaiters gestionan eventos, mientras que los pipielines
son cadenas de modificación, es decir, transportan un Array, String o Data para que sea modificado.
Son parecido también a las tareas(Tasks) pero en estas lo que se pretende es aplicar una tarea de un tipo
sobre una serie de parámetros y obtener un dato.
@note:
/web/noticias/filter_name_seo => NamespaceFull / Pipeline
/web/noticias 						=> Namespace
filter_name_seo 					=> Pipename ( nombre de la cañería )
**********************************************************************************  */
class Filter {
	/* Almacen de todos los filtros */
	private static $filters = array();

    /*
        Mantiene  información sobre el ultimo filtrado
    */
    private static $last = ['name' => ''];


	/**
		Al empezar el filtro, se llamará en primer lugar a todas las $pipeline que concuerden con el pipeline 
		al completo: "/team/seo/seo_name" y en segundo lugar aquellos $pipeline que concuerden con el nombre de la cañeria: "seo_name"
		
		@param NamespaceFull $pipeline suele ser el nombre de la cañería y puede ser en formato namespace. Ej: "\\team\\seo\\seo_name"; 
		@param Namespace $filter es el callback al que se llamará.

		@example Filter::attach("/team/seo/seo_name", array(self, "mifiltro") );
	*/
	public static function add($pipeline, $filter, $order = 65, $idfilter = null ) {
	    
	    //Sólo aquellos llamables a priori podrán añadirse
	     if(!is_callable($filter,  $syntax_only = true)) {
             \team\Debug::me('You are adding a filter to pipeline '.$pipeline.' which isn\'t a callback');

             return false;
         }

        return self::addFilter($pipeline, ['func' => $filter], $order, $idfilter);
	}

    private static function addFilter($pipeline, $data, $order = 65, $idfilter = null){

        //Comprobamos que no exista ya un elemento con el mismo id, en el mismo pipeline
        if(false !== self::exists($pipeline, $idfilter)){
            return false; //ya existe
        }

        //Si no existe una tubería asociada, la creamos
        if(!isset( self::$filters[$pipeline])  ) {
            self::restore($pipeline);
        }

        //Vamos buscando un hueco libre para el filtro a partir del orden que pidió
        for($max_order = 100; isset(self::$filters[$pipeline][$order]) && $order < $max_order; $order++);

        $data['id'] =  $idfilter;

        //Lo almacemanos todo para luego poder usarlo
        self::$filters[$pipeline][$order] =  $data;

        return $idfilter;
    }



    public static function addReturn($pipeline, $return,  $idfilter = null, $order = 65) {
        return self::addFilter($pipeline, ['return' => $return, 'type'=>'return'], $order, $idfilter);
    }

    public static function addValue($pipeline, $value, $idfilter = null, $order = 65) {
        return self::addFilter($pipeline, ['value' => $value, 'type'=>'value' ],$order, $idfilter);
    }

    public static function addKeyValue($pipeline,  $key = null, $value, $idfilter = null, $order = 65) {
        return self::addFilter($pipeline, ['value' => $value, 'key'=>$key, 'type'=>'keyvalue' ],$order, $idfilter);
    }


    public static function restore($pipeline) {
        self::$filters[$pipeline] = [];
    }

    public static function remove($pipeline, $idfilter = -1) {
        $key =   self::exists($pipeline, $idfilter);

        //Si no se ha pasado un idfilter, comprobamos sólo que
        //exista un pipeline.
        if($key && -1 === $idfilter) {
            unset(self::$filters[$pipeline]);
            return true;
        }

        if(false !== $key) {
            unset(self::$filters[$pipeline][$key]);
            return true;
        }

        return false;
    }


    public static function exists($pipeline, $idfilter = -1) {
        if(!isset($idfilter)) return false;

        //Si no se ha pasado un idfilter, comprobamos sólo que
        //exista un pipeline.
        if(-1 === $idfilter) {
            return isset(self::$filters[$pipeline]);
        }

        if(isset(self::$filters[$pipeline])) {
            $pipe2id = array_column(self::$filters[$pipeline], 'id');
            if(isset($pipe2id) ) {
              return array_search($idfilter, $pipe2id, $strict = true);
            }
        }
        return false;

    }

    public static function &  get($pipeline, $idfilter) {
          $key =   self::exists($pipeline, $idfilter);
          if(false !== $key) {
              return self::$filters[$pipeline][$key];
          }

        return false;
    }

	/**
		El usuario cliente lanza el proceso de filtrado mediante esta función
		@param mixed|mixed[]|null $data El dato a procesar por la cadena de filtros de namespace $pipeline
		@example \team\Filter::apply("/team/strings/uppercase", "Team Framework");

		@return Devuelve $data procesado por los filtros. Ej anterior: _TEAM_ FRAMEWORK
	*/
	public static function apply($pipeline, $data = "", ...$args ) {
		//Transmitimos después por el nombre de pipe ( al pipeline general ). 
		//Ej: "/team/seo/seo_name" -- transmitimos a --> "/team/seo/seo_name"
		//Si no hay filtros que procesen la información devolvemos el dato tal cual

        $last = ['name' => $pipeline];

        if(!isset(self::$filters[$pipeline]) ) {
                self::$last = $last;
				return $data;
		}

        ksort(self::$filters[$pipeline]);

        $last['filters'] =  self::$filters[$pipeline];

		//Vamos recorriendo todos los filtros en la tuberia
		foreach(self::$filters[$pipeline] as $order => $pipe ) {

            if(isset($pipe['func'])) {
                $target = $pipe['func'];
                if(!is_callable($target)) {
                    self::$last['errors'] = ['order' => $order, 'pipe' => $pipe];
                    continue;
                }

                //Llamamos al filtro
                $data = $target( $data, ...$args );
            }else  if(('return' == $pipe['type'])) {
                $data = $pipe['return'];
            }else {
                if(isset($pipe['key'])) {
                    $data[$pipe['key']] = $pipe['value'];
                }else {
                    $data[] = $pipe['value'];
                }
            }
		}

        $last['result'] =  $data;

        self::$last = $last;

		return $data;
	}

    public static function valueFiltered() {
        return array_key_exists('result', self::$last);
    }

    public static function getLast() {
        return self::$last;
    }


    public static function debug() {
        \team\Debug::me(self::$filters);
    }
}
