<?php

namespace team\data\htmlengines;

/**
Se encarga de llamar a un método del controller actual.
*/
class Mirror {


	static function __callStatic ($name, $params) {			
		if(0 === strpos($name, 'mirror_') ) {
			$name = substr($name, 7);
		}

		$namespace = \team\Context::get('NAMESPACE');
		\team\Debug::me("[{$namespace}][Template]:Not found a replacement for '{$name}'  ");
	}
}
