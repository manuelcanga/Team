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
 * @author  Stefan Pöhner <github@poe-php.de>
 *
 * @package LesserPhp
 */

abstract class Property implements \ArrayAccess
{
    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var int|null
     */
    protected $pos;

    /**
     * @var mixed
     */
    protected $value1;

    /**
     * @var mixed
     */
    protected $value2;

    /**
     * @var mixed
     */
    protected $value3;

    /**
     * Property constructor.
     *
     * @param int|null $pos
     * @param mixed    $value1
     */
    public function __construct($pos, $value1)
    {
        $this->pos    = $pos;
        $this->value1 = $value1;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return strtolower(str_replace([__NAMESPACE__ . '\\', '\\', 'Property'], '', get_class($this)));
    }

    /**
     * @return int|null
     */
    public function getPos()
    {
        return $this->pos;
    }

    /**
     * @return bool
     */
    public function hasPos()
    {
        return ($this->pos !== null);
    }

    /**
     * @return mixed
     */
    protected function getValue1()
    {
        return $this->value1;
    }

    /**
     * @param mixed $value
     */
    protected function setValue1($value)
    {
        $this->value1 = $value;
    }

    /**
     * @return mixed
     */
    protected function getValue2()
    {
        return $this->value2;
    }

    /**
     * @param mixed $value
     */
    protected function setValue2($value)
    {
        $this->value2 = $value;
    }

    /**
     * @return mixed
     */
    protected function getValue3()
    {
        return $this->value3;
    }

    /**
     * @param mixed $value
     */
    protected function setValue3($value)
    {
        $this->value3 = $value;
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        throw new \RuntimeException('Array access is deprecated! ');
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        throw new \RuntimeException('Array access is deprecated! ');
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        throw new \RuntimeException('Array access is deprecated! ');
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        throw new \RuntimeException('Array access is deprecated! ');
    }

    /**
     * @param string     $type
     * @param int|null   $pos
     * @param mixed      $value1
     * @param mixed|null $value2
     * @param mixed|null $value3
     *
     * @return self
     */
    public static function factory($type, $pos, $value1, $value2 = null, $value3 = null)
    {
        $type      = implode('', array_map('ucfirst', explode('_', $type)));
        $className = __NAMESPACE__ . '\Property\\' . ucfirst($type) . 'Property';

        if (!class_exists($className)) {
            throw new \UnexpectedValueException("Unknown property type: $type");
        }

        $property = new $className($pos, $value1);
        if (!$property instanceof self) {
            throw new \RuntimeException("$className must extend " . self::class);
        }

        $property->setValue2($value2);
        $property->setValue3($value3);

        return $property;
    }

    /**
     * @param array    $prop
     * @param int|null $pos
     *
     * @return Property
     */
    public static function factoryFromOldFormat(array $prop, $pos = null)
    {
        /*
         * A property array looks like this:
         * [-1] => optional position
         * [0]  => type
         * [1]  => value
         * [2]  => optional more information
         * [3]  => optional more information
         *
         * 0 and 1 are always present
         * only comments have the simplest form with only 1 value
         */

        if (!isset($prop[0], $prop[1])) {
            throw new \UnexpectedValueException('Too few property information given.');
        }

        $type   = $prop[0];
        $value1 = $prop[1];
        $value2 = null;
        $value3 = null;

        if (isset($prop[2])) {
            $value2 = $prop[2];
        }
        if (isset($prop[3])) {
            $value3 = $prop[3];
        }

        return self::factory($type, $pos, $value1, $value2, $value3);
    }
}
