<?php
/**
 * Developed by Manuel Canga ( dev@trasweb.net )
 * Date: 13/01/17
 * Time: 16:08
 */

namespace team\gui;


class Place
{

    protected static $items = [];

    public static function attachContent(string $place, $new_content, $position = "end", $order = 50) {

        return static::add($place, $order, 'content', function($content, $params, $engine) use ($new_content, $position) {
            if("start" === $position) {
                return $new_content.$content;
            }else if("end" === $position) {
                return $content.$new_content;

            }else if("full" === $position) {
                return $new_content;
            }

        });
    }


    public function wrap(string $place, $wrapper_start, $wrapper_end, $order = 50) {

        return static::add($place, $order, 'wrapper', function($content, $params, $engine) use ($wrapper_start, $wrapper_end) {
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
    public static  function attachView(string $place, $view, $_options = [], $isolate = false, $order = 50) {
        $view =  \team\FileSystem::stripExtension($view);
        $idView = \team\Sanitize::identifier($view);
        $options =  $_options;

        //Comprobamos si se quiere caché o no
        $cache_id = null;
        if(isset($_options['cache']) ) {
            $cache_id =  \team\Cache::checkIds($_options['cache'], $idView);
        }


        return static::add( $place, $order, 'view', function($content, $params, $engine) use ($view, $options, $isolate, $idView, $cache_id) {


            //Comprobamos si ya estaba la plantilla cacheada
            if(isset($cache_id) ) {
                $cache = \team\Cache::get($cache_id);
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

                \team\Cache::overwrite($cache_id,  $view_content, $cache_time );
            }

            return $content. $view_content;
        }, $idView);

    }

    public static  function attachWidget(string $place, $widget_name, $_options = [], $order = 50) {
        $idwidget = \team\Sanitize::identifier($widget_name);

        //Puede haber ocasiones que un widget requiera de colocar información en otras partes del html
        //es por ello, que le damos la oportunidad de que carguen la información que necesiten ya
        //para ello, cargaremos el script /config/placed.php
        //y llamaremos al evento \team\widget\{id_widget}
        $namespace =  \team\NS::explode($widget_name);

        \team\FileSystem::ping("/{$namespace['package']}/{$namespace['component']}/config/placed.php");
        \Team::event('\team\placed\\'.$idwidget, $place, $_options, $order);

        //Comprobamos si se quiere caché o no
        $cache_id = null;
        if(isset($_options['cache']) ) {
            $cache_id =  \team\Cache::checkIds($_options['cache'], $idwidget);
            unset($_options['cache']);
        }

        $options = $_options;
        return static::add($place, $order, 'widget', function($content, $params, $engine) use ($widget_name, $options,  $cache_id) {

            $params = $params + $options;
            $params['engine'] = $engine;
            $params['placed'] = true;

            $widget_content =  \team\Component::call($widget_name, $params,  $cache_id);

            return $content.$widget_content;
        }, $idwidget);

    }

    protected static function add(string $place, int $order = 50, string $type, callable $item, string $itemid = null) {
        //Si no existe una tubería asociada, la creamos
        if(!self::exists($place)  ) {
            self::restore($place);
        }

        //Vamos buscando un hueco libre para el filtro a partir del orden que pidió
        for($max_order = 100; static::exists($place, $order) && $order < $max_order; $order++);

        $itemid = $itemid?? $type.'_'.$order;

        //Lo almacemanos todo para luego poder usarlo
        static::$items[$place][$order] =  ['item' => $item, 'type' => $type, 'id' => $itemid, 'order' => $order];

        return true;
    }

    public static function restore(string $place) {
        static::$items[$place] = [];
    }


    public static function exists(string $place, $order = null) {
        if(isset($order) ){
            $exists = isset(static::$items[$place][$order]);
        }else {
            $exists =  isset(static::$items[$place]);
        }

        return $exists;
    }

    public static function sort(& $place) {
        ksort(static::$items[$place]);
    }

    public static function getPositionById($place, $itemid) {
        if(static::exists($place))
        {
            return array_column(static::$items[$place], 'id', 'order');
        }

        return  null;
    }

    public static function getItems(string $place) {
        if(!static::exists($place) ) {
            return [];
        }

        static::sort($place);

        return static::$items[$place];
    }

    public static function removePlace($place) {
        if(static::exists($place)) {
            unset(static::$items[$place]);
            return true;
        }
        return false;
    }

    public static function removePosition($place, $order){
        if(static::exists($place, $order)) {
            unset(static::$items[$place][$order]);
            if(empty(static::$items[$place])) {
                static::removePlace($place);
            }
            return true;
        }

        return false;
    }

    public static function removeItem($place, $itemid) {
        $items = static::getPositionById($place, $itemid);
        if(isset($items) && is_array($items)) {
            foreach($items as $order => $item) {
                static::removePosition($place, $order);
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
            $item = static::$items[$place];
            $str = $str?: $place;
        }else {
            $item = static::$items;
            $str = $str?: "places";
        }

        \team\Debug::me($item,  $str, $file, $line);

    }
}