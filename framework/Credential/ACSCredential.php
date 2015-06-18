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

require_once 'CredentialBase.php';

/*
 * @copyright  Copyright (c) 2010, Persistent Systems Limited (http://www.persistentsys.com)
 * @license    http://odataphp.codeplex.com/license
 */
class ACSCredential extends CredentialBase
{
    /**
     * @var ACSUtil
     *
     */
    protected $_acsUtil;

    /**
     * Construct ACSCredential instance.
     *
     * @param <string> $userName The user Name
     * @return <string>  $password The Password
     */
    public function ACSCredential($service_namespace, $wrap_name,
                            $wrap_password, $wrap_scope,
                            $claims = array(), $proxy = null)
    {
        $this->_acsUtil = new ACSUtil($service_namespace, $wrap_name,
                                      $wrap_password, $wrap_scope,
                                      $claims, $proxy);
    }

    /**
     *
     * @param <HttpProxy> $proxy
     */
    public function SetProxy($proxy)
    {
        if(!$this->_acsUtil->HasProxy())
        {
            $this->_acsUtil->SetProxy($proxy);
        }
    }

    /**
     *
     * @param <uri> $requestUrl
     * @return <array>
     */
    public function GetSingedHeaders($requestUrl)
    {
        try
        {
            return $this->_acsUtil->GetSingedHeaders();
        }
        catch(ACSUtilException $exception)
        {
            //re-throw the exception, client should handle this.
            throw $exception;
        }
    }

    /**
     * Get credential type.
     *
     * @return <CredentailType::*>
     */
    public function getCredentialType()
    {
        return CredentialType::ACS;
    }
};
?>
