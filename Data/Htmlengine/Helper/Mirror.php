<?php

namespace Team\Data\Htmlengine\Helper;

/**
Se encarga de llamar a un método del Controller actual.
*/
class Mirror {


	static function __callStatic ($name, $params) {			
		if(0 === strpos($name, 'mirror_') ) {
			$name = substr($name, 7);
		}

		$namespace = \Team\System\Context::get('NAMESPACE');
		\team\Debug::me("[{$namespace}][Template]:Not found a replacement for '{$name}'  ");
	}
}
