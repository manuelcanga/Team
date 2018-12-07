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

namespace Team\Data\Htmlengine;

use const Team\_SERVER_;

require_once(__DIR__ . "/Helper/Mirror.php");
require_once(__DIR__ . "/Helper/Config.php");

/** TODO: Optimized */
//ini_set('zlib.output_compression', '1');

/**
 * Notas:
 * En archivo: lib/data/Htmlengine/Smarty/sysplugins/smarty_internal_templatecompilerbase.php
 * Método: getPluginFromDefaultHandler
 * Se lanza el callback: registerDefaultPluginHandler
 * Para la busqueda de tags que no han sido definidos.
 */
class TemplateEngine
{
    private static $functions_user_cache = array();

    private $gui = null;

    function __construct()
    {
        //Le añadimos una referencia a la GUI Actual.
        $this->gui = \Team\System\Context::get("CONTROLLER");
    }

    static function __initialize()
    {
//      \Classes::addLoader("smartyAutoload");

        //Todo: filter o Task si no hay, por defecto devolverá un ' '.
        //Podría ser util para añadir cosas a la cabecera, al pie, etc.
        // class_alias('Smarty_Internal_Resource_File', 'Smarty_Resource_Insert', true);

        if (!defined('SMARTY_RESOURCE_CHAR_SET')) {
            define('SMARTY_RESOURCE_CHAR_SET', \Team\Config::get('CHARSET'));
        } else {
            return;
        }

        require_once(\team\_VENDOR_ . "/Smarty/Smarty.class.php");

        //Resources propioos
        class_alias('Smarty_Internal_Resource_File', 'Smarty_Resource_Component', true);
        class_alias('Smarty_Internal_Resource_File', 'Smarty_Resource_Commons', true);
        class_alias('Smarty_Internal_Resource_File', 'Smarty_Resource_App', true);
        class_alias('Smarty_Internal_Resource_File', 'Smarty_Resource_Root', true);
        class_alias('Smarty_Internal_Resource_File', 'Smarty_Resource_Theme', true);
        class_alias('Smarty_Internal_Resource_File', 'Smarty_Resource_Custom', true);
        class_alias('Smarty_Internal_Resource_File', 'Smarty_Resource_Team', true);

        $temporary_dir = self::getTemporaryTemplatesDir();

        //Creamos un directorio temporal que evite las posibles colisiones de temporales smarty entre sitios
        if (!file_exists($temporary_dir . "/smarty/compile")) {
            mkdir($temporary_dir . "/smarty/compile", 0777, true);
        }

        //Si no existe, habra que crearlo
        if (!file_exists($temporary_dir . "/smarty/cache")) {
            mkdir($temporary_dir . "/smarty/cache", 0777, true);
        }
    }

    public static function getTemporaryTemplatesDir()
    {
        return \Team\System\context::get('_TEMPORARY_', _SERVER_, 'templates');
    }

    public function transform(Array $_data)
    {
        $_data['_']["USER"] = \Team\User::getCurrent();
        $_data['_']['notices'] = \Team::getCurrent();
        $_data['USER_AGENT'] = \Team\Client\Http::checkUserAgent();

        //Lanzamos un evento de inicio de transformacion de plantilla
        //  $event = \Event('Transform', '\Team\view')->ocurred($data);

        $_data = \Team\Data\Filter::apply('\team\template\data', $_data);

        $engine = new \Smarty();

        //Obtenemos la plantilla que vamos a procesar
        $template = $this->getView($_data);

        $this->initializeEngine($engine, $_data, $template);

        //\team\Debug::out($_data);
        //Transformamos los datos que tenemos a datos utilizables por smarty
        $engine_data = $this->transformToEngineData($_data);

        $is_main = \Team\System\Context::isMain();
        \Team::event('\team\view\fetching', $template, $engine, $engine_data, $is_main);

        $result = $engine->fetch($template, $engine_data);

        if (!$this->gui || \Team\System\Context::get("SHOW_VIEWS", true)) {
            return $result;
        } else {
            return "";
        }
    }

    /**
     * Selecciona la plantilla por la que se comenzara el parseo de vistas
     * @param Array $data : Datos pasados a la plantilla
     */
    function getView(Array $_data = null)
    {
        $layout = \Team\System\Context::get('LAYOUT');
        $view = \Team\System\Context::get('VIEW');

        if (isset($view)) {
            $view = \Team\System\FileSystem::stripExtension($view);
        }

        if (isset($layout)) {
            $layout = \Team\System\FileSystem::stripExtension($layout);
        }

        $is_string = "string" === $layout;
        $is_layout = !$is_string && !empty($layout);
        $view_exists = !empty($view);

        //Si es un layout que usa el  "default_template_handler_func" de smarty(Team:framework/debug.tpl) la devolvemos ta cual
        if ($is_layout && strpos($layout, ':')) {
            return $layout;
        }
        //lo mismo que el anterior pero para vistas
        if (!$is_string && $view_exists) {
            return $view;
        }

        $template = '';
        if ($is_string) {
            $template = "eval:" . $view;
        }

        return $template;
    }

    /**
     * Inicializamos el objeto de smarty con la configuracion propia de Team
     * @param Smarty $_engine : objeto de smarty que inicializaremos.
     */
    function initializeEngine(\Smarty $_engine, $_data, $_template)
    {
        $app = \Team\System\Context::get('APP');
        $component = \Team\System\Context::get('COMPONENT');
        $response = \Team\System\Context::get('RESPONSE');
        $theme = _SCRIPTS_ . \Team\System\Context::get('_THEME_');

        $_engine->addPluginsDir($theme . '/commons/views/plugins');
        $_engine->addPluginsDir(_APPS_ . '/' . $app . '/commons/views/plugins');
        $_engine->addPluginsDir(_SCRIPTS_ . '/commons/views/plugins');
        $_engine->addPluginsDir(_TEAM_ . '/Data/Htmlengine/plugins');
        $_engine->addPluginsDir(_TEAM_ . '/Data/Htmlengine/form');

        //Si aún asi no se encuentran los elementos, se añade una funcion buscadora de elementos
        $_engine->registerDefaultPluginHandler(array($this, "customElements"));
        //Permitimos varios wrappers en la plantilla
        $_engine->default_template_handler_func = [$this, 'default_template_handler_func'];

        /**
         * @see https://github.com/smarty-php/smarty/blob/master/INHERITANCE_RELEASE_NOTES.txt
         */
        $_engine->inheritance_merge_compiled_includes = false;
        //Para poder encontrar  shortcode, necesitamos buscar entre las funciones del usuario. Cacheamos estas
        $functions = get_defined_functions();
        self::$functions_user_cache = $functions["user"];

        $view_cache = \Team\System\Context::get('VIEW_CACHE', false);

        $_engine->compile_check = !$view_cache;
        $_engine->caching = $view_cache;
        $compile_id = $app . \Team\System\Context::get('AREA') . \Team\System\Context::get('LAYOUT');
        $_engine->compile_id = \Team\Data\Filter::apply('\team\smarty\compile_id', $compile_id);

        //Un componente sólo vería sus cosas, aún así puede usar root: package: etc
        $_engine->template_dir = _APPS_ . '/';
        $temporary_dir = self::getTemporaryTemplatesDir();
        $_engine->setCompileDir($temporary_dir . "/smarty/compile");
        $_engine->setCacheDir($temporary_dir . "/smarty/cache");

        /** Usamos un filtro para que no haya espacio en blanco  */
        if ((bool)\Team\System\Context::get('MINIMIZE_VIEW', true)) {
            $_engine->loadFilter('output', 'trimwhitespace');
        }

        \Team::event('\team\smarty\initializing', $_engine, $_data, $_template);

        /** Activar el debug(para administradores):  http://MIDOMAIN.es?debug  */
        //$smarty->debugging = true;
        //$smarty->smarty_debug_id = "debug";
        // self::$engine ->debugging_ctrl =  ( \Team\User::level() >= \Team\User::Manager  ) ? 'URL' : 'NONE';
    }

    /**
     * Transformamos los datos que tenemos a datos utilizables por smarty
     * @param Array $_data : los datos que vamos a utilizar
     * @return \Smarty_Data : retornamos un objeto de datos smarty
     */
    function transformToEngineData(Array $_data)
    {
        //Definimos las que seran las constantes de configuracion de smarty
        $data = new \Smarty_Data();
        //Añadimos a la plantilla todas las constantes de configuracion
        $data->config_vars = new Helper\Config();
        if (\Team\System\Context::get("TRACE_CONFIG")) {
            \team\Debug::me($data->config_vars, "Variables de configuracion smarty");
        }

        $data->assign($_data);

        return $data;
    }

    /**
     * @TODO: En un futuro se debería de poder especificar con cada elemento si se cachea o no
     * Esta función lo que hace es localizar elementos que no se hayan definido previamente.
     * Aunque lo suyo es que se hubieran definido en los directorios de plugins ( ver método de "initializeSmarty"
     * más abajo )
     * )
     */
    function customElements($name, $type, $template, &$callback, &$script, &$cacheable)
    {
        $cacheable = false;

        //Por algún motivo que desconozco, no puedo mandar el callback con un ojecto
        //Así que con el apaño de un mirror solucionamos el problema
        //Todo esto para buscar si existe un evento
        switch ($type) {
            case \Smarty::PLUGIN_FUNCTION:
            case \Smarty::PLUGIN_BLOCK:
            case "modifier":
                /** Si hay una función de usuario creada con el mismo nombre, la llamamos
                 * con TEAM no hay necesidad de funciones, si la hay es por algo */
                if (in_array($name, self::$functions_user_cache)) {
                    $callback = $name;
                    return true;
                }

                $callback = array('\Team\Data\Htmlengine\Helper\Mirror', 'mirror_' . $name);
                return true;

            case \Smarty::PLUGIN_COMPILER:
                //  $callback = $name;
                return false;
            case "class":
                //  $callback = $name;
                return false;
        }

        return false;
    }

    public function default_template_handler_func($type, $name, &$content, &$modified, \Smarty $smarty)
    {
        $name = str_replace('.tpl', '', $name) . '.tpl';

        $component = \Team\System\Context::get('COMPONENT');

        $template = $name;

        $found_type = false;
        switch ($type) {
            case 'team':
                $template = _TEAM_ . "/Gui/Views/{$name}";
                $found_type = true;
                break;
            case 'theme':
                $template = _SCRIPTS_ . \Team\System\Context::get('_THEME_') . "/{$name}";
                $found_type = true;
                break;
            case 'custom':
                $template = _SCRIPTS_ . \Team\System\Context::get('_THEME_') . "/{$component}/views/{$name}";
                $found_type = true;
                break;
            case 'commons':
            case 'app':
                $found_type = true;
                $template = \Team\System\Context::get('_APP_') . "/commons/views/{$name}";
                break;
            case 'root':
                $found_type = true;
                $template = _APPS_ . "/commons/views/{$name}";
                break;
            case 'component':
                $found_type = true;
                $template = \Team\System\Context::get('_COMPONENT_') . "/views/{$name}";
                break;
            case 'file':
                $found_type = true;
                $template = \Team\System\Context::get('_APPS_') . "{$name}";
                break;
        }

        if ($found_type && !file_exists($template)) {
            \Debug::me("Not found view {$template} of type {$type} and name {$name}");

            $template = _TEAM_ . "/Gui/Views/layouts/void.tpl";
        }

        return $template;
    }

    function export($_target, Array $_data = [], Array $_options = [])
    {
    }
}
