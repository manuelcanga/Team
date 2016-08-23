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


if(!class_exists('\FileSystem', false) ) {
	class_alias('\team\FileSystem', 'FileSystem', false);
}


/** **************************************************************************************
	Funciones útiles para el trabajo a bajo nivel con el sistema de archivo
*************************************************************************************** */
final  class Filesystem
{


	/**
		Comprobamos si existe un archivo dentro de la ruta del sitio web
		@param String $file archivo que se quiere comprobar su existencia
		@return booleean si existe(true) o no existe(false)
	*/
	public static function exists($file, $base = _SITE_) {
		return file_exists($base.$file);
	}

	/*
		Comprueba si existe un archivo dentro de la ruta del servior y si es así,
		lo carga
		@param String $file archivo del que se quiere comprobar su existencia y si existe, cargar )
	*/
	public static function load($file, $base = _SITE_) {
		//\team\Debug::out("LOADING...".$file);
		if(self::exists($file, $base) ) {
			return include($base.$file);
		}
	}

	/*
		Comprueba si existe un archivo script dentro de la ruta del servior y si es así,
		lo incluye pasándole todos los argumentos
		@param String $file archivo del que se quiere comprobar su existencia y si existe, incluir )
	*/
	public static function script($___file, $___args = [], $___base = _SITE_) {
		//\team\Debug::out("LOADING...".$file);
		if(self::exists($___file, $___base) ) {
            extract($___args, EXTR_SKIP);
		            
			return include($___base.$___file);
		}
	}
	
	
	/**
		Comprobamos si existe un archivo, independientemente de la extension, dentro de la ruta del sitio web
		@param String $file archivo que se quiere comprobar su existencia sin extension
		@return booleean si existe(true) o no existe(false)
	*/
	public static function filename($file, $base = _SITE_) {
		$exists = glob($base.$file.'.*');
		return !empty($exists); 
	}

	/*
		Elimina una extensión de un archivo y le añade $new_extension si se le especifica
		@param string $_file: nombre del archivo al que se le quiere quiar la extensión
		@param string $_new_extension: nueva extensión, prefijada por punto, que se quiera añadir.
		@example \team\FileSystem::stripExtension('mivista.tpl'); //mivista
		@example \team\FileSystem::stripExtension('styles.min.css', '.css'); //style.css
	*/
	public static function stripExtension($_file, $_new_extension = '') {
		return preg_replace('/[\.].*/','', $_file).$_new_extension;
	}


	/**
		Obtiene el nombre de un archivo( sin ruta y sin extensión ).
		@param string $_file archivo del que se extraerá el nombre
	*/
	public static function basename($_file) {
		return self::stripExtension(basename($_file) );
	}


	/**
		Devuelve un array con los directorios que hay en una ruta dada
		@param $_dir es la ruta de un directorio a partir del que se va a obtener el listado
		@return array de directorios encontrados
	*/
	public static function getDirs($_dir = '/', $cache=true, $path = _SITE_)
	{
		$dir = rtrim($_dir, '//');
		static $cache = [];
		$location = $path.$dir;

		//Si tenemos los directorios en "caché", devolvemos estos
		if(isset($cache[$location]) && $cache) {
			return $cache[$location];
		}

		//No los teniamos en cache, hemos de ir reiterando para comprobar cuales de los archivos bajo la ubicación pedida
		//son directorios y los devolvemos. 
		//No consideramos los directorios ocultos( empiezan por . ), los que tengan un _ ( suele ser una manera de "desactivarlos" temporalmente )
		//y commons( por ser uno del sistema ). 
		$dirs = null;
		if(file_exists($location) )
		{
			$dhandle = opendir($location);	 //open workdir
			$dirs = array();		//arrays for saving directories.
			if($dhandle) {
				while(false !== ($fname = readdir($dhandle) ) ) {	//loop de archivos.

					//No consideraremos directorios validos los que
					//empiecen por "." o por "_" . Tampoco los llamados commons
					if( '.' == $fname[0] || '_' == $fname[0] || 'commons' == $fname  ) continue;

					//Si es un directorio, como esto es un listado de directorio lo agregamos a la lista
					if(is_dir($location.'/'.$fname ) ) array_push($dirs, $fname);
				}

				closedir($dhandle);
			}
			asort($dirs);
			$cache[$location] = $dirs;
		}
		return $dirs;
	}


    /**
        Hacemos notificación de algo ocurrido por sistema de archivos.
        Recordad que el nombre del evento es ucfirst. Ej: Initialize
     */
    public static  function notify($path, $eventname, $subpath= null,  $dirs_filter = null, $base = _SITE_) {

        $dirs =  self::getDirs($path, $cache=true, $base);


        if(!isset($subpath) ) {
            $subpath = '/events/';
        }

        if(isset($dirs_filter)) {
            $dirs = \team\Filter::apply($dirs_filter, $dirs);
        }

        if(!empty($dirs) ){
            $path = rtrim($path, '/');
            foreach($dirs as $dir) {
                //Cargamos el archivo de configuración
                self::load("{$path}/{$dir}{$subpath}{$eventname}.php", $base);
            }
        }

        return $dirs;
    }


    public static function toUnits($size) {
		$units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
		$power = $size > 0 ? floor(log($size, 1024)) : 0;
		return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
	}

	public static function getSize($file, $base = _SITE_) {
		return filesize($base.$file);
	}


}
