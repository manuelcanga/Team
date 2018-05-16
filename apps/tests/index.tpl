{html}
    {head}
{body}


{highlight}

    /** Podemos obtener la fecha actual en distintos formatos
        \Team\System\Date::current( formato_salida_fecha );
    */

    echo \Team\System\Date::current(); // {$current}
    echo \Team\System\Date::current('mysql'); // {$current_mysql}
    echo \Team\System\Date::current('fecha'); // {$current_fecha}
    echo \Team\System\Date::current('date'); // {$current_date}
    echo \Team\System\Date::current('datetime'); // {$current_datetime}
    echo \Team\System\Date::current('dia'); // {$current_dia}
    echo \Team\System\Date::current('timestamp'); // {$current_timestamp}
    echo \Team\System\Date::current('timestamp'); // {$current_timestamp}

{/highlight}
<hr />


{highlight}

    /** Podemos convertir tambien entre formatos
        \Team\System\Date::transform( fecha_a_transformar, formato_de_fecha, nuevo_formato_fecha );
    */
    echo  \Team\System\Date::transform('20/07/2010', 'fecha', 'database'); //{$transform_database}
    echo  \Team\System\Date::transform('20/07/2010', 'fecha', 'mes'); //{$transform_mes}
    echo \Team\System\Date::transform("{$transform_database}", 'database', 'fecha'); //{$transform_fecha}
    echo \Team\System\Date::transform("{$transform_database}", 'database', 'cookie'); //{$transform_cookie}
    echo \Team\System\Date::transform('10/10/2020 10:11:12', 'fechahora', 'rss'); //{$transform_fechahora}
    echo \Team\System\Date::transform('10/10/2020 10:11:12', 'fechahora', 'mes'); //{$transform_mes2}
    echo \Team\System\Date::transform('10/10/2020 10:11:12', 'fechahora', 'month'); //{$transform_month}
    echo   \Team\System\Date::transform('10/10/2020 10:11:12', 'fechahora', 'monthname'); //{$transform_monthname}

{/highlight}

    <hr />

{highlight}

    /** Podemos tambien hacer operaciones con una fecha
       \Team\System\Date::change( cambio, fecha, formato_fecha-entrada[, formato_fecha_salida ] );
    */
    echo  \Team\System\Date::change('+1 Month', '20/07/2010', 'fecha');  //{$change_fecha_mes}
    echo    \Team\System\Date::change('+1 Month', '20/07/2010', 'fecha', 'nombremes'); //{$change_fecha_month}

    echo   \Team\System\Date::change('+3 Years', '20/07/2010', 'fecha');  //{$change_fecha_years}
    echo  \Team\System\Date::change('+3 Years', '20/07/2010', 'fecha', 'year');   //{$change_fecha_year}

    echo   \Team\System\Date::change('+3 Days', '2000-07-10', 'database-date');   //{$change_fecha_days}
    echo team\Date::change('+3 Days', '2000-07-10', 'database-date','nombredia'); //{$change_fecha_dia}


    /** Tambien se pueden hacer cambios desde la fecha actual
    \Team\System\Date::get( cambio,formato_salida =  'timestamp', timestamp_desde_el_qu e_hacer_cambios = null] );
    */

    echo  \Team\System\Date::get('+1 Month', 'fecha');  //{$change2_fecha_mes}
    echo    \Team\System\Date::get('+1 Month','nombremes'); //{$change2_fecha_month}

    echo   \Team\System\Date::get('+3 Years', 'fecha');  //{$change2_fecha_years}
    echo  \Team\System\Date::get('+3 Years', 'year');   //{$change2_fecha_year}

    echo   \Team\System\Date::get('+3 Days',  'database-date');   //{$change2_fecha_days}
    echo team\Date::get('+3 Days', 'nombredia'); //{$change2_fecha_dia}


    /**
        Por supuesto que todo lo visto sirve no sólo para España:
    */
    setlocale(LC_TIME, "de_DE.utf8");


    echo  \Team\System\Date::get('+1 Month', 'fecha');  //{$change3_fecha_mes}
    echo    \Team\System\Date::get('+1 Month','nombremes'); //{$change3_fecha_month}

    echo   \Team\System\Date::get('+3 Years', 'fecha');  //{$change3_fecha_years}
    echo  \Team\System\Date::get('+3 Years', 'year');   //{$change3_fecha_year}

    echo   \Team\System\Date::get('+3 Days',  'database-date');   //{$change3_fecha_days}
    echo team\Date::get('+3 Days', 'nombredia'); //{$change3_fecha_dia}


{/highlight}


{/body}
{/html}