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
 *
 * To represent the return value of ObjectContext::GetReadStream API.
 */
class DataServiceStreamResponse
{
    protected $_httpResponse = null;
    protected $_headers = null;

    /**
     * To construct DataServiceStreamResponse instance.
     *
     * @param HttpResponse $httpResponse
     */
    public function DataServiceStreamResponse($httpResponse)
    {
        if(is_object($httpResponse) &&
           is_a($httpResponse, 'Microsoft_Http_Response'))
	{
            $this->_httpResponse = $httpResponse;
        }
        else
        {
            throw new ODataServiceException(Resource::ExpectedValidHttpResponse,
                                            '',
                                            array(),
                                            null);
        }
    }

    /**
     * To get all headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        if($this->_headers == null)
        {
            $this->_headers = $this->_httpResponse->getHeaders();
        }
        return $this->_headers;
    }

    /**
     * To get value of HTTP Content Disposition header
     *
     * @return string
     */

    public function getContentDisposition()
    {
        $headers = self::getHeaders();
        if(isset($headers[HttpRequestHeader::ContentDisposition]))
        {
            return $headers[HttpRequestHeader::ContentDisposition];
        }

        return null;
    }

    /**
     * To get value of HTTP Content Type header.
     *
     * @return string
     */
    public function getContentType()
    {
        $headers = self::getHeaders();
        if(isset($headers[HttpRequestHeader::ContentType]))
        {
            return $headers[HttpRequestHeader::ContentType];
        }

        return null;
    }

    /**
     * To get the associated binary stream.
     *
     * @return binaryStream
     */
    public function getStream()
    {
        return $this->_httpResponse->getBody();
    }
}
?>