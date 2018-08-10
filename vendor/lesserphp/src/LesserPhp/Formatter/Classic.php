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
class Classic implements FormatterInterface
{

    /**
     * @var string
     */
    private $indentChar = '  ';

    /**
     * @var string
     */
    private $break = "\n";

    /**
     * @var string
     */
    private $open = ' {';

    /**
     * @var string
     */
    private $close = '}';

    /**
     * @var string
     */
    private $selectorSeparator = ', ';

    /**
     * @var string
     */
    private $assignSeparator = ':';

    /**
     * @var string
     */
    private $openSingle = ' { ';

    /**
     * @var string
     */
    private $closeSingle = ' }';

    /**
     * @var bool
     */
    private $disableSingle = false;

    /**
     * @var bool
     */
    private $breakSelectors = false;

    /**
     * @var bool
     */
    private $compressColors = false;

    /**
     * @var int
     */
    private $indentLevel = 0;

    /**
     * @param int $n
     *
     * @return string
     */
    public function indentStr($n = 0)
    {
        return str_repeat($this->indentChar, max($this->indentLevel + $n, 0));
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return string string
     */
    public function property($name, $value)
    {
        return $name . $this->assignSeparator . $value . ';';
    }

    /**
     * @param $block
     *
     * @return bool
     */
    protected function isEmpty($block)
    {
        if (empty($block->lines)) {
            foreach ($block->children as $child) {
                if (!$this->isEmpty($child)) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * @param $block
     *
     * @return void
     */
    public function block($block)
    {
        if ($this->isEmpty($block)) {
            return;
        }

        $inner = $pre = $this->indentStr();

        $isSingle = !$this->disableSingle &&
            $block->type === null && count($block->lines) === 1;

        if (!empty($block->selectors)) {
            $this->indentLevel++;

            if ($this->breakSelectors) {
                $selectorSeparator = $this->selectorSeparator . $this->break . $pre;
            } else {
                $selectorSeparator = $this->selectorSeparator;
            }

            echo $pre . implode($selectorSeparator, $block->selectors);
            if ($isSingle) {
                echo $this->openSingle;
                $inner = '';
            } else {
                echo $this->open . $this->break;
                $inner = $this->indentStr();
            }
        }

        if (!empty($block->lines)) {
            $glue = $this->break . $inner;
            echo $inner . implode($glue, $block->lines);
            if (!$isSingle && !empty($block->children)) {
                echo $this->break;
            }
        }

        foreach ($block->children as $child) {
            $this->block($child);
        }

        if (!empty($block->selectors)) {
            if (!$isSingle && empty($block->children)) {
                echo $this->break;
            }

            if ($isSingle) {
                echo $this->closeSingle . $this->break;
            } else {
                echo $pre . $this->close . $this->break;
            }

            $this->indentLevel--;
        }
    }

    /**
     * @return string
     */
    public function getSelectorSeparator()
    {
        return $this->selectorSeparator;
    }

    /**
     * @param string $separator
     */
    protected function setSelectorSeparator($separator)
    {
        $this->selectorSeparator = $separator;
    }

    /**
     * @return bool
     */
    public function getCompressColors()
    {
        return $this->compressColors;
    }

    /**
     * @param bool $compress
     */
    protected function setCompressColors($compress)
    {
        $this->compressColors = $compress;
    }

    /**
     * @param bool $breakSelectors
     */
    protected function setBreakSelectors($breakSelectors)
    {
        $this->breakSelectors = $breakSelectors;
    }

    /**
     * @param bool $disableSingle
     */
    protected function setDisabledSingle($disableSingle)
    {
        $this->disableSingle = $disableSingle;
    }

    /**
     * @param string $breakChar
     */
    protected function setBreakChar($breakChar)
    {
        $this->break = $breakChar;
    }

    /**
     * @param string $openChar
     */
    protected function setOpenChar($openChar)
    {
        $this->open = $openChar;
    }

    /**
     * @param string $assignOperator
     */
    protected function setAssignOperator($assignOperator)
    {
        $this->assignSeparator = $assignOperator;
    }
}
