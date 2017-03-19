<?php 

namespace team\start\url;

if(!defined("_SITE_") ) die("Hello,  World");



/**
 Un framework debería de proporcionar un sistema de parseo de url propio y dando la opción de reemplazarlo.
 Aquí se encuentra el código de parseo de url de TEAM. 

*/
\team\Task::join('\team\parse_url', function($args, $url, $package) {
    $args->response = \team\Check::key($args->response, null);


    $new_url_path_list = $args->url_path_list;


    //Si no hay url que proccesar, obvio que nos saltamos el proceso de parseo,
    if(!empty($args->url_path_list) ) {
        $url_path_list = $args->url_path_list;


		/**
			El primer subpath númerico sera el id, a no ser que ya haya uno, entonces se sale. 
			El primer subpath no numérico será el component si no se había añadido
			El segundo subpath no numérico será el response si no se había añadido y se sale. 
			Si se llega a un elemento que no es ninguno de los anteriores, se supone que es parte de la url parseable por el programador
			por lo que se vuelve a poner y se sale.
		*/
		$filters_list = [];
        $numeric_filters_list = [];
        $char_filters_list = [];
		$new_url_path_list = [];
		while(!empty($url_path_list) ) {
			$subpath = array_shift($url_path_list);
			if(is_numeric($subpath) ) {
                $subpath = \team\Check::id($subpath, 0);

                if(!isset($args->id) ) {
					$args->id = $subpath;
                    $new_url_path_list[] = $subpath;
                }else {
				    $filters_list[] = $subpath;
                    $numeric_filters_list[] = $subpath;
                }
			}else {
			    $subpath = \team\Check::key($subpath, null);

				if( strlen($subpath) >= 3 && ( !$args->component || !$args->response ) ) {
					if(!$args->component ) {
						$args->component =  $subpath;
					}else {
						$args->response =  $subpath;
					}
				}else  {
                      $filters_list[] = $subpath;
                       $char_filters_list[] = $subpath;

                    continue;
                }

                $new_url_path_list[] = $subpath;
			}
		}


		if(!$args->id && !empty($args->item_id) ) {
			$args->id = $args->item_id;
		}


		$args->id = \team\Check::id($args->id);
        $args->filters_list =$filters_list;
        $args->numeric_filters_list = $numeric_filters_list;
        $args->char_filters_list = $char_filters_list;

        $args->_self_ = \team\Sanitize::trim( implode('/', $new_url_path_list), '/');
    }else {
        $args->filters_list = [];
        $args->_self_ = '/';
    }

     $args->url_path_list =  $new_url_path_list ;



    $this->finish();

    return $args;

});
