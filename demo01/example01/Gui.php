<?php

namespace demo01\example01;

class Gui extends \team\Gui {

    public function index() {
       $hello_world=<<<INSTRUCTION
        <p>[file:/demo01/example01/Gui.php]</p>
        <p>This is a GUI. You can use GUI when you need create HTML.</p> 
        <p>There are many ways to show HTML in browsers.</p> 
        <p>The  easiest is return a string, like this, from a GUI.</p>
        <p>Now, you can follow with <a href='/example02/'>views</a>.</p>
INSTRUCTION;
       return $hello_world;
    }


}
