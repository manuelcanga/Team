<?php

namespace demo01\example04;

use \team\gui\Place;
class Gui extends \team\Gui {

    protected function commons() {
        //we add a css file to our template
        $this->addCss('styles');
        //We defined a layout inside this component( example04 )
        $this->setLayout('/commons/layout', 'example04');
        //Commons elements:
        //!header
        Place::view('top', $this->getView('/commons/header'));
        //!logo
        Place::view('logo', $this->getView('/commons/menu'));
        //!sidebar
        Place::view('site_content', $this->getView('/commons/sidebar'));
        //!footer
        Place::view('bottom', $this->getView('/commons/footer'));

        $this->previous_examples = 'example03';
    }


    public function index() {
    }

    public function news() {
    }

    public function about() {
    }

}
