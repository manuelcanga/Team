<?php
/**
 * Developed by Manuel Canga ( dev@trasweb.net )
 * Date: 11/01/17
 * Time: 9:11
 */

namespace Team\Datatype;

abstract class Base implements \ArrayAccess
{
    use \Team\Data\Box;

    protected $contexts = [];


    /**** FORMATS ****/
    public function __toString() {
            return self::out('Html');
    }

    public function getContext($context, $default = null) {
        return $this->contexts[$context]?? $default;
    }

    public function setContext($context, $value) {
        $this->contexts[$context] = $value;
        return $this;
    }



    public function out($_type = NULL, $options = [], $isolate = true) {
        \Team\System\Context::open($isolate);

        \Team\System\Context::set($this->contexts);

        $format_class = new \Team\Data\Format\Format();

        $type = $_type?? $format_class->filter($_type);

        if( !isset($type) &&  isset($this->out) ) {
            $type = \Team\Data\Check::key($this->get("out"), "Array");

            unset($this->out);
        }

        //Factory de vistas
        $obj = $format_class->get($type);



        if(!isset($obj) ) {
            \Team::system("Not found Data format  for {$type}", '\team\Dataformat_Not_Found');
        }

        $out =  $obj->renderer($this->data, $options);

        \Team\System\Context::close();

        return $out;
    }



}