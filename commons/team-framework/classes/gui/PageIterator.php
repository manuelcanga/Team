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

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS 'AS IS' AND
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
namespace team\gui;


/**
	Simple iterator for pages( pagination )
*/
class PageIterator implements \ArrayAccess,  \Iterator, \Countable{
    use \team\data\Storage;

	protected $iteratorPage = 0;
	protected $index = 1;
	protected $currentPage = 1;

	function __construct($pagination) {
		if(isset($pagination['page']) )
			$this->currentPage = $pagination['page'];


		$this->data = $pagination;
		$this->data['goFirst'] = $this->goFirst();
		$this->data['goEnd'] = $this->goEnd();
		$this->data['goPrev'] = $this->goPrev();
		$this->data['goNext'] = $this->goNext();
	}

	
	/**
		Comprueba si es posible avanzar hacia la página primera
		@return false si no es posible avanzar hacia la primera página(porque ya estamos en ella ). Si se puede, devuelve la url de la primera página
	*/
	public function goFirst() {	
		if ( $this->start === 1 ) return false;
		return $this->getPagedUrl(['page' => null]);
	}


	/**
		Comprueba si es posible avanzar hacia la última página
		@return false si no es posible avanzar hacia la última página(porque ya estamos en ella ). Si se puede, devuelve la url de la última página
	*/
	public function goEnd() {	
		if ( $this->end === $this->pages ) return false;	

		return $this->getPagedUrl(['page' => $this->pages]);
	}

	/**
		Comprueba si es posible retroceder hacia la página previa
		@return false si no es posible retroceder hacia la anterior página(porque estamos en la primera ). Si se puede, devuelve la url de la anterior página
	*/
	public function goPrev() {	
		if ( $this->currentPage === 1) return false;
				
		return $this->getPagedUrl(['page' => $this->prev]);
	}

	/**
		Comprueba si es posible avanzar hacia la página siguiente
		@return false si no es posible avanzar hacia la siguiente página(porque estamos en la última ). Si se puede, devuelve la url de la página siguiente.
	*/
	public function goNext() {
		if ( $this->currentPage  === $this->pages ) return false;
				
		return $this->getPagedUrl(['page' => $this->next]);
	}


	/** -------------- Countable -------------- */
	/**
		Al hacer un count sobre la paginación se obtiene el número de páginas que hay
	*/
	public function count() {
		return $this->pages;
	}


	/** -------------- Iterator -------------- */



	/**
		Como valor siempre se devolverá el objeto de paginación. Así siempre podrá llamar fácilmente
		a los métodos. Si se desea imprimir el valor de la página actual, se lanza toString, que la mostrará 
	*/

	public function IteratorConfig() {
	 	$this->page = $this->iteratorPage;	
		$this->classes = $this->getClasses();


		if(1 === $this->iteratorPage) {
			$this->url = $this->getPagedUrl(['page' => null]);
		}else {
			$this->url = $this->getPagedUrl();
		}
	}

	 public function current (  ) { return $this; }

	public function key (  ) { return $this->getPagedUrl();	}

	//Pasamos a la siguiente página
	 public function next ( ) {	
		++$this->index;
	 	 ++$this->iteratorPage;	
		$this->iteratorConfig();

		return $this->iteratorPage;
	} 


	/**
		Construimos la paginación si aún no estaba
	*/
	public function rewind (  ) {
		$this->iteratorPage =  max($this->start,1);
		$this->iteratorConfig();
		$this->index = 1;
	}

	 public function valid (  ) { 
		$overflow =  $this->iteratorPage > $this->end; 
		if($overflow) {
			$this->iteratorPage = 0;
		}
		return ! $overflow;
	}

	/* ------------ ITERATOR HELPERS ---------------- */
	public function __toString() {
		return ''.$this->iteratorPage;
	}

	public function getPagedUrl($vars = []) {
		return \team\Url::to($this->baseUrl, $vars + $this->data);
	}

	public function getClasses($extra = '') {
		$classes = '';
		if($this->isCurrent() ) {
			$classes .= 'current ';
		}
		if($this->isFirst()) {
			$classes .= 'first ';
		}
		if($this->isLast()) {
			$classes .= 'last ';
		}

		$classes .= ' page-'.$this->iteratorPage;
		$classes .= ' pos'.$this->index;
		if(!empty($extra) ) {
			$classes .= ' '.$extra;
		}

		return trim($classes);
	}

	public function getUrl() {
	    return $this->getPagedUrl();
    }

	//Comprobamos si la página actual es la primera
	public function isFirst()  {	return $this->first = ( $this->iteratorPage == $this->start );}
	//Comprobamos si la página actual es la última
	public function isLast() {	return $this->last =  ( $this->iteratorPage == $this->end );	}
	//Comprobamos si la página actual es la actual
	public function isCurrent() { return  $this->current = ( $this->iteratorPage === $this->currentPage );	}
}
