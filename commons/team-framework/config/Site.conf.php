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
namespace config\team; 



class Site  extends \team\Config {


	/** 
		Establece un entorno de ejecucion.
		Los usados por el sistemas son Local y Production.
		Sin embargo, puedes usar el entorno que mas se adapte a sus necesidades
	*/
	protected  $ENVIROMENT =  \team\ENVIROMENT;

	/* __________________________ GENERALES _____________________  */
	protected  $LANG = 'es_ES';
	protected  $CHARSET = 'UTF-8';
	protected $TIMEZONE = 'Europe/Madrid';


	/*  _________________________  TRANSFORM ________________________ */
	//Motor que se usara para procesar las vistas
	protected $HTML_ENGINE = "TemplateEngine";
	

	/* _________ METODOS DE CONFIURACION POR PERFILES */
	protected function onSetup() {
		   date_default_timezone_set($this->TIMEZONE);
            ini_set('date.timezone', $this->TIMEZONE);

			$lang = $this->LANG;
			$charset = $this->CHARSET;
			$locale = $lang.'.'.$charset;
			setlocale(LC_ALL,$locale );
			putenv('LANG='.$locale);
			putenv('LANGUAGE='.$locale); 


	}
}
 
