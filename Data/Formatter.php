<?php
/**
 * Created by PhpStorm.
 * User: trasweb
 * Date: 24/01/18
 * Time: 19:11
 */

namespace Team\Data;


class Formatter
{
    protected $data;

    public function __construct(array $data = []) {
        $this->data = $data;
    }

    public static function change(array $data, string $toFormat = 'string', array $arguments = []) {
        $formatter = new Formatter($data);
        return $formatter->$toFormat($arguments);
    }

    public function url($options = []) {
        return http_build_query($this->data);
    }


    public function terminal($options = []) {
        if(!empty($this->data) ) {
            foreach($this->data as $_key => $_value) {
                if(!is_array($_value) && !is_object($_value) )
                    echo "{$_key} => {$_value}\n";
            }
        }
        return "";
    }


    public function string($options = []) {
        return  print_r($this->data,true);
    }


    public function params($options = []) {
        $separator_fields = $options['fields']?? '="';
        $separator_params = $options['params']?? ' ';
        $end = $options['end']?? '"';
        $prefix = $options['prefix']?? 'param_';

        $result = '';
        foreach($this->data as $field => $value) {
            if(is_numeric($field) ) {
                $name = $prefix.$field;
            }else {
                $name = $field;
            }

            $result .= "{$separator_params}{$name}{$separator_fields}{$end}";
        }
        return ltrim($result, $separator_params);
    }


    public function object($options = []) {
        return (object) $this->data;
    }


    public function xml($options = []) {
        return  $this->array_to_xml($this->data, new \SimpleXMLElement('<root/>'))->asXML();
    }

    protected function array_to_xml(array $arr,  $xml)
    {
        foreach ($arr as $k => $v) {
            is_array($v)
                ? $this->array_to_xml($v, $xml->addChild($k))
                : $xml->addChild($k, $v);
        }
        return $xml;
    }


    public function json($options = []) {
        $data = $this->convert_entities($this->data);

        return json_encode($data);
    }

    private function  convert_entities($data) {
        if (is_array($data)) {
            return array_map([$this, 'convert_entities'],$data);
        }

        if (is_numeric($data)) {
            return (strpos('.', $data)===false)? (float)$data : (int)$data;
        }


        if('UTF-8' != \Team\Config::get('CHARSET') ) {
            $data = \Team\Data\Sanitize::toJs((string)$data);
            return utf8_encode($data);
        }else {
            return (string)$data;
        }
    }

    public function html($options = []) {
        $type_engine = $this->data["HTML_ENGINE"]?? \Team\Config::get("HTML_ENGINE");

        $engine = $this->getHtmlEngine($type_engine);

        return $engine->transform($this->data);
    }

    private function getHtmlEngine($type_engine) {

        $type_engine = $this->filterEngine($type_engine, "TemplateEngine");
        if(!isset($type_engine) ) return null;


        $class = \Team\Data\Filter::apply('\team\htmlengine\\'.$type_engine, '\Team\Data\Htmlengine\\'.$type_engine);


        return  \Team\Loader\Classes::factory($class, true);
    }

    private function filterEngine($type, $default) {
        return  ucfirst(\team\data\Check::key($type, $default));
    }
}