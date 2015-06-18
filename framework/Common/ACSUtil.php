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
require_once 'Resource/Messages.php';
require_once 'Common/Collection.php';
require_once 'WebUtil/HttpVerb.php';
require_once 'WebUtil/HttpRequest.php';
require_once 'WebUtil/Microsoft_Http_Response.php';
require_once 'WebUtil/HttpResponse.php';
require_once 'Common/HttpProxy.php';
require_once 'Exception/ACSUtilException.php';
require_once 'Exception/InvalidOperation.php';

/*
 * @copyright  Copyright (c) 2010, Persistent Systems Limited (http://www.persistentsys.com)
 * @license    http://odataphp.codeplex.com/license
 */
class ACSUtil
{
    protected $_service_namespace;
    protected $_wrap_name;
    protected $_wrap_password;
    protected $_wrap_scope;
    protected $_claims;
    protected $_proxy;
    protected $_token;

    /**
     * Construct ACSUtil instance.
     *
     * @param $service_namespace The Service namespace
     * @param $wrap_name The user name
     * @param $wrap_password The password i.e. issuer key
     * @param $wrap_scope Applies To
     * @param $claims array of claims
     * @param <HttpProxy> $proxy
     */
    public function ACSUtil($service_namespace, $wrap_name,
                            $wrap_password, $wrap_scope,
                            $claims = array(), $proxy = null)
    {
        $this->_service_namespace = $service_namespace;
        $this->_wrap_name = $wrap_name;
        $this->_wrap_password = $wrap_password;
        $this->_wrap_scope = $wrap_scope;
        $this->_claims = $claims;
        $this->_proxy = $proxy;
    }

    /**
     * To create authorization header.
     *
     * @return <array>
     */
    public function GetSingedHeaders()
    {
        $this->GetACSToken();
        return array('authorization' => 'WRAP access_token="' .
                                        urldecode($this->_token) . '"');
    }

    /**
     * To set the proxy.
     *
     * @param <HttpProxy> $proxy
     */
    public function SetProxy($proxy)
    {
        $this->_proxy = $proxy;
    }

    /**
     *
     * @return <boolean>
     */
    public function HasProxy()
    {
        return isset($this->_proxy);
    }

    /**
     * To get toekn from ACS.
     *
     * @return <string>
     * @throws ACSUtilException
     */
    public function GetACSToken()
    {
        $postBody = 'wrap_name' .     '=' . urlencode($this->_wrap_name) . '&' .
                    'wrap_password' . '=' . urlencode($this->_wrap_password) . '&' .
                    'wrap_scope'    . '=' . $this->_wrap_scope;
        foreach ($this->_claims  as $key => $value)
        {
        	$postBody  = $postBody . '&' . $key . '=' . $value;
        }

    	$url = 'https://' . $this->_service_namespace . '.' .
               'accesscontrol.windows.net' . '/' . 'WRAPv0.9';
    	$httpRequest = new HttpRequest(HttpVerb::POST, $url, null,
                                       $this->_proxy, array(), $postBody,
                                       false);
    	$httpRawResponse = null;
    	try
    	{
    		$httpRawResponse = $httpRequest->GetResponse();
    	}
    	catch(InvalidOperation $exception)
    	{
    		throw new ACSUtilException($exception->getError(), array(), null);
    	}

    	$httpResponse = HttpResponse::fromString($httpRawResponse);
        if($httpResponse->isError())
        {
        	throw new ACSUtilException($httpResponse->getMessage() .
                                           '<br/>' .
                                           $httpResponse->getBody(),
                                           $httpResponse->getHeaders(),
                                           $httpResponse->getCode());
        }

        $this->_token = $httpResponse->getBody();
        if (strpos($this->_token, 'Error') !== false)
        {
            throw new ACSUtilException('Invalid Token received:' . $this->_token,
                                       array(),
                                       null);
        }

        $params = explode('&', $this->_token);
        if (isset($params[0]) && strpos($params[0], 'wrap_access_token') === 0)
        {
                $parts = explode('=', $params[0]);
                $this->_token = $parts[1];
        }
        else
        {
        	throw new ACSUtilException('Invalid Token received:' . $this->token,
                                           array(),
                                           null);
        }

        return $this->_token;
    }
}
?>
