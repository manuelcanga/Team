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
use LesserPhp\Block;

class Directive extends Block
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var mixed
     */
    public $value;

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return 'directive';
    }
}
