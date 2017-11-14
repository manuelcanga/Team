<?php
/**
 * Developed by Manuel Canga ( dev@trasweb.net )
 * Date: 5/01/17
 * Time: 16:27
 */

namespace team;


abstract class I18N
{
    public static function setTimezone($timezone = null) {
        $timezone = \team\Context::get('TIMEZONE', $timezone, '\team\I18N');

        date_default_timezone_set($timezone);
        ini_set('date.timezone', $timezone);
    }

    public static function setLocale($locale = null) {

        $lang = null;
        $charset = null;

        if(isset($locale)) {
            list($lang, $charset) = explode('.', $locale);
        }

        $lang =  \team\Context::get('LANG', $lang, '\team\I18N');
        $charset = \team\Context::get('CHARSET', $charset, '\team\I18N');

        $locale = $lang.'.'.$charset;

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