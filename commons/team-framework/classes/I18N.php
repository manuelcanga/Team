<?php
/**
 * Developed by Manuel Canga ( dev@trasweb.net )
 * Date: 5/01/17
 * Time: 16:27
 */

namespace team;


class I18N
{
    public static function setUp() {
        \team\I18N::setTimezone();
        \team\I18N::setLocale();
    }

    public static function setTimezone() {
        $timezone = \team\Config::get('TIMEZONE');

        \Team::event('\team\settimezone', $timezone);

        date_default_timezone_set($timezone);
        ini_set('date.timezone', $timezone);
    }

    public static function setLocale() {
        $lang =  \team\Config::get('LANG');
        $charset = \team\Config::get('CHARSET');

        $locale = $lang.'.'.$charset;

        \Team::event('\team\setlocale', $locale, $lang, $charset);

        setlocale(LC_ALL,$locale );
        putenv('LANG='.$locale);
        putenv('LANGUAGE='.$locale);

    }

    static function length($string) {
        if (function_exists('mb_strlen') ) {
            return mb_strlen($string,  \team\Config::get('CHARSET') );
        }

        return strlen($string);
    }

}