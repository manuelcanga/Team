<?php
/**
New Licence bsd:
Copyright (c) <2012>, Manuel Jesus Canga Muñoz
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


namespace team\data\stores;

/**
  Inicializa un Data a traves de un archivo de configuracion 
*/

class Config  implements \team\interfaces\data\Store 
{
	 private static $data = array();
	 
	 public function import($_namespace  = NULL, $_options = null) {
		//Si no se ha especficado ningun namespace salimos
		if(!$_namespace) return array();
		
		//Buscamos variables de configuracion en el namespace pasado ( y de niveles inferiores )
		$config_vars = self::getVars($_namespace);

		$info = \team\NS::explode($_namespace);

		//Añadimos los datos que faltan		
		$config_vars["NAMESPACE"] 			         = $_namespace;
		$config_vars["_SITE_"]	  		      	     = _SITE_;
  	    $config_vars["PACKAGE"]              		 = $info["package"];
  	    $config_vars["_PACKAGE_"]              		 = _SITE_.'/'.$info["package"];
		$config_vars["COMPONENT"]	  			     = $info["component"];
		$config_vars["BASE"]						 = '/'.$info["package"];

		 if(isset($info["component"])) {
  	    	$config_vars["BASE"]              	  = $config_vars["_PACKAGE_"].'/'.$info["component"];
  	    	$config_vars["BASE"]              		  = $config_vars["BASE"].'/'.$info["component"];
		 }else {
		     $config_vars["BASE"]           = "";
		 }


        //Lanzamos un evento de fin de importacion de configuracion
		$vars = $config_vars;

		$data = new \team\Data($config_vars);		
		$event = \Event('Import', '\team\config')->ocurred($data);
		if($event->attended()) {
		    $this->data =   $data->getData();
		}



		return  $this->data;
	}

	public function setData(& $data) {
		$this->data =& $data;
	}
	
	public function export( $_target, Array $_data = NULL) {}
	
	

 	/* 
		Vamos a ir buscando archivos de configuracion de acciones, desde el nivel actual hasta el más bajo, hasta encontrar uno
	*/
	function getVars($_namespace) {
		$desc_namespace = rtrim($_namespace, "\\");
		$vars = array();

		while($desc_namespace) {

			$class_name = \team\NS::basename($desc_namespace, "/");
			$path = \team\NS::shift($desc_namespace);
			$path = \team\NS::toPath($path);
			//Buscamos si existe el archivo de configuración

			if( \team\config\Factory::findConfig($class_name, $path ) ) {
				$vars = $desc_namespace."\\".$class_name::getVars();
				if(!empty($vars) ) break;
			}
			//Bajamos un nivel de namespace
			$desc_namespace = \team\NS::shift($desc_namespace);
		}

		//Se mira en common
		if(empty($vars) )
			$vars = \config\Team::getVars();

		//Se mira en Team
		if(empty($vars) )
			$vars = \config\team\Team::getVars();;

		return $vars;

	}

}
