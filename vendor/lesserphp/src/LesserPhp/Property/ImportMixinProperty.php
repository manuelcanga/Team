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

class ImportMixinProperty extends \LesserPhp\Property
{
    /**
     * @return string
     */
    public function getType()
    {
        return 'import_mixin';
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getValue1();
    }
}
