<?php

class Main
{
    private static $host = 'https://devza.com';

    public function init()
    {
        $res = $this->makeGetCall('/cftest.php');

        if ($res['status'] === 503) {
            $htmlData = $this->getHtmlBody($res['body']);
            $postUrl = $this->parseBodyAndGetUrlForXhr($htmlData);
            $paramKey = 'v_'.$htmlData['ray_id'];
            $firstPostReq = $this->makePostRequest($postUrl, [$paramKey => '']);
            print_r($firstPostReq);
            die();
        }

        return $res;
    }

    private function makePostRequest($path, $params)
    {
        $cookiePath = '/tmp/bypass.txt';
        $headers[] = 'content-type: application/json;charset=UTF-8';
        $headers[] = 'accept: application/json';
        $ch = curl_init(self::$host.$path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiePath);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiePath);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_HEADER, 0);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        ob_start();
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        ob_end_clean();
        curl_close ($ch);
        return ['status' => $httpCode, 'body' => $response];
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
         * By looking content of dynamically inject JS file,
         * I noticed that this string is used to for a url for the next xhr request
         */
        $scriptUrl = '/cdn-cgi/challenge-platform/h/g/orchestrate/jsch/v1';
        $scriptData = (string)$this->makeGetCall($scriptUrl)['body'];
        $delimiter = ',/0.';
        $urlPathFromScript = '0.'.explode('/,', explode($delimiter, $scriptData)[1])[0].'/';
        $url = '/cdn-cgi/challenge-platform/h/g/generate/ov1/';
        $url .= $urlPathFromScript;
        $url .= $htmlData['ray_id'].'/';
        $url .= trim($htmlData['cHash']);
        return $url;
    }

    private function makeGetCall($path)
    {

        $cookiePath = '/tmp/bypass.txt';

        $ch = curl_init(self::$host.$path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);

        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiePath);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiePath);

        curl_setopt($ch, CURLOPT_HEADER, 0);

        ob_start();
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        ob_end_clean();
        curl_close ($ch);
        return ['status' => $httpCode, 'body' => $response];
    }
}




$app = new Main();
$res = $app->init();
print_r($res);
die();
