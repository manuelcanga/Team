<?php
namespace LesserPhp;

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

class Block
{
    /**
     * @var Block|null
     */
    public $parent;

    /**
     * @var string|null
     */
    public $type;

    /**
     * @var int
     */
    public $id;

    /**
     * @var bool
     */
    public $isVararg = false;

    /**
     * @var array|null
     */
    public $tags;

    /**
     * @var Property[]
     */
    public $props = [];

    /**
     * @var Block[]
     */
    public $children = [];

    /**
     * Position of this block.
     *
     * @var int
     */
    public $count;

    /**
     * Add a reference to the parser so
     * we can access the parser to throw errors
     * or retrieve the sourceName of this block.
     *
     * @var Parser
     */
    public $parser;

    /**
     * Block constructor.
     *
     * @param Parser     $parser
     * @param int        $id
     * @param int        $count
     * @param array|null $tags
     * @param Block|null $parent
     */
    public function __construct(Parser $parser, $id, $count, array $tags = null, Block $parent = null)
    {
        $this->parser = $parser;
        $this->id     = $id;
        $this->count  = $count;
        $this->type   = $this->getType();
        $this->tags   = $tags;
        $this->parent = $parent;
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return null;
    }

    /**
     * @param Parser      $parser
     * @param int         $id
     * @param int         $count
     * @param string|null $type
     * @param array|null  $tags
     * @param Block|null  $parent
     *
     * @return Block
     */
    public static function factory(Parser $parser, $id, $count, $type = null, array $tags = null, Block $parent = null)
    {
        if ($type === null) {
            $className = self::class;
        } else {
            $className = __NAMESPACE__ . '\Block\\' . ucfirst($type);
        }

        if (!class_exists($className)) {
            throw new \UnexpectedValueException("Unknown block type: $type");
        }

        $block = new $className($parser, $id, $count, $tags, $parent);

        return $block;
    }
}
