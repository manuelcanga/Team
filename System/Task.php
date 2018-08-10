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

namespace Team\System;

//Create a new helper function. E.g  Task("/web/list/Init", null);
if(!function_exists("Task") ) {
	function Task($task, $default = "") { return new \Team\System\Task($task, $default); }
}

/**
	Clase para la gestion de tareas o trabajos
	Es parecida a Filter, pero mientras que en Filter sólo se quiere que un dato sea variado
	en task lo que se pretende es que uno o más procedimientos realicen una tarea
	También es parecida a eventos, pero los eventos se encargan de gestionar situaciones especiales en el sistema
*/
class Task implements \ArrayAccess{
    use \Team\Data\Box;

	/** 	@type Array de callbacks
			@desc Lista de trabajadores 
	*/
	private static $workers = array();

	/**
		@type Namespace
		@desc Namespace de la tarea a realizar
	*/
	private $task;

	/**
		@type mixed|callable
		@defult si no es callable es el valor devueelto si ningún trabajador atendió la tarea
		Si es callable se llamará para obtener el valor por defecto si nadie atendió la tarea
	*/
	private $default;

	/**
		@type int|boolean
		@desc Indica si algún worker canceló la tarea
	*/
	private $canceled = 0;

	/**
		@type int|boolean
		@desc Indica si algún worker finalizó la tarea
	*/
	private $finished = 0;

	/**
		@type int|boolean
		@desc Indica si algún worker realizó la tarea
	*/
	private $done = 0;

	/**
		@type mixed
		@desc Resultado de la tarea 
	*/
	private $result = null;

	/** 
		Devolvemos el namespace de la tarea
	*/
	public function getName() {
	  return $this->task;
	}

	/**
		Devolvemos el resultado de la tarea
	*/
	public function getResult() {
		return $this->result;
	}

	/**
		Avisamos de cancelacion de la tarea, para evitar que siga su propagacion( OJO, se usará/llamará el valor por defecto ).
	*/
	public function cancel($_state = true) { $this->canceled = $_state; }

	/**
		Comprobabamos si se ha cancelado la tarea, para evitar que siga su propagacion( OJO, se usará/llamará el valor por defecto ).
	*/
	public function canceled() { return $this->canceled; }


	/**
		Avisamos de finalizacion de la tarea, para evitar que siga su propagacion.
	*/
	public function finish($_state = true) { $this->finished = $_state;  }

	/**
		Comprobabamos si se ha finalizado la tarea, para evitar que siga su propagacion.
	*/
	public function finished() { return $this->finished; }


	/**
		Avisamos de reaizado de la tarea, OJO, la propagación continua, aunque no se usará/llamará el valor por defecto
	*/
	public function done($_state = true) { $this->done = $_state;  }

	/**
		Comprobabamos si se ha reaizado la tarea, OJO, la propagación continua, aunque no se usará/llamará el valor por defecto
	*/
	public function isDone() { return $this->done; }

	/**
		Constructor de la clase.
		Instanciamos un objeto, guardando el namespace de la tarea
		@param Namespace $_event establece el nombre del evento ( en formato namespace )
	*/
	public function __construct($task, $default = null) {
			//Remove \\ of start. Eg: \Team\news\Insert ->  Team\news\Insert
			$this->task = trim($task, "\\");
			$this->default = $default;
	}


	/**
		Check if exists workers for a task
	*/
	public static function workerExists($task) {
		$task = trim($task, "\\");
		return isset(self::$workers[$task]);
	}

	/**
		Añadimos un trabajador a una tarea 
		@param namespace $task Tarea a realizar
		@param callable $worker Trabajador a añadir
		@param int $order Posición en la cadena de producción.
	*/
	public static function join($task, $worker, $order = 65) {

		$task = trim($task, "\\");
		if(!is_callable($worker,  $syntax_only = true) ) return ;

		$order = \Team\Data\Check::id($order);

		if(!isset(self::$workers[$task]) )
			self::$workers[$task] = [];

		//Vamos buscando un hueco libre para el trabajador a partir del orden que pidió
		for($max_order = 100; isset(self::$workers[$task][$order]) && $order < $max_order; $order++);
		
		//Guardamos el worker
		self::$workers[$task][$order] = $worker;
	}


	/**
		Se encarga de que se realize la tarea con los parámetros especficiados
	*/
	public function with( &...$params ) {
		return $this->transmit($params);
	}


	/**
		Se encarga de que alguien realize la tarea con los parámetros especficiados
        Si ninguno de ellos cancela la tarea o lo finaliza, se devuelve el resultado por defecto.
	*/
	public function transmit(& $params = array() ) {

		 $task = $this->task;

        if(self::workerExists($task) ) {

            ksort(self::$workers[$task]);
            
            foreach(self::$workers[$task] as $worker) {
				//mandamos la tarea al trabajador
				$worker = $worker->bindTo($this);

				$result =  $worker(...$params );

				if(isset($result) ) $this->result = $result;
				if($this->canceled() ) break;
				if($this->finished() ) return $this->result;
			}

		}


		if($this->isDone() ) {
            return $this->result;
        }



        //Si se especificó un valor por defecto( y este no era un callback), lo develvemos
		if(!is_callable($this->default) ) return $this->default;

		//Añadimos en el último lugar a la lista de parámetros el objeto de la tarea actual
		$params[] = $this;
		$params[] = $task;



		//Llamamos a la función por defecto.
		return $this->result =  ($this->default)( ...$params );
	}


}
 
