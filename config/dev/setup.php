<?php

namespace team;

if(!defined('_SCRIPT_')) die("Hello, World!");


\team\DB::add([
        'user'       => 'my_user',
        'password'   => 'my_passwd',
        'name'       => 'my_db',
        'host'       => 'localhost',
        'prefix'        => 'prefix_',
        'charset'   => 'UTF8',
    ]
);


\team\Config::set('SHOW_ERRORS', true);
\team\Config::set('SHOW_IN_NAVIGATOR', true);
\team\Config::get('VIEW_CACHE', false);


