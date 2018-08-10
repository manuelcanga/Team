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

class ListValue extends AbstractValue
{
    /**
     * @var string
     */
    private $delimiter;

    /**
     * @var AbstractValue[]
     */
    private $items;

    /**
     * @inheritdoc
     */
    public function getCompiled()
    {
        $compiled = [];
        foreach ($this->items as $item) {
            $compiled[] = $item->getCompiled();
        }

        return implode($this->delimiter, $compiled);
    }

    /**
     * @inheritdoc
     */
    public function initializeFromOldFormat(array $value)
    {
        $this->delimiter = $value[1];
        $this->items     = [];

        foreach ($value[2] as $item) {
            $this->items[] = self::factory($this->compiler, $this->coerce, $this->options, $item);
        }
    }
}
