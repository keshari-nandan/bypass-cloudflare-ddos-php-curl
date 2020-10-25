<?php

namespace App;

include_once 'Response.php';

class Request
{
    private static $host = 'https://devza.com';
    private static $cookiePath;
    public static $useCookieJar = true; // Define we store cookie in file or in array
    public static $debug = false; // Can be used to set the curl verbose

    /**
     * This method can be used to send get request
     *
     * @param $url
     * @param array $headers
     * @param bool $isWebRequest
     *
     * @return bool|string
     */
    public static function get($url, $headers = [], $isWebRequest = false)
    {
        return self::initCurl($url, $headers, [], false, $isWebRequest);
    }


    /**
     * This method can be used to send post request
     *
     * @param $url
     * @param $params
     * @param array $headers
     * @param bool $isWebRequest
     *
     * @return Response
     */
    public static function post($url, $params, $headers = [], $isWebRequest = false)
    {
        return self::initCurl($url, $headers, $params, true, $isWebRequest);
    }


    /**
     * This method initialize the curl request and sends the request based on the parameter given
     *
     * @param $requestUrl
     * @param array $headers
     * @param array $params
     * @param bool $isPost
     * @param bool $isWebRequest
     *
     * @return Response
     */
    public static function initCurlWithFullUrl($requestUrl, $headers = [], $params = [], $isPost = false, $isWebRequest = false)
    {

        if (self::$useCookieJar && !file_exists(self::$cookiePath)) {
            self::$cookiePath = '/tmp/cloudflare_bypass_cookie.txt';
        }

        if (!$isWebRequest) {
            $headers[] = 'content-type: application/json;charset=UTF-8';
            $headers[] = 'accept: application/json';
        }

        $userAgent = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/86.0.4240.80 Safari/537.36";

        $ch = curl_init($requestUrl);
        $responseHeaders = [];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, self::$debug);

        if ($isPost) {
            curl_setopt($ch, CURLOPT_POST, $isPost);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        }
        curl_setopt($ch,CURLOPT_ENCODING , "gzip");
        curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);

        /*
         * If cookiejar (variable: $userCookieJar) is set to true only then cookie file will be used
         * else cookie will be stored in array and will be with request
         */
        if (self::$useCookieJar) {
            curl_setopt($ch, CURLOPT_COOKIESESSION, true);
            curl_setopt($ch, CURLOPT_COOKIEJAR, self::$cookiePath);
            curl_setopt($ch, CURLOPT_COOKIEFILE, self::$cookiePath);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        if (self::$useCookieJar) {
            curl_setopt($ch, CURLOPT_HEADER, 0);
        }

        self::getResponseHeader($ch, $responseHeaders);

        ob_start();
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        ob_end_clean();

        curl_close ($ch);
        return new Response($response, $responseHeaders, $httpCode);
    }

    /**
     * This method initialize the curl request and sends the request based on the parameter given
     *
     * @param $url
     * @param array $headers
     * @param array $params
     * @param bool $isPost
     * @param bool $isWebRequest
     *
     * @return Response
     */
    private static function initCurl($url, $headers = [], $params = [], $isPost = false, $isWebRequest = false)
    {
        return self::initCurlWithFullUrl(self::$host.$url, $headers, $params, $isPost, $isWebRequest);
    }


    /**
     * This method returns the headers of response
     *
     * @param $ch
     * @param $responseHeaders
     */
    private static function getResponseHeader(&$ch, &$responseHeaders)
    {
        curl_setopt($ch, CURLOPT_HEADER, 0);

        // this function is called by curl for each header received
        curl_setopt($ch, CURLOPT_HEADERFUNCTION,
            function($curl, $header) use (&$responseHeaders)
            {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) { // ignore invalid headers
                    return $len;
                }

                $responseHeaders[strtolower(trim($header[0]))][] = trim($header[1]);

                return $len;
            }
        );
    }
}
