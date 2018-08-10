<?php
/**
 * Created by PhpStorm.
 * User: trasweb
 * Date: 14/11/17
 * Time: 13:29
 */

namespace demo\welcome;


class Gui extends \Team\Controller\Gui
{
    public function index() {
        \Team\Config::add('theme', 'color', 'grey');

        $this->addCss('main.less');
        $this->setTitle("Welcome to my new web");
    }
}