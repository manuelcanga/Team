<?php
/**
 * Developed by Manuel Canga ( dev@trasweb.net )
 * Date: 11/01/17
 * Time: 9:11
 */

namespace team\types;

abstract class Base implements \ArrayAccess
{
    use \team\data\Box;



    /**** FORMATS ****/
    public function __toString() {
            return self::out('Html');
    }

    public function out($_type = NULL, $options = [], $defaults = []) {
        $format_class = new \team\data\formats\Format();

        $type = $_type?? $format_class->filter($_type);

        if( !isset($type) &&  isset($this->out) ) {
            $type = \team\Check::key($this->get("out"), "Array");

            unset($this->out);
        }

        //Factory de vistas
        $obj = $format_class->get($type);



        if(!isset($obj) ) {
            \Team::system("Not found Data format  for {$type}", '\team\Dataformat_Not_Found');
        }

        return $obj->renderer($this->data + $defaults, $options);
    }



}