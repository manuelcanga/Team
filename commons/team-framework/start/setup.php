<?php

namespace team\start\setup;

if(!defined("_SITE_") ) die("Hello,  World");


date_default_timezone_set(\team\Config::get('TIMEZONE') );
ini_set('date.timezone', \team\Config::get('TIMEZONE') );

$lang = \team\Config::get('LANG');
$charset = \team\Config::get('CHARSET');

$locale = $lang.'.'.$charset;
setlocale(LC_ALL,$locale );
putenv('LANG='.$locale);
putenv('LANGUAGE='.$locale);
