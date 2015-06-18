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
class HttpProxy
{
    /**
     *
     * @var <url>
     */
    private $_address;

    /**
     *
     * @var <int>
     */
    private $_port;

    /**
     *
     * @var <string>
     */
    private $_username;

    /**
     *
     * @var <string>
     */
    private $_password;

    /**
     * Construct HttpProxy instance.
     *
     * @param <string> $address
     * @param <int> $port
     * @param <string> $username
     * @param <string> $password
     */
    public function HttpProxy($address, $port, $username = null, $password = null)
    {
        $this->_address = $address;
        $this->_port = $port;
        $this->_username = $username;
        $this->_password = $password;
    }

    /**
     * To get proxy address.
     *
     * @return <string> Proxy Address
     */
    public function getProxyAddress()
    {
        return $this->_address;
    }

    /**
     * To get proxy port.
     *
     * @return <string> Proxy Port
     */
    public function getProxyPort()
    {
        return $this->_port;
    }

    /**
     * To get proxy user name.
     *
     * @return <string> Proxy UserName
     */
    public function getUserName()
    {
  		return $this->_username;
    }

    /**
     * To get proxy password.
     *
     * @return <string> Proxy Password
     */
    public function getPassword()
    {
    	return $this->_password;
    }
};
?>