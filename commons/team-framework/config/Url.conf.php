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

class Url  extends \team\Config {

    /* __________________________ AREAS _____________________  */


    /** Especificamos si queremos areas asociados a paquetes y componentes o no
     *  El AREA '' o '/' se refiere al area principal (  )
     * A cada subdominio se le puede asignar un target( /package/component )
     * y zonas( suburls asociadas a package y componentes )
     *
     */
    protected $AREAS = ['/' =>  '/package/welcome'];

    
    /* __________________________ DOMINIO _____________________  */

    protected $PROTOCOL = null;    
	protected $DOMAIN = null;
	
	protected $REQUEST_METHOD = null;

    

	/* _________ METODOS DE CONFIURACION POR PERFILES */
	protected function onSetup() {
        global $_CONTEXT;
	
        if(!isset($this->DOMAIN) ) {
            $this->DOMAIN =  trim($_SERVER["SERVER_NAME"], '/');
        }

        
        if(!isset($this->REQUEST_METHOD) ) {
            $method  = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE']?? $_SERVER['_method']?? $_SERVER["REQUEST_METHOD"];

            $this->REQUEST_METHOD =  strtoupper($method);
        }


        $is_ssl = false;
        if ( isset($_SERVER['HTTPS']) ) {
            if ( 'on' == strtolower($_SERVER['HTTPS']) )
                $is_ssl = true;
            if ( '1' == $_SERVER['HTTPS']  )
                $is_ssl = true;
        } elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
            $is_ssl = true;
        }

        $this->IS_SSL = $is_ssl;

        if(!isset($this->PROTOCOL) ) {
            $this->PROTOCOL =  $is_ssl? 'https://' : 'http://';
        }

	}
}
 
