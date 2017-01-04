<?php
/**
New Licence bsd:
Copyright (c) <2014>, Manuel Jesus Canga Muñoz
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


namespace team\classes\builders;


require_once(_TEAM_.'/classes/controller/Actions.php');


class Actions extends Builder {


	
	public function checkParent($class) {
		return is_subclass_of($this->controller, '\team\Actions');
	}


	/** 
		Este método permite al Builder saber la clase de respuesta o controller
      que debe de instanciar
	*/
    public function getTypeController() {
        return 'Actions';
    }



	public function checkErrors(\team\Data $_data) {
		//-------Gestion de errores-----------
		$_data->ok = !\Team::getResult();
		$_data->nok = !$_data->ok;


		//------ NOTIFICACIONES -----------
		$_data->notices = array(
			"result" 	=>  \Team::getResult(), 
			"code"		 	=>  \Team::getCode(), 
			"msg"		 	=>  \Team::getMsg(), 
			"details" 	=>  \Team::getDetails() 
		);

	}

	


	public function transform(\team\Data &$_data, & $_controller, $_result ) {
		if(!empty($_result) ) {
			//Si lo que se devuelve es un string. Lo consideramos una salida en bruto
			if(is_string($_result) ) {
				return $_result;
			}

			//Si es una operacion y se devuelve un array. Se considera ese el resultado
			if( is_array($_result) ) {
				$_data->setData($_result);
			}
		}

	//	Event("Pre_Out", '\team\actions')->ocurred($_data);
		$_data->out = $_data->out($this->out);
	//	Event("Post_Out", '\team\actions')->ocurred($_data);


		return $_data->out;
	}


	/**
		Se devuelve un error( para caso de critical )
	*/
	public function getCriticalError($SE = null) {

		$msg = \team\Config::get('CRITICAL_MESSAGE', 'We are in maintenance, sorry');

		$_data = new \team\Data();

		//-------Gestion de errores-----------
		$_data->nok = true;
		$_data->ok = !$_data->nok;

		$result = '';
		$details = '';
		$type = 'system';
		$code = 'critical';
		if(!isset($SE) ) {
			$result = \Team::getResult();
			$type = \Team::getType();
			$details = \Team::getDetails();
		}


		//------ NOTIFICACIONES -----------
		$_data->notices = array(
			"result" 	=>  $result, 
			"msg"		 	=>  $msg, 
			"details" 	=>  $details, 
			"type" 		=> $type,
			"code" 		=> $code 
		);

		return $_data->out($this->out);
	}

    /**
    Mandamos al navegador los header necesarios
     */
    function sendHeader() {
        //header("Content-Type: application/x-www-form-urlencoded;charset=".CHARSET);
        //setlocale(LC_ALL,"es_ES",  "es_ES.UTF-8", "es", "spanish");


        $this->sendHeaderHTTP('application/'.$this->out);

    }

}
