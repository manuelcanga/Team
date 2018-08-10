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

class RawColorValue extends AbstractValue
{
    /**
     * @var string
     */
    private $value;

    /**
     * @inheritdoc
     */
    public function getCompiled()
    {
        if ($this->options['compressColors']) {
            return $this->compiler->compileValue($this->coerce->coerceColor(['raw_color', $this->value]));
        }

        return $this->value;
    }

    /**
     * @inheritdoc
     */
    public function initializeFromOldFormat(array $value)
    {
        $this->value = $value[1];
    }
}
