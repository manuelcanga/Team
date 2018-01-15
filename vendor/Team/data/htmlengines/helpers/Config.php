<?php
/**
 * Developed by Manuel Canga ( dev@trasweb.net )
 * Date: 5/01/17
 * Time: 16:51
 */

namespace Team\data\htmlengines;


class Config  implements \ArrayAccess
{
    /* ------------------- ArrayAccess  ---------------------- */

    public function offsetUnset($offset){
        return \Team\system\Context::delete($offset);
    }

    public function offsetExists($offset) {
        return  true;
    }

    public function   offsetGet($offset) {
        return \Team\system\Context::get($offset, '');
    }


    public function  offsetSet($offset, $valor) {
        return \Team\system\Context::get($offset, $valor);

    }

}