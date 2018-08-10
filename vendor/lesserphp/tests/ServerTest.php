<?php

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
class ServerTest extends \PHPUnit\Framework\TestCase
{

    public function testCheckedCachedCompile()
    {
        $server = new \LesserPhp\Compiler();
        $server->setImportDirs([__DIR__ . '/inputs/test-imports/']);
        $css = $server->checkedCachedCompile(__DIR__ . '/inputs/import.less', '/tmp/less.css');

        $this->assertFileExists('/tmp/less.css');
        $this->assertFileExists('/tmp/less.css.meta');
        $this->assertEquals($css, file_get_contents('/tmp/less.css'));
        $this->assertNotNull(unserialize(file_get_contents('/tmp/less.css.meta')));
    }
}
