<?php

namespace package\welcome;

class Gui extends \team\Gui {

	/**
	  Before response
	*/
	protected function commons() {
		$this->title = ' - By Trasweb'; 
	}

	/**
	  Default response
	*/
    public function index() {        
		$this->title = 'Index response '.$this->title;
    }
    
    
    /**
	  After response
    */
    protected function custom() {
		$this->title .= ' - TEAM framework '.TEAM_VERSION;    
	}
}
