<?php

require __DIR__ . '/vendor/autoload.php';

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

/**
 * The LESS compiler and parser.
 *
 * Converting LESS to CSS is a three stage process. The incoming file is parsed
 * by `lessc_parser` into a syntax tree, then it is compiled into another tree
 * representing the CSS structure by `lessc`. The CSS tree is fed into a
 * formatter, like `lessc_formatter` which then outputs CSS as a string.
 *
 * During the first compile, all values are *reduced*, which means that their
 * types are brought to the lowest form before being dump as strings. This
 * handles math equations, variable dereferences, and the like.
 *
 * The `parse` function of `lessc` is the entry point.
 *
 * In summary:
 *
 * The `lessc` class creates an instance of the parser, feeds it LESS code,
 * then transforms the resulting tree to a CSS tree. This class also holds the
 * evaluation context, such as all available mixins and variables at any given
 * time.
 *
 * The `lessc_parser` class is only concerned with parsing its input.
 *
 * The `lessc_formatter` takes a CSS tree, and dumps it to a formatted string,
 * handling things like indentation.
 */
class lessc extends \LesserPhp\Compiler
{

    // for compatibilty reasons with older copies of lessphp
}
