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


namespace Team\data;

/*  **********************************************************************************
Sistema de modificación en cascada
Se diferencia de los Configs, y sus modifiers, en:
- Los Filters se realizan normalmente una vez en cada petición. Mientras que los modifiers se lanzan varias veces.
- Los Filters permite añadir varias variables de contexto. Mientras que los modifiers sólo permite una( $place )
- Los Filters facilita la modificacion de información recientemente creada. Los Config es para información asignada mucho antes de su uso.
- Los Config sirven como almacenamiento temporal de variables o variables de configuración.
- Los Config pueden ser usados en las vistas( Smarty ) como {#VAR_CONFIG#}

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
    al completo: "/Team/seo/seo_name" y en segundo lugar aquellos $pipeline que concuerden con el nombre de la cañeria: "seo_name"

    @param NamespaceFull $pipeline suele ser el nombre de la cañería y puede ser en formato namespace. Ej: "\\Team\\seo\\seo_name";
    @param Namespace $filter es el callback al que se llamará.

    @example Filter::attach("/Team/seo/seo_name", array(self, "mifiltro") );
     */
    public static function add($pipeline, $filter, int $order = 65 ) {

        //Sólo aquellos llamables a priori podrán añadirse
        if(!is_callable($filter,  $syntax_only = true)) {
            \Team\Debug::me('You are adding a filter to pipeline '.$pipeline.' which isn\'t a callback');

            return false;
        }

        //Si no existe una tubería asociada, la creamos
        if(!self::exists($pipeline)  ) {
            self::restore($pipeline);
        }

        //Vamos buscando un hueco libre para el filtro a partir del orden que pidió
        for($max_order = 100; isset(self::$filters[$pipeline][$order]) && $order < $max_order; $order++);

        //Lo almacemanos todo para luego poder usarlo
        self::$filters[$pipeline][$order] =  $filter;

        return true;

    }

    public static function restore($pipeline) {
        self::$filters[$pipeline] = [];
    }


    public static function exists($pipeline) {
        return isset(self::$filters[$pipeline]);
    }


    /**
    El usuario cliente lanza el proceso de filtrado mediante esta función
    @param mixed|mixed[]|null $data El dato a procesar por la cadena de filtros de namespace $pipeline
    @example \Team\data\Filter::apply("/Team/strings/uppercase", "Team Framework");

    @return Devuelve $data procesado por los filtros. Ej anterior: _TEAM_ FRAMEWORK
     */
    public static function apply($pipeline, $data = "", ...$args ) {
        //Transmitimos después por el nombre de pipe ( al pipeline general ).
        //Ej: "/Team/seo/seo_name" -- transmitimos a --> "/Team/seo/seo_name"
        //Si no hay filtros que procesen la información devolvemos el dato tal cual

        $last = ['name' => $pipeline, 'initial' => $data];

        if(!isset(self::$filters[$pipeline]) ) {
            self::$last = $last;
            return $data;
        }

        ksort(self::$filters[$pipeline]);

        $last['filters'] =  self::$filters[$pipeline];


        //Vamos recorriendo todos los filtros en la tuberia
        foreach(self::$filters[$pipeline] as $order => $target ) {
            if(!is_callable($target)) {
                self::$last['errors'] = ['order' => $order, 'pipe' => $pipeline];
                continue;
            }

            //Llamamos al filtro
            $data = $target( $data, ...$args );

        }

        $last['end'] =  $data;

        self::$last = $last;

        return $data;
    }

    public static function valueFiltered() {
        return array_key_exists('end', self::$last);
    }

    public static function getLast() {
        return self::$last;
    }


    public static function debug() {
        \Team\Debug::me(self::$filters);
    }
}
