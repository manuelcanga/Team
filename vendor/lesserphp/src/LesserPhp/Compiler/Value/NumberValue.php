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

class NumberValue extends AbstractValue
{
    /**
     * @var float
     */
    private $number;

    /**
     * @var string
     */
    private $unit;

    /**
     * @inheritdoc
     */
    public function getCompiled()
    {
        $num = $this->number;
        if (isset($this->options['numberPrecision'])) {
            $num = round($num, $this->options['numberPrecision']);
        }

        return $num . $this->unit;
    }

    /**
     * @inheritdoc
     */
    public function initializeFromOldFormat(array $value)
    {
        $this->number = $value[1];
        $this->unit   = $value[2];
    }
}
