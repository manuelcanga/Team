<?php

namespace Team;

if(!defined('_TEAM_')) die("Hello, World!");

/**
 * Developed by Manuel Canga ( dev@trasweb.net )
 * Date: 4/01/17
 * Time: 15:39
 */

\team\Config::set('AREAS',  [
         '/' =>  '/demo/welcome',
        '/checks' =>  '/tests/demo/welcome',
    ]
);

\team\Config::set('TESTS_APP_PATH', _APPS_.'/tests');