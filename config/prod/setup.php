<?php

namespace Team;

if(!defined('_TEAM_')) die("Hello, World!");


\Team\System\DB::add([
        'user'       => 'my_user',
        'password'   => 'my_passwd',
        'name'       => 'my_db',
        'host'       => 'localhost',
        'prefix'        => 'prefix_',
        'charset'   => 'UTF8',
    ]
);


\team\Config::set('SHOW_ERRORS', true);
\team\Config::set('SHOW_IN_NAVIGATOR', false);