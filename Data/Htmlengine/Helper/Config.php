<?php
/**
 * Developed by Manuel Canga ( dev@trasweb.net )
 * Date: 5/01/17
 * Time: 16:51
 */

namespace Team\Data\Htmlengine\Helper;


class Config  implements \ArrayAccess
{
    /* ------------------- ArrayAccess  ---------------------- */

    public function offsetUnset($offset){
        return \Team\System\Context::delete($offset);
    }

    public function offsetExists($offset) {
        return  true;
    }

    public function   offsetGet($offset) {
        return \Team\System\Context::get($offset, '');
    }


    public function  offsetSet($offset, $valor) {
        return \Team\System\Context::get($offset, $valor);

    }

}