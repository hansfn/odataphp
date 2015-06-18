<?php
/*
   Copyright 2010 Persistent Systems Limited

   Licensed under the Apache License, Version 2.0 (the "License");
   you may not use this file except in compliance with the License.
   You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

   Unless required by applicable law or agreed to in writing, software
   distributed under the License is distributed on an "AS IS" BASIS,
   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
   See the License for the specific language governing permissions and
   limitations under the License.
 */

/*
 * @copyright  Copyright (c) 2010, Persistent Systems Limited (http://www.persistentsys.com)
 * @license    http://odataphp.codeplex.com/license
 */
class HttpResponse extends Microsoft_Http_Response
{
    /**
     *
     * This function will extract the final HTTP line. For example if Windows auth
     * is enabled then the http response will contains 3 http headers. First two
     * headers will be auth related and 3rd will be the actual IIS response specific
     * to the request. We need to extract the 3rd one. Similarily in the case of
     * Digest auth the repsonse will contains one auth header and one header specific
     * to the request.
     *
     * @param string $rawHttpBatchResponse
     * @param int [out] $index
     * @return string http line (ex: HTTP/1.1 202 Accepted) on return $index will
     * contains the index of http line.
     */
    protected static function ExtractCorrectHttpLine($rawHttpResponse, &$index = 0)
    {
        if(preg_match_all ("|HTTP/[\d\.x]+ \d+ [^\r\n]+|", $rawHttpResponse,
                           $multiArray, PREG_OFFSET_CAPTURE))
        {
            if (isset($multiArray[0]))
            {
                if (!(isset($multiArray[0][0]) &&
                      isset($multiArray[0][0][0])))
                {
                    return null;
                }

                $correctHeaderIndex = count($multiArray[0]) - 1;
                $index = $multiArray[0][$correctHeaderIndex][1];
                return $multiArray[0][$correctHeaderIndex][0];
            }
        }

        return null;
    }

    /**
     * To retrive the final http header and body from the response. The http
     * response from sever can contains multiple headers (for ex: if auth is
     * enabled on the server). This function extract the final http headers
     * and body.
     *
     * @param string $rawHttpResponse
     */
    protected static function ExtractCorrectResponse($rawHttpResponse)
    {
        $index;
        self::ExtractCorrectHttpLine($rawHttpResponse, $index);
        return  substr($rawHttpResponse, $index);
    }

    /**
     * To extract the response code from a response string.
     *
     * @param string $rawResponseString
     * @return int
     */
    public static function extractCode($rawResponseString)
    {
        $responseString = self::ExtractCorrectResponse($rawResponseString);
        return parent::extractCode($responseString);
    }

     /**
     * To extract the HTTP message from a response.
     *
     * @param string $rawResponseString
     * @return string
     */
    public static function extractMessage($rawResponseString)
    {
        $responseString = self::ExtractCorrectResponse($rawResponseString);
        return parent::extractMessage($responseString);
    }

    /**
     * To extract the HTTP version from a response.
     *
     * @param string $rawResponseString
     * @return string
     */
    public static function extractVersion($rawResponseString)
    {
        $responseString = self::ExtractCorrectResponse($rawResponseString);
        return parent::extractVersion($responseString);
    }

    /**
     * To extract the headers from a response string.
     *
     * @param string $rawResponseString
     * @return array
     */
    public static function extractHeaders($rawResponseString)
    {
        $responseString = self::ExtractCorrectResponse($rawResponseString);
        return parent::extractHeaders($responseString);
    }

    /**
     * To extract the body from a response string.
     *
     * @param string $response_str
     * @return string
     */
    public static function extractBody($rawResponseString)
    {
        $responseString = self::ExtractCorrectResponse($rawResponseString);
        return parent::extractBody($responseString);
    }

    /**
     * Create a new Microsoft_Http_Response object from a string.
     *
     * @param string $response_str
     * @return Microsoft_Http_Response
     */
    public static function fromString($response_str)
    {
        $code    = self::extractCode($response_str);
        $headers = self::extractHeaders($response_str);
        $body    = self::extractBody($response_str);
        $version = self::extractVersion($response_str);
        $message = self::extractMessage($response_str);
        return new Microsoft_Http_Response($code, $headers, $body, $version, $message);
    }
}
?>