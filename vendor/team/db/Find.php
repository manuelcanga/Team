<?php
/**
 * Creado por Manuel Canga
 * Date: 18/09/16
 * Time: 10:22
 */

namespace team\db;


class Find implements \ArrayAccess{
    use \team\data\Storage,  \team\db\Database;

    protected $model= null;
    protected $elements = [];

    protected $queryLog = '';
    protected $select = '*';
    protected $from = NULL;
    protected $where = null;
    protected $groupBy = null;
    protected $having = null;
    protected $order = 'DESC';
    protected $orderBy = null;
    protected $limit;
    protected $offset;



    public function search($customizer = null, ...$args) {
        $this->commons($customizer, $args);

        if($customizer && method_exists($this,  $customizer) ) {
            $this->$customizer(...$args);
        }

        $this->custom($customizer, $args);

        return $this->elements = $this->findElements();
    }

    /** -------------------- Events  ------------------ */


    /** 					*/
    public function onInitialize($data) {
        $this->import($data);
    }

    /** Before Customizer */
    public function commons() {

    }

    /** After customizer but before build */
    public function custom() { }

    /** When elements are found */
    protected function onFound($elements){

        if($this->model) {
            return new \team\db\Collection($elements, $this->model);
        }

        return $elements;
    }

    /** -------------------- SETTERS / GETTERS  ------------------ */


    /**
    Añade un Model que gestione el modelo de datos del paginador
    @param string $model objeto de active record

    @TODO: Cambiar por Model.
     */
    public function setModel( $model = null) {
        $this->model = is_object($model)? get_class($model) : $model;

        if(!$this->from && ($this->model)::TABLE) {
            $this->from = ($this->model)::TABLE;
        }

        if(!$this->orderBy && ($this->model)::ID) {
            $this->orderBy = ($this->model)::ID;
        }


        return $this;
    }

    /** -------------------- SETTERS / GETTERS QUERY ------------------ */
    public function setSelect($_select = null, $overwrite = true) {
        if($_select != null && !$overwrite)
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
        $this->orderBy = \team\data\Check::key($_order_by, null);
        $this->setOrder($_order);

        return $this;
    }

    public function setFrom($_from = null, $_full = false) {

        if($this->from && !$_full) {
            $this->from .= ', '.$_from;
        }else {
            $this->from = $_from;
        }

        return $this;
    }

    public function setWhere($_where = null, $_full = false) {

        if($_full) {
            $this->where[] = ['0' => $_where];
        }else {
            $this->where[] = $_where;
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



    /** -------------------- SEARCHING ------------------ */


    protected function findElements() {
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

        $database = $this->getDatabase();
        return  $this->onFound($database->get($query, $this->data) );

    }


} 