{html}
    {head}
{body}


{highlight}

    /** Podemos obtener la fecha actual en distintos formatos
        \team\system\Date::current( formato_salida_fecha );
    */

    echo \team\system\Date::current(); // {$current}
    echo \team\system\Date::current('mysql'); // {$current_mysql}
    echo \team\system\Date::current('fecha'); // {$current_fecha}
    echo \team\system\Date::current('date'); // {$current_date}
    echo \team\system\Date::current('datetime'); // {$current_datetime}
    echo \team\system\Date::current('dia'); // {$current_dia}
    echo \team\system\Date::current('timestamp'); // {$current_timestamp}
    echo \team\system\Date::current('timestamp'); // {$current_timestamp}

{/highlight}
<hr />


{highlight}

    /** Podemos convertir tambien entre formatos
        \team\system\Date::transform( fecha_a_transformar, formato_de_fecha, nuevo_formato_fecha );
    */
    echo  \team\system\Date::transform('20/07/2010', 'fecha', 'database'); //{$transform_database}
    echo  \team\system\Date::transform('20/07/2010', 'fecha', 'mes'); //{$transform_mes}
    echo \team\system\Date::transform("{$transform_database}", 'database', 'fecha'); //{$transform_fecha}
    echo \team\system\Date::transform("{$transform_database}", 'database', 'cookie'); //{$transform_cookie}
    echo \team\system\Date::transform('10/10/2020 10:11:12', 'fechahora', 'rss'); //{$transform_fechahora}
    echo \team\system\Date::transform('10/10/2020 10:11:12', 'fechahora', 'mes'); //{$transform_mes2}
    echo \team\system\Date::transform('10/10/2020 10:11:12', 'fechahora', 'month'); //{$transform_month}
    echo   \team\system\Date::transform('10/10/2020 10:11:12', 'fechahora', 'monthname'); //{$transform_monthname}

{/highlight}

    <hr />

{highlight}

    /** Podemos tambien hacer operaciones con una fecha
       \team\system\Date::change( cambio, fecha, formato_fecha-entrada[, formato_fecha_salida ] );
    */
    echo  \team\system\Date::change('+1 Month', '20/07/2010', 'fecha');  //{$change_fecha_mes}
    echo    \team\system\Date::change('+1 Month', '20/07/2010', 'fecha', 'nombremes'); //{$change_fecha_month}

    echo   \team\system\Date::change('+3 Years', '20/07/2010', 'fecha');  //{$change_fecha_years}
    echo  \team\system\Date::change('+3 Years', '20/07/2010', 'fecha', 'year');   //{$change_fecha_year}

    echo   \team\system\Date::change('+3 Days', '2000-07-10', 'database-date');   //{$change_fecha_days}
    echo team\Date::change('+3 Days', '2000-07-10', 'database-date','nombredia'); //{$change_fecha_dia}


    /** Tambien se pueden hacer cambios desde la fecha actual
    \team\system\Date::get( cambio,formato_salida =  'timestamp', timestamp_desde_el_qu e_hacer_cambios = null] );
    */

    echo  \team\system\Date::get('+1 Month', 'fecha');  //{$change2_fecha_mes}
    echo    \team\system\Date::get('+1 Month','nombremes'); //{$change2_fecha_month}

    echo   \team\system\Date::get('+3 Years', 'fecha');  //{$change2_fecha_years}
    echo  \team\system\Date::get('+3 Years', 'year');   //{$change2_fecha_year}

    echo   \team\system\Date::get('+3 Days',  'database-date');   //{$change2_fecha_days}
    echo team\Date::get('+3 Days', 'nombredia'); //{$change2_fecha_dia}


    /**
        Por supuesto que todo lo visto sirve no sólo para España:
    */
    setlocale(LC_TIME, "de_DE.utf8");


    echo  \team\system\Date::get('+1 Month', 'fecha');  //{$change3_fecha_mes}
    echo    \team\system\Date::get('+1 Month','nombremes'); //{$change3_fecha_month}

    echo   \team\system\Date::get('+3 Years', 'fecha');  //{$change3_fecha_years}
    echo  \team\system\Date::get('+3 Years', 'year');   //{$change3_fecha_year}

    echo   \team\system\Date::get('+3 Days',  'database-date');   //{$change3_fecha_days}
    echo team\Date::get('+3 Days', 'nombredia'); //{$change3_fecha_dia}


{/highlight}


{/body}
{/html}