<?php
/**
 * This file is part of TEAM.
 *
 * TEAM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, in version 2 of the License.
 *
 * TEAM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with TEAM.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Team\System;

/** **************************************************************************************
 * Funciones útiles para el trabajo a bajo nivel con el sistema de archivo
 *************************************************************************************** */
final class Filesystem
{
    const BYTE = 1;
    const KB_IN_BYTES = 1024 * self::BYTE;
    const MB_IN_BYTES = 1024 * self::KB_IN_BYTES;
    const GB_IN_BYTES = 1024 * self::MB_IN_BYTES;
    const TB_IN_BYTES = 1024 * self::GB_IN_BYTES;

    /**
     * Include a file( using a basepath ) if this exists wasn't included before
     *
     * @param string $file File to include.
     * @param string $base Basepath to use.
     * @return bool|mixed
     */
    public static function ping(string $file, string $base = _APPS_)
    {
        if (self::exists($file, $base)) {
            return include_once($base . $file);
        }

        return false;
    }

    /**
     * Include a file(  using a basepath ) if this exists
     *
     * @param string $file File to include.
     * @param string $base Basepath to use.
     * @return bool|mixed
     */
    public static function load($file, $base = _APPS_)
    {
        if (self::exists($file, $base)) {
            return include($base . $file);
        }
    }


    /**
     * Check if a file exists using a basepath
     * @param String $file archivo que se quiere comprobar su existencia
     * @return bool si existe(true) o no existe(false)
     */
    public static function exists(string $file, string $base = _APPS_): bool
    {
        if ('/' !== $file[0]) {
            return false;
        }

        return file_exists($base . $file);
    }

    /**
     * Check if a view exists. If this exists, args are extracted and then it is the view included
     *
     * @param string $___view File to use as view.
     * @param array $___args Arguments to pass to view.
     * @param string $___base Basepath to use.
     *
     * @return string A view should return a string always.
     */
    public static function view(string $___view, array $___args = [], string $___base = _APPS_): string
    {
        if (self::exists($___view, $___base)) {
            extract($___args, EXTR_SKIP);

            return include($___base . $___view);
        }

        return '';
    }

    /**
     * Comprobamos si existe un archivo, independientemente de la extension, dentro de la ruta del sitio web
     * @param String $file archivo que se quiere comprobar su existencia sin extension
     * @return bool si existe(true) o no existe(false)
     */
    public static function filename(string $filename, string $base = _APPS_): bool
    {
        $exists = glob($base . $filename . '.*');

        return !empty($exists);
    }

    /**
     * Obtiene el nombre de un archivo( sin ruta y sin extensión ).
     * @param string $_file archivo del que se extraerá el nombre
     */
    public static function basename(string $file): string
    {
        return self::stripExtension(basename($file));
    }

    /**
     * Devuelve la extensión del archivo $file
     * @param $file
     */
    public static function getFileExtension($file)
    {
        return pathinfo($file, PATHINFO_EXTENSION);
    }

    /**
     * Remove extension from file. E.g:  styles.css => styles
     * @param string $file filename with extension to remove
     * @param null|string $extension_to_remove Remove this specific extension
     * @return string return file without extension or without specific extension
     */
    public static function stripExtension(string $file, ?string $extension_to_remove = null): string
    {
        if (isset($extension_to_remove)) {
            return str_replace($extension_to_remove, '', $file);
        }

        return preg_replace('/\\.[^.\\s]{2,4}$/u', '', $file);
    }

    /**
     * Obtiene el nombre o ruta del archivo sin rutas relativas al principio
     * @param string $file archivo o ruta del que se quitará la ruta relativa del principio
     * @example  ./noticias/prueba.tpl => noticias/prueba.tpl
     */
    public static function stripRelativePath(string $file): string
    {
        return ltrim($file, './\\');
    }


    /**
     * Devuelve un array con los directorios que hay en una ruta dada
     * @param $_dir es la ruta de un directorio a partir del que se va a obtener el listado
     * @return array de directorios encontrados
     */
    public static function getDirs(string $_dir = '/',bool $cache = true, string $path = _APPS_): array
    {
        $dir = rtrim($_dir, '//');
        static $dirs_cache = [];
        $location = $path . $dir;

        //Si tenemos los directorios en "caché", devolvemos estos
        if (isset($dirs_cache[$location]) && $cache) {
            return $dirs_cache[$location];
        }

        //No los teniamos en cache, hemos de ir reiterando para comprobar cuales de los archivos bajo la ubicación pedida
        //son directorios y los devolvemos.
        //No consideramos los directorios ocultos( empiezan por . ), los que tengan un _ ( suele ser una manera de "desactivarlos" temporalmente )
        //y commons( por ser uno del sistema ).
        $dirs = null;
        if (file_exists($location)) {
            $dhandle = opendir($location);     //open workdir
            $dirs = array();        //arrays for saving directories.
            if ($dhandle) {
                while (false !== ($fname = readdir($dhandle))) {    //loop de archivos.
                    //No consideraremos directorios validos los que
                    //empiecen por "." o por "_" . Tampoco los llamados commons
                    if ('.' == $fname[0] || '_' == $fname[0]) {
                        continue;
                    }

                    //Si es un directorio, como esto es un listado de directorio lo agregamos a la lista
                    if (is_dir($location . '/' . $fname)) {
                        array_push($dirs, $fname);
                    }
                }

                closedir($dhandle);
            }
            asort($dirs);
            $dirs_cache[$location] = $dirs;
        }
        return $dirs;
    }

    public static function toUnits($size)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }

    public static function getSize(string $file, string $base = _APPS_): int
    {
        return (int)filesize($base . $file);
    }

    /**
     * Sube un archivo enviado por formulario al sistema de archivo
     * @param $identifier
     * @param null $options
     * @return array|null
     */
    public static function upload(string $identifier, ?array $options = null): array
    {
        $uploaded_file = array();

        if (empty($_FILES[$identifier]['name'])) {
            return $uploaded_file;
        }

        $file =& $_FILES[$identifier];

        if ($file['error'] != UPLOAD_ERR_OK && $file['size']) {
            \Team::warning('Archivo no se pudo subir', 'ERROR_' . $file['error']);

            return $uploaded_file;
        }

        extract($file);
        $ext = self::getFileExtension($name);
        $name = self::stripExtension($name);
        $type = self::ext2type($ext);

        if (!$ext) {
            \Team::warning('No se permiten archivos sin extension', 'ERROR_NO_EXTENSION');
            return $uploaded_file;
        }

        if (isset($options['allow']) && is_array($options['allow']) && !in_array($type, $options['allow'])) {
            \Team::warning('Archivo no se encuentra entre los permitidos', 'ERROR_ALLOW_' . $type);

            return $uploaded_file;
        }

        if (isset($options['disallow']) && is_array($options['disallow']) && in_array($type, $options['disallow'])) {
            \Team::warning('Archivo se encuentra entre los no permitidos', 'ERROR_DISALLOW_' . $type);

            return $uploaded_file;
        }

        $base_upload = $options['dir'] ?? \Team\System\Context::get(
            'BASE_UPLOAD',
            \Team\System\Date::current('base_upload')
        );
        $uploads_path = $options['path'] ?? self::getUploadsDir();

        self::mkdirRecursive($uploads_path . $base_upload);

        if (!empty($options['keep_name'])) {
            $new_name = \Team\Data\Sanitize::filename($name);
        } else {
            $new_name = md5(\Team\System\Date::current('timestamp') . '_' . $tmp_name);
        }

        $starting_name = $new_name;
        $i = 2; //if file exists, is the second instance
        do {
            $file = $base_upload . '/' . $new_name . '.' . $ext;

            $file_exists = self::exists($file, $uploads_path);
            if ($file_exists) {
                $new_name = $starting_name . '_' . $i;
                $i++;
            }
        } while ($file_exists);

        if (!move_uploaded_file($tmp_name, $uploads_path . $file)) {
            \Team::warning('Archivo no se pudo mover al destino', 'ERROR_MOVING');

            return $uploaded_file;
        }

        return $uploaded_file = [
            'file' => $file,
            'name' => $new_name,
            'size' => $size,
            'ext' => $ext,
            'type' => $type,
            'path' => $uploads_path,
            'dir' => $base_upload
        ];
    }

    /**
     * Retrieve the file type based on the extension name.
     *
     * @param string $ext The extension to search.
     * @return string|void The file type, example: audio, video, document, spreadsheet, etc.
     */
    public static function ext2type($ext)
    {
        $ext = strtolower($ext);

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
        $ext2type = \Team\Data\Filter::apply('\team\filesystem\ext2type', array(
            'image' => array('jpg', 'jpeg', 'jpe', 'gif', 'png', 'bmp', 'tif', 'tiff', 'ico'),
            'audio' => array(
                'aac',
                'ac3',
                'aif',
                'aiff',
                'm3a',
                'm4a',
                'm4b',
                'mka',
                'mp1',
                'mp2',
                'mp3',
                'ogg',
                'oga',
                'ram',
                'wav',
                'wma'
            ),
            'video' => array(
                '3g2',
                '3gp',
                '3gpp',
                'asf',
                'avi',
                'divx',
                'dv',
                'flv',
                'm4v',
                'mkv',
                'mov',
                'mp4',
                'mpeg',
                'mpg',
                'mpv',
                'ogm',
                'ogv',
                'qt',
                'rm',
                'vob',
                'wmv'
            ),
            'document' => array(
                'doc',
                'docx',
                'docm',
                'dotm',
                'odt',
                'pages',
                'pdf',
                'xps',
                'oxps',
                'rtf',
                'wp',
                'wpd',
                'psd',
                'xcf'
            ),
            'spreadsheet' => array('numbers', 'ods', 'xls', 'xlsx', 'xlsm', 'xlsb'),
            'interactive' => array('swf', 'key', 'ppt', 'pptx', 'pptm', 'pps', 'ppsx', 'ppsm', 'sldx', 'sldm', 'odp'),
            'text' => array('asc', 'csv', 'tsv', 'txt'),
            'archive' => array('bz2', 'cab', 'dmg', 'gz', 'rar', 'sea', 'sit', 'sqx', 'tar', 'tgz', 'zip', '7z'),
            'code' => array('css', 'htm', 'html', 'php', 'js'),
        ));

        foreach ($ext2type as $type => $exts) {
            if (in_array($ext, $exts)) {
                return $type;
            }
        }
    }

    public static function getUploadsDir()
    {
        return \Team\System\Context::get('_UPLOADS_', \team\System\Context::get('_TEMPORARY_'));
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
    public static function mkdirRecursive($target)
    {
        $wrapper = null;

        // Strip the protocol.
        if (self::isStream($target)) {
            list($wrapper, $target) = explode('://', $target, 2);
        }

        // From php.net/mkdir user contributed notes.
        $target = str_replace('//', '/', $target);

        // Put the wrapper back on the target.
        if ($wrapper !== null) {
            $target = $wrapper . '://' . $target;
        }

        /*
         * Safe mode fails with a trailing slash under certain PHP versions.
         * Use rtrim() instead of untrailingslashit to avoid formatting.php dependency.
         */
        $target = rtrim($target, '/');
        if (empty($target)) {
            $target = '/';
        }

        if (file_exists($target)) {
            return @is_dir($target);
        }

        // We need to find the permissions of the parent folder that exists and inherit that.
        $target_parent = dirname($target);
        while ('.' != $target_parent && !is_dir($target_parent)) {
            $target_parent = dirname($target_parent);
        }

        // Get the permission bits.
        if ($stat = @stat($target_parent)) {
            $dir_perms = $stat['mode'] & 0007777;
        } else {
            $dir_perms = 0777;
        }

        if (@mkdir($target, $dir_perms, true)) {
            /*
             * If a umask is set that modifies $dir_perms, we'll have to re-set
             * the $dir_perms correctly with chmod()
             */
            if ($dir_perms != ($dir_perms & ~umask())) {
                $folder_parts = explode('/', substr($target, strlen($target_parent) + 1));
                for ($i = 1, $c = count($folder_parts); $i <= $c; $i++) {
                    @chmod($target_parent . '/' . implode('/', array_slice($folder_parts, 0, $i)), $dir_perms);
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Test if a given path is a stream URL
     *
     * @param string $path The resource path or URL.
     * @return bool True if the path is a stream URL.
     */
    public static function isStream($path)
    {
        $wrappers = stream_get_wrappers();
        $wrappers_re = '(' . implode('|', $wrappers) . ')';

        return preg_match("!^$wrappers_re://!", $path) === 1;
    }

    public static function rmUploaded($file, $path = null)
    {
        $_UPLOADS_ = $path ?? self::getUploadsDir();

        return unlink($_UPLOADS_ . $file);
    }

    public static function download($file, $name = null, $isUploaded = true)
    {
        if ($isUploaded) {
            $path = self::getUploadsDir();
            $file = $path . $file;
        }

        if (!file_exists($file)) {
            \Team::warning("File not found");
            return false;
        }

        $filename = $name ?: basename($file);
        $extension = self::getFileExtension($filename);

        $mimes = self::getMimeTypes();

        $content_type = $mimes[$extension] ?? 'application/octet-stream';

        ob_clean();
        header('Content-Type:' . $content_type);
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"" . $filename . "\"");
        readfile($file);

        die();
    }

    /**
     * Retrieve list of mime types and file extensions.
     *
     * @return array Array of mime types keyed by the file extension
     */
    public static function getMimeTypes()
    {
        return \Team\Data\Filter::apply('\team\filesystem\mime_types', array(
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
        ));
    }

    /**
     * Obtiene la ruta absoluta(desde el raiz del proyecto ) de un recurso.
     * @param path $suppath , es la ruta desde la raiz del component( si el rescurso esta en un component)
     * o desde el paquete( si el recurso está en commons de un paquete )
     * @param $component componente en el que se encuentra el recurso ( por defecto el actual )
     * @param $package paquete dónde se encuentra el recurso ( por defecto el actual )
     */
    public static function getPath($subpath, $component = null, $app = null)
    {
        $subpath = trim($subpath, '/');
        $component = $component ?? \Team\System\Context::get('COMPONENT');
        $app = $app ?? \Team\System\Context::get('APP');

        if ('root' === $app || 'root' === $component) {
            return "commons/{$subpath}/";
        }

        return "{$app}/{$component}/{$subpath}/";
    }

    /**
     * Determine if a directory is writable.
     *
     * @param string $path Path to check for write-ability.
     * @return bool Whether the path is writable.
     */
    public static function issWritable($path)
    {
        return @is_writable($path);
    }

    /**
     * Retrieve list of fa icons and file extensions.
     *
     * @return array Array of icons keyed by the file extension
     */
    public static function getIcons()
    {
        return \Team\Data\Filter::apply('\team\filesystem\icons', array(
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
        ));
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
    public function joinPath($base, $path)
    {
        if (self::isAbsolutePath($path)) {
            return $path;
        }

        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }

    /**
     * Test if a give filesystem path is absolute.
     *
     * For example, '/foo/bar'
     *
     * @param string $path File path.
     * @return bool True if path is absolute, false is not absolute.
     */
    public static function isAbsolutePath($path)
    {
        /*
         * This is definitive if true but fails if $path does not exist or contains
         * a symbolic link.
         */
        if (realpath($path) == $path) {
            return true;
        }

        if (strlen($path) == 0 || $path[0] == '.') {
            return false;
        }

        // A path starting with / or \ is absolute; anything else is relative.
        return ($path[0] == '/' || $path[0] == '\\');
    }

    /**
     * Normalize a filesystem path.
     *
     * @param string $path Path to normalize.
     * @return string Normalized path.
     */
    public function normalizePath($path)
    {
        $path = str_replace('\\', '/', $path);

        return $path;
    }
}
