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
 * Licensed under MIT or GPLv3, see LICENSE
 * @package LesserPhp
 */
class NodeEnv
{
    public $seenNames;

    /**
     * @var \LesserPhp\NodeEnv
     */
    private $parent;

    /**
     * @var array
     */
    private $store = [];

    /**
     * @var \stdClass
     */
    private $block;

    /**
     * @var array|null
     */
    private $selectors;

    /**
     * @var array|null
     */
    private $arguments = [];

    /**
     * @var array
     */
    private $imports = [];

    /**
     * @return \LesserPhp\NodeEnv
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param \LesserPhp\NodeEnv $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return array
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @param array $store
     */
    public function setStore($store)
    {
        $this->store = $store;
    }

    /**
     * @param string $index
     * @param $value
     */
    public function addStore($index, $value)
    {
        $this->store[$index] = $value;
    }

    /**
     * @return \stdClass
     */
    public function getBlock()
    {
        return $this->block;
    }

    /**
     * @param string $block
     */
    public function setBlock($block)
    {
        $this->block = $block;
    }

    /**
     * @return array
     */
    public function getSelectors()
    {
        return $this->selectors;
    }

    /**
     * @param array $selectors
     */
    public function setSelectors($selectors)
    {
        $this->selectors = $selectors;
    }

    /**
     * @return mixed
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param mixed $arguments
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @param null $index
     *
     * @return array
     */
    public function getImports($index = null)
    {
        if ($index === null) {
            return $this->imports;
        }
        return $this->imports[$index];
    }

    /**
     * @param string $index
     * @param $value
     */
    public function addImports($index, $value)
    {
        $this->imports[$index] = $value;
    }
}
