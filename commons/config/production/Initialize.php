<?php

namespace team;

if(!defined('_SITE_')) die("Hello, World!");


\team\DB::set([
        'user'       => 'my_user',
        'password'   => 'my_passwd',
        'name'       => 'my_db',
        'host'       => 'localhost',
        'prefix'        => 'prefix_',
        'charset'   => 'UTF8',
    ]
);


