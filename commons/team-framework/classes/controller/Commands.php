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

namespace team;


/**
		@TODO:
		Añadir soporte para colores como tiene los logs. Quizás usando:
		$cli->put("lo que sea", "red" -color de texto-, "white" -color de fondo-);
		o mejor aún
		$cli->put("<red>Lo que sea</red>"); y hacerlo válido también para los Logs
		Para ello es obvio que tendremos que separar de Log todo esto y hacer algo que funcione para los dos
		También podría hacerse como un sistema de plantilas smarty tal que así:
		{red}Bienvenido a TEAM{/red}
*/
class Commands extends Controller {

    const DEPENDENCIES = '/commands/';


    /*
        Obtenemos una línea input desde el terminal
        Muy útil para requerir datos
    */
    function getLine() {
		return trim(fgets(STDIN)); 
	}

	/**
		Visualizamos una línea por la salida estandar.
		¿ De verdad que se usará este método ?
	*/
    function putLine($out) {
		echo $out."\n\r";
	}


    public function __toString() { return $this->data->out("terminal");}

}
