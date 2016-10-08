<?php
/**
 * Created by PhpStorm.
 * User: trasweb
 * Date: 8/10/16
 * Time: 13:36
 */

namespace team;


class Http
{



    /**
     * Send a HTTP header to disable content type sniffing in browsers which support it.
     *
     *
     * @see http://blogs.msdn.com/ie/archive/2008/07/02/ie8-security-part-v-comprehensive-protection.aspx
     * @see http://src.chromium.org/viewvc/chrome?view=rev&revision=6985
     */
    static function sendNosniffHeader() {
        @header( 'X-Content-Type-Options: nosniff' );
    }

    /**
     * Send a HTTP header to limit rendering of pages to same origin iframes.
     *
     *
     * @see https://developer.mozilla.org/en/the_x-frame-options_response_header
     */
    static function sendFrameOptionsHeader() {
        @header( 'X-Frame-Options: SAMEORIGIN' );
    }

    /**
     * Get the header information to prevent caching.
     *
     * The several different headers cover the different ways cache prevention
     * is handled by different browsers
     *
     *
     * @return array The associative array of header names and field values.
     */
    static function getNocacheHeaders() {
        $headers = array(
            'Expires' => 'Wed, 11 Jan 1984 05:00:00 GMT',
            'Cache-Control' => 'no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        );


        $headers = (array) \team\Filter::apply( '\team\http\nocache_headers', $headers );
        $headers['Last-Modified'] = false;
        return $headers;
    }

    /**
     * Set the headers to prevent caching for the different browsers.
     *
     * Different browsers support different nocache headers, so several
     * headers must be sent so that all of them get the point that no
     * caching should occur.
     *
     *
     * @see \team\Http::getNocacheHeaders()
     */
    static function sendNoCacheHeaders() {
        $headers = self::getNocacheHeaders();

        unset( $headers['Last-Modified'] );
        @header_remove( 'Last-Modified' );

        foreach ( $headers as $name => $field_value )
            @header("{$name}: {$field_value}");
    }

    /**
     * Set the headers for caching for $days days with JavaScript content type.
     */
    function sendCacheJavascriptHeaders($days = 10) {
        $expiresOffset = $days * \team\Date::A_DAY;

        header( "Content-Type: text/javascript; charset=" . get_bloginfo( 'charset' ) );
        header( "Vary: Accept-Encoding" ); // Handle proxies
        header( "Expires: " . gmdate( "D, d M Y H:i:s", time() + $expiresOffset ) . " GMT" );
    }

    /**
     * Get information about user agent.
     *
     * @params string $key only retrieve $key field of user agent information
     *
     */
    static function checkUserAgent($key = null) {
        static $user_agent;

        if(isset($user_agent) )  {
            return $key? $user_agent[$key] : $user_agent;
        }

        $http_user_agent = $_SERVER['HTTP_USER_AGENT'];

        $mobile = false;
        $computer = false;
        $tablet = false;
        $device = 'computer';
        $navigator = 'explorer';

        if(!empty($http_user_agent) ) {
            $is_mobile = strpos($http_user_agent, 'Mobile') !== false;
            $is_android = strpos($http_user_agent, 'Android') !== false;

            //¿is tablet?
            if ( stripos($http_user_agent, 'Tablet') !== false || ($is_android && !$is_mobile)
                || strpos($http_user_agent, 'Kindle') !== false ) {
                $tablet =  true;
                $device = $navigator = "tablet";
            }

            //¿is mobile?
            if(!$tablet && ($is_mobile || strpos($http_user_agent, 'Silk/') !== false
                    || strpos($http_user_agent, 'BlackBerry') !== false
                    || strpos($http_user_agent, 'Opera Mini') !== false
                    || strpos($http_user_agent, 'Opera Mobi') !== false ) ) {
                $mobile = true;
                $device = $navigator = "mobile";
            }

            //¿is desktop?
            if(!$mobile && !$tablet) {
                $computer = true;
                if (strpos($http_user_agent, 'Chrome') !== false) {
                    $navigator = "chrome";
                }else if (strpos($http_user_agent, 'Firefox') !== false) {
                    $navigator = "firefox";
                }else {
                    $navigator = "explorer";
                }
            }

        }


        $user_agent = ['navigator' => $navigator, 'device' => $device, 'computer' => $computer, 'mobile' => $mobile,'tablet' => $tablet, 'desktop' => ($computer || $tablet) ];

        $user_agent = \team\Filter::apply('\team\user_agent', $user_agent);


        return $key? $user_agent[$key] : $user_agent;
    }


    function redirect($redirect, $code = 301, $protocol = 'http://',  $domain = null) {
        $redirect = \team\Sanitize::internalUrl($redirect);

        if(!$domain) {
            $domain = \team\Context::get('DOMAIN');
        }


        $domain = str_replace($protocol, '',$domain);

        $domain = rtrim($domain, '/');

        header("Location: {$protocol}{$domain}{$redirect}", true, $code);
        exit();
    }

}