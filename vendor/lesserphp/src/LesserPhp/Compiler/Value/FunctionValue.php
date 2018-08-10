<?php
namespace LesserPhp\Compiler\Value;

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
 * @package LesserPhp
 */

class FunctionValue extends AbstractValue
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $args;

    /**
     * @inheritdoc
     */
    public function getCompiled()
    {
        return $this->name . '(' . $this->compiler->compileValue($this->args) . ')';
    }

    /**
     * @inheritdoc
     */
    public function initializeFromOldFormat(array $value)
    {
        $this->name = $value[1];
        $this->args = $value[2];
    }
}
