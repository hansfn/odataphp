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
class WindowsCredential extends CredentialBase
{
    private $_userName;
    private $_password;

    /**
     * Construct WindowsCredential instance.
     *
     * @param string $userName The doamin and username in the form domain/username
     * @return string  $password The Password
     */
    public function WindowsCredential($userName, $password)
    {
        $this->_userName = $userName;
        $this->_password = $password;
    }

    /**
     * Get domainname\username    .
     *
     */
    public function getUserName()
    {
        return $this->_userName;
    }

    /**
     * Get password.
     *
     */
    public function getPassword()
    {
        return $this->_password;
    }

    /**
     * Dummy function
     *
     * @param HttpProxy $proxy
     */
    public function SetProxy($proxy)
    {
        //dummy
    }

    /**
     * Dummy function
     *
     * @param Url $requestUrl
     */
    public function GetSingedHeaders($requestUrl)
    {
        //dummy
    }

    /**
     * Get credential type.
     *
     * @return CredentailType::*
     */
    public function getCredentialType()
    {
        return CredentialType::WINDOWS;
    }
};
?>