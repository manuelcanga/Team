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

require_once(__DIR__.'/PageIterator.php');


class Pagination implements \ArrayAccess,  \Iterator{
    use \team\data\Storage,  \team\db\Database;


	protected $range = 4;

	protected $urlBase = '/';

	protected $withPagination = true;
	protected $elementsForPage = 10;
	protected $currentPage = 1;

	protected $start = 1;
	protected $prev = null;
	protected $next = null;
	protected $end = 1;
	protected $pages = 1;
	protected $count = -1;
	protected $limit;
	protected $offset;


	protected $queryLog = '';
	protected $select = '*';
	protected $from = NULL;
	protected $where = null;
	protected $groupBy = null;
	protected $having = null;
	protected $order = 'DESC';
	protected $orderBy = null;


	protected $elements = [];
	protected $pagination = null;
	protected $collection = [];

	protected $model= null;
	protected $GUI = null;

	
	/** Params for url */
	public $url = null;
	/** Url to check */
	public $urlToCheck = null;

	 function __construct( $_elements_for_page = 10,  $current_page = 1, $data = [] ) {

        if(\team\Gui::checkUserAgent('mobile')) {
            $this->range = 2;
        }

		$this->setElementsForPage($_elements_for_page);
        $this->setCurrentPage($current_page);
		$this->data = [];
		$this->url = new \team\Data($data);
		$this->GUI = \team\Context::get('CONTROLLER');

		$base_url = \team\Context::get('_SELF_');
		if(!empty($url))  {
			$this->setBaseUrl($base_url.':page');
		}

         $current_url = \team\Context::get('URL');
         if(!empty($current_url)){
             $this->setUrlToCheck($current_url);
         }

		$this->onInitialize($data);
	}

	/** 					*/
	public function onInitialize($data) {
        $this->import($data);
	}

	/** Before Customizer */
	public function commons() {

    }

	/** After customizer but before build */
	public function custom() { }

	/** After build and when create pagination */
	public function onBuild($data, $collection) { 
			$this->collection=$collection;
			return new \team\gui\PageIterator($data);
	}

	/**
		Añade un Model que gestione el modelo de datos del paginador
		@param team\db\Model $model objeto de active record

		@TODO: Cambiar por Model. 
	*/
	public function setModel(\team\db\Model $model = null) {
		$this->model = $model;

		return $this;
	}

	public function setGUI(\team\Controller $GUI = null) {
		$this->GUI = $GUI;
	}

    public function setUrlToCheck($url) {
        $this->urlToCheck = $url;
    }

	public function setBaseUrl($_url) {
		$this->urlBase= $_url;
		return $this;
	}


	public function parseUrl($url,&$filtros = []) {
		if(\team\Url::match($this->urlToCheck,  $url, $filtros) ) {
			 $this->import($filtros);
			return true;
		}else {
			return false;
		}
	}



	protected function build($customizer = null) {
		if(isset($this->pagination))  return $this->pagination;

		$this->commons();

		if($customizer && method_exists($this,  $customizer) ) {
	       $this->$customizer();
		} 

		$this->custom($customizer);

		$this->buildElements();

		if($this->pages>1 && $this->count >=1) {
			$this->withPagination = true;
			$this->prev = $this->getPagePrev();
			$this->next = $this->getPageNext();
			$this->buildLimits();
			$this->start =$this->getStart();
			$this->end =  $this->getEnd();

		}else {
			$this->withPagination = false;
		}


		 $collection = [
			"withPagination" 	  => $this->withPagination,
			"elements" 			  => $this->elements,
			"offset" 			  => $this->offset,
			'limit'			  	  => $this->limit,
			'numElements'		  => $this->count,
		  	'pages'	  			  => $this->pages,
		  	'page'	  			  => $this->currentPage,
		  	'next'	 	  		  => $this->next,
		  	'prev'		  		  => $this->prev,
		  	'start'	  		  	  => $this->start,
		  	'end'	 			  => $this->end,
		  	'urlBase'	 		  => $this->urlBase,
			'classes' 			  => [],
			'url'				  => ''
		];



		return $this->pagination = $this->onBuild($collection + $this->url->getData(), $collection, $customizer);
	}

	public function getCollection() {
		return $this->collection;
	}

	public function getPagination($custom =null) {
		if(isset($this->pagination) ) 
			return $this->pagination;
		else
			return $this->build($custom);

	}


	public function debug($title='Collection') {
		\team\Debug::me($this->queryLog, $title.' Query');
	}



	/** -------------------- SETTERS / GETTERS Elements ------------------ */


	public function setElementsForPage($_elements_for_page = 'all') {
        if( 'all' !== $_elements_for_page) {
		    $this->elementsForPage = \team\Check::id($_elements_for_page, $this->elementsForPage);
        }else
            $this->elementsForPage = 'all';

		return $this;

	}
	public function getElements($custom = null) {
		if(!$this->pagination) {
			$this->build($custom);
		}

		return $this->elements;
	}

    /**
     *  Para casos que sólo se quiera un elemento por página o sólo se quiera devolver el primer elemento */
    public function getElement() {
      $elements = $this->getElements();

      if(1 === $this->elementsForPage && !empty($elements) ) {
          return $elements->first();
      }

      return $elements;
    }

	public function getItems() {
		return $this->getElements();
	}

	/**
		Total de elementos
	*/
	public function getcount() { return $this->count;	}
	public function setCount($_num = 0) {
		$this->count = Check::id($_num,0);
		return $this;
	}


	/** -------------------- SETTERS / GETTERS PAGES ------------------ */

	public function putPage($_currentPage) {
		$this->setCurrentPage($_currentPage);
		return $this;
	}


	public function setCurrentPage($_currentPage) {
		$this->currentPage =  \team\Check::id($_currentPage, 1);
		return $this;
	}

	public function getCurrentPage() {
		return $this->currentPage;
	}

	//Pagina desde la que empezaremos a mostrar la paginación Ej: 5 6 7 8 |9| 10 11 12 13 . este caso 5
	public function getStart() {
		return $this->start =  \team\Check::id($this->start, 1);
	}

	public function getEnd() {
		return $this->end =  \team\Check::id($this->end, $this->pages);
	}

	//Pagina hasta la que mostraremos la paginación Ej: 5 6 7 8 |9| 10 11 12 13 .  en este caso 13
	public function buildLimits() {
		$range = $this->range;
		if($this->currentPage > 10) {
			$range--;
			if($this->currentPage > 100) {
				$range--;
			}
		}

		$this->start =  \team\Check::id($this->currentPage -  $range, 1);
		$max_range = ($range*2)+1;


		if($this->currentPage <=  ($this->range +1) )
			$end =  $max_range;
		else
			$end = $this->currentPage + $range;

		if($end > $this->pages) {
			$end = min($this->pages, $end);
			$this->start = max($end - $max_range, 1);

		}


		 $this->end = $end;
	}

	public function getPageNext() {
		if($this->currentPage < $this->pages) 
			return $this->currentPage+1;
		else
			return  $this->pages;
	}


	public function getPagePrev() {
		if($this->currentPage > 1) 
			return $this->currentPage-1;
		else
			return  1;
	}




	/** -------------------- SETTERS / GETTERS QUERY ------------------ */
	public function setSelect($_select = null) {
		if($_select != null) 
			$this->select .= ", ".$_select;
		else
			$this->select = $_select;
		return $this;
	}

	public function setOrder($_order) {
		if(!isset($_order) ) {
			$this->order = "";
		}else if($_order == "ASC" || $_order == "DESC")
			$this->order = $_order;
		else
			$this->order = "DESC";

	}

	public function setOrderBy($_order_by, $_order = 'DESC'){
		$this->orderBy = \team\Check::key($_order_by, null);
		$this->setOrder($_order);

		return $this;
	}

	public function setFrom($_from = null) {
		$this->from = $_from;

		return $this;
	}

	public function setWhere($_where = null, $_full = 0) {

		if($this->where != NULL && $_where != null && !$_full )
			$this->where .= " AND ( $_where ) ";
		else if($_where != NULL )
			$this->where = "( $_where ) ";
		else {
			$this->where = '';
		}

		return $this;
	}

	public function getLimit() { return $this->limit; }
	public function getOffset() { return $this->offset; }

	/** -------------------- BUILDING QUERIES ------------------ */
	public function buildSelect() { return $this->select;	}
	public function buildWhere() {return $this->where;}
	public function buildGroupBy() {return $this->groupBy;}
	public function buildHaving() {return $this->having;}
	public function buildFrom() {	return $this->from;	}
    public function buildOrder() {return $this->order;}
    public function buildOrderBy() {return $this->orderBy.' '.$this->buildOrder();}



	/** -------------------- BUILDING Elements ------------------ */
	protected function buildElements() {
		/** Obtenemos el número de elementos que queremos paginar */

   		 $this->count = $this->buildCount();

		//No hay elementos
		if($this->count == 0) {
			$this->pages = 0;
			return $this->components = null;
		}

		/** calculamos el número de páginas totales que hay de elementos */
		if( $this->elementsForPage  === 'all')  {
			//Si se ha decidido que no haya paginación( es decir, todos los elemenos en una misma página )
			$this->pages = 1;
			$this->currentPage = 1;

		}else {
			if(!$this->elementsForPage) {
				$this->pages = 1;
			}else {
				$this->pages = \Check::id(ceil($this->count/$this->elementsForPage), 1);
			}

			/** Validamos que la pagina actual sea mayor o igual que 1 y menor o igual que el número máximo de paginas */
			if(!$this->currentPage) $this->currentPage = 1;
			if($this->currentPage >= $this->pages ) $this->currentPage = $this->pages;


			if(!isset($this->offset) )
				$this->offset = ($this->currentPage-1)*$this->elementsForPage;


			if(!isset($this->limit) ) {
				//Si estamos en la última página
				if($this->currentPage == $this->pages) {

					$this->limit = $this->count - ($this->currentPage-1)*$this->elementsForPage;
					//El techo es el pico de elementos que queden por mostrar

				}else {
					//El techo de los elementos es el número de elementos por pagina
					$this->limit = $this->elementsForPage;
				}
			}

		}



		return  $this->buildPagedElements();


	}


	/** Obtenemos el número de elementos */
	function buildCount() {
		if(	$this->count != -1 ) $this->count;


		$database = $this->getDatabase();

		$query = [
			'select' => 'count(*) as total',
			'from' => $this->buildFrom(),
			'where' => $this->buildWhere()
		];


		//si hay group_by, no podemos contar los elementos con count tal y como está.
		$group_by = $this->buildGroupBy();
		if(!empty($group_by) ) {
		   $query['select'] = "count(distinct $group_by) as total";

			$having =  $this->buildHaving();
			if(!empty($having) ) {
				if(empty($query['where'] )) {
					$query['where'] = $having;
				}else {
					$query['where']  = "( {$query['where']} ) AND ( $having ) ";
				}
			}
		}

		$this->queryLog = $query;

		$result = $database->get($query, $this->data);

		if(count($result) ) {
			$this->count = $result[0]['total'];
		}else {
			$this->count = 0;
		}


		return $this->count;
	}


	protected function buildPagedElements() {

		$query = [
			'select' => $this->buildSelect(),
			'from' => $this->buildFrom(),
			'where' => $this->buildWhere(),
			'group_by' => $this->buildGroupBy(),
			'having' => $this->buildHaving(),
			'order_by' => $this->buildOrderBy(),
			'limit' => $this->limit,
			'offset' => $this->offset,
		];


		$this->queryLog = $query;

		
		if($this->model) {
			return $this->elements = $this->model->findAll($query, $this->data);
		}else {
			$database = $this->getDatabase();
			return $this->elements = $database->get($query, $this->data);
		}
	}

	public function getPagedUrl($vars = []) {
		return \team\Url::to($this->urlBase, $vars + $this->url->getData() );
	}

}
