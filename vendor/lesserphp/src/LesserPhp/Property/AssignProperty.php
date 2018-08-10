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

class AssignProperty extends \LesserPhp\Property
{
    /**
     * @param string $prefixToCheck Single character to check.
     *
     * @return bool
     */
    public function nameHasPrefix($prefixToCheck)
    {
        return ($this->getName() && $this->getName()[0] === $prefixToCheck);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getValue1();
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->getValue2();
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->setValue2($value);
    }
}
