<?php

include_once 'Request.php';
include_once 'Response.php';

use App\Request;
use App\Response;

class Main
{
    private static $host = 'https://devza.com';
    private static $expectCt;
    private static $reportTo;

    public function init()
    {
        $res = Request::get('/cftest.php');

        if ((int)$res->getStatusCode() === 503) {
            $htmlData = $this->getHtmlBody($res->getResponse());
            $postUrl = $this->parseBodyAndGetUrlForXhr($htmlData);
            $paramKey = 'v_'.$htmlData['ray_id'];
            $firstPostReq = Request::post($postUrl, [$paramKey => '']);
            print_r($firstPostReq->getHeaders());
            die();
        }

        return $res;
    }

    private function getHtmlBody($body)
    {
        $document = new DOMDocument();
        $document->loadHTML($body);
        $htmlData = [];

        /*
         * Extract the from data from the html form tag
         */
        foreach ($document->getElementsByTagName('input') as $input) {
            $htmlData[$input->getAttribute('name')] = $input->getAttribute('value');
        }

        /*
         * Get the ray_id from the html
         */
        foreach ($document->getElementsByTagName('code') as $code) {
            $htmlData['ray_id'] = $code->nodeValue;
        }

        /*
         * Extract the JS variables inside the script tag
         */
        foreach ($document->getElementsByTagName('script') as $script) {
            if ($script instanceof DOMElement) {
                $scriptValue = $script->nodeValue;
                $scriptFirstExplode = explode('window._cf_chl_opt=', $scriptValue);
                $scriptSecondExplode = explode("window._cf_chl_enter =", $scriptFirstExplode[1]);
                $scriptVariables = explode(',', $scriptSecondExplode[0]);
                foreach ($scriptVariables as $index => $str) {
                    if ($index === 0) {
                        $str = str_replace('{', '', $str);
                    }

                    if ($index < 6) {
                        $str = trim($str);
                        $data = explode(':', $str);
                        if (count($data) > 1) {
                            $htmlData[$data[0]] = str_replace('"', '', $data[1]);
                        }
                    }
                    // TODO extract other js object properties too if needed
                }
            }
        }

        return $htmlData;
    }

    private function parseBodyAndGetUrlForXhr($htmlData)
    {
        /*
         * I got this url by analysing html returned by the url https://devza.com/cftest.php
         * This $scriptUrl url will return a minified JS which contains the information needed to inform
         */
        $scriptUrl = '/cdn-cgi/challenge-platform/h/g/orchestrate/jsch/v1';
        $res = Request::get($scriptUrl);
        $headers = $res->getHeaders();
        if (array_key_exists('expect-ct', $headers)) {
            self::$expectCt = $headers['expect-ct'];
        }
        if (array_key_exists('report-to', $headers)) {
            self::$reportTo = $headers['report-to'];
        }

        $scriptData = (string)$res->getResponse();
        $delimiter = ',/0.';
        $urlPathFromScript = '0.'.explode('/,', explode($delimiter, $scriptData)[1])[0].'/';
        $url = '/cdn-cgi/challenge-platform/h/g/generate/ov1/';
        $url .= $urlPathFromScript;
        $url .= $htmlData['ray_id'].'/';
        $url .= trim($htmlData['cHash']);

        /*
         * This will form a url which is used at the end to tell the server
         * that browser is safe and then server passes the request to actual host.
         *
         * I noticed as below -
         *
         * Url: https://devza.com/cdn-cgi/challenge-platform/h/g/generate/ov1/0.8121086861055473:1603645414:03d11a3202107c611cce457b50ab30a8c2b5b27edd662c638dc9157f3c8191d4/5e7d8955ac641acc/2bb648d50bfec4d
         *      <----------- Fixed -----------------------------------------><---- Extracted by loading the dynamically inject js and then parsing the js script ----------><-- Ray ID -----><-- cHash(JS variable) ->
         */

        return $url;
    }
}


$app = new Main();
$res = $app->init();
print_r($res);
die();
