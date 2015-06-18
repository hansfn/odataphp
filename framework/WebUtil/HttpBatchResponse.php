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
class HttpBatchResponse
{
    /**
     * Collection of HttpResponse objects, each represents one changeset.
     *
     * @var HttpResponse[]
     */
    protected $_httpResponses;

    /**
     * The raw http batch response.
     *
     * @var string
     */
    protected $_rawHttpBatchResponse;

    /**
     *
     * @var string
     */
    protected $_correctHttpLine;

    /**
     * The changeset boundary.
     *
     * @var string
     */
    protected $_changesetBoundary;

    /**
     * Construct a HttpBatchResponse.
     *
     * @param Microsoft_Http_Response[] $httpResponses
     * @param string $rawHttpBatchResponse
     * @return string No Return value
     * Construct a HttpBatchResponse object
     */
    public function HttpBatchResponse($httpResponses, $rawHttpBatchResponse,
                                      $changesetBoundary)
    {
        $this->_httpResponses = $httpResponses;
        $this->_rawHttpBatchResponse = $rawHttpBatchResponse;
        $this->_changesetBoundary = $changesetBoundary;
        $this->_correctHttpLine = self::ExtractCorrectHttpLine($rawHttpBatchResponse);
    }

    /**
     * To extract Http Code.
     * HTTP Batch Response can be in one of the two formats
     *	1. HTTP/1.1 100 Continue [newline][newline] HTTP/1.1 Code Message
     *     In this case the actual code is the code part of HTTP string in second line
     *	2. HTTP/1.1 Code Message
     *
     * @return int
     */
    public function GetCode()
    {
        if($this->_correctHttpLine != null)
        {
            preg_match("|^HTTP/[\d\.x]+ (\d+)|", $this->_correctHttpLine, $m);
            if (isset($m[1])) { return (int)$m[1]; }
        }

        return false;
    }

    /**
     * To extract Http message.
     * HTTP Batch Response can be in one of the two formats
     *	1. HTTP/1.1 100 Continue [newline][newline] HTTP/1.1 Code Message
     *     In this case the actual code is the Message part of HTTP string in second line
     *	2. HTTP/1.1 Code Message
     *
     * @return int
     */
    public function GetMessage()
    {
        if($this->_correctHttpLine != null)
        {
            preg_match("|^HTTP/[\d\.x]+ \d+ ([^\r\n]+)|",
                       $this->_correctHttpLine, $m);
            if (isset($m[1])) { return $m[1]; }
        }
        return false;
    }

    /**
     * This function return true if batch header contains any error else false
     * Note that this is the error returned by webserver[IIS], not by OData service.
     * If any data service error happened then there will be only one changeset
     * and HTTP header of that changeset contains actual error and HTTP status
     * message of batch response will be 'Accepted'
     *
     * @return boolean
     */
    public function IsError()
    {
        if($this->_correctHttpLine != null)
        {
            preg_match("|^HTTP/[\d\.x]+ (\d+)|", $this->_correctHttpLine, $m);
            if (isset($m[1]))
            {
                $floorVal = floor((int)$m[1] / 100);
                return ($floorVal == 4 || $floorVal == 5);
            }
        }

        return false;
    }

    /**
     * To extract toplevel headers of batchresponse.
     *
     * @return array
     */
    public function GetHeaders()
    {
        return HttpResponse::extractHeaders($this->_rawHttpBatchResponse);
    }

    /**
     * To get the batch response body.
     *
     * @return string
     */
    public function GetRawHttpResponse()
    {
        return $this->_rawHttpBatchResponse;
    }

    /**
     * Create an HttpResponse object from batchRepsonse.
     *
     * @return Microsoft_Http_Response
     */
    public function GetAsHttpResponse()
    {
        $parts = explode("--" . $this->_changesetBoundary,
                         $this->_rawHttpBatchResponse, 2);
        return new Microsoft_Http_Response($this->GetCode(),
                                           HttpResponse::extractHeaders($parts[0]),
                                           (count($parts) == 2) ?
                                           ('--' . $this->_changesetBoundary . "\n" . $parts[1]) :
                                           $parts[0]);
    }


    /**
     * To get array of Microsoft_Http_Response, each represents a changeset.
     *
     * @return array
     */
    public function GetSubBatchHttpResponses()
    {
        return $this->_httpResponses;
    }

    /**
     * Construct a HttpBatchResponse object from raw batch response.
     *
     * @param string $httpBatchResponse
     * @param HttpBatchResponse
     */
    public static function Create($httpBatchResponse)
    {
        $changesetBoundary = null;
        $httpResponses = array();
        if (!self::CheckIsError($httpBatchResponse))
        {
            $changesetBoundary = HttpBatchResponse::ExtractChangesetBoundary($httpBatchResponse);
            if (!isset($changesetBoundary))
            {
                throw new InvalidOperation(Resource::InvalidBatchResponseNoCSBoundary);
            }

            $httpResponses = HttpBatchResponse::ExtractHttpResponses($httpBatchResponse,
                                                                     $changesetBoundary);
        }

        return new HttpBatchResponse($httpResponses, $httpBatchResponse,
                                     $changesetBoundary);
    }

    /**
     * This function returns true if batch header of $rawHttpBatchResponse
     * contains HTTP status code for error else false.
     *
     * @param string $rawHttpBatchResponse
     * @return bool
     */
    protected static function CheckIsError($rawHttpBatchResponse)
    {
        $httpLine = self::ExtractCorrectHttpLine($rawHttpBatchResponse);
        if($httpLine != null)
        {
            preg_match("|^HTTP/[\d\.x]+ (\d+)|", $httpLine, $m);
            if (isset($m[1]))
            {
                $floorVal = floor((int)$m[1] / 100);
                return ($floorVal == 4 || $floorVal == 5);
            }
        }

        return false;
    }

    /**
     * To extract the final HTTP line. For example if Windows auth is enabled then
     * the http response will contains 3 http headers. First two headers will be
     * auth related and 3rd will be the actual IIS response specific to the request.
     * We need to extract the 3rd one. Similarily in the case of Digest auth the
     * repsonse will contains one auth header and one header specific to the request.
     *
     * @param string $rawHttpBatchResponse
     * @return string http line (ex: HTTP/1.1 202 Accepted)
     *
     */
    protected static function ExtractCorrectHttpLine($rawHttpBatchResponse)
    {
        if(preg_match_all("|HTTP/[\d\.x]+ \d+ [^\r\n]+|", $rawHttpBatchResponse,
                          $multiArray, PREG_OFFSET_CAPTURE))
        {
            if (isset($multiArray[0]))
            {
                if (!(isset($multiArray[0][0]) &&
                      isset($multiArray[0][0][0])))
                {
                    return null;
                }

                $prevHeader = $multiArray[0][0][0];
                $index = self::ExtractBatchBoundaryIndex($rawHttpBatchResponse);
                unset($multiArray[0][0]);
                //If BatchBoundry tag is not present, then return the last HTTP
                //line from the collection.
                if($index == -1)
                {
                    $count = count($multiArray[0]);
                    if($count > 0)
                    {
                        $prevHeader = $multiArray[0][$count][0];
                    }
                }
                else
                {
                    foreach($multiArray[0] as $array)
                    {
                        if ($array[1] > $index)
                        {
                            break;
                        }

                        $prevHeader = $array[0];
                    }
                }

                return $prevHeader;
            }
        }

        return null;
    }

    /**
     * To extract the changeset boundary, if not exists return null.
     *
     * @param string $rawHttpBatchResponse
     * @return string
     */
    protected static function ExtractChangesetBoundary($httpBatchResponse)
    {
        preg_match("|boundary=(changesetresponse_[^\r\n]+)|",
                   $httpBatchResponse, $m);
        if (isset($m[1])) { return $m[1]; }
        return null;
    }

    /**
     * To extract the batch boundary index.
     *
     * @param string $rawHttpBatchResponse
     * @return int The index of batch boundary string
     */
    protected static function ExtractBatchBoundaryIndex($httpBatchResponse)
    {
        preg_match("|boundary=(batchresponse_[^\r\n]+)|", $httpBatchResponse,
                   $m, PREG_OFFSET_CAPTURE);
        if (isset($m[1]) && isset($m[1][1])) { return $m[1][1]; }

        return -1;
    }

    /**
     * This function will extract the changesets from raw batch response and generate
     * a list of Microsoft_Http_Response objects.
     *
     * @param string $rawHttpBatchResponse
     * @param string changesetBoundary
     * @return array
     */
    protected static function ExtractHttpResponses($httpBatchResponse, $changesetBoundary)
    {
        $httpResponses = array();
        $parts = explode( "--" . $changesetBoundary, $httpBatchResponse);
        $count = count($parts);
        if($count < 2)
        {
            return $httpResponses;
        }

        unset($parts[0]);
        unset($parts[$count-1]);
        foreach ($parts as $changeSet)
        {
            $subParts = preg_split('|(?:\r?\n){2}|m', $changeSet, 2);
            $httpResponses[] = Microsoft_Http_Response::fromString($subParts[1]);
        }

        return $httpResponses;
    }
}
?>