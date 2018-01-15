<?php

function __test() {
    $date = new \Team\data\Data();
    $date->view = 'index.tpl';


    /** @var Currents  */
    $date->current =   \Team\system\Date::current();
    $date->current_mysql =   \Team\system\Date::current('mysql');
    $date->current_fecha =   \Team\system\Date::current('fecha');
    $date->current_date =   \Team\system\Date::current('date');
    $date->current_datetime =   \Team\system\Date::current('datetime');
    $date->current_dia =  \Team\system\Date::current('dia');
    $date->current_timestamp =  \Team\system\Date::current('timestamp');


    /** @var transforms  */
    $date->transform_database =   \Team\system\Date::transform('20/07/2010', 'fecha', 'database');
    $date->transform_mes =   \Team\system\Date::transform('20/07/2010', 'fecha', 'mes');
    $date->transform_fecha =   \Team\system\Date::transform( $date->transform_database, 'database', 'fecha');
    $date->transform_cookie =   \Team\system\Date::transform( $date->transform_database, 'database', 'cookie');
    $date->transform_fechahora =   \Team\system\Date::transform('10/10/2020 10:11:12', 'fechahora', 'rss');
    $date->transform_mes2 =   \Team\system\Date::transform('10/10/2020 10:11:12', 'fechahora', 'mes');
    $date->transform_month =   \Team\system\Date::transform('10/10/2020 10:11:12', 'fechahora', 'month');
    $date->transform_monthname =   \Team\system\Date::transform('10/10/2020 10:11:12', 'fechahora', 'monthname');


    /** @var changes  */
    $date->change_fecha_mes =   \Team\system\Date::change('+1 Month', '20/07/2010', 'fecha');
    $date->change_fecha_month =   \Team\system\Date::change('+1 Month', '20/07/2010', 'fecha', 'nombremes');

    $date->change_fecha_years =   \Team\system\Date::change('+3 Years', '20/07/2010', 'fecha');
    $date->change_fecha_year =   \Team\system\Date::change('+3 Years', '20/07/2010', 'fecha', 'year');

    $date->change_fecha_days =   \Team\system\Date::change('+3 Days', '2000-07-10', 'database-date');
    $date->change_fecha_dia =   \Team\system\Date::change('+3 Days', '2000-07-10', 'database-date','nombredia');


    /** @var changes2  */
    $date->change2_fecha_mes =   \Team\system\Date::get('+1 Month',  'fecha');
    $date->change2_fecha_month =   \Team\system\Date::get('+1 Month', 'nombremes');

    $date->change2_fecha_years =   \Team\system\Date::get('+3 Years', 'fecha');
    $date->change2_fecha_year =   \Team\system\Date::get('+3 Years', 'year');

    $date->change2_fecha_days =   \Team\system\Date::get('+3 Days',  'database-date');
    $date->change2_fecha_dia =   \Team\system\Date::get('+3 Days', 'nombredia');

    \Team\system\Context::set('CHARSET', 'UTF-8');
    setlocale(LC_TIME, "de_DE.utf8");

    $date->change3_fecha_mes =   \Team\system\Date::get('+1 Month',  'fecha');
    $date->change3_fecha_month =   \Team\system\Date::get('+1 Month', 'nombremes');

    $date->change3_fecha_years =   \Team\system\Date::get('+3 Years', 'fecha');
    $date->change3_fecha_year =   \Team\system\Date::get('+3 Years', 'year');

    $date->change3_fecha_days =   \Team\system\Date::get('+3 Days',  'database-date');
    $date->change3_fecha_dia =   \Team\system\Date::get('+3 Days', 'nombredia');


    echo $date->out('html');


}
