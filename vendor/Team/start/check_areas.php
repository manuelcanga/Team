<?php 

namespace Team\start\areas;

if(!defined('_TEAM_') ) die("Hello,  World");



/**
   Comprobamos si la url actual concuerda con algún área
   las áreas sirven asignar un subpath de url a un paquete
   ej: /panel  => 'private'
   Con el área anterior una url del tipo: /panel/noticias/listado, quedaría desglosado en:
   package: private, component: noticias, response: listado,  AREA: 'panel', _URL_ = '/panel'
*/        

\team\system\Task::join('\team\url', function(& $url) {

    /*
     * Es posible asignar una acción main por variable de configuración/constante. Eso sobreescribirá la acción main dependiente de la url
     */
    $main = getMainFromConfig($url);


    /*
     * Las areas son un mecanismos para crear zonas dentro del sitio web. Las zonas no son más que una asociación de una url base a un paquete o response.
     * Ejemplo: Podríamos tener una zona area_privada con url base ( /clientes ) asociado al paquete 'usuarios'. Así, para cualquier suburl de /clientes
     * se haría cargo el paquete usuarios
     */
    list($_area_, $area_params) = findCurrentArea( $url, $main);

    setConfigArea($_area_);

    \Team::event('\team\areas', $_area_,  $area_params, $main);

    $this->main = $main;
    $this->area_params = $area_params;

}, 45);


function getMainFromConfig(string $url) {
    $main =  \Team\data\Filter::apply('\team\url\main', \Team\Config::get('MAIN'), $url );
    \Team\Config::set('MAIN', $main);
    return $main;
}

function getAreasFromConfig() {
   return (array) \Team\Config::get('AREAS');
}


function findCurrentArea( & $url, &$main) {


    $areas = getAreasFromConfig();


    $_area_ = '/'; //current area path
    $area_params = []; //curren area params

    //Aunque haya $main hay que seguir, porque el nuevo main debe tener también area
    //y debería de ser la equivalente a la url que hubiera sido( y sino, siempre se puede adaptar por modificadores )
    if(empty($areas) ) {
            return [$_area_, $area_params];
    }



        //las áreas más largas tienen prioridad a la hora de comprobación con url
    //esto es así porque una base /noticias/enlaces es mas especifica(menos matchs) que /noticias
    $areas = sortAreas($areas);


    //Nos aseguramos que las comparaciones no coja en mitad de la cadena añadiendo un / a la url y a las áreas.
    //Ej:  base: /noticias/listado  url: /noticias/listado-autores, daría un match. Sin embargo, si trimeamos y añaddimos un /
    //Ej2:   base: noticias/listado url: noticias/listado-autores/  no daría match.
    $_url =  \Team\data\Sanitize::trim($url);

    $_main = null;
    if(isset($areas['/']) ) {
        $_main = $areas['/'];
    }

    foreach($areas as $_area =>  $_params) {
        $_area =  \Team\data\Sanitize::trim($_area);

        if(!substr_compare($_area, $_url, 0, strlen($_area) ) ) {

            $_main = $_params;

            //La base ya la hemos proccesado así que la quitamos de la url
            //Recuerda que substr empieza en 0, de ahí el +1
            $url = substr($url,strlen($_area));
            $url = \Team\data\Sanitize::ltrim($url);

            $_area_ = rtrim('/'.$_area, '/');
            break;
        }

    }


    // '/panel/' => ['panel:component:response', 'param1' => 'var1', 'param2' => 'var2'];
    if(is_array($_main) ) {
        $area_params  = $_main;
        $_main = array_shift($area_params);
    }

    if(!isset($main)) {
        $main = $_main;
    }

    $area =  \Team\data\Filter::apply('\team\area', $_area_, $area_params );

    return [$_area_, $area_params];
}


function sortAreas($areas) {
    $keys = array_map('strlen', array_keys($areas));
    array_multisort($keys, SORT_DESC, $areas);

    return $areas;
}


function setConfigArea(string $_area_) {
    $_area_ = trim($_area_, '/');

    $_area_ =  \Team\data\Filter::apply('\team\area', $_area_ );

    $area =  \Team\data\Sanitize::identifier($_area_);

    \Team\Config::set("AREA", $area); //Identificador area sin slash. Ej: cms
    \Team\Config::set("_AREA_", rtrim('/'.$_area_,'/') ); //path a area con slash( al principio y no al final. Ej: /cms


}