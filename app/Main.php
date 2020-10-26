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

            /*
             * Noticed there two post request sent at the end of the process so inform the server
             * that it can redirect the request to actual host
             */

            // I figured out the request key name, but was unable figure out how that data was generated
            // I think it is generated in JS script, since it is minified, so I was unable to figure out the logic written in there
            $firstReqParamKey = ['v_'.$htmlData['ray_id'] => $htmlData['r']];
            $firstPostReq = Request::post($postUrl, $firstReqParamKey);
            // I figured out the request key name, but was unable figure out how that data was generated
            // I think it is generated in JS script, since it is minified, so I was unable to figure out the logic written in there
            $secondReqParamKey = ['v_'.$htmlData['ray_id'] => ''];
            $secondPostReq = Request::post($postUrl, $secondReqParamKey);

            // Now since we have called the all the api's and all the required has be set, we can request for final output by submitting the form
            $finalReq = Request::post($htmlData['action'], $htmlData['form'], [], true);
            return $finalReq->getResponse();
        }

        return $res;
    }

    private function getHtmlBody($body)
    {
        $document = new DOMDocument();
        $document->loadHTML($body);
        $htmlData = [];

        /*
         * Get form action
         */
        $selector = new DOMXPath($document);
        $result = $selector->query('//form');
        $form = $result->item(0);
        if (null !== $form) {
            $htmlData['action'] = $form->getAttribute('action');
        }

        /*
         * Extract the from data from the html form tag
         */
        $formData = [];
        foreach ($document->getElementsByTagName('input') as $input) {
            $formData[$input->getAttribute('name')] = $input->getAttribute('value');
        }
        $htmlData['form'] = $formData;

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
                    // TODO extract other js object properties here, if needed
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
