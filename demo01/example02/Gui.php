<?php

namespace demo01\example02;

class Gui extends \team\Gui {

    public function index() {
        $this->myvar = '¡Hello, world, again!';
    }
    

	public function setting_view() {
		$this->setView('other_example.tpl');
	}

	public function full_page() {
        \team\gui\Place::content('about_places', '<p>We can also use "places". These are hook places where we can embedded content, views or widgets</p>');
        \team\gui\Place::content('about_places', '<p>Places will be seen in others examples as well. Now, <a href="/example03/">the following example, please</a>');
	}
}
