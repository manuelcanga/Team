<?php
/**
New Licence bsd:
Copyright (c) <2012>, Manuel Jesus Canga Muñoz
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions are met:
    * Redistributions of source code must retain the above copyright
      notice, this list of conditions and the following disclaimer.
    * Redistributions in binary form must reproduce the above copyright
      notice, this list of conditions and the following disclaimer in the
      documentation and/or other materials provided with the distribution.
    * Neither the name of the trasweb.net nor the
      names of its contributors may be used to endorse or promote products
      derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
DISCLAIMED. IN NO EVENT SHALL Manuel Jesus Canga Muñoz BE LIABLE FOR ANY
DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
(INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
(INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

*/



/**
	Error de sistema. 
	Se usa para poder recuperar Team de errores graves cometidos por el programador
*/
class System_Error extends \Exception {
	protected $backtrace = array();
	protected $line;
	protected $file;
	protected $function;
	protected $class;
	protected $namespace = "team";
	protected $type = "SYSTEM";
	protected $codeName = "";
	protected $level = null;
	
	public function __construct($_msg = NULL, $_code_name = NULL, $_code = NULL, $_file = null, $line = null, $function = null, $class = NULL) {

		$backtrace = debug_backtrace();
		if(count($backtrace) > 3)
			$this->backtrace = array_slice(debug_backtrace(),3);
		else
			$this->backtrace = array_slice(debug_backtrace(),1);

        //Si existe file es proque se ha lanzado directamente desde \team::system y no hace falta asignar nada más
        if(!$this->file) {
            if ($_file) {
                $this->file = $_file;
                $this->line = $_line;
                $this->function = $function;
                $this->class = $class;

            } else {

                $this->file = $this->backtrace[0]["file"];
                $this->line = $this->backtrace[0]["line"];
                $this->function = $this->backtrace[1]["function"];
                if (isset($this->backtrace[1]["class"])) {
                    $this->class = $this->backtrace[1]["class"];
                }
            }
        }

		$this->codeName = $_code_name;
		if(!isset($_code_name)  ) {
			if( isset(\team\notices\Errors::$php_errors_code[$_code] ) ) {
				$this->codeName = \team\notices\Errors::$php_errors_code[$_code];
			}else {
				$this->codename = 'E_SYSTEM';
			}
		}

		$this->level = \team\Context::getState(); //estado actual
		$this->namespace = \team\Context::get('NAMESPACE');

		parent::__construct($_msg, $_code);
	}
	
	public function getBacktrace() { return $this->backtrace(); }
	public function getFunction() { return $this->function; }
	public function getClass() { return $this->class; }
	public function getNamespace() { return $this->namespace; }
	public function getCodeName() { return $this->codeName; } 
	public function getState() { return $this->level; }
	public function getType() { return $this->type; }

	
	public function setNamespace($_namespace) { $this->namespace = $_namespace; }
	public function setType($type) { $this->type = $type; }
	public function setFile($file) { $this->file = $file; }
	public function setLine($line) { $this->line = $line; }
	public function setState($state) { $this->level = $state; }
	
	public function & getData() {
		$info = new \team\Data();
		$info->msg = $this->getMessage();
		$info->line = $this->getLine();
		$info->file = $this->getFile();
		$info->function = $this->getFunction();
		$info->class = $this->getClass();
		$info->codeName = $this->getCodeName();
		$info->code = $this->getCode();
		$info->state = $this->getState();
		
		return $info;
	}

	public function debug($msg = "System_Error") { \team\Debug::me("[{$this->namespace}][{$this->codeName}]: {$this->message}", $msg, $this->file, $this->line);  }
}
