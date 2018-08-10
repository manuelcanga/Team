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

class DirectiveProperty extends \LesserPhp\Property implements CanCompile
{
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
     * @inheritdoc
     */
    public function compile(\LesserPhp\Compiler $compiler)
    {
        $cv = $compiler->compileValue($compiler->reduce($this->getValue()));

        // Format: '@name value;'
        return $compiler->getVPrefix() . $this->getName() . ' ' . $cv . ';';
    }
}
