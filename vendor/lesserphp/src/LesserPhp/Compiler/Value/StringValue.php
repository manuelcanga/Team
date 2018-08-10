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

class StringValue extends AbstractValue
{
    /**
     * @var string
     */
    private $delimiter;

    /**
     * @var array|string
     */
    private $content;

    /**
     * @inheritdoc
     */
    public function getCompiled()
    {
        $content = $this->content;
        foreach ($content as &$part) {
            if (is_array($part)) {
                $part = $this->compiler->compileValue($part);
            }
        }

        return $this->delimiter . implode($content) . $this->delimiter;
    }

    /**
     * @inheritdoc
     */
    public function initializeFromOldFormat(array $value)
    {
        $this->delimiter = $value[1];
        $this->content   = $value[2];
    }
}
