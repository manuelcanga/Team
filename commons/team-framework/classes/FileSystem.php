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
    const BYTE = 1;
    const KB_IN_BYTES = 1024 * self::BYTE;
    const MB_IN_BYTES = 1024 * self::KB_IN_BYTES;
    const GB_IN_BYTES = 1024 * self::MB_IN_BYTES;
    const TB_IN_BYTES = 1024 * self::GB_IN_BYTES;


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
    Comprueba si existe un archivo dentro de la ruta del servior y si es así,
    lo carga sólo una vez
    @param String $file archivo del que se quiere comprobar su existencia y si existe, cargar )
*/
    public static function ping($file, $base = _SITE_) {
        //\team\Debug::out("LOADING...".$file);
        if(self::exists($file, $base) ) {
             include_once($base.$file);
            return true;
        }
        return false;
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


    /**
     * Test if a give filesystem path is absolute.
     *
     * For example, '/foo/bar'
     *
     * @param string $path File path.
     * @return bool True if path is absolute, false is not absolute.
     */
    public static function isAbsolutePath( $path ) {
        /*
         * This is definitive if true but fails if $path does not exist or contains
         * a symbolic link.
         */
        if ( realpath($path) == $path )
            return true;

        if ( strlen($path) == 0 || $path[0] == '.' )
            return false;

        // A path starting with / or \ is absolute; anything else is relative.
        return ( $path[0] == '/' || $path[0] == '\\' );
    }


    /**
     * Join two filesystem paths together.
     *
     * For example, 'give me $path relative to $base'. If the $path is absolute,
     * then it the full path is returned.
     *
     * @param string $base Base path.
     * @param string $path Path relative to $base.
     * @return string The path with the base or absolute path.
     */
    function joinPath( $base, $path ) {
        if ( self::isAbsolutePath($path) )
            return $path;

        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Normalize a filesystem path.
     *
     * @param string $path Path to normalize.
     * @return string Normalized path.
     */
    function normalizePath( $path ) {
        $path = str_replace( '\\', '/', $path );

        return $path;
    }


    /**
     * Test if a given path is a stream URL
     *
     * @param string $path The resource path or URL.
     * @return bool True if the path is a stream URL.
     */
    function isStream( $path ) {
        $wrappers = stream_get_wrappers();
        $wrappers_re = '(' . join('|', $wrappers) . ')';

        return preg_match( "!^$wrappers_re://!", $path ) === 1;
    }

    /**
     * Recursive directory creation based on full path.
     *
     * Will attempt to set permissions on folders.
     *
     *
     * @param string $target Full path to attempt to create.
     * @return bool Whether the path was created. True if path already exists.
     */
    function mkdirRecursive( $target ) {
        $wrapper = null;

        // Strip the protocol.
        if ( self::isStream( $target ) ) {
            list( $wrapper, $target ) = explode( '://', $target, 2 );
        }

        // From php.net/mkdir user contributed notes.
        $target = str_replace( '//', '/', $target );

        // Put the wrapper back on the target.
        if ( $wrapper !== null ) {
            $target = $wrapper . '://' . $target;
        }

        /*
         * Safe mode fails with a trailing slash under certain PHP versions.
         * Use rtrim() instead of untrailingslashit to avoid formatting.php dependency.
         */
        $target = rtrim($target, '/');
        if ( empty($target) )
            $target = '/';

        if ( file_exists( $target ) )
            return @is_dir( $target );

        // We need to find the permissions of the parent folder that exists and inherit that.
        $target_parent = dirname( $target );
        while ( '.' != $target_parent && ! is_dir( $target_parent ) ) {
            $target_parent = dirname( $target_parent );
        }

        // Get the permission bits.
        if ( $stat = @stat( $target_parent ) ) {
            $dir_perms = $stat['mode'] & 0007777;
        } else {
            $dir_perms = 0777;
        }

        if ( @mkdir( $target, $dir_perms, true ) ) {

            /*
             * If a umask is set that modifies $dir_perms, we'll have to re-set
             * the $dir_perms correctly with chmod()
             */
            if ( $dir_perms != ( $dir_perms & ~umask() ) ) {
                $folder_parts = explode( '/', substr( $target, strlen( $target_parent ) + 1 ) );
                for ( $i = 1, $c = count( $folder_parts ); $i <= $c; $i++ ) {
                    @chmod( $target_parent . '/' . implode( '/', array_slice( $folder_parts, 0, $i ) ), $dir_perms );
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Determine if a directory is writable.
     *
     * @param string $path Path to check for write-ability.
     * @return bool Whether the path is writable.
     */
    function issWritable( $path ) {
        return @is_writable( $path );
    }
}
