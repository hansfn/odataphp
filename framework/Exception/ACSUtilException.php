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
class ACSUtilException extends Exception
{
    /**
     *
     * @var string
     */
    protected $_error;

    /**
     *
     * @var array
     */
    protected $_headers;

    /**
     *
     * @var int
     */
    protected $_statusCode;

    /**
     * Construct a ACSUtilException instance.
     *
     * @param string $errorStr Error String as plain string or Atom format
     * @param string $content_type
     */
    public function ACSUtilException($errorStr, $headers = array(), $statusCode = '')
    {
        $this->_error = $errorStr;
        $this->_headers = $headers;
        $this->_statusCode = $statusCode;
    }

    /**
     * To get the error.
     *
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * To get http headers if any associated with the error.
     *
     * @return array
     */
    public function getHeaders()
    {
	return $this->_headers;
    }

    /**
     * To get http status code associated with the error
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->_statusCode;
    }
 }
?>
