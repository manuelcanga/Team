<?php

namespace LesserPhp\Library;

use LesserPhp\Color\Converter;
use LesserPhp\Compiler;
use LesserPhp\Exception\GeneralException;

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
class Functions
{

    /**
     * @var \LesserPhp\Library\Assertions
     */
    private $assertions;

    /**
     * @var \LesserPhp\Library\Coerce
     */
    private $coerce;

    /**
     * @var \LesserPhp\Compiler
     */
    private $compiler;

    /**
     * @var \LesserPhp\Color\Converter
     */
    private $converter;

    public static $TRUE = ["keyword", "true"];
    public static $FALSE = ["keyword", "false"];
    public static $lengths = ["px", "m", "cm", "mm", "in", "pt", "pc"];
    public static $times = ["s", "ms"];
    public static $angles = ["rad", "deg", "grad", "turn"];
    public static $lengths_to_base = [1, 3779.52755906, 37.79527559, 3.77952756, 96, 1.33333333, 16];


    /**
     * Functions constructor.
     *
     * @param \LesserPhp\Library\Assertions $assertions
     * @param \LesserPhp\Library\Coerce     $coerce
     * @param \LesserPhp\Compiler           $compiler
     * @param \LesserPhp\Color\Converter    $converter
     */
    public function __construct(Assertions $assertions, Coerce $coerce, Compiler $compiler, Converter $converter)
    {
        $this->assertions = $assertions;
        $this->coerce = $coerce;
        $this->compiler = $compiler; // temporary solution to get it working
        $this->converter = $converter;
    }

    /**
     * @param array $args
     *
     * @return array
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function pow(array $args)
    {
        list($base, $exp) = $this->assertions->assertArgs($args, 2, 'pow');

        return [
            'number',
            pow($this->assertions->assertNumber($base), $this->assertions->assertNumber($exp)),
            $args[2][0][2],
        ];
    }

    /**
     * @return float
     */
    public function pi()
    {
        return M_PI;
    }

    /**
     * @param array $args
     *
     * @return array
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function mod(array $args)
    {
        list($a, $b) = $this->assertions->assertArgs($args, 2, 'mod');

        return ['number', $this->assertions->assertNumber($a) % $this->assertions->assertNumber($b), $args[2][0][2]];
    }

    /**
     * @param array $color
     *
     * @return int
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function red(array $color)
    {
        $color = $this->coerce->coerceColor($color);
        if ($color === null) {
            throw new GeneralException('color expected for red()');
        }

        return $color[1];
    }

    /**
     * @param array $color
     *
     * @return int
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function green(array $color)
    {
        $color = $this->coerce->coerceColor($color);
        if ($color === null) {
            throw new GeneralException('color expected for green()');
        }

        return $color[2];
    }

    /**
     * @param array $color
     *
     * @return int
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function blue(array $color)
    {
        $color = $this->coerce->coerceColor($color);
        if ($color === null) {
            throw new GeneralException('color expected for blue()');
        }

        return $color[3];
    }

    /**
     * @param array $args
     *
     * @return array
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function convert(array $args)
    {
        list($value, $to) = $this->assertions->assertArgs($args, 2, 'convert');

        // If it's a keyword, grab the string version instead
        if (is_array($to) && $to[0] === 'keyword') {
            $to = $to[1];
        }

        return $this->convertMe($value, $to);
    }

    /**
     * @param array $num
     *
     * @return array
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function abs(array $num)
    {
        return ['number', abs($this->assertions->assertNumber($num)), $num[2]];
    }

    /**
     * @param array $args
     *
     * @return int
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function min(array $args)
    {
        $values = $this->assertions->assertMinArgs($args, 1, 'min');

        $firstFormat = $values[0][2];

        $minIndex = 0;
        $minValue = $values[0][1];

        foreach ($values as $a => $value) {
            $converted = $this->convertMe($value, $firstFormat);

            if ($converted[1] < $minValue) {
                $minIndex = $a;
                $minValue = $value[1];
            }
        }

        return $values[$minIndex];
    }

    /**
     * @param array $args
     *
     * @return int
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function max(array $args)
    {
        $values = $this->assertions->assertMinArgs($args, 1, 'max');

        $firstFormat = $values[0][2];

        $maxIndex = 0;
        $maxValue = $values[0][1];

        foreach ($values as $a => $value) {
            $converted = $this->convertMe($value, $firstFormat);

            if ($converted[1] > $maxValue) {
                $maxIndex = $a;
                $maxValue = $value[1];
            }
        }

        return $values[$maxIndex];
    }

    /**
     * @param $num
     *
     * @return float
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function tan($num)
    {
        return tan($this->assertions->assertNumber($num));
    }

    /**
     * @param $num
     *
     * @return float
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function sin($num)
    {
        return sin($this->assertions->assertNumber($num));
    }

    /**
     * @param $num
     *
     * @return float
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function cos($num)
    {
        return cos($this->assertions->assertNumber($num));
    }

    /**
     * @param $num
     *
     * @return array
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function atan($num)
    {
        $num = atan($this->assertions->assertNumber($num));

        return ['number', $num, 'rad'];
    }

    /**
     * @param $num
     *
     * @return array
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function asin($num)
    {
        $num = asin($this->assertions->assertNumber($num));

        return ['number', $num, 'rad'];
    }

    /**
     * @param $num
     *
     * @return array
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function acos($num)
    {
        $num = acos($this->assertions->assertNumber($num));

        return ['number', $num, 'rad'];
    }

    /**
     * @param $num
     *
     * @return float
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function sqrt($num)
    {
        return sqrt($this->assertions->assertNumber($num));
    }

    /**
     * @param $value
     *
     * @return mixed
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function extract($value)
    {
        list($list, $idx) = $this->assertions->assertArgs($value, 2, 'extract');
        $idx = $this->assertions->assertNumber($idx);
        // 1 indexed
        if ($list[0] === 'list' && isset($list[2][$idx - 1])) {
            return $list[2][$idx - 1];
        }

        return null;
    }

    /**
     * @param $value
     *
     * @return array
     */
    public function isnumber($value)
    {
        return $this->toBool($value[0] === 'number');
    }

    /**
     * @param $value
     *
     * @return array
     */
    public function isstring($value)
    {
        return $this->toBool($value[0] === 'string');
    }

    /**
     * @param $value
     *
     * @return array
     */
    public function iscolor($value)
    {
        $yesno = $this->coerce->coerceColor($value) !== null;
        return $this->toBool($yesno);
    }

    /**
     * @param $value
     *
     * @return array
     */
    public function iskeyword($value)
    {
        return $this->toBool($value[0] === 'keyword');
    }

    /**
     * @param $value
     *
     * @return array
     */
    public function ispixel($value)
    {
        return $this->toBool($value[0] === 'number' && $value[2] === 'px');
    }

    /**
     * @param $value
     *
     * @return array
     */
    public function ispercentage($value)
    {
        return $this->toBool($value[0] === 'number' && $value[2] === '%');
    }

    /**
     * @param $value
     *
     * @return array
     */
    public function isem($value)
    {
        return $this->toBool($value[0] === 'number' && $value[2] === 'em');
    }

    /**
     * @param $value
     *
     * @return array
     */
    public function isrem($value)
    {
        return $this->toBool($value[0] === 'number' && $value[2] === 'rem');
    }

    /**
     * @param $color
     *
     * @return string
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function rgbahex($color)
    {
        $color = $this->coerce->coerceColor($color);
        if ($color === null) {
            throw new GeneralException('color expected for rgbahex');
        }

        return sprintf(
            '#%02x%02x%02x%02x',
            isset($color[4]) ? $color[4] * 255 : 255,
            $color[1],
            $color[2],
            $color[3]
        );
    }

    /**
     * @param $color
     *
     * @return string
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function argb($color)
    {
        return $this->rgbahex($color);
    }

    /**
     * Given an url, decide whether to output a regular link or the base64-encoded contents of the file
     *
     * @param  array $value either an argument list (two strings) or a single string
     *
     * @return string        formatted url(), either as a link or base64-encoded
     */
    public function data_uri($value)
    {
        $mime = ($value[0] === 'list') ? $value[2][0][2] : null;
        $url = ($value[0] === 'list') ? $value[2][1][2][0] : $value[2][0];

        $fullpath = $this->findImport($url);

        if ($fullpath && ($fsize = filesize($fullpath)) !== false) {
            // IE8 can't handle data uris larger than 32KB
            if ($fsize / 1024 < 32) {
                if ($mime === null) {
                    $finfo = new \finfo(FILEINFO_MIME);
                    $mime = explode('; ', $finfo->file($fullpath));
                    $mime = $mime[0];
                }

                //todo find out why this suddenly breakes data-uri-test
                if ($mime !== null && $mime !== 'text/x-php') {
                    // fallback if the mime type is still unknown
                    $url = sprintf('data:%s;base64,%s', $mime, base64_encode(file_get_contents($fullpath)));
                }
            }
        }

        return 'url("' . $url . '")';
    }

    // utility func to unquote a string

    /**
     * @param array $arg
     *
     * @return array
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function e(array $arg)
    {
        switch ($arg[0]) {
            case 'list':
                $items = $arg[2];
                if (isset($items[0])) {
                    return $this->e($items[0]);
                }
                throw new GeneralException('unrecognised input');
            case 'string':
                $arg[1] = '';

                return $arg;
            case 'keyword':
                return $arg;
            default:
                return ['keyword', $this->compiler->compileValue($arg)];
        }
    }

    /**
     * @param array $args
     *
     * @return array
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function _sprintf(array $args)
    {
        if ($args[0] !== 'list') {
            return $args;
        }
        $values = $args[2];
        $string = array_shift($values);
        $template = $this->compiler->compileValue($this->e($string));

        $i = 0;
        if (preg_match_all('/%[dsa]/', $template, $m)) {
            foreach ($m[0] as $match) {
                $val = isset($values[$i]) ?
                    $this->compiler->reduce($values[$i]) : ['keyword', ''];

                // lessjs compat, renders fully expanded color, not raw color
                $color = $this->coerce->coerceColor($val);
                if ($color !== null) {
                    $val = $color;
                }

                $i++;
                $rep = $this->compiler->compileValue($this->e($val));
                $template = preg_replace(
                    '/' . Compiler::pregQuote($match) . '/',
                    $rep,
                    $template,
                    1
                );
            }
        }

        $d = $string[0] === 'string' ? $string[1] : '"';

        return ['string', $d, [$template]];
    }

    /**
     * @param array $arg
     *
     * @return array
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function floor(array $arg)
    {
        $value = $this->assertions->assertNumber($arg);

        return ['number', floor($value), $arg[2]];
    }

    /**
     * @param array $arg
     *
     * @return array
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function ceil(array $arg)
    {
        $value = $this->assertions->assertNumber($arg);

        return ['number', ceil($value), $arg[2]];
    }

    /**
     * @param array $arg
     *
     * @return array
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function round(array $arg)
    {
        if ($arg[0] !== 'list') {
            $value = $this->assertions->assertNumber($arg);

            return ['number', round($value), $arg[2]];
        } else {
            $value = $this->assertions->assertNumber($arg[2][0]);
            $precision = $this->assertions->assertNumber($arg[2][1]);

            return ['number', round($value, $precision), $arg[2][0][2]];
        }
    }

    /**
     * @param array $arg
     *
     * @return array
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function unit(array $arg)
    {
        if ($arg[0] === 'list') {
            list($number, $newUnit) = $arg[2];

            return [
                'number',
                $this->assertions->assertNumber($number),
                $this->compiler->compileValue($this->e($newUnit)),
            ];
        } else {
            return ['number', $this->assertions->assertNumber($arg), ''];
        }
    }


    /**
     * @param array $args
     *
     * @return array
     */
    public function darken(array $args)
    {
        list($color, $delta) = $this->compiler->colorArgs($args);

        $hsl = $this->converter->toHSL($color);
        $hsl[3] = $this->converter->clamp($hsl[3] - $delta, 100);

        return $this->converter->toRGB($hsl);
    }

    /**
     * @param array $args
     *
     * @return array
     */
    public function lighten(array $args)
    {
        list($color, $delta) = $this->compiler->colorArgs($args);

        $hsl = $this->converter->toHSL($color);
        $hsl[3] = $this->converter->clamp($hsl[3] + $delta, 100);

        return $this->converter->toRGB($hsl);
    }

    /**
     * @param array $args
     *
     * @return array
     */
    public function saturate(array $args)
    {
        list($color, $delta) = $this->compiler->colorArgs($args);

        $hsl = $this->converter->toHSL($color);
        $hsl[2] = $this->converter->clamp($hsl[2] + $delta, 100);

        return $this->converter->toRGB($hsl);
    }

    /**
     * @param array $args
     *
     * @return array
     */
    public function desaturate(array $args)
    {
        list($color, $delta) = $this->compiler->colorArgs($args);

        $hsl = $this->converter->toHSL($color);
        $hsl[2] = $this->converter->clamp($hsl[2] - $delta, 100);

        return $this->converter->toRGB($hsl);
    }

    /**
     * @param array $args
     *
     * @return array
     */
    public function spin(array $args)
    {
        list($color, $delta) = $this->compiler->colorArgs($args);

        $hsl = $this->converter->toHSL($color);

        $hsl[1] += $delta % 360;
        if ($hsl[1] < 0) {
            $hsl[1] += 360;
        }

        return $this->converter->toRGB($hsl);
    }

    /**
     * @param array $args
     *
     * @return int[]
     */
    public function fadeout(array $args)
    {
        list($color, $delta) = $this->compiler->colorArgs($args);
        $color[4] = $this->converter->clamp((isset($color[4]) ? $color[4] : 1) - $delta / 100);

        return $color;
    }

    /**
     * @param array $args
     *
     * @return int[]
     */
    public function fadein(array $args)
    {
        list($color, $delta) = $this->compiler->colorArgs($args);
        $color[4] = $this->converter->clamp((isset($color[4]) ? $color[4] : 1) + $delta / 100);

        return $color;
    }

    /**
     * @param array $color
     *
     * @return float
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function hue(array $color)
    {
        $hsl = $this->converter->toHSL($this->assertions->assertColor($color));

        return round($hsl[1]);
    }

    /**
     * @param array $color
     *
     * @return float
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function saturation(array $color)
    {
        $hsl = $this->converter->toHSL($this->assertions->assertColor($color));

        return round($hsl[2]);
    }

    /**
     * @param array $color
     *
     * @return float
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function lightness(array $color)
    {
        $hsl = $this->converter->toHSL($this->assertions->assertColor($color));

        return round($hsl[3]);
    }

    /**
     * get the alpha of a color
     * defaults to 1 for non-colors or colors without an alpha
     *
     * @param array $value
     *
     * @return int|null
     */
    public function alpha(array $value)
    {
        $color = $this->coerce->coerceColor($value);
        if ($color !== null) {
            return isset($color[4]) ? $color[4] : 1;
        }

        return null;
    }

    /**
     * set the alpha of the color
     *
     * @param array $args
     *
     * @return int[]
     */
    public function fade(array $args)
    {
        list($color, $alpha) = $this->compiler->colorArgs($args);
        $color[4] = $this->converter->clamp($alpha / 100.0);

        return $color;
    }

    /**
     * @param array $arg
     *
     * @return array
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function percentage(array $arg)
    {
        $num = $this->assertions->assertNumber($arg);

        return ['number', $num * 100, '%'];
    }

    /**
     * mixes two colors by weight
     * mix(@color1, @color2, [@weight: 50%]);
     * http://sass-lang.com/docs/yardoc/Sass/Script/Functions.html#mix-instance_method
     *
     * @param array $args
     *
     * @return mixed
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function mix(array $args)
    {
        if ($args[0] !== 'list' || count($args[2]) < 2) {
            throw new GeneralException('mix expects (color1, color2, weight)');
        }

        list($first, $second) = $args[2];
        $first = $this->assertions->assertColor($first);
        $second = $this->assertions->assertColor($second);

        $firstAlpha = $this->alpha($first);
        $secondAlpha = $this->alpha($second);

        if (isset($args[2][2])) {
            $weight = $args[2][2][1] / 100.0;
        } else {
            $weight = 0.5;
        }

        $w = $weight * 2 - 1;
        $a = $firstAlpha - $secondAlpha;

        $w1 = (($w * $a === -1 ? $w : ($w + $a) / (1 + $w * $a)) + 1) / 2.0;
        $w2 = 1.0 - $w1;

        $new = [
            'color',
            $w1 * $first[1] + $w2 * $second[1],
            $w1 * $first[2] + $w2 * $second[2],
            $w1 * $first[3] + $w2 * $second[3],
        ];

        // do not change the following to type safe comparison...
        if ($firstAlpha != 1.0 || $secondAlpha != 1.0) {
            $new[] = $firstAlpha * $weight + $secondAlpha * ($weight - 1);
        }

        return $this->compiler->fixColor($new);
    }

    /**
     * @param array $args
     *
     * @return array|null
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function contrast(array $args)
    {
        $darkColor = ['color', 0, 0, 0];
        $lightColor = ['color', 255, 255, 255];
        $threshold = 0.43;

        if ($args[0] === 'list') {
            $inputColor = isset($args[2][0]) ? $this->assertions->assertColor($args[2][0]) : $lightColor;
            $darkColor = isset($args[2][1]) ? $this->assertions->assertColor($args[2][1]) : $darkColor;
            $lightColor = isset($args[2][2]) ? $this->assertions->assertColor($args[2][2]) : $lightColor;
            if (isset($args[2][3])) {
                if (isset($args[2][3][2]) && $args[2][3][2] === '%') {
                    $args[2][3][1] /= 100;
                    unset($args[2][3][2]);
                }
                $threshold = $this->assertions->assertNumber($args[2][3]);
            }
        } else {
            $inputColor = $this->assertions->assertColor($args);
        }

        $inputColor = $this->coerce->coerceColor($inputColor);
        $darkColor = $this->coerce->coerceColor($darkColor);
        $lightColor = $this->coerce->coerceColor($lightColor);

        //Figure out which is actually light and dark!
        if ($this->luma($darkColor) > $this->luma($lightColor)) {
            $t = $lightColor;
            $lightColor = $darkColor;
            $darkColor = $t;
        }

        $inputColorAlpha = $this->alpha($inputColor);
        if (($this->luma($inputColor) * $inputColorAlpha) < $threshold) {
            return $lightColor;
        }

        return $darkColor;
    }

    /**
     * @param array $color
     *
     * @return float
     */
    public function luma(array $color)
    {
        $color = $this->coerce->coerceColor($color);
        return (0.2126 * $color[1] / 255) + (0.7152 * $color[2] / 255) + (0.0722 * $color[3] / 255);
    }


    /**
     * @param array $number
     * @param       $to
     *
     * @return array
     * @throws \LesserPhp\Exception\GeneralException
     */
    public function convertMe(array $number, $to)
    {
        $value = $this->assertions->assertNumber($number);
        $from = $number[2];

        // easy out
        if ($from == $to) {
            return $number;
        }

        // check if the from value is a length
        if (($fromIndex = array_search($from, static::$lengths)) !== false) {
            // make sure to value is too
            if (in_array($to, static::$lengths)) {
                // do the actual conversion
                $toIndex = array_search($to, static::$lengths);
                $px = $value * static::$lengths_to_base[$fromIndex];
                $result = $px * (1 / static::$lengths_to_base[$toIndex]);

                $result = round($result, 8);

                return ['number', $result, $to];
            }
        }

        // do the same check for times
        if (in_array($from, static::$times) && in_array($to, static::$times)) {
            // currently only ms and s are valid
            if ($to === 'ms') {
                $result = $value * 1000;
            } else {
                $result = $value / 1000;
            }

            $result = round($result, 8);

            return ['number', $result, $to];
        }

        // lastly check for an angle
        if (in_array($from, static::$angles)) {
            // convert whatever angle it is into degrees
            $deg = $value;
            if ($from === 'rad') {
                $deg = rad2deg($value);
            } else {
                if ($from === 'turn') {
                    $deg = $value * 360;
                } else {
                    if ($from === 'grad') {
                        $deg = $value / (400 / 360);
                    }
                }
            }

            // Then convert it from degrees into desired unit
            $result = 0;
            if ($to === 'deg') {
                $result = $deg;
            }

            if ($to === 'rad') {
                $result = deg2rad($deg);
            }

            if ($to === 'turn') {
                $result = $value / 360;
            }

            if ($to === 'grad') {
                $result = $value * (400 / 360);
            }

            $result = round($result, 8);

            return ['number', $result, $to];
        }

        // we don't know how to convert these
        throw new GeneralException("Cannot convert {$from} to {$to}");
    }

    /**
     * @param bool $a
     *
     * @return array
     */
    public function toBool($a)
    {
        if ($a) {
            return static::$TRUE;
        } else {
            return static::$FALSE;
        }
    }

    /**
     * attempts to find the path of an import url, returns null for css files
     *
     * @param string $url
     *
     * @return null|string
     */
    public function findImport($url)
    {
        foreach ($this->compiler->getImportDirs() as $dir) {
            $full = $dir . (mb_substr($dir, -1) !== '/' ? '/' : '') . $url;
            if ($this->fileExists($file = $full . '.less') || $this->fileExists($file = $full)) {
                return $file;
            }
        }

        return null;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function fileExists($name)
    {
        return is_file($name);
    }
}
