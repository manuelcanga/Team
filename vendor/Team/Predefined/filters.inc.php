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


//Data Formats
Filter::add('\team\data\format\url',      function($data, $options = []) { return \Team\Data\Formatter::change($data, 'url', $options); });
Filter::add('\team\data\format\terminal', function($data, $options = []) { return \Team\Data\Formatter::change($data, 'terminal', $options); });
Filter::add('\team\data\format\string',   function($data, $options = []) { return \Team\Data\Formatter::change($data, 'string', $options); });
Filter::add('\team\data\format\params',   function($data, $options = []) { return \Team\Data\Formatter::change($data, 'params', $options); });
Filter::add('\team\data\format\object',   function($data, $options = []) { return \Team\Data\Formatter::change($data, 'object', $options); });
Filter::add('\team\data\format\xml',      function($data, $options = []) { return \Team\Data\Formatter::change($data, 'xml', $options); });
Filter::add('\team\data\format\json',     function($data, $options = []) { return \Team\Data\Formatter::change($data, 'json', $options); });
Filter::add('\team\data\format\html',     function($data, $options = []) { return \Team\Data\Formatter::change($data, 'html', $options); });




