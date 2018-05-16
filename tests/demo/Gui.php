<?php
/**
 * Created by PhpStorm.
 * User: trasweb
 * Date: 16/05/18
 * Time: 16:21
 */

namespace tests\demo;


class Gui extends \Team\Controller\Gui
{
    function welcome() {
        $demo = new \demo\welcome(['out' => 'html']);
        $this->msg = trim($demo->index() );


    }
}