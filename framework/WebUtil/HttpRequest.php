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
class HttpRequest
{
    /**
     * curl reference.
     *
     * @var resource
     */
    protected $_curlHandle;

    /**
     * The http request url.
     * @var url
     */
    protected $_url;

    /**
     * The http request headers.
     *
     * @var array
     */
    protected $_curlHeaders;

    /**
     * The http request methos.
     *
     * @var HttpVerb
     */
    protected $_httpMethod;

    /**
     * Handle to the temporary file for PUT operation.
     *
     * @var resource
     */
    protected $_putFileHandle;

    /**
     * The http request body for POST or PUT.
     *
     * @var string
     */
    protected $_requestBody;

    /**
     * The credential required to perform the request.
     *
     * @var Credentials (WinodwsCredential, ACSCredential or AzureTableCredential)
     */
    protected $_credential;

    /**
     * The proxy settings.
     *
     * @var HttpProxy
     */
    protected $_proxy;

    /**
     * The http headers as collection.
     *
     * @var Collection
     */
    public $Headers;

    /**
     * Flag to decide request body type (POST/PUT) if body exists.
     * @var boolean
     */
    protected $_isPostBody;

    /**
     * Construct HttpRequest Instance.
     *
     * @param HttpVerb $httpMethod
     * @param Uri $url
     * @param Credentials $credential
     * @param Proxy $proxy
     * @param array $headers
     * @param string $postBody
     * @param boolean $credentialsInHeader
     */
    public function HttpRequest($httpMethod, $url, $credential, $proxy,
                                $headers = array(), $postBody = null,
                                $credentialsInHeader = false)
    {
        $this->_httpMethod = $httpMethod;
        $this->_url = $url;
        $this->_credential = $credential;
        $this->_proxy = $proxy;
        $this->Headers = new Collection();
        $this->_isPostBody = false;
        $this->_requestBody = null;
        $this->_putFileHandle = null;

        if (!is_null($postBody))
        {
            $this->_isPostBody = true;
            $this->_requestBody = $postBody;
        }

        $this->Headers->CopyFrom($headers);

        $this->_curlHandle = curl_init();
        curl_setopt($this->_curlHandle, CURL_HTTP_VERSION_1_1, true);
        curl_setopt($this->_curlHandle, CURLOPT_HEADER, true);
        curl_setopt($this->_curlHandle, CURLOPT_USERAGENT,
                                        Resource::USER_AGENT);
        curl_setopt($this->_curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->_curlHandle, CURLOPT_RETURNTRANSFER,  true);
        curl_setopt($this->_curlHandle, CURLOPT_FOLLOWLOCATION,  true);
    }

    /**
     * To get the request body as html friendly.
     * @return string
     */
    public function getHTMLFriendlyBody()
    {
        return nl2br(htmlspecialchars($this->_requestBody));
    }

    /**
     * To get the request body for POST or PUT.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->_requestBody;
    }

    /**
     * To set the post request body.
     *
     * @param string $postBody
     */
    public function setPostBody($postBody)
    {
        $this->_requestBody = $postBody;
        $this->_isPostBody = true;
    }

    /**
     *  To set the put request body.
     *
     * @param string $putBody
     */
    public function setPutBody($putBody)
    {
        $this->_requestBody = $putBody;
        $this->_isPostBody = false;
    }

    /**
     * To get the http method.
     *
     * @return HttpVerb
     */
    public function getMethod()
    {
        return $this->_httpMethod;
    }

    /**
     * To set the http method.
     *
     * @param HttpVerb $httpVerb
     */
    public function setMethod($httpVerb)
    {
        $this->_httpMethod =$httpVerb;
    }

    /**
     * To get the request Uri.
     *
     * @return Uri
     */
    public function getUri()
    {
        return $this->_url;
    }

    /**
     * To set the request Uri.
     *
     * @param Uri $url
     */
    public function setUri($url)
    {
        $this->_url = $url;
    }

    /**
     * To get the proxy.
     *
     * @return HttpProxy
     */
    public function getProxy()
    {
        return $this->_proxy;
    }

    /**
     * To set the proxy.
     *
     * @param HttpProxy $proxy
     */
    public function setProxy($proxy)
    {
        $this->_proxy = $proxy;
    }

    /**
     * To get the credential.
     *
     * @return ACSCredential/AzureTableCredential/WindowsCredential
     */
    public function getCredential()
    {
        return $this->_credential;
    }

    /**
     * To set the credential.
     *
     * @param $credential ACSCredential/AzureTableCredential/WindowsCredential
     */
    public function setCredential($credential)
    {
        $this->_credential = $credential;
    }

    /**
     * To set up curl instance based on options, before invoking the request.
     *
     * @throws InvalidOperation
     */
    protected function _finalize()
    {
        curl_setopt($this->_curlHandle, CURLOPT_URL, $this->_url);
        switch ($this->_httpMethod)
        {
            case HttpVerb::GET:
                curl_setopt($this->_curlHandle, CURLOPT_HTTPGET, true);
                break;
            case HttpVerb::POST:
                curl_setopt($this->_curlHandle, CURLOPT_POST, true);
                break;
            case HttpVerb::PUT:
                curl_setopt($this->_curlHandle, CURLOPT_PUT, true);
                break;
            default: /*To handle MERGE, DELETE, HEAD etc..*/
                curl_setopt($this->_curlHandle, CURLOPT_CUSTOMREQUEST,
                            $this->_httpMethod);
                break;
        }

        if (isset($this->_credential))
        {
            if($this->_credential->getCredentialType() == CredentialType::WINDOWS)
            {
                $userNamePwd = $this->_credential->getUserName() .
                               ":" .
                               $this->_credential->getPassword();
                curl_setopt($this->_curlHandle, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                curl_setopt($this->_curlHandle, CURLOPT_USERPWD, $userNamePwd);
            }
            else
            {
                $this->_credential->SetProxy($this->_proxy);
                $headers = $this->_credential->GetSingedHeaders($this->_url);
                $this->Headers->CopyFrom($headers);
            }
        }

        if (isset($this->_proxy))
        {
            $proxyip_port = $this->_proxy->getProxyAddress() .
                            ":" .
                            $this->_proxy->getProxyPort();
            curl_setopt($this->_curlHandle, CURLOPT_PROXY, $proxyip_port);
            $proxyUserName = $this->_proxy->getUserName();
            if($proxyUserName)
            {
            	curl_setopt($this->_curlHandle, CURLOPT_PROXYUSERPWD,
                            $proxyUserName . ":" . $this->_proxy->getPassword());
                curl_setopt($this->_curlHandle, CURLOPT_HTTPPROXYTUNNEL, 1);
            }
        }

         if (!is_null($this->_requestBody))
        {
            if($this->_isPostBody)
            {
                if($this->_httpMethod != HttpVerb::POST &&
                   $this->_httpMethod != HttpVerb::MERGE)
                {
                    throw new InvalidOperation(Resource::InvalidHttpVerbForSetPostBody);
                }

                curl_setopt($this->_curlHandle, CURLOPT_POSTFIELDS,
                            $this->_requestBody);
                //Here we should to set 'Content-Length' Header which is equal to
                //the length of POST body. But setting this header explicitly
                //cause server to return 'HTTP Error 400. The request verb is invalid'
                //if we pass credential. Might be in cURL credential may be passed
                //along with POST body, so the length we specifed not be correct.
                //So do not specifiy this header. cURL will set this field based
                //on correct post size
            }
            else
            {
                $this->_putFileHandle = tmpfile();
                fwrite($this->_putFileHandle, $this->_requestBody);
                fseek($this->_putFileHandle, 0);
                curl_setopt($this->_curlHandle, CURLOPT_INFILE,
                            $this->_putFileHandle);
                curl_setopt($this->_curlHandle, CURLOPT_INFILESIZE,
                            strlen($this->_requestBody));
            }
        }

        $headers1 = array();
        $headers2 = $this->Headers->GetAll();
        foreach ($headers2 as $key => $value)
        {
            $headers1[] = $key.': '.$value;
        }

        if(!empty($headers1))
        {
            curl_setopt($this->_curlHandle, CURLOPT_HTTPHEADER, $headers1);
        }
    }

    /**
     * To set http headers. This function will merge the $headers with existing
     * headers.
     *
     * @param array $headers
     */
    public function ApplyHeaders($headers = array())
    {
        $this->Headers->CopyFrom($headers);
    }

    /**
     * To get the headers as array of key-value pairs.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->Headers->GetAll();
    }


   /**
    * To perform a HTTP request and return the response as raw string.
    *
    * @return string
    * @throws InvalidOperation
    */
    public function GetResponse()
    {
        $this->_finalize();
        $httpResponse = curl_exec($this->_curlHandle);
        if ($httpResponse)
        {
            if($this->_putFileHandle != null)
            {
                fclose($this->_putFileHandle);
            }

            curl_close($this->_curlHandle);
            return $httpResponse;
        }
        else
        {
            throw new InvalidOperation('Error occured during request for ' .
                                        $this->_url . ': ' .
                                        curl_error($this->_curlHandle));
        }
    }
}
?>