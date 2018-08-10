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
use LesserPhp\Compiler;
use LesserPhp\Library\Coerce;

abstract class AbstractValue
{
    /**
     * @var Compiler
     */
    protected $compiler;

    /**
     * @var Coerce
     */
    protected $coerce;

    /**
     * @var array
     */
    protected $options = [
        'numberPrecision' => null,
        'compressColors'  => false,
    ];

    /**
     * AbstractValue constructor.
     *
     * @param Compiler $compiler
     * @param Coerce   $coerce
     * @param array    $options
     */
    public function __construct(Compiler $compiler, Coerce $coerce, array $options = [])
    {
        $this->compiler = $compiler;
        $this->coerce   = $coerce;
        $this->options  = array_replace($this->options, $options);
    }

    /**
     * @param Compiler $compiler
     * @param Coerce   $coerce
     * @param array    $options
     * @param array    $value
     *
     * @return self
     */
    public static function factory(Compiler $compiler, Coerce $coerce, array $options, array $value)
    {
        $nameParts      = explode('_', $value[0]);
        $camelCase      = array_reduce($nameParts, function ($carry, $item) {
            return $carry . ucfirst($item);
        }, '');
        $valueClassName = 'LesserPhp\Compiler\Value\\' . $camelCase . 'Value';

        if (class_exists($valueClassName)) {
            $valueClass = new $valueClassName($compiler, $coerce, $options);
            if ($valueClass instanceof self) {
                $valueClass->initializeFromOldFormat($value);

                return $valueClass;
            }
        }

        throw new \UnexpectedValueException('unknown value type: ' . $value[0]);
    }

    /**
     * @return string
     */
    abstract public function getCompiled();

    /**
     * Initialize value from old array format.
     *
     * @param array $value
     *
     * @return void
     * @deprecated
     */
    abstract public function initializeFromOldFormat(array $value);
}
