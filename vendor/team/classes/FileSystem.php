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
	public static function exists($file, $base = _SCRIPT_) {
	    if(!is_string($file) ||  '/' !== $file[0]) return false;

		return file_exists($base.$file);
	}

	/*
		Comprueba si existe un archivo dentro de la ruta del servior y si es así,
		lo carga
		@param String $file archivo del que se quiere comprobar su existencia y si existe, cargar )
	*/
	public static function load($file, $base = _SCRIPT_) {
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
    public static function ping($file, $base = _SCRIPT_) {
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
	public static function script($___file, $___args = [], $___base = _SCRIPT_) {
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
	public static function filename($file, $base = _SCRIPT_) {
		$exists = glob($base.$file.'.*');
		return !empty($exists); 
	}

	/**
		Obtiene el nombre de un archivo( sin ruta y sin extensión ).
		@param string $_file archivo del que se extraerá el nombre
	*/
	public static function basename($_file) {
		return self::stripExtension(basename($_file) );
	}

    /**
        Obtiene el nombre o ruta del archivo sin rutas relativas al principio
        @param string $_file archivo o ruta del que se quitará la ruta relativa del principio
        @example  ./noticias/prueba.tpl => noticias/prueba.tpl
     */
    public static function stripRelativePath($_file) {
        return ltrim($_file, './\\');
    }


    /*
        Elimina una extensión de un archivo y le añade $new_extension si se le especifica
        @param string $_file: nombre del archivo al que se le quiere quiar la extensión
        @param string $_new_extension: nueva extensión, prefijada por punto, que se quiera añadir.
        @example \team\FileSystem::stripExtension('mivista.tpl'); //mivista
        @example \team\FileSystem::stripExtension('styles.min.css', '.css'); //style.css
    */
    public static function stripExtension($_file, $_new_extension = '') {

        //Usanso ?! se evita que se borre a partir de directorios con punto. Ejemplo: /misitio.net/prueba.php
        //Pues se toma la última ocurrencia
        //resultado /misitio.net/prueba  sin usarlo quedaría como /misitio
        return  preg_replace('/[\.].*(?!.*[\.].*)/','', $_file).$_new_extension;
    }

    /**
     * Devuelve la extensión del archivo $file
     * @param $file
     */
	public static function getExtension($file) {
        return pathinfo($file, PATHINFO_EXTENSION);
    }

	/**
		Devuelve un array con los directorios que hay en una ruta dada
		@param $_dir es la ruta de un directorio a partir del que se va a obtener el listado
		@return array de directorios encontrados
	*/
	public static function getDirs($_dir = '/', $cache=true, $path = _SCRIPT_)
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
    public static  function notify($path, $eventname, $subpath= null,  $dirs_filter = null, $base = _SCRIPT_) {

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

	public static function getSize($file, $base = _SCRIPT_) {
		return filesize($base.$file);
	}

    /**
     * Sube un archivo enviado por formulario al sistema de archivo
     * @param $identifier
     * @param null $options
     * @return array|bool
     */
	public static function upload($identifier, $options = null) {
        if(!isset($_FILES[$identifier]) || empty($_FILES[$identifier]['name'])) {
            return false;
        }

        $file =& $_FILES[$identifier];

        if($file['error'] !=  UPLOAD_ERR_OK && $file['size']) {
            \Team::warning('Archivo no se pudo subir', 'ERROR_'.$file['error']);

            return false;
        }


        extract($file);
        $ext =  self::getExtension($name);
        $name = self::stripExtension($name);
        $type = self::ext2type($ext);


        if(!$ext) {
            \Team::warning('No se permiten archivos sin extension', 'ERROR_NO_EXTENSION');
            return false;
        }


        if(isset($options['allow']) && is_array($options['allow']) && !in_array($type, $options['allow'])) {
            \Team::warning('Archivo no se encuentra entre los permitidos', 'ERROR_ALLOW_'.$type);

            return false;
        }

        if(isset($options['disallow']) &&  is_array($options['disallow']) && in_array($type, $options['disallow'])) {
            \Team::warning('Archivo se encuentra entre los no permitidos', 'ERROR_DISALLOW_'.$type);

            return false;
        }

        $uploads_dir = $options['dir']?? \team\Context::get('UPLOADS_DIR', \team\Date::current('uploads_dir'));
        $uploads_path =  $options['path']?? \team\Context::get('UPLOADS_PATH', _TEMPORARY_);

        self::mkdirRecursive($uploads_path.$uploads_dir);

        if( isset($options['keep_name']) && $options['keep_name']) {
            $new_name = \team\Sanitize::identifier(\team\Sanitize::chars($name) );
        }else {
            $new_name = md5(\team\Date::current('timestamp').'_'.$tmp_name);
        }


        $starting_name = $new_name;
        $i = 2; //if file exists, is the second instance
        do {
            $file = $uploads_dir.'/'.$new_name.'.'.$ext;

            $file_exists = self::exists($file, $uploads_path);
            if($file_exists) {
                $new_name = $starting_name.'_'.$i;
                $i++;
            }

        }while($file_exists);

        if(!move_uploaded_file($tmp_name, $uploads_path.$file) ) {
            \Team::warning('Archivo no se pudo mover al destino', 'ERROR_MOVING');

            return false;
        }


        return ['file' => $file, 'name' => $new_name, 'size' => $size, 'ext' => $ext, 'type'=> $type, 'path' => $uploads_path, 'dir' =>$uploads_dir];

    }

    public static function rmUploaded($file, $path = null) {
       $UPLOADS_PATH = $path??  \team\Context::get('UPLOADS_PATH', _TEMPORARY_);

        return unlink($UPLOADS_PATH.$file);
    }

    public static function download($file, $name = null, $isUploaded = true) {

	    if($isUploaded) {
            $path = \team\Context::get('UPLOADS_PATH', _TEMPORARY_);
            $file = $path.$file;
        }

        if(!file_exists($file)) {
            \Team::warning("File not found");
            return false;
        }

        $filename = $name?: basename($file);
	    $extension = self::getExtension($filename);

	    $mimes = self::getMimeTypes();

        $content_type = $mimes[$extension]?? 'application/octet-stream';


        ob_clean();
        header('Content-Type:'.$content_type);
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $filename . "\"");
        readfile($file);

        die();
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
    public function joinPath( $base, $path ) {
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
    public function normalizePath( $path ) {
        $path = str_replace( '\\', '/', $path );

        return $path;
    }

    /**
    Obtiene la ruta absoluta(desde el raiz del proyecto ) de un recurso.
    @param path $suppath, es la ruta desde la raiz del component( si el rescurso esta en un component)
    o desde el paquete( si el recurso está en commons de un paquete )
    @param $component componente en el que se encuentra el recurso ( por defecto el actual )
    @param $package paquete dónde se encuentra el recurso ( por defecto el actual )
     */
    public static function getPath($subpath, $component = null, $package = null) {

        $subpath = trim($subpath, '/');
        $component = $component?? \team\Context::get('COMPONENT');
        $package = $package?? \team\Context::get('PACKAGE');

        if ('root' === $package || 'root' === $component) {
            return "commons/{$subpath}/";
        }

        return "{$package}/{$component}/{$subpath}/";
    }


        /**
     * Test if a given path is a stream URL
     *
     * @param string $path The resource path or URL.
     * @return bool True if the path is a stream URL.
     */
    public static  function isStream( $path ) {
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
    public static function mkdirRecursive( $target ) {
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
    public static function issWritable( $path ) {
        return @is_writable( $path );
    }


    /**
     * Retrieve the file type based on the extension name.
     *
     * @param string $ext The extension to search.
     * @return string|void The file type, example: audio, video, document, spreadsheet, etc.
     */
    public static function ext2type( $ext ) {
        $ext = strtolower( $ext );

        /**
         * Filter file type based on the extension name.
         *
         * @since 2.5.0
         *
         * @see wp_ext2type()
         *
         * @param array $ext2type Multi-dimensional array with extensions for a default set
         *                        of file types.
         */
        $ext2type = \team\Filter::apply( '\team\filesystem\ext2type', array(
            'image'       => array( 'jpg', 'jpeg', 'jpe',  'gif',  'png',  'bmp',   'tif',  'tiff', 'ico' ),
            'audio'       => array( 'aac', 'ac3',  'aif',  'aiff', 'm3a',  'm4a',   'm4b',  'mka',  'mp1',  'mp2',  'mp3', 'ogg', 'oga', 'ram', 'wav', 'wma' ),
            'video'       => array( '3g2',  '3gp', '3gpp', 'asf', 'avi',  'divx', 'dv',   'flv',  'm4v',   'mkv',  'mov',  'mp4',  'mpeg', 'mpg', 'mpv', 'ogm', 'ogv', 'qt',  'rm', 'vob', 'wmv' ),
            'document'    => array( 'doc', 'docx', 'docm', 'dotm', 'odt',  'pages', 'pdf',  'xps',  'oxps', 'rtf',  'wp', 'wpd', 'psd', 'xcf' ),
            'spreadsheet' => array( 'numbers',     'ods',  'xls',  'xlsx', 'xlsm',  'xlsb' ),
            'interactive' => array( 'swf', 'key',  'ppt',  'pptx', 'pptm', 'pps',   'ppsx', 'ppsm', 'sldx', 'sldm', 'odp' ),
            'text'        => array( 'asc', 'csv',  'tsv',  'txt' ),
            'archive'     => array( 'bz2', 'cab',  'dmg',  'gz',   'rar',  'sea',   'sit',  'sqx',  'tar',  'tgz',  'zip', '7z' ),
            'code'        => array( 'css', 'htm',  'html', 'php',  'js' ),
        ) );

        foreach ( $ext2type as $type => $exts )
            if ( in_array( $ext, $exts ) )
                return $type;
    }




    /**
     * Retrieve list of mime types and file extensions.
     *
     * @return array Array of mime types keyed by the file extension
     */
    public static function getMimeTypes() {
        return \team\Filter::apply( '\team\filesystem\mime_types', array(
            // Image formats.
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpe' => 'image/jpeg',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'bmp' => 'image/bmp',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'ico' => 'image/x-icon',
            // Video formats.
            'asf' => 'video/x-ms-asf',
            'asx' => 'video/x-ms-asf',
            'wmv' => 'video/x-ms-wmv',
            'wmx' => 'video/x-ms-wmx',
            'wm' => 'video/x-ms-wm',
            'avi' => 'video/avi',
            'divx' => 'video/divx',
            'flv' => 'video/x-flv',
            'mov' => 'video/quicktime',
            'qt' => 'video/quicktime',
            'mpeg' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'mpe' => 'video/mpeg',
            'mp4' => 'video/mp4',
            'm4v' => 'video/mp4',
            'ogv' => 'video/ogg',
            'webm' => 'video/webm',
            'mkv' => 'video/x-matroska',
            '3gp' => 'video/3gpp', // Can also be audio
            '3gpp' => 'video/3gpp', // Can also be audio
            '3g2' => 'video/3gpp2', // Can also be audio
            '3gp2' => 'video/3gpp2', // Can also be audio
            // Text formats.
            'txt' => 'text/plain',
            'asc' => 'text/plain',
            'c' => 'text/plain',
            'cc' => 'text/plain',
            'h' => 'text/plain',
            'srt' => 'text/plain',
            'csv' => 'text/csv',
            'tsv' => 'text/tab-separated-values',
            'ics' => 'text/calendar',
            'rtx' => 'text/richtext',
            'css' => 'text/css',
            'htm' => 'text/html',
            'html' => 'text/html',
            'vtt' => 'text/vtt',
            'dfxp' => 'application/ttaf+xml',
            // Audio formats.
            'mp3' => 'audio/mpeg',
            'm4a' => 'audio/mpeg',
            'm4b' => 'audio/mpeg',
            'ra' => 'audio/x-realaudio',
            'ram' => 'audio/x-realaudio',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'oga' => 'audio/ogg',
            'mid' => 'audio/midi',
            'midi' => 'audio/midi',
            'wma' => 'audio/x-ms-wma',
            'wax' => 'audio/x-ms-wax',
            'mka' => 'audio/x-matroska',
            // Misc application formats.
            'rtf' => 'application/rtf',
            'js' => 'application/javascript',
            'pdf' => 'application/pdf',
            'swf' => 'application/x-shockwave-flash',
            'class' => 'application/java',
            'tar' => 'application/x-tar',
            'zip' => 'application/zip',
            'gz' => 'application/x-gzip',
            'gzip' => 'application/x-gzip',
            'rar' => 'application/rar',
            '7z' => 'application/x-7z-compressed',
            'exe' => 'application/x-msdownload',
            'psd' => 'application/octet-stream',
            'xcf' => 'application/octet-stream',
            // MS Office formats.
            'doc' => 'application/msword',
            'pot' => 'application/vnd.ms-powerpoint',
            'pps' => 'application/vnd.ms-powerpoint',
            'ppt' => 'application/vnd.ms-powerpoint',
            'wri' => 'application/vnd.ms-write',
            'xla' => 'application/vnd.ms-excel',
            'xls' => 'application/vnd.ms-excel',
            'xlt' => 'application/vnd.ms-excel',
            'xlw' => 'application/vnd.ms-excel',
            'mdb' => 'application/vnd.ms-access',
            'mpp' => 'application/vnd.ms-project',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'docm' => 'application/vnd.ms-word.document.macroEnabled.12',
            'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
            'dotm' => 'application/vnd.ms-word.template.macroEnabled.12',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xlsm' => 'application/vnd.ms-excel.sheet.macroEnabled.12',
            'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
            'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
            'xltm' => 'application/vnd.ms-excel.template.macroEnabled.12',
            'xlam' => 'application/vnd.ms-excel.addin.macroEnabled.12',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'pptm' => 'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
            'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
            'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroEnabled.12',
            'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
            'potm' => 'application/vnd.ms-powerpoint.template.macroEnabled.12',
            'ppam' => 'application/vnd.ms-powerpoint.addin.macroEnabled.12',
            'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
            'sldm' => 'application/vnd.ms-powerpoint.slide.macroEnabled.12',
            'onetoc' => 'application/onenote',
            'onetoc2' => 'application/onenote',
            'onetmp' => 'application/onenote',
            'onepkg' => 'application/onenote',
            'oxps' => 'application/oxps',
            'xps' => 'application/vnd.ms-xpsdocument',
            // OpenOffice formats.
            'odt' => 'application/vnd.oasis.opendocument.text',
            'odp' => 'application/vnd.oasis.opendocument.presentation',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
            'odg' => 'application/vnd.oasis.opendocument.graphics',
            'odc' => 'application/vnd.oasis.opendocument.chart',
            'odb' => 'application/vnd.oasis.opendocument.database',
            'odf' => 'application/vnd.oasis.opendocument.formula',
            // WordPerfect formats.
            'wp' => 'application/wordperfect',
            'wpd' => 'application/wordperfect',
            // iWork formats.
            'key' => 'application/vnd.apple.keynote',
            'numbers' => 'application/vnd.apple.numbers',
            'pages' => 'application/vnd.apple.pages',
        ) );
    }

    /**
     * Retrieve list of fa icons and file extensions.
     *
     * @return array Array of icons keyed by the file extension
     */
    public static function getIcons() {
        return  \team\Filter::apply( '\team\filesystem\icons', array(
            // Image formats.
            'jpg' => 'fa-file-image-o',
            'jpeg' => 'fa-file-image-o',
            'jpe' => 'fa-file-image-o',
            'gif' => 'fa-file-image-o',
            'png' => 'fa-file-image-o',
            'bmp' => 'fa-file-image-o',
            'tiff' => 'fa-file-image-o',
            'tif' => 'fa-file-image-o',
            'ico' => 'fa-file-image-o',
            // Video formats.
            'asf' => 'fa-file-video-o',
            'asx' => 'fa-file-video-o',
            'wmv' => 'fa-file-video-o',
            'wmx' => 'fa-file-video-o',
            'wm' => 'fa-file-video-o',
            'avi' => 'fa-file-video-o',
            'divx' => 'fa-file-video-o',
            'flv' => 'fa-file-video-o',
            'mov' => 'fa-file-video-o',
            'qt' => 'fa-file-video-o',
            'mpeg' => 'fa-file-video-o',
            'mpg' => 'fa-file-video-o',
            'mpe' => 'fa-file-video-o',
            'mp4' => 'fa-file-video-o',
            'm4v' => 'fa-file-video-o',
            'ogv' => 'fa-file-video-o',
            'webm' => 'fa-file-video-o',
            'mkv' => 'fa-file-video-o',
            '3gp' => 'fa-file-video-o', // Can also be audio
            '3gpp' => 'fa-file-video-o', // Can also be audio
            '3g2' => 'fa-file-video-o', // Can also be audio
            '3gp2' => 'fa-file-video-o', // Can also be audio
            // Text formats.
            'txt' => 'fa-file-text-o',
            'asc' => 'fa-file-text-o',
            'c' => 'fa-file-text-o',
            'cc' => 'fa-file-text-o',
            'h' => 'fa-file-text-o',
            'srt' => 'fa-file-text-o',
            'csv' => 'fa-file-text-o',
            'tsv' => 'fa-file-text-o',
            'ics' => 'fa-file-text-o',
            'rtx' => 'fa-file-text-o',
            'css' => 'fa-file-code-o',
            'htm' => 'fa-file-text-o',
            'html' => 'fa-file-text-o',
            'vtt' => 'fa-file-text-o',
            'dfxp' => 'fa-file-text-o',
            // Audio formats.
            'mp3' => 'fa-file-audio-o',
            'm4a' => 'fa-file-audio-o',
            'm4b' => 'fa-file-audio-o',
            'ra' => 'fa-file-audio-o',
            'ram' => 'fa-file-audio-o',
            'wav' => 'fa-file-audio-o',
            'ogg' => 'fa-file-audio-o',
            'oga' => 'fa-file-audio-o',
            'mid' => 'fa-file-audio-o',
            'midi' => 'fa-file-audio-o',
            'wma' => 'fa-file-audio-o',
            'wax' => 'fa-file-audio-o',
            'mka' => 'fa-file-audio-o',
            // Misc application formats.
            'rtf' => 'fa-file-text-o',
            'js' => 'fa-file-code-o',
            'pdf' => 'fa-file-pdf-o',
            'swf' => 'fa-file-code-o',
            'class' => 'fa-file-code-o',
            'tar' => 'fa-file-archive-o',
            'zip' => 'afa-file-archive-o',
            'gz' => 'afa-file-archive-o',
            'gzip' => 'fa-file-archive-o',
            'rar' => 'fa-file-archive-o',
            '7z' => 'fa-file-archive-o',
            'exe' => 'fa-file',
            'psd' => 'fa-file-image-o',
            'xcf' => 'fa-file-image-o',
            // MS Office formats.
            'doc' => 'fa-file-word-o',
            'pot' => 'fa-file-powerpoint-o',
            'pps' => 'fa-file-powerpoint-o',
            'ppt' => 'fa-file-powerpoint-o',
            'wri' => 'fa-file-word-o',
            'xla' => 'fa-file-excel-o',
            'xls' => 'fa-file-excel-o',
            'xlt' => 'fa-file-excel-o',
            'xlw' => 'fa-file-excel-o',
            'mdb' => 'fa-stack-exchange',
            'mpp' => 'file',
            'docx' => 'fa-file-word-o',
            'docm' => 'fa-file-word-o',
            'dotx' => 'fa-file-word-o',
            'dotm' => 'fa-file-word-o',
            'xlsx' => 'fa-file-excel-o',
            'xlsm' => 'fa-file-excel-o',
            'xlsb' => 'fa-file-excel-o',
            'xltx' => 'fa-file-excel-o',
            'xltm' => 'fa-file-excel-o',
            'xlam' => 'fa-file-excel-o',
            'pptx' => 'fa-file-powerpoint-o',
            'pptm' => 'fa-file-powerpoint-o',
            'ppsx' => 'fa-file-powerpoint-o',
            'ppsm' => 'fa-file-powerpoint-o',
            'potx' => 'fa-file-powerpoint-o',
            'potm' => 'fa-file-excel-o',
            'ppam' => 'fa-file-excel-o',
            'sldx' => 'fa-file-powerpoint-o',
            'sldm' => 'fa-file-excel-o',
            'onetoc' => 'fa-sticky-note-o',
            'onetoc2' => 'fa-sticky-note-o',
            'onetmp' => 'fa-sticky-note-o',
            'onepkg' => 'fa-sticky-note-o',
            'oxps' => 'fa-sticky-note-o',
            'xps' => 'fa-sticky-note-o',
            // OpenOffice formats.
            'odt' => 'fa-file-word-o',
            'odp' => 'fa-file-powerpoint-o',
            'ods' => 'fa-file-excel-o',
            'odg' => 'fa-file-image-o',
            'odc' => 'fa-file-excel-o',
            'odb' => 'fa-stack-exchange',
            'odf' => 'file',
            // WordPerfect formats.
            'wp' => 'fa-file-word-o',
            'wpd' => 'fa-file-word-o',
            // iWork formats.
            'key' => 'fa-file',
            'numbers' => 'fa-file',
            'pages' => 'fa-file',
        ) );
    }
}
