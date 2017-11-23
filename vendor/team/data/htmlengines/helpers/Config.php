<?php
/**
 * Developed by Manuel Canga ( dev@trasweb.net )
 * Date: 5/01/17
 * Time: 16:51
 */

namespace team\data\htmlengines;


class Config  implements \ArrayAccess
{
    /* ------------------- ArrayAccess  ---------------------- */

    public function offsetUnset($offset){
        return \team\Context::delete($offset);
    }

    public function offsetExists($offset) {
        return  true;
    }

    public function   offsetGet($offset) {
        return \team\Context::get($offset, '');
    }


    public function  offsetSet($offset, $valor) {
        return \team\Context::get($offset, $valor);

    }

}