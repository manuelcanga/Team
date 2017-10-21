<?php
/**
 * Created by PhpStorm.
 * User: trasweb
 * Date: 8/10/16
 * Time: 13:36
 */

namespace team;


abstract class Http
{

    /**
     * Send a HTTP header to disable content type sniffing in browsers which support it.
     *
     *
     * @see http://blogs.msdn.com/ie/archive/2008/07/02/ie8-security-part-v-comprehensive-protection.aspx
     * @see http://src.chromium.org/viewvc/chrome?view=rev&revision=6985
     */
    public static function sendNoSniffHeader() {
        @header( 'X-Content-Type-Options: nosniff' );
    }

    /**
     * Send a HTTP header to limit rendering of pages to same origin iframes.
     *
     *
     * @see https://developer.mozilla.org/en/the_x-frame-options_response_header
     */
    public static function sendFrameOptionsHeader() {
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
    public static function getNoCacheHeaders() {
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
    public static function sendNoCacheHeaders() {
        $headers = self::getNocacheHeaders();

        unset( $headers['Last-Modified'] );
        @header_remove( 'Last-Modified' );

        foreach ( $headers as $name => $field_value )
            @header("{$name}: {$field_value}");
    }

    /**
     * Set the headers for caching for $days days with JavaScript content type.
     */
    public function sendCacheJavascriptHeaders($days = 10) {
        $expiresOffset = $days * \team\Date::A_DAY;

        header( "Content-Type: text/javascript; charset=" . get_bloginfo( 'charset' ) );
        header( "Vary: Accept-Encoding" ); // Handle proxies
        header( "Expires: " . gmdate( "D, d M Y H:i:s", time() + $expiresOffset ) . " GMT" );
    }

    /**
     * Get information about user agent.
     *
     * @params string $key only retrieve $key field of user agent information
     * @params boolean $mustRecheck forcing a check of user agent again
     *
     */
    public static function checkUserAgent($key = null, $mustRecheck = false) {
        static $user_agent;

        if(!$mustRecheck && isset($user_agent) )  {
            return $key? $user_agent[$key] : $user_agent;
        }

        $http_user_agent = $_SERVER['HTTP_USER_AGENT'];

        $mobile = false;
        $computer = false;
        $tablet = false;
        $bot = false;
        $device = 'computer';
        $navigator = 'explorer';

        if(!empty($http_user_agent) ) {
            $is_mobile = strpos($http_user_agent, 'Mobile') !== false;
            $is_android = strpos($http_user_agent, 'Android') !== false;

            //¿is tablet?
            if ( stripos($http_user_agent, 'Tablet') !== false
                || ($is_android && !$is_mobile)
                || strpos($http_user_agent, 'Kindle') !== false
                || strpos($http_user_agent, 'iPad') !== false
                ) {
                $tablet =  true;
                $device = $navigator = "tablet";
            }

            //¿is mobile?
            if(!$tablet && ($is_mobile
                    || strpos($http_user_agent, 'Silk/') !== false
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

                $bot =  strpos($http_user_agent, 'bot') !== false;
            }


        }


        $user_agent = ['navigator' => $navigator, 'device' => $device, 'bot' => $bot,  'computer' => $computer, 'mobile' => $mobile,'tablet' => $tablet, 'desktop' => ($computer || $tablet) ];

        $user_agent = \team\Filter::apply('\team\user_agent', $user_agent);


        return $key? $user_agent[$key] : $user_agent;
    }



    public static function redirect($redirect, $code = 301, $protocol = null) {
        $redirect = \team\Sanitize::internalUrl($redirect);

        $domain = \team\Context::get('DOMAIN');

        $port = \team\Context::get('PORT');
        $with_port = '';
        if('80' != $port) {
            $with_port = ":{$port}";
        }

        $protocol = $protocol?? \team\Context::get('PROTOCOL');

        $domain = str_replace($protocol, '',$domain);
        $domain = rtrim($domain, '/');

        $new_location = "{$protocol}{$domain}{$with_port}{$redirect}";

        header("Location: {$new_location}", true, $code);
        exit();
    }



    /**
     * Retrieve the description for the HTTP status.
     *
     * @param int $code HTTP status code.
     * @return string Empty string if not found, or description if found.
     */
    public static function getStatusHeaderDesc( $code ) {
        static $code2header_desc;

        $code = \team\Check::id( $code, 0);

        if ( !isset( $code2header_desc ) ) {
            $code2header_desc = array(
                100 => 'Continue',
                101 => 'Switching Protocols',
                102 => 'Processing',

                200 => 'OK',
                201 => 'Created',
                202 => 'Accepted',
                203 => 'Non-Authoritative Information',
                204 => 'No Content',
                205 => 'Reset Content',
                206 => 'Partial Content',
                207 => 'Multi-Status',
                226 => 'IM Used',

                300 => 'Multiple Choices',
                301 => 'Moved Permanently',
                302 => 'Found',
                303 => 'See Other',
                304 => 'Not Modified',
                305 => 'Use Proxy',
                306 => 'Reserved',
                307 => 'Temporary Redirect',
                308 => 'Permanent Redirect',

                400 => 'Bad Request',
                401 => 'Unauthorized',
                402 => 'Payment Required',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
                406 => 'Not Acceptable',
                407 => 'Proxy Authentication Required',
                408 => 'Request Timeout',
                409 => 'Conflict',
                410 => 'Gone',
                411 => 'Length Required',
                412 => 'Precondition Failed',
                413 => 'Request Entity Too Large',
                414 => 'Request-URI Too Long',
                415 => 'Unsupported Media Type',
                416 => 'Requested Range Not Satisfiable',
                417 => 'Expectation Failed',
                418 => 'I\'m a teapot',
                421 => 'Misdirected Request',
                422 => 'Unprocessable Entity',
                423 => 'Locked',
                424 => 'Failed Dependency',
                426 => 'Upgrade Required',
                428 => 'Precondition Required',
                429 => 'Too Many Requests',
                431 => 'Request Header Fields Too Large',
                451 => 'Unavailable For Legal Reasons',

                500 => 'Internal Server Error',
                501 => 'Not Implemented',
                502 => 'Bad Gateway',
                503 => 'Service Unavailable',
                504 => 'Gateway Timeout',
                505 => 'HTTP Version Not Supported',
                506 => 'Variant Also Negotiates',
                507 => 'Insufficient Storage',
                510 => 'Not Extended',
                511 => 'Network Authentication Required',
            );
        }

        return $code2header_desc[$code]?? '';
    }


    /**
     * Set HTTP status header.
     *
     * @param int    $code        HTTP status code.
     * @param string $description Optional. A custom description for the HTTP status.
     */
    public static function sendStatusHeader( int $code, $description = '' ) {
        if ( ! $description ) {
           http_response_code($code);
            return ;
        }

        $protocol = \team\Context::get('PROTOCOL');
        $status_header = "$protocol $code $description";

        $status_header = \team\Filter::apply('\team\http\status_header', $status_header, $code, $description, $protocol);

        @header( $status_header, true, $code );
    }
}