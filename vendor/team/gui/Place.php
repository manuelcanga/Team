<?php
/**
 * Developed by Manuel Canga ( dev@trasweb.net )
 * Date: 13/01/17
 * Time: 16:08
 */

namespace team\gui;


use team\Sanitize;

abstract class Place
{

    protected static $items = [];

    public static function getHtml($place, $content,  $params, $engine) {
        if (empty($place)) {
            return $content;
        }

        $items = self::getItems($place);
        if(!empty($items)) {
            foreach($items as $order => $target ) {
                $func = $target['item'];
                $content = $func( $content, $params, $engine );
            }
        }

        return trim($content);
    }

    public static function addClass($place, $class, $overwrite = false,  $order = 40) {
        $placeid = \team\data\Sanitize::identifier($place);

        \team\data\Filter::add('\team\gui\classes\\'.$placeid, function($classes) use($class, $overwrite){
            if($overwrite) {
                $classes = [$class];
            }else {
                $classes[] = $class;
            }

            return $classes;
        }, $order);
    }

    public static function getClasses($place, $classes) {
        if(!empty($place)) {
            $placeid = \team\data\Sanitize::identifier($place);

            $classes = \team\data\Filter::apply('\team\gui\classes\\'.$placeid, (array)$classes, $place);

        }

        return implode(' ', (array)$classes);
    }


    public static function void(string $place) {

        return self::add($place, $order = 0, 'void', function($content, $params, $engine) {
            return '';
        });
    }


    protected static function addContentInPosition( $new_content, $position, $content ) {
        if("start" === $position) {
            return $new_content.$content;
        }else if("end" === $position) {
            return $content.$new_content;

        }else if("full" === $position) {
            return $new_content;
        }
    }

    public static function content(string $place, $new_content, $position = "end", $order = 40) {

        return self::add($place, $order, 'content', function($content, $params, $engine) use ($new_content, $position) {
            return self::addContentInPosition($new_content, $position, $content);
        });
    }

    public static function file(string $place, $file_with_content, $position = "end", $order = 40) {

        return self::add($place, $order, 'content', function($content, $params, $engine) use ($file_with_content, $position) {
            $new_content = '';

            if(\team\system\FileSystem::exists($file_with_content) ){
                $new_content = file_get_contents(_SCRIPT_.$file_with_content);
            }

            return self::addContentInPosition($new_content, $position, $content);
        });
    }


    public function wrap(string $place, $wrapper_start, $wrapper_end, $order = 40) {

        return self::add($place, $order, 'wrapper', function($content, $params, $engine) use ($wrapper_start, $wrapper_end) {
            return $wrapper_start.$content. $wrapper_end;
        });
    }

    /**
     * @param $view vista que se incluirá en el lugar
     * @param $place punto de anclaje en el que queremos incluir la vista. Si empieza por \, se tomará como pipeline el lugar completo.
     * Sino se añadirá a \team\place
     * @param bool $isolate determinada si la plantilla heredará el entorno de la plantilla padre( isolate = false ) o será independiente( isolate = true )
     * @param bool $order  orden de colocación de la vista respecto a otra en el mismo lugar.
     */
    public static  function view(string $place, $view, $_options = [], $order = 40, $position = 'end') {
        $view =  \team\system\FileSystem::stripExtension($view);
        $idView = \team\data\Sanitize::identifier($view);
        $options =  $_options;

        $isolate = true;
        if(isset($_options['isolate']) ) {
            $isolate = (bool)$_options['isolate'];
        }

        //Comprobamos si se quiere caché o no
        $cache_id = null;
        if(isset($_options['cache']) ) {
            $cache_id =  \team\system\Cache::checkIds($_options['cache'], $idView);
        }


        return self::add( $place, $order, 'view', function($content, $params, $engine)
                        use ($view, $options, $isolate, $idView, $cache_id, $position) {


            //Comprobamos si ya estaba la plantilla cacheada
            if(isset($cache_id) ) {
                $cache = \team\system\Cache::get($cache_id);
                if(!empty($cache)) {
                    return $content.$cache;
                }
            }

            //    \Debug::out(get_class_methods($engine) );
            //Si se quiere con todas las variables del padre
            if($isolate) { //aislado, sólo se quiere las variables que se le pasen
                $engine->assign($params);
                $engine->assign($options);
                $view_content = $engine->fetch($view.'.tpl');
            }else {
                $father = $engine;
                $template = $engine->createTemplate($view.'.tpl', $idView, $idView, $father);
                $template->assign($params);
                $template->assign($options);
                $view_content = $template->fetch();
            }

            //Si se ha pedido sistema de caché, lo guardamos
            if(isset($cache_id) ) {
                $cache_time = $options['cachetime']?? null;

                \team\system\Cache::overwrite($cache_id,  $view_content, $cache_time );
            }

            return self::addContentInPosition($view_content, $position, $content);

        }, $idView);

    }

    public static  function widget(string $place, $widget_name, $_options = [], $order = 40, $position = 'end') {
        $idwidget = \team\data\Sanitize::identifier($widget_name);

        //Puede haber ocasiones que un widget requiera de colocar información en otras partes del html
        //es por ello, que le damos la oportunidad de que carguen la información que necesiten ya
        //para ello, cargaremos el script /config/placed.php
        //y llamaremos al evento \team\widget\{id_widget}
        $namespace =  \team\system\NS::explode($widget_name);

        \team\system\FileSystem::ping("/{$namespace['package']}/{$namespace['component']}/config/placed.php");
        \Team::event('\team\placed\\'.$idwidget, $place, $_options, $order);

        //Comprobamos si se quiere caché o no
        $cache_id = null;
        if(isset($_options['cache']) ) {
            $cache_id =  \team\system\Cache::checkIds($_options['cache'], $idwidget);
            unset($_options['cache']);
        }

        $options = $_options;
        return self::add($place, $order, 'widget', function($content, $params, $engine) use ($widget_name, $options,  $cache_id,  $position) {

            $params = $params + $options;
            $params['engine'] = $engine;
            $params['placed'] = true;

            $widget_content =  \team\Component::call($widget_name, $params,  $cache_id);

            return self::addContentInPosition($widget_content, $position, $content);

        }, $idwidget);

    }

    protected static function add(string $place, int $order = 40, string $type, callable $item, string $itemid = null) {
        //Si no existe una tubería asociada, la creamos
        if(!self::exists($place)  ) {
            self::restore($place);
        }

        //Vamos buscando un hueco libre para el filtro a partir del orden que pidió
        for($max_order = 100; self::exists($place, $order) && $order < $max_order; $order += 4 );

        $itemid = $itemid?? $type.'_'.$order;

        //Lo almacemanos todo para luego poder usarlo
        self::$items[$place][$order] =  ['item' => $item, 'type' => $type, 'id' => $itemid, 'order' => $order];

        return true;
    }

    public static function reset() {
        self::$items = [];
    }


    public static function restore(string $place) {
        self::$items[$place] = [];
    }


    public static function exists(string $place, $order = null) {
        if(isset($order) ){
            $exists = isset(self::$items[$place][$order]);
        }else {
            $exists =  isset(self::$items[$place]);
        }

        return $exists;
    }

    public static function sort(& $place) {
        ksort(self::$items[$place]);
    }

    public static function getPositionById($place, $itemid) {
        if(self::exists($place))
        {
            return array_column(self::$items[$place], 'id', 'order');
        }

        return  null;
    }

    public static function getItems(string $place) {
        if(!self::exists($place) ) {
            return [];
        }

        self::sort($place);

        return self::$items[$place];
    }

    public static function removePlace($place) {
        if(self::exists($place)) {
            unset(self::$items[$place]);
            return true;
        }
        return false;
    }

    public static function removePosition($place, $order){
        if(self::exists($place, $order)) {
            unset(self::$items[$place][$order]);
            if(empty(self::$items[$place])) {
                self::removePlace($place);
            }
            return true;
        }

        return false;
    }

    public static function removeItem($place, $itemid) {
        $items = self::getPositionById($place, $itemid);
        if(isset($items) && is_array($items)) {
            foreach($items as $order => $item) {
                self::removePosition($place, $order);
            }
            return true;
        }
        return false;
    }

    public static function debug($place = null, $str = '') {
        $backtrace = debug_backtrace();
        $file = $backtrace[0]['file'];
        $line = $backtrace[0]['line'];


        if(isset($place)) {
            $item = self::$items[$place];
            $str = $str?: $place;
        }else {
            $item = self::$items;
            $str = $str?: "places";
        }

        \team\Debug::me($item,  $str, $file, $line);

    }
}