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

namespace Team\Builder;

require_once(_TEAM_ . '/Controller/Gui.php');


class Gui extends Builder {


	public function checkParent($class) {
		return is_subclass_of($this->controller, '\team\controller\Gui');
	}


	/** 
		Este método permite al Builder saber la clase de respuesta o Controller
      que debe de instanciar
	*/
    public function getTypeController() {
        return 'Gui';
    }


    public function checkErrors(\team\data\Data $_data) {
        //-------Gestion de errores-----------
        $ok = !\Team::getResult();
        $nok = !$_data->ok;

        //------ NOTIFICACIONES -----------
        $notices = array(
            "result" 	=>  \Team::getResult(),
            "msg"		 	=>  \Team::getMsg(),
            "details" 	=>  \Team::getDetails()
        );


        $_data['_']['ok'] = $ok;
        $_data['_']['nok'] = $nok;
        $_data['_']['NOTICES'] = $notices;

    }

    public function transform(\team\data\Data &$_data, $_controller, $_result = null ) {

        $hubo_resultado_devuelto_por_response = isset($_result)  && is_string($_result);

        //Si es una gui y se devuelve un valor string, se considera eso la salida html
        //Antes se renderizaba la salida, pero era un derroche tremendo de recursos
        //Por no hablar que un response podía devolver una salida ya renderizada(de, por ejemplo, un widget )
        //y entonces había un doble renderizado.
        if($hubo_resultado_devuelto_por_response ) {
            return $_result;
        }


        /** Añadimos las variables del sistema */
        $out = '';
        $params = $_controller->getParams();


        //Sólo para html añadimos los argumentos de la GUI
        $_data['_'] = $params;

        $out = $_data->out($this->out, [], $isolate = false);

        return $out;
    }

    /**
    Se devuelve un valor por defecto.
     */
    public function getCriticalError($SE = null) {
        //Para las GUI no podemos mostrarle un aviso de error del sistema, hemos de enviar el error al programador cliente para que lo maneje.
        if( !$SE instanceof \Team\System\Exception\System_error ) {
            $SE = new \Team\System\Exception\System_Error($SE->getMessage(), '\team\views\errors',$SE->getCode(), $SE->getFile(), $SE->getLine() /*, $SE->getFunction()*/ );
            //Guardamos el namespace actual
            $SE->setNamespace(\Team\System\Context::get('NAMESPACE'));
        }
        \Team::systemException($SE );


        return '';
    }



    function sendHeader() {
        //header("Content-Type: application/x-www-form-urlencoded;charset=".CHARSET);
        //setlocale(LC_ALL,"es_ES",  "es_ES.UTF-8", "es", "spanish");


        $this->sendHeaderHTTP('text/html');

    }
}
