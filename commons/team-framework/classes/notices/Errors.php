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


class Errors {

	/** Errores posibles en php. Si tiene asociado un 1 es que puede continuar a pesar del error. 0 es que no es posible continuar */
	public $php_errors = array(
			E_ERROR => 0, E_WARNING => 1,  E_PARSE => 0, E_NOTICE => 1,  E_CORE_ERROR => 0,E_CORE_WARNING => 1,  E_COMPILE_ERROR => 0,
			E_COMPILE_WARNING => 1, E_USER_ERROR => 0, E_USER_WARNING => 1, E_USER_NOTICE => 1, E_STRICT => 1, E_RECOVERABLE_ERROR => 0,
			E_DEPRECATED => 1,  E_USER_DEPRECATED => 1
	); 
	
	public static $php_errors_code = array( 
			E_ERROR => "E_ERROR", E_WARNING => "E_WARNING",  E_PARSE => "E_PARSE", E_NOTICE => "E_NOTICE",  E_CORE_ERROR => "E_CORE_ERROR",
			E_CORE_WARNING => "E_CORE_WARNING",  E_COMPILE_ERROR => "E_COMPILE_ERROR",  E_COMPILE_WARNING => "E_COMPILE_WARNING", 
			E_USER_ERROR => "E_USER_ERROR", E_USER_WARNING => "E_USER_WARNING", E_USER_NOTICE => "E_USER_NOTICE", E_STRICT => "E_STRICT", 
			E_RECOVERABLE_ERROR => "E_RECOVERABLE_ERROR", E_DEPRECATED => "E_DEPRECATED",  E_USER_DEPRECATED => "E_USER_DEPRECATED"
	);

	
	/**
	  Comprobamos si hubo un error asociado a las vistas
	*/
	private function isViewError($context) {
	  return isset($context["_smarty_tpl"]);
	}
	
	
	/**
	  Mostramos un error producido por las vistas
	*/
	private function showViewError($_errno, $_errstr, $_errfile, $_errline, $_context, $_errorcode ) {
		  $error_reporting_template = \team\Config::get('VIEWS_ERROR_LEVEL', E_ALL & ~E_NOTICE );

		/** 
		  Si se escogio desde las opciones de configuración mostrar el error de las vistas, lo hacemos. Sino, lo dejamos pasar 
		*/
		if ( $_errno & $error_reporting_template) {
			$template = $_context["_smarty_tpl"]->_current_file;
			$_errline = "..";
			$_errfile = $file;

		  \team\Debug::me($_context, "[{$namespace}][".$_errorcode."]: {$_errstr} in template  {$template} ", $_errfile, $_errline);
		}else {
			return true;
		}
	
	}
	
	public function PHPError($_errno, $_errstr, $_errfile, $_errline, $_context = array()) {

		$namespace = \team\Context::get('NAMESPACE');
		$is_critical = true;
		$_errorcode =  self::$php_errors_code[$_errno]?? 'CRITICAL';

		if($this->isViewError($_context) ) { 
			return $this->showViewError($_errno, $_errstr, $_errfile, $_errline, $_context, $_errorcode);
		//Comprobamos si es uno de los errores que bloquean la continuación de la ejecución
		}else if(!$this->php_errors[$_errno]  ) {


            $is_critical = true;

			//Nuestro último intento de salvar el sistema, probamos a visualizar un mensaje de error
			if(empty($_context) && class_exists('\Context') && \team\Context::getIndex()  > 1) {
				$builder = \team\Context::get('CONTROLLER_BUILDER');

				$result = '';
				if(isset($builder) ) {
					$result = $builder->error();
					echo $result;
					$is_critical = false;
				}

			}
		}else {
			//Es un fallo genérico que puede continuarse
			$is_critical = false;
		}


        \team\Debug::me($_context, "[{$namespace}][".$_errorcode."]: {$_errstr} ", $_errfile, $_errline);
		

		if($is_critical)
		   return \Team::Critical();
		else
			return true;
	}




	/**
		Cuando hay un error critico de PHP( ej: FATAL ) o cuando hay un error en un controller del primer contexto
		Finalmente cuando se acaba el proceso PHP( ya que es la única oportunidad que queda a veces para recoger errores )
	*/
	public  function critical($e = null) {

	    static $num_criticals  = 0;

		$error = error_get_last();
		$data = new \team\Data();
		$data->namespace = \team\Context::get('NAMESPACE');
		$critical = true;

		if(isset($error['type']) && $error['type'] >= 0) {
			$_errno = $error['type'];

			//Hemos podido llegar aquí por culpa de algun error en zona no-framework de algún usuario
			//y este error podría no ser crítico
			if(isset(self::$php_errors_code[$_errno]) ) {
				$data->code =  self::$php_errors_code[$_errno];
				$critical = !self::$php_errors_code[$_errno];
			}else {
				$data->code =   $error['type'];
			}

            $data->msg = $error['message'];
			$data->file = $error['file'];
			$data->line = $error['line'];

		}else if(isset($e) && $e instanceof \Throwable) {
			$data->msg = $e->getMessage();
			$data->line = $e->getLine();
			$data->file = $e->getFile();
			$data->code =  $e->getCode();				
		}else {

            //framework's halting
			\team\Context::close();
			return 	\Team::event('\team\halt', $data);;
		}


		if($critical) {
			$data->num_criticals =  ++$num_criticals;
		
			//Only one critical, please
			if($data->num_criticals >1 && empty($error) ) return false;
		}


    	\team\Debug::me("[{$data->namespace}][{$data->code}]: {$data->msg}",  '',  $data->file,  $data->line );

		if(!$critical) {
			return true;
		}

		//Asignamos un contextlevel para que quien lo recoja sepa si es o no main dónde se produjo el error
		$data->context  = new \team\Data( \team\Context::getContext() );
		$data->level = \team\Context::getIndex();
		if( \team\Context::get('out') != 'html' ) {
		
			$context_main = \team\Context::before();
			//Si el método main no es el que ha cascado, llamamos a su método critical para que lo arregle todo.
			$builder = \team\Context::get('CONTROLLER_BUILDER');

			if(isset($builder) ) {
				echo  $builder->getCriticalError($data);
				die();
			}
		}
		//Mala suerte, hemos llegado hasta aquí sin que la acción main pudiera hacer nada. Toca buscar ayuda(¿algún awaiter disponible?)
        $type = 'CRITICAL';
		echo \Team::event('\team\CRITICAL', $data,$type);

		return true;


	}
	
}
