<?php

function __test() {
    $date = new \team\Data();
    $date->view = 'index.tpl';


    /** @var Currents  */
    $date->current =   \team\Date::current();
    $date->current_mysql =   \team\Date::current('mysql');
    $date->current_fecha =   \team\Date::current('fecha');
    $date->current_date =   \team\Date::current('date');
    $date->current_datetime =   \team\Date::current('datetime');
    $date->current_dia =  \team\Date::current('dia');
    $date->current_timestamp =  \team\Date::current('timestamp');


    /** @var transforms  */
    $date->transform_database =   \team\Date::transform('20/07/2010', 'fecha', 'database');
    $date->transform_mes =   \team\Date::transform('20/07/2010', 'fecha', 'mes');
    $date->transform_fecha =   \team\Date::transform( $date->transform_database, 'database', 'fecha');
    $date->transform_cookie =   \team\Date::transform( $date->transform_database, 'database', 'cookie');
    $date->transform_fechahora =   \team\Date::transform('10/10/2020 10:11:12', 'fechahora', 'rss');
    $date->transform_mes2 =   \team\Date::transform('10/10/2020 10:11:12', 'fechahora', 'mes');
    $date->transform_month =   \team\Date::transform('10/10/2020 10:11:12', 'fechahora', 'month');
    $date->transform_monthname =   \team\Date::transform('10/10/2020 10:11:12', 'fechahora', 'monthname');


    /** @var changes  */
    $date->change_fecha_mes =   \team\Date::change('+1 Month', '20/07/2010', 'fecha');
    $date->change_fecha_month =   \team\Date::change('+1 Month', '20/07/2010', 'fecha', 'nombremes');

    $date->change_fecha_years =   \team\Date::change('+3 Years', '20/07/2010', 'fecha');
    $date->change_fecha_year =   \team\Date::change('+3 Years', '20/07/2010', 'fecha', 'year');

    $date->change_fecha_days =   \team\Date::change('+3 Days', '2000-07-10', 'database-date');
    $date->change_fecha_dia =   \team\Date::change('+3 Days', '2000-07-10', 'database-date','nombredia');


    /** @var changes2  */
    $date->change2_fecha_mes =   \team\Date::get('+1 Month',  'fecha');
    $date->change2_fecha_month =   \team\Date::get('+1 Month', 'nombremes');

    $date->change2_fecha_years =   \team\Date::get('+3 Years', 'fecha');
    $date->change2_fecha_year =   \team\Date::get('+3 Years', 'year');

    $date->change2_fecha_days =   \team\Date::get('+3 Days',  'database-date');
    $date->change2_fecha_dia =   \team\Date::get('+3 Days', 'nombredia');

    \team\Context::set('CHARSET', 'UTF-8');
    setlocale(LC_TIME, "de_DE.utf8");

    $date->change3_fecha_mes =   \team\Date::get('+1 Month',  'fecha');
    $date->change3_fecha_month =   \team\Date::get('+1 Month', 'nombremes');

    $date->change3_fecha_years =   \team\Date::get('+3 Years', 'fecha');
    $date->change3_fecha_year =   \team\Date::get('+3 Years', 'year');

    $date->change3_fecha_days =   \team\Date::get('+3 Days',  'database-date');
    $date->change3_fecha_dia =   \team\Date::get('+3 Days', 'nombredia');


    echo $date->out('html');


}
