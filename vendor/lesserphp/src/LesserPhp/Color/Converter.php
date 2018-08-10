<?php

namespace LesserPhp\Color;

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
class Converter
{

    /**
     * @param array $color
     *
     * @return array
     */
    public function toHSL($color)
    {
        if ($color[0] === 'hsl') {
            return $color;
        }

        $r = $color[1] / 255;
        $g = $color[2] / 255;
        $b = $color[3] / 255;

        $min = min($r, $g, $b);
        $max = max($r, $g, $b);

        $L = ($min + $max) / 2;
        if ($min == $max) {
            $S = $H = 0;
        } else {
            if ($L < 0.5) {
                $S = ($max - $min) / ($max + $min);
            } else {
                $S = ($max - $min) / (2.0 - $max - $min);
            }

            if ($r == $max) {
                $H = ($g - $b) / ($max - $min);
            } elseif ($g == $max) {
                $H = 2.0 + ($b - $r) / ($max - $min);
            } elseif ($b == $max) {
                $H = 4.0 + ($r - $g) / ($max - $min);
            }
        }

        $out = [
            'hsl',
            ($H < 0 ? $H + 6 : $H) * 60,
            $S * 100,
            $L * 100,
        ];

        if (count($color) > 4) {
            $out[] = $color[4];
        } // copy alpha

        return $out;
    }

    /**
     * @param double $comp
     * @param double $temp1
     * @param double $temp2
     *
     * @return double
     */
    protected function toRGBHelper($comp, $temp1, $temp2)
    {
        if ($comp < 0) {
            $comp += 1.0;
        } elseif ($comp > 1) {
            $comp -= 1.0;
        }

        if (6 * $comp < 1) {
            return $temp1 + ($temp2 - $temp1) * 6 * $comp;
        }
        if (2 * $comp < 1) {
            return $temp2;
        }
        if (3 * $comp < 2) {
            return $temp1 + ($temp2 - $temp1) * ((2 / 3) - $comp) * 6;
        }

        return $temp1;
    }

    /**
     * Converts a hsl array into a color value in rgb.
     * Expects H to be in range of 0 to 360, S and L in 0 to 100
     *
     * @param array $color
     *
     * @return array
     */
    public function toRGB(array $color)
    {
        if ($color[0] === 'color') {
            return $color;
        }

        $H = $color[1] / 360;
        $S = $color[2] / 100;
        $L = $color[3] / 100;

        if ($S === 0) {
            $r = $g = $b = $L;
        } else {
            $temp2 = $L < 0.5 ? $L * (1.0 + $S) : $L + $S - $L * $S;
            $temp1 = 2.0 * $L - $temp2;

            $r = $this->toRGBHelper($H + 1 / 3, $temp1, $temp2);
            $g = $this->toRGBHelper($H, $temp1, $temp2);
            $b = $this->toRGBHelper($H - 1 / 3, $temp1, $temp2);
        }

        // $out = array('color', round($r*255), round($g*255), round($b*255));
        $out = ['color', $r * 255, $g * 255, $b * 255];
        if (count($color) > 4) {
            $out[] = $color[4];
        } // copy alpha

        return $out;
    }

    /**
     * @param int $v
     * @param int $max
     * @param int $min
     *
     * @return int
     */
    public function clamp($v, $max = 1, $min = 0)
    {
        return min($max, max($min, $v));
    }
}
