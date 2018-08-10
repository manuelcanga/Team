<?php

namespace LesserPhp\Formatter;

/**
 * lesserphp
 * https://www.maswaba.de/lesserphp
 *
 * LESS CSS compiler, adapted from http://lesscss.org
 *
 * Copyright 2013, Leaf Corcoran <leafot@gmail.com>
 * Copyright 2016, Marcus Schwarz <github@maswaba.de>
 * Licensed under MIT or GPLv3, see LICENSE
 * @package LesserPhp
 */
class Lessjs extends Classic
{

    /**
     * Lessjs constructor.
     */
    public function __construct()
    {
        $this->setAssignOperator(': ');
        $this->setDisabledSingle(true);
        $this->setBreakSelectors(true);
        $this->setSelectorSeparator(',');
    }
}
