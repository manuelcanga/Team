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
SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE. */


namespace team\notices;

class Notice {
	/** Tipos de avisos finales. Además de los finales están los intermedios: Warnings, Infos y Events */
	const SUCCESS = 0;     
	const ERROR = 1;   
	const SYSTEM = 2;

	/** 
		listado de avisos(  array("result" => 0, "msg" => "", 0 => array(Infos) , 1 => array(Notices) ) )
		result: es el resultado de la operacion: 0  ( success ) , 1 ( Error ), 2 ( ErrorSystem )
		msg: Es el mensaje de éxito o fracaso
	 */
	private $result = 0;
	private	$type = 0;
	private $code = '';
	private $msg = '';
	private	$details = [];
	private $INFOS = [];
	private $WARNINGS = [];

	/**
	Se acabó el proceso todo OK
	*/
	public function success($msg, $code = null,$data = null)	{  	
		return $this->addNotice(self::SUCCESS, 'SUCCESS',$data, $code, $msg,  $this->INFOS);
	}

	 /**
		Aviso informativo. Es una notificación de tipo intermedio de carácter postiivo
	*/
    public function info($msg)  {
		$this->INFOS[] =  $msg;
	 }



	 /**
		Aviso alerta. Es una notificación de tipo intermedio de carácter negativo
	*/
    public function warning($msg, $code, $data = null) {
        \team\Debug::me($msg, $code, null, null, 2);
		$this->WARNINGS[$code] =  $msg;
	 }



	/**
		Se acabó el proceso con fallo
	*/
    public function error($msg, $code = null, $data = null)	{
        \team\Debug::me($msg, $code, null, null, 2);
		return $this->addNotice(self::ERROR, 'ERROR', $data, $code, $msg,  $this->WARNINGS);
	 }


	/**
		Hubo un error de sistema.
	*/
	public  function system($msg, $code = NULL, $data = null, $level = 2, $file = null, $line = null)  {
		$canceled = $this->addNotice(self::SYSTEM,  'SYSTEM', $data, $code, $msg, [ $code => $msg]);

		if($canceled) return true;

		if($file == null || $line == null)
	        \team\Debug::getFileLine($file, $line, $level);

		$e = new \System_Error($this->msg, $code);
		$e->setFile($file);
		$e->setLine($line);
		$e->setType("SYSTEM");
		throw $e;

		return self::SYSTEM;

	}


	/** ------------------------------ SETTERS ------------------------------- */

	/**
		Adding a notice
	*/
	private function addNotice($result, $type, $data, $code, $msg, $details) {

		if($code) {
			//Avisamos del error
            $canceled = null;
            if(isset($code)) {
                $canceled = \Team::event($code, $type, $data, $msg);
            }

			$canceled = ($canceled)?: \Team::event('\team\\'.strtolower($type), $code, $data, $msg);
			if($canceled) return $canceled;
		}


		$this->result = $result;
		$this->type   = $type;
		$this->code = $code;
		$this->msg	  = $msg;
		$this->details = $details;

		return false;
	}

	public  function setResult($result) { 
		return $this->result = $result; 
	}


	 /** ---------------------------- GETTERS --------------------------------- */
	
	public function get() 	 { return ['result' => $this->result, 'type' => $this->type, 'code' => $this->code, 'msg' => $this->msg, 'details' => $this->details ]; }
	public function getMsg() { return $this->msg; }
	public function getResult() { return $this->result; }
	public function getType() { return $this->type; }
	public function getCode() { return $this->code; }
	public function getDetails() { return $this->details; }

	 /** ---------------------------- CHECKERS --------------------------------- */

	public  function check()		{  return !$this->result;  }
    public  function ok()			{  return !$this->result;  }
    public  function nok()			{  return $this->result; 	}
	public  function had($code) 	{  return isset($this->WARNINGS[$code]); }
	public  function clear($code) 	{ if( $this->had($code) ) unset($this->WARNINGS[$code] ); }

	public  function getInfos() 	{ return $this->INFOS; }
    public  function getWarnings()  { return $this->WARNINGS;  }
    public  function checkWarnings() {  return !empty($this->WARNINGS);  }
    public  function checkInfos()   {  return !empty($this->INFOS);  }

}
