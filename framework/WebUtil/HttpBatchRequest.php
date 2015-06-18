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
class HttpBatchRequest
{

    /**
     * Instance of HttpRequest to perfrom batch request.
     *
     * @var HttpRequest
     */
    protected $_httpRequest;

    /**
     * * Construct a HttpBatchRequest instance.
     *
     * @param string $url
     * @param string $batchBoundary
     * @param string $batchRequestBody
     * @param Credential $credential
     * @param array $headers
     * @return No return Value
     */
    public function HttpBatchRequest($url, $batchBoundary, $batchRequestBody,
                                     $credential, $proxy, $headers = array(),
                                     $credentialsInHeaders = false)
    {
        $requestHeaders = array();
        foreach ($headers as $key => $value)
        {
            $requestHeaders[$key] = $value;
        }

        $requestHeaders['Accept'] = 'application/atom+xml,application/xml';
        $requestHeaders['Content-Type'] = "multipart/mixed; boundary=" . $batchBoundary;
        $requestHeaders['DataServiceVersion'] = Resource::DataServiceVersion_1;
        $requestHeaders['MaxDataServiceVersion'] = Resource::DataServiceVersion_2;
        $this->_httpRequest = new HttpRequest('POST', $url, $credential, $proxy,
                                              $requestHeaders, $batchRequestBody,
                                              $credentialsInHeaders);
    }

    /**
     * To get the HttpReqest which will be used to perfrom batch request.
     *
     * @return HttpRequest
     */
    public function GetRawHttpRequest()
    {
        return $this->_httpRequest;
    }

    /**
     * To perform a http batch request and return instance of HTTPBatchResponse
     * class representing the batch response.
     *
     * @return HttpBatchResponse
     */
    public function GetResponse()
    {
        $rawHttpBatchResponse = $this->_httpRequest->GetResponse();
        return HttpBatchResponse::Create($rawHttpBatchResponse);
    }
}
?>
