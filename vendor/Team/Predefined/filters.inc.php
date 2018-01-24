<?php

namespace Team\Predefined;

use \Team\Config;
use \Team\Data\Filter;

if(!defined('_TEAM_')) die("Hello, World!");

//Avoid proxys domains
Filter::add('\team\request_uri', function($url) {
    return  parse_url($url, PHP_URL_PATH);
});


Config::setUp();
