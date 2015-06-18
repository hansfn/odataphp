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
 * To Represent additional metadata that is included in a request message to the OData service.
 */
class DataServiceRequestArgs
{
    /**
     *
     * @var array
     */
    protected $_headers;

    /**
     * To construct DataServiceRequestArgs instance.
     *
     */
    public function DataServiceRequestArgs()
    {
        $this->_headers = array();
    }

    /**
     * To get a specific HTTP header.
     *
     * @param HttpRequestHeader::* $header
     * @Return string
     */
    protected function GetHeaderValue($header)
    {
        if(array_key_exists($header, $this->_headers))
        {
            return $this->_headers[$header];
        }

        return null;
    }

    /**
     * To set a header value.
     *
     * @param HttpRequestHeader::* $header
     * @param string $value
     */
    protected function SetHeaderValue($header, $value)
    {
        if($value == null)
        {
            if(array_key_exists($header, $this->_headers))
            {
                unset($this->_headers[$header]);
            }
        }
        else
        {
            $this->_headers[$header] = $value;
        }
    }

    /**
     * To get the Accept header of the request message.
     *
     * @param string $value
     */
    public function getAcceptContentType()
    {
        return self::GetHeaderValue(HttpRequestHeader::Accept);
    }

    /**
     * To set the Accept header of the request message.
     *
     * @param string $value
     */
    public function setAcceptContentType($value)
    {
        self::SetHeaderValue(HttpRequestHeader::Accept, $value);
    }

    /**
     * To get the Content-Type header of the request message.
     *
     * @param string $value
     */
    public function getContentType()
    {
        return self::GetHeaderValue(HttpRequestHeader::ContentType);
    }

    /**
     * To set the Content-Type header of the request message.
     *
     * @param string $value
     */
    public function setContentType($value)
    {
        self::SetHeaderValue(HttpRequestHeader::ContentType, $value);
    }

    /**
     * To get the Slug header of the request message.
     *
     * @return string
     */
    public function getSlug()
    {
        return self::GetHeaderValue(HttpRequestHeader::Slug);
    }

    /**
     * To set the Slug header of the request message.
     *
     * @return string
     */
    public function setSlug($value)
    {
        self::SetHeaderValue(HttpRequestHeader::Slug, $value);
    }

    /**
     * To get all headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_headers;
    }
}
?>