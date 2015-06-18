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
class OperationResponse
{
    /**
     * To hold the HttpHeaders of the HttpResponse object containing query result.
     *
     * @var array
     */
    protected $_headers;

    /**
     * To hold the exception string if OData service throws any error while running
     * the query.
     *
     * @var string
     */
    protected $_innerException;

    /**
     * To hold the HttpStatusCode of the HttpResponse object containing query result.
     *
     * @var int
     */
    protected $_statusCode;

    /**
     * Construct OperationResponse instance.
     *
     * @param array $headers
     * @param string $error
     * @param int $statusCode
     */
    public function OperationResponse($headers, $error, $statusCode)
    {
        $this->_headers = $headers;
        $this->_innerException = $error;
        $this->_statusCode = $statusCode;
    }

    /**
     * To get error
     *
     * @return string
     */
    public function getError()
    {
        return $this->_headers;
    }

    /**
     * To get Http Headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_innerException;
    }

    /**
     * To get Http Status code.
     *
     * @return int
     */
    public function getStatusCode()
    {
       return $this->_statusCode;
    }
}
?>