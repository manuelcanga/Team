<?php

namespace package\welcome;

class Gui extends \team\Gui {

	/**
	  Before response
	*/
	protected function commons() {
		$this->setTitle('By Trasweb');
	}

	/**
	  Default response
	*/
    public function index() {
        $this->setTitle('Index response', $separator=true, $before=true);
    }
    
    
    /**
	  After response
    */
    protected function custom() {
        $this->setTitle('TEAM framework '.TEAM_VERSION);
	}
}
