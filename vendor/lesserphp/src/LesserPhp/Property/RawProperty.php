<?php

namespace LesserPhp\Property;

/**
 * lesserphp
 * https://www.maswaba.de/lesserphp
 *
 * LESS CSS compiler, adapted from http://lesscss.org
 *
 * Copyright 2013, Leaf Corcoran <leafot@gmail.com>
 * Copyright 2016, Marcus Schwarz <github@maswaba.de>
 * Copyright 2017, Stefan Pöhner <github@poe-php.de>
 * Licensed under MIT or GPLv3, see LICENSE
 *
 * @author  Stefan Pöhner <github@poe-php.de>
 *
 * @package LesserPhp
 */

class RawProperty extends \LesserPhp\Property implements CanCompile
{
    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->getValue1();
    }

    /**
     * @inheritdoc
     */
    public function compile(\LesserPhp\Compiler $compiler)
    {
        unset($compiler);

        return $this->getValue();
    }
}
