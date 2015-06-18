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
class AzureTableCredential extends CredentialBase
{
    /**
     *
     * @var AzureTableUtil
     */
    protected $_azureTableUtil;

    /**
     * Construct AzureTableCredential instance.
     *
     * @param string $userName The user Name
     * @return string  $password The Password
     */
    public function AzureTableCredential($accountName, $accountKey, $usePathStyleUri = false)
    {
        $this->_azureTableUtil = new AzureTableUtil($accountName, $accountKey,
                                                    $usePathStyleUri = false);
    }

    /**
     *
     * @param HttpProxy $proxy
     */
    public function SetProxy($proxy)
    {
        //dummy
    }

    /**
     * Set account name for Windows Azure
     *
     * @param string $value
     */
    public function setAccountName($value)
    {
        $this->_azureTableUtil->setAccountName($value);
    }

    /**
     * Set account key for Windows Azure
     *
     * @param string $value
     */
    public function setAccountkey($value)
    {
        $this->_azureTableUtil->setAccountkey($value);
    }

    /**
     * Set use path-style uri
     *
     * @param boolean $value
     */
    public function setUsePathStyleUri($value = false)
    {
        $this->_azureTableUtil->setUsePathStyleUri($value);
    }

    /**
     * To create authorization header for $requestUrl and other required headers.
     *
     * @param Uri $requestUrl
     * @return array
     */
    public function GetSingedHeaders($requestUrl)
    {
        return $this->_azureTableUtil->GetSingedHeaders($requestUrl);
    }

    /**
     * Get credential type.
     *
     * @return CredentailType::*
     */
    public function getCredentialType()
    {
        return CredentialType::AZURE;
    }
};
?>
