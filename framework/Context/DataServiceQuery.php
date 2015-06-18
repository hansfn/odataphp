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
class DataServiceQuery
{
   /**
    * To hold the base url to an OData Service entity instance or set.
    *
    * @var <Url>
    */
   protected $_entitySetUrl;

   /**
    * To hold reference to ObjectContext instance.
    *
    * @var <ObjectContext>
    */
   protected $_context;

   /**
    * Array of valid OData Service system query options.
    *
    * @var <array>
    */
   protected static $systemQueryOptions = array('$expand',
                                                '$filter',
                                                '$inlinecount',
                                                '$orderby',
                                                '$select',
                                                '$skip',
                                                '$top',
                                                '$skiptoken');
   /**
    *
    * @var <array>
    */
   protected $_options = array();

   /**
    * To hold expand query options.
    *
    * @var <array>
    */
   protected $_expand = array();

   /**
    * To hold orderby query options.
    *
    * @var <array>
    */
   protected $_orderby = array();

   /**
    * To hold select query options.
    *
    * @var <array>
    */
   protected $_select = array();

   /**
    * To hold additional parameter to OData Service other than query options.
    *
    * @var <array>
    */
   protected $_other = array();

   /**
    * Constructs DataServiceQuery instance.
    *
    * @param <type> $entitySetUrl Partial Url pointing to entity Set in OData Service
    * @param <type> $context The DataService Context
    */
   public function DataServiceQuery($entitySetUrl, $context)
   {
       //Client is allowed to pass entitySet name in either
       //'EntitySetName' or '/EntitySetName' format (with or without slash)
       $entitySetUrl = ltrim($entitySetUrl, '/');
       $this->_entitySetUrl = $context->GetBaseUriWithSlash() . $entitySetUrl;
       $this->_context = $context;
   }

   /**
    * To add a query option.
    *
    * @param string $option The string value that contains the name of the
    *                 query string option to add
    * @param string $value The string that contains the value of the query
    *                 string option
    * @return DataServiceQuery Self reference that includes the requested
    *                            query option
    * @throws DataServiceRequestException
    */
   public function AddQueryOption($option, $value)
   {
        $option = trim($option);

        if(!isset($option) || $option == '')
        {
            throw new DataServiceRequestException(self::CreateQueryOperationResponse(Resource::NoEmptyQueryOption));
        }

        if($option[0] == '$' &&
           !in_array($option, self::$systemQueryOptions))
        {
            throw new DataServiceRequestException(self::CreateQueryOperationResponse(Resource::ReservedCharNotAllowed . $option));
        }

        if($option == '$expand')
        {
            $value = trim($value);
            if(isset($value) && $value != '')
            {
                $this->_expand[] = $value;
            }
        }
        else if($option == '$orderby')
        {
            $value = trim($value);
            if(isset($value) && $value != '')
            {
                $this->_orderby[] = $value;
            }
        }
        else if($option == '$select')
        {
            $value = trim($value);
            if(isset($value) && $value != '')
            {
                $this->_select[] = $value;
            }
        }
        else if(in_array($option, self::$systemQueryOptions))
        {
            if(array_key_exists($option, $this->_options))
            {
                throw new DataServiceRequestException(self::CreateQueryOperationResponse(Resource::NoDuplicateOption . $option));
            }

            $this->_options[$option] = $value;
        }
        else
        {
            $this->_other[] =  $option . '=' . $value;
        }

	return $this;
   }

   /**
    * To add a expand query option.
    *
    * @param string $path A string value that contains the requesting URI.
    * @return DataServiceQuery Self reference that includes the requested
    *                            $expand query option
    * @throws DataServiceRequestException
    */
   public function Expand($expression)
   {
       return $this->AddQueryOption('$expand', $expression);
   }

   /**
    * To add an orderby query option. Sort the results by the criteria
    * given in the expression. Multiple properties can be indicated by separating
    * them with a comma. The sort order can be controlled by using the 'asc' (default)
    * and 'desc' modifiers.
    * e.g. $svc->Customers()->OrderBy('City desc,CompanyName asc')->Execute();
    * This will return all Customers entities from WCF Data Service which are sorted
    * by 'CompanyName' property in acsending order and 'City' property in desending order.
    *
    * @param string $expression
    * @return DataServiceQuery Self reference that includes the requested
    *                            $orderby option
    * @throws DataServiceRequestException
    */
   public function OrderBy($expression)
   {
       return $this->AddQueryOption('$orderby', $expression);
   }

   /**
    * To add an select query option. For requesting an applicable subset of an
    * entity's properties for a particular query, using this option client
    * applications can optimize for bandwidth consumption.
    * e.g. $svc->Customers()->filter("Country eq 'USA'")
    *                           ->Select('CustomerID,CompanyName')
    *                           ->Execute();
    * The result is a collection of Customers entity with 'Country' property equal
    * to 'USA', each Customers entity contain value for 'CustomerID' and 'CompanyName'
    * property, all other properties will be null.
    *
    * @param string $expression
    * @return DataServiceQuery Self reference that includes the requested
    *                            $orderby option
    * @throws DataServiceRequestException
    */
   public function Select($expression)
   {
       return $this->AddQueryOption('$select', $expression);
   }

   /**
    * To add top query option. Restrict the maximum number of entities to be returned.
    * e.g. $svc->Customers()->top('10')->Execute().
    * This will return first 10 Customers entities from WCF Data Service.
    *
    * @param int $count
    * @return DataServiceQuery Self reference that includes the requested
    *                            $top option
    * @throws DataServiceRequestException
    */
   public function Top($count)
   {
        return $this->AddQueryOption('$top', $count);
   }

   /**
    * To add skip query option. Skip the number of rows given in the count parameter
    * when returning results.
    * e.g. $svc->Customers()->Skip('30')->Execute().
    * This will return Customers entities from WCF Data Service by after skipping the
    * first 30 entites.
    *
    * @param int $count
    * @return DataServiceQuery Self reference that includes the requested
    *                            $skip option
    * @throws DataServiceRequestException
    */
   public function Skip($count)
   {
        return $this->AddQueryOption('$skip', $count);
   }

   /**
    * To add a filter query option. Restrict the entities returned
    * from a query by applying the expression specified.
    * e.g. $svc->Customers()
    *              ->Filter( "City eq 'London'")
    *              ->Execute();
    *
    * This will return all Customers entities from WCF Data Service with 'City'
    * property equal to 'London'.
    *
    * @param string $expression
    * @return DataServiceQuery Self reference that includes the requested
    *                            $filter option
    * @throws DataServiceRequestException
    */
   public function Filter($expression)
   {
       return $this->AddQueryOption('$filter', $expression);
   }

   /**
    * To add inlinecount query option. This option directs the service to include
    * the count of all the entities along with the entities that are returned.
    * e.g. $res = $svc->Customers()
    *                   ->Filter("City eq 'London'")
    *                   ->IncludeTotalCount()
    *                   ->Execute();
    * This will return all Customers entities from WCF Data Service with 'City'
    * property equal to 'London' and count of all the entities. To access count
    * from the result, use $res->TotalCount();
    *
    * @return DataServiceQuery Self reference that includes the requested
    *                            $inlinecount=allpages query option
    * @throws DataServiceRequestException
    */
   public function IncludeTotalCount()
   {
       return $this->AddQueryOption('$inlinecount', 'allpages');
   }

   /**
    * To get raw count from OData service. The count query option can be added
    * to any entity sets on the server, the result is a plaintext response that
    * represents the count of entities in that set.
    * e.g. $count = $svc->Customers()->Filter("City eq 'London'")->Count();
    * This will return the number of Customers entity with 'City' property equal
    * to 'London'.
    *
    * @return int Returns Row count
    * @throws DataServiceRequestException
    */
   public function Count()
   {
       if(array_key_exists('$inlinecount', $this->_options))
       {
           throw new DataServiceRequestException(self::CreateQueryOperationResponse(Resource::NoCountAndInLineCount));
       }

       $query = $this->_entitySetUrl . '/$count?' . self::buildQueryOption();
       //We need to set DataServiceVersion as 2.0 as $count option
       //is supported only with version 2 header.
       $httpRequest = $this->_context->CreateRequest($query,
                                                     HttpVerb::GET,
                                                     true,
                                                     'application/atom+xml,application/xml',
                                                     Resource::DataServiceVersion_2);
       $isError = false;
       $innerException = '';
       $response = $this->_context->ExecuteAndReturnResponse($httpRequest,
                                                             Resource::DataServiceVersion_2,
                                                             $isError,
                                                             $innerException);
       if($isError)
       {
           $queryOperationResponse = new QueryOperationResponse($response->getHeaders(),
                                                                $innerException,
                                                                $response->getCode(),
                                                                $httpRequest->getUri());
           throw new DataServiceRequestException($queryOperationResponse);
       }

       return $response->getBody();
   }

   /**
    * Executes an OData Service query.
    *
    * @return QueryOperationResponse
    * @throws DataServiceRequestException
    */
   public function Execute()
   {
       $queryOption = self::buildQueryOption();
       $query = $this->_entitySetUrl . '?' . $queryOption;
       $requestVersion = Resource::DataServiceVersion_1;

       //We need to set DataServiceVersion header as 2.0, in 4 cases:
       //a. If query url contains inlinecount option
       //b. If query url contains count option
       //c. If query url contains skiptoken option
       //d. If query url contains select option
       if((strpos($query, '$inlinecount') !== FALSE)||
          (strpos($query, '$count') !== FALSE)||
          (strpos($query, '$skiptoken') !== FALSE)||
          (strpos($query, '$select') !== FALSE))
       {
           $requestVersion = Resource::DataServiceVersion_2;
       }

       $httpRequest = $this->_context->CreateRequest($query,
                                                     HttpVerb::GET,
                                                     false,
                                                     'application/atom+xml,application/xml',
                                                     $requestVersion);
       return $this->_context->ExecuteAndProcessResult($httpRequest, Resource::DataServiceVersion_2);
   }

   /**
    * To get request Uri.
    *
    * @return Uri
    */
   public function RequestUri()
   {
        return $this->_entitySetUrl . '?' . self::buildQueryOption();
   }

   /**
    * Clear all options added.
    *
    */
   public function ClearAllOptions()
   {
        $this->_options = array();
        $this->_expand = array();
        $this->_other = array();
   }

   /**
    * To build final query from all options.
    *
    * @return string The query options provided by client
    */
   protected function buildQueryOption()
   {
       $this->_expand = array_unique($this->_expand);
       $expandOption = null;
       foreach($this->_expand as $expand)
       {
           $expandOption .= $expand . ',';
       }

       if(isset($expandOption))
       {
           $expandOption = '$expand=' . $expandOption;
       }

       $this->_orderby = array_unique($this->_orderby);
       $orderbyOption = null;
       foreach($this->_orderby as $orderby)
       {
           $orderbyOption .= $orderby . ',';
       }

       if(isset($orderbyOption))
       {
           $orderbyOption = '$orderby=' . $orderbyOption;
       }

       $this->_select = array_unique($this->_select);
       $selectOption = null;
       foreach($this->_select as $select)
       {
           $selectOption .= $select . ',';
       }

       if(isset($selectOption))
       {
           $selectOption = '$select=' . $selectOption;
       }

       $query = null;
       foreach($this->_options as $key => $value)
       {
           $query .= $key . '=' . $value . '&';
       }

       foreach($this->_other as $other)
       {
           $query .= $other . '&';
       }

       if(!isset($query))
       {
           $query = '&';
       }

       if(isset($expandOption))
       {
           $query = rtrim($query, '&');
           $query = $query . '&' . $expandOption . '&';
       }

       if(isset($orderbyOption))
       {
           $query = rtrim($query, '&');
           $query = $query . '&' . $orderbyOption . '&';
       }

       if(isset($selectOption))
       {
           $query = rtrim($query, '&');
           $query = $query . '&' . $selectOption . '&';
       }

       return str_replace(' ', '+', rtrim($query, ',&'));
   }

   /**
    * To create QueryOperationResponse object using $errorMessage
    *
    * @param string $errorMessage
    * @return QueryOperationResponse
    *
    */
   protected function CreateQueryOperationResponse($errorMessage)
   {
        $queryOperationResponse = new QueryOperationResponse(array(),
                                                             $errorMessage,
                                                             '','');
        return $queryOperationResponse;
   }
}
?>