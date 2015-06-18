<?php
/**
 * Copyright (c) 2009, RealDolmen
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of RealDolmen nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY RealDolmen ''AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL RealDolmen BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Microsoft
 * @package    Microsoft_Http
 * @version    $Id$
 * @copyright  Copyright (c) 2009, RealDolmen (http://www.realdolmen.com)
 * @license    http://phpazure.codeplex.com/license
 */
class AzureTableUtil
{
    /**
     * Storage header prefix.
     *
     */
    const PREFIX_STORAGE_HEADER  = "x-ms-";

    /**
     * azure api version.
     *
     */
    const AZURE_API_VERSION = '2009-04-14';

    /**
     * Account name for Windows Azure.
     *
     * @var <string>
     */
    protected $_accountName = '';

    /**
     * Account key for Windows Azure.
     *
     * @var <string>
     */
    protected $_accountKey = '';

    /**
     * Use path-style uri
     *
     * @var <string>
     */
    protected $_usePathStyleUri = false;

    /**
     * Construct the AzureTableUtil instance.
     *
     * @param <string> $accountName
     * @param <string> $accountKey
     * @param <uri> $usePathStyleUri
     */
    public function AzureTableUtil($accountName, $accountKey, $usePathStyleUri = false)
    {
        $this->_accountName = $accountName;
	$this->_accountKey = base64_decode($accountKey);
	$this->_usePathStyleUri = $usePathStyleUri;
    }

    /**
     * Set account name for Windows Azure
     *
     * @param <string> $value
     */
    public function setAccountName($value)
    {
        $this->_accountName = $value;
    }

    /**
     * Set account key for Windows Azure
     *
     * @param <string> $value
     */
    public function setAccountkey($value)
    {
        $this->_accountKey = base64_decode($value);
    }

    /**
     * Set use path-style uri
     *
     * @param <boolean> $value
     */
    public function setUsePathStyleUri($value = false)
    {
        $this->_usePathStyleUri = $value;
    }


    /**
     * Prepare query string for signing
     *
     * @param  <string> $value Original query string
     * @return <string> Query string for signing
     */
    protected function prepareQueryStringForSigning($value)
    {
        // Check for 'comp='
        if(strpos($value, 'comp=') === false)
        {
            // If not found, no query string needed
            return '';
        }
        else
        {
            // If found, make sure it is the only parameter being used
            if(strlen($value) > 0 && strpos($value, '?') === 0)
            {
                $value = substr($value, 1);
            }

            // Split parts
            $queryParts = explode('&', $value);
            foreach ($queryParts as $queryPart)
            {
                if (strpos($queryPart, 'comp=') !== false)
                {
                    return '?' . $queryPart;
                }
            }

            // Should never happen...
            return '';
        }
    }

    /**
     * To create authorization header for $requestUrl and other required headers.
     *
     * @param <Uri> $requestUrl
     * @return <array>
     */
    public function GetSingedHeaders($requestUrl)
    {
        $headers = array();
        //extract the query string + path
        //http://host:port/path?querystring
        $parts = $this->Parse_RequestUrl($requestUrl);
        if ($this->_usePathStyleUri)
        {
            $parts['path'] = substr($parts['path'], strpos($parts['path'], '/'));
        }

        $queryString = $this->prepareQueryStringForSigning($parts['query']);
        $canonicalizedResource  = '/' . $this->_accountName;
        if ($this->_usePathStyleUri)
        {
            $canonicalizedResource .= '/' . $this->_accountName;
        }

        $canonicalizedResource .= $parts['path'];
        if ($queryString !== '')
        {
            $canonicalizedResource .= $queryString;
        }

        $requestDate = '';
        if (isset($headers[self::PREFIX_STORAGE_HEADER . 'date']))
        {
            $requestDate = $headers[self::PREFIX_STORAGE_HEADER . 'date'];
        }
        else
        {
            $requestDate = gmdate('D, d M Y H:i:s', time()) . ' GMT'; // RFC 1123
        }

        $stringToSign = array();
        $stringToSign[] = $requestDate; // Date
        $stringToSign[] = $canonicalizedResource;
        $stringToSign = implode("\n", $stringToSign);
        $signString = base64_encode(hash_hmac('sha256', $stringToSign, $this->_accountKey, true));
        //The current version of azure table support only dataservice version 1.0.
        //even though the context set the data service version to 2.0. Setting header
        //here will overwrite that value.
        $headers['DataServiceVersion'] = Resource::DataServiceVersion_1;
        $headers['x-ms-version'] = self::AZURE_API_VERSION;
        $headers[self::PREFIX_STORAGE_HEADER . 'date'] = $requestDate;
        $headers['Authorization'] = 'SharedKeyLite ' . $this->_accountName . ':' . $signString;
        return $headers;
    }

    /**
     * Parse the Url to azure table.
     *
     * @param <uri> $url
     * @return <array>
     */
    protected function Parse_RequestUrl($url)
    {
        $urlParts = parse_url($url);

        if(!isset($urlParts['path']))
        {
            $urlParts['path'] = '/';
        }

        if(!isset($urlParts['query']))
        {
            $urlParts['query'] = '';
        }
        else
        {
            $urlParts['query'] = '?' . $urlParts['query'];
        }

        $urlParts['path'] = str_replace(' ', '%20', $urlParts['path']);
        $urlParts['query'] = str_replace(' ', '%20', $urlParts['query']);
        $urlParts['path'] = str_replace('+', '%20', $urlParts['path']);
        $urlParts['query'] = str_replace('+', '%20', $urlParts['query']);
        return $urlParts;
    }
}
?>
