<?php 

namespace team\start\areas;

if(!defined("_SITE_") ) die("Hello,  World");



/**
   Comprobamos si la url actual concuerda con algún área
   las áreas sirven asignar un subpath de url a un paquete
   ej: /panel  => 'private'
   Con el área anterior una url del tipo: /panel/noticias/listado, quedaría desglosado en:
   package: private, component: noticias, response: listado,  AREA: 'panel', _URL_ = '/panel'
*/        

\team\Task::join('\team\url', function(& $url) {

    /*
     * Es posible asignar una acción main por variable de configuración/constante. Eso sobreescribirá la acción main dependiente de la url
     */
    $main = getMainFromConfig($url);

    list($_area_, $area_params) = findCurrentArea( $url, $main);


	$this->main = $main;
	$this->area_params = $area_params;

    setConfigArea($_area_);

}, 45);


function getMainFromConfig(string $url) {
    $main =  \team\Filter::apply('\team\url\main', \team\Config::get('MAIN'), $url );
    \team\Config::set('MAIN', $main);
    return $main;
}

function getAreasFromConfig() {
   return (array) \team\Config::get('AREAS');
}


function findCurrentArea( & $url, &$main) {


    /*
 * Las areas son un mecanismos para crear zonas dentro del sitio web. Las zonas no son más que una asociación de una url base a un paquete o response.
 * Ejemplo: Podríamos tener una zona area_privada con url base ( /clientes ) asociado al paquete 'usuarios'. Así, para cualquier suburl de /clientes
 * se haría cargo el paquete usuarios
 */
    $areas = getAreasFromConfig();
    $_area_ = '/'; //current area path
    $area_params = []; //curren area params

    if(empty($areas) || isset($main) ) {
            return [$_area_, $area_params];
    }



        //las áreas más largas tienen prioridad a la hora de comprobación con url
    //esto es así porque una base /noticias/enlaces es mas especifica(menos matchs) que /noticias
    sortAreas($areas);
    //Nos aseguramos que las comparaciones no coja en mitad de la cadena añadiendo un / a la url y a las áreas.
    //Ej:  base: /noticias/listado  url: /noticias/listado-autores, daría un match. Sin embargo, si trimeamos y añaddimos un /
    //Ej2:   base: noticias/listado url: noticias/listado-autores/  no daría match.
    $_url =  \team\Sanitize::trim($url);

    if(isset($areas['/']) ) {
        $main = $areas['/'];
    }

    foreach($areas as $_area =>  $_params) {
        $_area =  \team\Sanitize::trim($_area);

        if(!substr_compare($_area, $_url, 0, strlen($_area) ) ) {

            $main = $_params;

            //La base ya la hemos proccesado así que la quitamos de la url
            //Recuerda que substr empieza en 0, de ahí el +1
            $url = substr($url,strlen($_area));
            $url = \team\Sanitize::ltrim($url);

            $_area_ = rtrim('/'.$_area, '/');
            break;
        }

    }


    // '/panel/' => ['panel:component:response', 'param1' => 'var1', 'param2' => 'var2'];
    if(is_array($main) ) {
        $area_params  = $main;
        $main = array_shift($area_params);
    }else { //'/panel/' => 'panel:component:response',
        $main = $main;
    }

    return [$_area_, $area_params];
}


function sortAreas($areas) {
    $keys = array_map('strlen', array_keys($areas));
    array_multisort($keys, SORT_DESC, $areas);

    return $areas;
}


function setConfigArea(string $_area_) {
    \team\Config::set("AREA",  \team\Sanitize::identifier(trim($_area_, '/')) ); //Identificador area sin slash. Ej: cms
    \team\Config::set("_AREA_", \team\Sanitize::trim($_area_, '/') ); //path a area con slash( al principio y al final. Ej: /cms/
}