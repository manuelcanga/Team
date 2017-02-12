<?php
/**
 * Developed by Manuel Canga ( dev@trasweb.net )
 * Date: 11/01/17
 * Time: 9:11
 */

namespace team\types;

abstract class Type extends Base
{

    /** Debería de ser al reves */
    public function __construct($_origin = NULL, array $_options = [], $_defaults = []) {

        //Check if implements Box instead
        if($_defaults instanceof \team\types\Base ) {
            $this->data = $_defaults->get();
        }else if(is_array($_defaults) ) {
            $this->data = $_defaults;
        }

        $this->initialize($_origin, $_options);
    }

    protected function initialize($_origin = NULL, array $_options = []) {

    }

}