<?php

namespace team;


class Enviroment
{




    static function checkServer($key = null) {
        static $server = null;

        if(isset($server)) {
            return $key? $server[$key] : $server;
        }

        $software = 'undefined';
        /**
         * Whether the server software is Apache or something else
         */
        $apache = (strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false || strpos($_SERVER['SERVER_SOFTWARE'], 'LiteSpeed') !== false);
        $software = $apache? 'apache' : $software;


        /**
         * Whether the server software is Nginx or something else
         */
        $nginx = (strpos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false);
        $software = $nginx? 'nginx' : $software;

        /**
         * Whether the server software is IIS or something else
         */
        $IIS = !$apache && (strpos($_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS') !== false || strpos($_SERVER['SERVER_SOFTWARE'], 'ExpressionDevServer') !== false);
        $software = $IIS? 'IIS' : $software;

        /**
         * Whether the server software is IIS 7.X or greater
         */
        $iis7 = $IIS && \team\Check::id( substr( $_SERVER['SERVER_SOFTWARE'], strpos( $_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS/' ) + 14 ) ) >= 7;
        $software = $iis7? 'iis7' : $software;

        $server = ['software' => $software, "apache" => $apache, "nginx" => $nginx, "IIS" => $IIS, 'iis7' => $iis7  ];

        return $key? $server[$key] : $server;
    }

    /**
     * Does the specified module exist in the Apache config?
     **
     *
     * @param string $mod     The module, e.g. mod_rewrite.
     * @param bool   $default Optional. The default return value if the module is not found. Default false.
     * @return bool Whether the specified module is loaded.
     */
    static function apacheModLoaded($mod, $default = false) {

        if ( !self::checkServer('apache') )
            return false;

        if ( function_exists( 'apache_get_modules' ) ) {
            $mods = apache_get_modules();
            if ( in_array($mod, $mods) )
                return true;
        } elseif ( function_exists( 'phpinfo' ) && false === strpos( ini_get( 'disable_functions' ), 'phpinfo' ) ) {
            ob_start();
            phpinfo(8);
            $phpinfo = ob_get_clean();
            if ( false !== strpos($phpinfo, $mod) )
                return true;
        }
        return $default;
    }


}
