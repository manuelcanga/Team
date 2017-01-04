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

	global $_CONTEXT;

   $main = $_CONTEXT['MAIN'];

	$areas = \team\Config::get('AREAS');
	$_area_ = '/';
	$area_params = [];
	if(!empty($areas) && !isset($main) ) {
		//las áreas más largas tienen prioridad a la hora de comprobación con url
		//esto es así porque una base /noticias/enlaces es mas especifica(menos matchs) que /noticias
		$keys = array_map('strlen', array_keys($areas));
		array_multisort($keys, SORT_DESC, $areas);

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
	 }
	  
	  

	$this->main = $main;
	$this->area_params = $area_params;
	
	$_CONTEXT["AREA"] = \team\Sanitize::identifier(trim($_area_, '/'));
	$_CONTEXT["_AREA_"] = \team\Sanitize::trim($_area_, '/'); 

}, 45);

