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

require_once 'Parser/AtomParser.php';

/*
 * @copyright  Copyright (c) 2010, Persistent Systems Limited (http://www.persistentsys.com)
 * @license    http://odataphp.codeplex.com/license
 */
class ODataServiceException extends Exception
{
    protected $_error;
    protected $_detailedError;
    protected $_headers;
    protected $_statusCode;

    /**
     * The error returned by OData service will be in Atom format in this case
     * AtomParser will be used to retrive exact error from the atom, if some
     * other error happens for example, if credentials are wrong, in this case
     * IIS will retruns error in plain string format.
     *
     * @param string $errorStr Error String as plain string or Atom format
     * @param string $content_type
     */
    public function ODataServiceException($errorStr, $content_type = '',
                                          $headers = array(), $statusCode = '')
    {
        $this->_headers = $headers;
        $this->_statusCode = $statusCode;
        AtomParser::GetErrorDetails($errorStr, $this->_error,
                                    $this->_detailedError, $content_type);
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
     * To get the detailed error description.
     *
     * @return string
     */
    public function getDetailedError()
    {
        return $this->_detailedError;
    }

    /**
     * To get http headers associated with the error
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
