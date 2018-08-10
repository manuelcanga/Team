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

class ColorValue extends AbstractValue
{
    /**
     * @var int
     */
    private $red;

    /**
     * @var int
     */
    private $green;

    /**
     * @var int
     */
    private $blue;

    /**
     * @var float
     */
    private $alpha;

    /**
     * @inheritdoc
     */
    public function getCompiled()
    {
        $red   = round($this->red);
        $green = round($this->green);
        $blue  = round($this->blue);

        if ($this->alpha !== null && $this->alpha != 1) {
            return 'rgba(' . $red . ',' . $green . ',' . $blue . ',' . $this->alpha . ')';
        }

        $hex = sprintf("#%02x%02x%02x", $red, $green, $blue);

        if ($this->options['compressColors']) {
            // Converting hex color to short notation (e.g. #003399 to #039)
            if ($hex[1] === $hex[2] && $hex[3] === $hex[4] && $hex[5] === $hex[6]) {
                $hex = '#' . $hex[1] . $hex[3] . $hex[5];
            }
        }

        return $hex;
    }

    /**
     * @inheritdoc
     */
    public function initializeFromOldFormat(array $value)
    {
        $this->red   = $value[1];
        $this->green = $value[2];
        $this->blue  = $value[3];

        if (isset($value[4])) {
            $this->alpha = $value[4];
        }
    }
}
