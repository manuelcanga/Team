<?php

namespace package\welcome;

class Gui extends \team\Gui {

	/**
	  Before response
	*/
	protected function commons() {
		$this->SEO_TITLE = ' - By Trasweb'; 
	}

	/**
	  Default response
	*/
    public function index() {
		$this->SEO_TITLE = 'Index response '.$this->title;
		 $this->setLayout('team:layouts/default');
    }
    
    
    /**
	  After response
    */
    protected function custom() {
		$this->SEO_TITLE .= ' - TEAM framework '.TEAM_VERSION;    
	}
}
