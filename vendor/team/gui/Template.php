<?php
/**
 * Developed by Manuel Canga ( dev@trasweb.net )
 * Date: 13/01/17
 * Time: 18:29
 */

namespace team\gui;


class Template extends \team\datatype\Type
{
    use \team\gui\View;

    protected $contexts = [];

    public function initialize($view =  null, array $contexts = []) {
        $this->contexts = $contexts;
        $this->setContext('VIEW',  $view );
    }

    public function fromString($content =  null) {
        $this->setContext('VIEW',  $content?? $this->getContext('VIEW') );
        $this->setContext('LAYOUT', 'string');
    }

    public function getHtml($isolate = true, array $options = []) {
        return $this->out("html",$options, $isolate);
    }
}