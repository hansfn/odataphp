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
 *
 * To represent the return value of ObjectContext::Execute and DataServiceQuery::Execute
 * methods.
 */
class QueryOperationResponse
{
    protected $_headers;
    protected $_innerException;
    protected $_statusCode;
    protected $_query;
    /**
     *
     * @var EntityCollction.
     * Result of the query execution as collection of entity instance objects.
     */
    public $Result;
    public $CountValue;
    public $ObjectIDToNextLinkUrl = array();

    /**
     * Construct a QueryOperationResponse instance.
     *
     * @param array $headers
     * @param string $innerException
     * @param int $statusCode
     * @param Uri $query
     */
    public function QueryOperationResponse($headers,
                                           $innerException,
                                           $statusCode,
                                           $query)
    {
	$this->_headers = $headers;
        $this->_innerException = $innerException;
        $this->_statusCode = $statusCode;
        $this->_query = $query;
    }

    /**
     * To get the error string.
     *
     * @return string
     */
    public function getError()
    {
        return $this->_innerException;
    }

    /**
     * To get the headers.
     *
     * @return array
     */
    public function getHeaders()
    {
	return $this->_headers;
    }

    /**
     * To get the status code.
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->_statusCode;
    }

    /**
     * To get the OData Service query.
     *
     * @return Uri
     */
    public function getQuery()
    {
        return $this->_query;
    }

    /**
     * To get the total count
     *
     * @return int
     * @throws ODataServiceException
     */
    public function TotalCount()
    {
        if($this->CountValue == -1)
        {
            throw new ODataServiceException(Resource::CountNotPresent,
                                           '',
                                           array(),
                                           null);
        }
        return $this->CountValue;
    }

    /**
     * To get the DataServiceQueryContinuation instance.
     *
     * @param EntitySetCollection $collection
     * @return DataServiceQueryContinuation
     */
    public function GetContinuation($collection = null)
    {
        if($collection == null)
        {
            if($this->ObjectIDToNextLinkUrl[0] == null)
            {
                return null;
            }
            return new DataServiceQueryContinuation($this->ObjectIDToNextLinkUrl[0]);
        }

        if(!isset($collection[0]))
        {
            return null;
        }

        $key = $collection[0]->getObjectID();
        if(!array_key_exists($key, $this->ObjectIDToNextLinkUrl))
        {
            $queryOperationResponse = new QueryOperationResponse(array(),
                                                             Resource::CollectionNotBelongsToQueryResponse,
                                                             '','');
            throw new DataServiceRequestException($queryOperationResponse);
        }

        if($this->ObjectIDToNextLinkUrl[$key] == null)
        {
            return null;
        }

        return new DataServiceQueryContinuation($this->ObjectIDToNextLinkUrl[$key]);
    }
}
