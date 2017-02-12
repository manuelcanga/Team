<?php

namespace demo01\example03;

class Gui extends \team\Gui {

    public function index() {
        //we add a css file to our template
        $this->addCss('styles');
    }


    public function with_includes() {
        $this->addCss('styles');
    }

    public function with_places() {
        $this->addCss('styles');
        //we hook two views  in 'main_content' place and another in 'footer' place
        \team\gui\Place::view('main_content', $this->getView('/for_places/header'));
        \team\gui\Place::view('main_content', $this->getView('/for_places/content'));
        \team\gui\Place::view('footer', $this->getView('/for_places/footer'));

	//a view hooks in logo place in /for_places/header
        \team\gui\Place::view('logo', $this->getView('/for_places/menu'));
    }

    public function with_places2() {
        $this->addCss('styles');
        //we hook two views  in 'main_content' place and another in 'footer' place
        \team\gui\Place::view('main_content', $this->getView('/for_places2/header'));
        \team\gui\Place::view('main_content', $this->getView('/for_places2/content')); 
        \team\gui\Place::view('footer', $this->getView('/for_places2/footer'));

	//we use a hook in /for/places2/content
        \team\gui\Place::view('site_content', $this->getView('/for_places2/sidebar'));

	//a view hooks in logo place of previous example
        \team\gui\Place::view('logo', $this->getView('/for_places/menu'));

    }
}
