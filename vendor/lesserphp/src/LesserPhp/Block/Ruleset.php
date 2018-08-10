<?php
namespace LesserPhp\Block;

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

class Ruleset extends Directive
{
    /**
     * @inheritdoc
     */
    public function getType()
    {
        // this deliberately returns null
        // the concept of a ruleset block does not exists, but it behaves like a directive without the directive type
        return null;
    }
}
