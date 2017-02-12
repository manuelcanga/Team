<?php

namespace team;

if(!defined('_SITE_')) die("Hello, World!");

/**
 * Developed by Manuel Canga ( dev@trasweb.net )
 * Date: 4/01/17
 * Time: 15:39
 */

\team\Config::set('AREAS',  [
    '/' =>  '/demo01/example01',
    '/demo02/' =>  '/demo02/example01' ]

);

\team\Config::set('ENVIROMENT', 'local');