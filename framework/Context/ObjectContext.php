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

require_once 'Interfaces/BaseObject.php';
require_once 'Azure/TableEntry.php';
require_once 'Azure/Tables.php';
require_once 'Resource/Messages.php';
require_once 'Common/Dictionary.php';
require_once 'Common/Collection.php';
require_once 'Context/EntityStates.php';
require_once 'Interfaces/Entity.php';
require_once 'Context/ResourceBox.php';
require_once 'Context/RelatedEnd.php';
require_once 'Context/SaveResult.php';
require_once 'Common/Guid.php';
require_once 'Common/ClientType.php';
require_once 'Common/ACSUtil.php';
require_once 'Common/AzureTableUtil.php';
require_once 'Common/ContentType.php';
require_once 'Credential/CredentialType.php';
require_once 'Credential/CredentialBase.php';
require_once 'Credential/AzureTableCredential.php';
require_once 'Credential/ACSCredential.php';
require_once 'Credential/WindowsCredential.php';
require_once 'Common/Utility.php';
require_once 'Context/DataServiceQueryContinuation.php';
require_once 'Context/QueryComponents.php';
require_once 'Context/QueryOperationResponse.php';
require_once 'Context/DataServiceQuery.php';
require_once 'Context/DataServiceStreamResponse.php';
require_once 'Context/DataServiceRequestArgs.php';
require_once 'Context/SaveChangesOptions.php';
require_once 'Context/DataServiceSaveStream.php';
require_once 'Context/ContentStream.php';
require_once 'Context/DataServiceResponse.php';
require_once 'Context/OperationResponse.php';
require_once 'Parser/AtomParser.php';
require_once 'WebUtil/HttpBatchRequest.php';
require_once 'WebUtil/HttpBatchResponse.php';
require_once 'WebUtil/HttpRequest.php';
require_once 'WebUtil/Microsoft_Http_Response.php';
require_once 'WebUtil/HttpResponse.php';
require_once 'WebUtil/HttpRequestHeader.php';
require_once 'WebUtil/HttpVerb.php';
require_once 'Common/HttpProxy.php';
require_once 'Exception/ODataServiceException.php';
require_once 'Exception/DataServiceRequestException.php';
require_once 'Exception/InvalidOperation.php';
require_once 'Exception/ACSUtilException.php';
require_once 'Exception/InternalError.php';

/*
 * @copyright  Copyright (c) 2010, Persistent Systems Limited (http://www.persistentsys.com)
 * @license    http://odataphp.codeplex.com/license
 */
class ObjectContext
{
    /**
     * The url to OData Service
     *
     * @var Uri
     */
    protected $_baseURI;

    /**
     * $_baseURI with a slash
     *
     * @var Uri
     */
    protected $_baseUriWithSlash;

    /**
     *
     * @var <array<string>>
     */
    protected $_entities = array();

    /**
     * Associative array to map from entity set to entity type. During proxy
     * class generation this array will be populated.
     *
     * @var <array<key => value>>
     */
    protected $_entitySet2Type = array();

    /**
     * Associative array to map from entity type to entity set. During proxy
     * class generation this array will be populated
     *
     * @var <array<key => value>>
     */
    protected $_entityType2Set = array();

    /**
     * Associative arry holding association type. 0..1, 1 or *
     *
     * @var array
     */
    protected $_association = array();

    /**
     * This dictionary will be used to track all the Odata service entites
     * currently in the context.
     * [Note: Do not access this dictionary from your application, it is used internally]
     *
     * Key=>Object : Value=>ResourceBox
     */
    public $ObjectToResource;

    /**
     * This dictionary will be used to track all the OData service related entites
     * currently in the context.
     * [Note: Do not access this dictionary from your application, it is used internally]

     * Key=>RelatedEnd : Value=>RelatedEnd
     */
    public $Bindings;

    /**
     * This array will track the objects in the context which have identity
     * (ex: object in the context which result of a query execution)
     * [Note: Do not access this array from your application, it is used internally]
     *
     * @var array
     */
    public $IdentityToResource;

    /**
     * Hold the id of next changed entries
     *
     * @var int
     */
    protected $_nextChange;

    /**
     * Credential Object holding credential information [Windows/ACS/Azure]
     *
     * @var Credential
     */
    public $Credential;

    /**
     * Proxy Object holding ProxyServer information
     *
     * @var HttpProxy
     */
    public $HttpProxy;

    /**
     * This array holds custom headers as key-value
     *
     * @var array
     */
    public $CustomHeaders = array();

    /**
     *
     * @var string
     */
    public $Accept;

    /**
     *
     * @var string
     */
    public $Content_Type;

    /**
     * Whether to use post tunnelling or not.
     *
     * @var bool
     */
    public $UsePostTunneling;

    /**
     * To hold save changes option, SaveChangesOptions::Batch or
     * SaveChangesOptions::None
     *
     * @var SaveChangesOptions
     */
    protected $_saveChangesOptions;

    /**
     * Decide how to perfrom updation, if set true then PUT else MERGE
     *
     * @var bool
     */
    protected $_replaceOnUpdateOption;

    /**
     * To hold the instance of the class containing the call back function
     * which is to be invoked before sending request to OData Service.
     *
     * @var classInstance
     */
    protected $_onBefore_cb_instance;

    /**
     * To hold the name of the call back function which is to be invoked before
     * sending request to OData Service.
     *
     * @var string
     */
    protected $_onBefore_cb_function;

    /**
     * To hold the instance of the class containing the call back function
     * which is to be invoked after receiving response from OData Service
     * and before processing it.
     *
     * @var classInstance
     */
    protected $_onAfter_cb_instance;

    /**
     * To hold the name of the call back function which is to be invoked after
     * receiving response from OData Service and before processing it.
     *
     * @var type
     */
    protected $_onAfter_cb_function;

    /**
     * Constructs a new ObjectContext object.
     *
     * @param string $uri
     */
    public function __construct($uri)
    {
        $this->ObjectToResource = new Dictionary();
        $this->Bindings = new Dictionary();
        $this->IdentityToResource = array();
        $this->_nextChange = 0;
        $this->Credential = null;
        $this->HttpProxy = null;
        $this->_onBefore_cb_instance = null;
        $this->_onBefore_cb_function = null;
        $this->_onAfter_cb_instance = null;
        $this->_onAfter_cb_function = null;
        $this->_baseURI = $uri;
        $this->_baseUriWithSlash = $this->_baseURI;
        $this->CustomHeaders = array();
        if($this->_baseUriWithSlash[(strlen($this->_baseUriWithSlash) - 1)] != '/')
        {
            $this->_baseUriWithSlash = $this->_baseUriWithSlash . '/';
        }

        $this->Accept = Resource::Accept_ATOM;
        $this->Content_Type = Resource::Content_Type_ATOM;
        $this->UsePostTunneling = false;
        $this->_saveChangesOptions = Resource::DefaultSaveChangesOptions;
        $this->_replaceOnUpdateOption = false;
    }

    /**
     * To set the SaveChange mode batch or Non-Batch.
     *
     * @param SaveChangesOptions $saveChangesOptions
     * @throws InvalidOperation
     */
    public function SetSaveChangesOptions($saveChangesOptions)
    {
        if($saveChangesOptions != SaveChangesOptions::None &&
           $saveChangesOptions != SaveChangesOptions::Batch)
        {
            throw new InvalidOperation(Resource::InvalidSaveChangesOptions);
        }
        $this->_saveChangesOptions = $saveChangesOptions;
    }

    /**
     * To specify whether to replace all values of the entity in the data source
     * with values from the updated entity.
     *
     * @param boolean $replaceOnUpdate
     */
    public function SetReplaceOnUpdate($replaceOnUpdate)
    {
        $this->_replaceOnUpdateOption = $replaceOnUpdate;
    }

    /**
     * To insert an object into OData service.
     *
     * @param string $entityName The class name of entity to be inserted
     * @param Object $object The instance of entity to be inserted
     * @throws InvalidOperation
     */
    public function AddObject($entityName, $object)
    {
        $this->ThrowExceptionIfNotValidObject($object, 'AddObject');
        if ($this->ObjectToResource->ContainsKey($object))
        {
            throw new InvalidOperation(Resource::EntityAlreadyContained,
                                       Resource::EntityAlreadyContained_Details);
        }

        $editLink = $entityName;
        $resourceBox = new ResourceBox(null, $editLink, $object);
        $resourceBox->State = EntityStates::Added;
        $this->ObjectToResource->Add($object, $resourceBox);
        $this->IncrementChange($resourceBox);
    }

    /**
     * To update an entity instance in the OData service.
     *
     * @param Object $object The instance of entity to be updated
     * @throws InvalidOperation
     */
    public function UpdateObject($object)
    {
        $this->ThrowExceptionIfNotValidObject($object, 'UpdateObject');
        $resourcebox = null;
        if (!$this->ObjectToResource->TryGetValue($object, $resourcebox))
        {
            throw new InvalidOperation(Resource::EntityNotContained,
                                       Resource::EntityNotContained_Details);
        }

        if (EntityStates::Unchanged == $resourcebox->State)
        {
            $resourcebox->State = EntityStates::Modified;
            $this->IncrementChange($resourcebox);
        }
    }

    /**
     * To create an association between two entity instances. This method only
     * supports adding entity relationships with multiplicity = *.
     *
     * @param Object $sourceObject The source object participating the association
     * @param Object $targetObject The target object participating the association
     * @param string $entityName The class name of target object
     * @throws InvalidOperation
     */
    public function AddLink($sourceObject, $sourceProperty, $targetObject)
    {
        $this->ThrowExceptionIfNotValidObject($sourceObject, 'AddLink');
        $this->ThrowExceptionIfNotValidObject($targetObject, 'AddLink');
        $key = new RelatedEnd($sourceObject, $sourceProperty, $targetObject);
        $this->ValidateAddLink($key);
        $key->State = EntityStates::Added;
        $this->Bindings->Add($key, $key);
        $sourceResourceBox = null;
        $this->ObjectToResource->TryGetValue($sourceObject, $sourceResourceBox);
        $sourceResourceBox->RelatedLinkCount++;
        $this->IncrementChange($key);
    }

    /**
     * To delete an entity instance from the OData service.
     *
     * @param Object $object The entity instance to be deleted.
     * @throws InvalidOperation
     */
    public function DeleteObject($object)
    {
        $this->ThrowExceptionIfNotValidObject($object, 'DeleteObject');
        $resourceBox = null;
        if (!$this->ObjectToResource->TryGetValue($object, $resourceBox))
        {
            throw new InvalidOperation(Resource::EntityNotContained,
                                       Resource::EntityNotContained_Details);
        }

        $state = $resourceBox->State;
        if (EntityStates::Added == $state)
        {
            if (null != $resourceBox->Identity)
            {
                unset($this->IdentityToResource[$resourceBox->Identity]);
            }
            $this->DetachRelated($resourceBox);
            $resourceBox->State = EntityStates::Detached;
            $this->ObjectToResource->Remove($object);
        }
        else if (EntityStates::Deleted != $state)
        {
            $resourceBox->State = EntityStates::Deleted;
            $this->IncrementChange($resourceBox);
        }
    }

    /**
     * To delete an association between two entity instances. This method only
     * supports removing entity relationships with multiplicity = *.
     *
     * @param Object $sourceObject The source object participating the association
     * @param Object $targetObject The target object participating the association
     * @param string $entityName The class name of target object
     * @throws InvalidOperation
     */
    public function DeleteLink($sourceObject, $sourceProperty, $targetObject)
    {
        $this->ThrowExceptionIfNotValidObject($sourceObject, 'DeleteLink');
        $this->ThrowExceptionIfNotValidObject($targetObject, 'DeleteLink');
        $key = new RelatedEnd($sourceObject, $sourceProperty, $targetObject);
        $this->ValidateDeleteLink($key);
        $bindingValue = null;
        $this->Bindings->TryGetValue($key, $bindingValue);
        if($bindingValue != null && EntityStates::Added == $bindingValue->State)
        {
            $this->DetachExistingLink($key);
            return;
        }
        $sourceResourceBox = null;
        $this->ObjectToResource->TryGetValue($sourceObject, $sourceResourceBox);
        $targetResourcebox = null;
        $this->ObjectToResource->TryGetValue($targetObject, $targetResourcebox);

        if((($bindingValue == null) &&
                 ((EntityStates::Added == $sourceResourceBox->State) ||
                  (EntityStates::Added == $targetResourcebox->State))))
        {
            throw new InvalidOperation(Resource::NoRelationWithInsertEnd,
                                       Resource::NoRelationWithInsertEnd_Details);
        }
        else if ($bindingValue == null)
        {
            $this->Bindings->Add($key, $key);
            $sourceResourceBox->RelatedLinkCount++;
            $bindingValue = $key;
        }
        if (EntityStates::Deleted != $bindingValue->State)
        {
            $bindingValue->State = EntityStates::Deleted;
            $this->IncrementChange($bindingValue);
        }
    }

    /**
     * To create and remove association between two entity instances. This method
     * only supports adding or deleting entity relationships with multiplicity = 1.
     *
     * @param Object $sourceObject The source object participating the association
     * @param string $sourceProperty The property on the source object that identifies the target object of the new link.
     * @param Object $targetObject The target object participating the association.
     * @throws InvalidOperation
     */
    public function SetLink($sourceObject, $sourceProperty, $targetObject)
    {
        $this->ThrowExceptionIfNotValidObject($sourceObject, 'SetLink');
        if(null != $targetObject)
        {
            $this->ThrowExceptionIfNotValidObject($targetObject, 'SetLink');
        }
        $key = new RelatedEnd($sourceObject, $sourceProperty, $targetObject);
        $this->ValidateSetLink($key);
        $key1 = $this->DetachReferenceLink($sourceObject, $sourceProperty, $targetObject);
        if($key1 == null)
        {
              $key1 = $key;
              $key1->State = EntityStates::Added;
              $this->Bindings->Add($key1, $key1);
        }

        if (EntityStates::Modified != $key1->State)
        {
            $key1->State = EntityStates::Modified;
            $sourceResourceBox = null;
            $this->ObjectToResource->TryGetValue($sourceObject, $sourceResourceBox);
            $sourceResourceBox->RelatedLinkCount++;
            $this->IncrementChange($key1);
        }
    }

    /**
     *
     * To add HTTP Custom headers to an entity in added or modified state,
     * so that when SaveChanges invokes these header will be added to the http
     * header specific to the entity.
     *
     * @param Object $object Entity Instance in Added or Modified state
     * @param array $headers HTTP Header
     * @throws InvalidOperation
     */
    public function SetEntityHeaders($object, $headers)
    {
        if(!is_array($headers))
        {
            throw new InvalidOperation(Resource::EntityHeaderOnlyArray, null);
        }

        $this->ThrowExceptionIfNotValidObject($object, 'SetEntityHeaders');
        if (!$this->ObjectToResource->TryGetValue($object, $resourcebox))
        {
           throw new InvalidOperation(Resource::EntityNotContained,
                                      Resource::EntityNotContained_Details);
        }

        if(!($resourcebox->State == EntityStates::Added ||
             $resourcebox->State == EntityStates::Modified))
        {
           throw new InvalidOperation(Resource::EntityHeaderCannotAppy, null);
        }

        $resourcebox->Headers = $headers;
    }

    /**
     * Saves the changes DataServiceContext is tracking to OData service.
     *
     * @throws ODataServiceException
     */
    public function SaveChanges()
    {
        $result = new SaveResult($this, $this->_saveChangesOptions);
        if($this->_saveChangesOptions == SaveChangesOptions::Batch)
        {
            $result->BatchRequest($this->_replaceOnUpdateOption);
        }
        else
        {
            $result->NonBatchRequest($this->_replaceOnUpdateOption);
        }
    }

    /**
     * To check the $entry is in modified state or not.
     * [Note: Do not call this function from your application, it is used internally]
     *
     * @param $entry The ResourceBox or RelatedEnd
     * @Return bool
     */
    public function HasModifiedResourceState($entry)
    {
        if(EntityStates::Unchanged != $entry->State)
        {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * Loads deferred content from the OData service. If $DataServiceQueryContinuation
     * is null then this API will load deferred content for a specified property
     * from the data service else loads a page of related entities by using the next
     * link URI in $dataServiceQueryContinuation.
     *
     * @param Object $SourceObject Instance of the entity into which value of
     *                 the property to be loaded.
     * @param Object $PropertyName Name of the property whose value to be loaded.
     * @param DataServiceQueryContinuation [optional] Used in the case of Server Side Paging
     * @Return QueryOperationResponse
     * @throws InvalidOperation, DataServiceRequestException
     */
    public function LoadProperty($sourceObject, $propertyName,
                                 $dataServiceQueryContinuation = null)
    {
        $this->ThrowExceptionIfNotValidObject($sourceObject, 'LoadProperty');
        $requestUri = null;
        $resourceBox = null;
        if (!$this->ObjectToResource->TryGetValue($sourceObject, $resourceBox))
        {
             throw new InvalidOperation(Resource::EntityNotContained,
                                        Resource::EntityNotContained_Details);
        }

        $state = $resourceBox->State;
        if (EntityStates::Added == $state)
        {
            throw new InvalidOperation(Resource::NoLoadWithInsertEnd, null);
        }

        try
        {
            $property = new ReflectionProperty($sourceObject, $propertyName);
        }
        catch(ReflectionException $exception)
        {
            throw new InvalidOperation(Resource::NoLoadWithUnknownProperty .
                                       $propertyName);
        }

        if($dataServiceQueryContinuation != null)
        {
            $requestUri = $dataServiceQueryContinuation->getNextLinkUri();
        }
        else
        {
            $requestUri = $resourceBox->GetResourceUri($this->_baseUriWithSlash) .
                                        "/" .
                                        $propertyName;
        }

        $httpRequest = $this->CreateRequest($requestUri,
                                            'GET',
                                            false,
                                            'application/atom+xml,application/xml',
                                            Resource::DataServiceVersion_3);
        $queryResponse = $this->ExecuteAndProcessResult($httpRequest,
                                                        Resource::DataServiceVersion_3);
        $property->setValue($sourceObject, $queryResponse->Result);
        return $queryResponse;
    }

    /**
     * Create a HttpRequest for retriving the entity set identified by the
     * $uriOrDSQueryContinuation parameter and returns the seralized
     * entity objects as a collection.
     *
     * @param $uriOrDSQueryContinuation Uri or DataServiceQueryContinutation
     * @return QueryOperationResponse
     * @throws DataServiceRequestException
     */
    public function Execute($uriOrDSQueryContinuation)
    {
        $queryComponents = null;

	if(is_string($uriOrDSQueryContinuation))
	{
            //Client is allowed to pass entitySet name in either
            //'EntitySetName' or '/EntitySetName' format (with or without slash)
            $uriOrDSQueryContinuation = ltrim($uriOrDSQueryContinuation, '/');
            $requestUri = $this->_baseUriWithSlash . $uriOrDSQueryContinuation;            
            $queryComponents = new QueryComponents($requestUri, null);
	}
	else if(is_object($uriOrDSQueryContinuation) &&
		is_a($uriOrDSQueryContinuation,
		     'DataServiceQueryContinuation'))
	{
            $queryComponents = $uriOrDSQueryContinuation->CreateQueryComponents();
	}
	else
	{
            $queryOperationResponse = new QueryOperationResponse(array(),
                                                                 Resource::InvalidExecuteArg,
                                                                 '',
                                                                 '');
	    throw new DataServiceRequestException($queryOperationResponse);
	}

        $queryComponents->Uri = str_replace(' ', '+', $queryComponents->Uri);
        $requestVersion = Resource::DataServiceVersion_1;
        //We need to set DataServiceVersion header as 2.0, in 4 cases:
        //a. If query url contains inlinecount option
        //b. If query url contains count option
        //c. If query url contains skiptoken option
        //d. If query url contains select option
        if((strpos($queryComponents->Uri, '$inlinecount') !== FALSE)||
           (strpos($queryComponents->Uri, '$count') !== FALSE)||
           (strpos($queryComponents->Uri, '$skiptoken') !== FALSE)||
           (strpos($queryComponents->Uri, '$select') !== FALSE))
        {
            $requestVersion = Resource::DataServiceVersion_3;
        }

        $httpRequest = $this->CreateRequest($queryComponents->Uri,
                                            'GET',
                                            false,
                                            'application/atom+xml,application/xml',
                                            $requestVersion);
        return $this->ExecuteAndProcessResult($httpRequest,
                                              Resource::DataServiceVersion_3);
    }

    /**
     * Execute the HTTP request and returns seralized entity objects
     * [Note: Do not call this function from your application, it is used internally]
     *
     * @param HttpRequest $httpRequest
     * @param string $dataServiceVersion
     * @return QueryOperationResponse
     * @throws DataServiceRequestException
     */
    public function ExecuteAndProcessResult($httpRequest, $dataServiceVersion)
    {
        $response = $this->ExecuteAndReturnResponse($httpRequest,
                                                    $dataServiceVersion,
                                                    $isError,
                                                    $innerException);
        $queryOperationResponse = new QueryOperationResponse($response->getHeaders(),
                                                             $innerException,
                                                             $response->getCode(),
                                                             $httpRequest->getUri());
       if($isError)
       {
           throw new DataServiceRequestException($queryOperationResponse);
       }

       $parser = new AtomParser($response->getBody(), $this);
       $parser->EnumerateObjects($queryOperationResponse);
       return $queryOperationResponse;
    }

    /**
     * Execute a Http request and returns the Http Response. If any error happens
     * then the $isError flag will be set to true and $innerException will
     * contain the exception string. From the returned http response object more
     * details (http headers, code, message and body) can be retrieved.
     * [Note: Do not call this function from your application, it is used internally]
     *
     * @param HttpRequest $httpRequest
     * @param string $dataServiceVersion
     * @param boolean [out] $isError
     * @param string [out] $innerException
     * @return HttpResponse
     */
    public function ExecuteAndReturnResponse($httpRequest, $dataServiceVersion,
                                             &$isError, &$innerException)
    {
        $this->OnBeforeRequestInternal($httpRequest);
        $httpRawResponse ='';
        //need a try catch, because during curl_exec, if curl failed to
        //connet to the OData Service it will throw InvalidOperation
        //exception
        try
        {
            $httpRawResponse = $httpRequest->GetResponse();
        }
        catch(InvalidOperation $exception)
        {
            $isError = true;
            $innerException = $exception->getError() .
                              $exception->getDetailedError();
            return new Microsoft_Http_Response('404', array());
        }

        $httpResponse = HttpResponse::fromString($httpRawResponse);
        $this->OnAfterResponseInternal($httpResponse);
        $isError = $httpResponse->IsError();
        $headers = $httpResponse->getHeaders();

        if($isError)
        {
            if(isset($headers[HttpRequestHeader::ContentType]))
            {
                if(strpos(strtolower($headers[HttpRequestHeader::ContentType]),
                   strtolower(Resource::Content_Type_ATOM)) !== FALSE)
                {
                    $innerException = $httpResponse->getMessage();
                }
                else
                {
                    $outerError = $innerError = null;
                    /*The error string can be in the format: retrive the error
                    <?xml version="1.0" encoding="utf-8" standalone="yes"?>
                    <error xmlns="http://schemas.microsoft.com/ado/2007/08/dataservices/metadata">
                    <code></code>
                    <message xml:lang="en-US">The error</message>
                    </error>*/
                    AtomParser::GetErrorDetails($httpResponse->getBody(),
                                                $outerError,
                                                $innerError);
                    $innerException = $outerError . "<br/>" . $innerError;
                }
            }
            else
            {
                $innerException = $httpResponse->getMessage();
            }
        }

        if(isset($headers['Dataserviceversion']) &&
        ((int)$headers['Dataserviceversion'] > (int)$dataServiceVersion))
        {
            $isError = true;
            $innerException = Resource::VersionMisMatch .
                              $headers['Dataserviceversion'];
        }

        return $httpResponse;
    }

    /**
     * To increment the changeOrder associated with Entry (ResourceBox or RelatedEnd).
     * [Note: Do not call this function from your application, it is used internally]
     *
     * @param $resourceBoxOrRelatedEnd ResourceBox or RelatedEnd
     */
    protected function IncrementChange($resourceBoxOrRelatedEnd)
    {
        $resourceBoxOrRelatedEnd->ChangeOrder = ++$this->_nextChange;
    }

    /**
     * To check creating a link between $relatedEnd::SourceResource and
     * $relatedEnd::TargetResource is valid based on their current states.
     * [Note: Do not call this function from your application, it is used internally]
     *
     * @param  $relatedEnd The RelatedEnd
     * @throws InvalidOperation
     */
     protected function ValidateAddLink($relatedEnd)
     {
        $sourceObject = $relatedEnd->GetSourceResource();
        $sourceProperty = $relatedEnd->GetSourceProperty();
        $targetObject = $relatedEnd->GetTargetResource();
        $sourceResourceBox = null;
        if (!$this->ObjectToResource->TryGetValue($sourceObject, $sourceResourceBox))
        {
             throw new InvalidOperation(Resource::EntityNotContained,
                                        Resource::EntityNotContained_Details);
        }

        $targetResourceBox = null;
        if (!$this->ObjectToResource->TryGetValue($targetObject, $targetResourceBox))
        {
             throw new InvalidOperation(Resource::EntityNotContained,
                                        Resource::EntityNotContained_Details);
        }

        if (($sourceResourceBox->State == EntityStates::Deleted) ||
            (($targetResourceBox != null) &&
             ($targetResourceBox->State == EntityStates::Deleted)))
        {
            throw new InvalidOperation(Resource::NoRelationWithDeleteEnd,
                                       Resource::NoRelationWithDeleteEnd_Details);

        }

        if ($this->Bindings->ContainsKey($relatedEnd))
        {
            throw new InvalidOperation(Resource::RelationAlreadyContained,
                                       Resource::RelationAlreadyContained_Details);
        }

        try
        {
            $property = new ReflectionProperty($sourceObject, $sourceProperty);
        }
        catch(ReflectionException $ex)
        {
            throw new InvalidOperation(Resource::NoPropertyForTargetObject,
                                       sprintf(Resource::NoPropertyForTargetObject_Details,
                                               $sourceProperty));
        }

        $attributes = Utility::getAttributes($property);
        if(!isset($attributes['Relationship']) ||
           !isset($attributes['ToRole']) ||
           !isset($attributes['FromRole'])||
           !isset($attributes['Type']))
        {
            throw new InvalidOperation(Resource::NoRelationBetweenObjects, null);
        }

        if($attributes["Type"] != "NavigationProperty")
        {
            throw new InvalidOperation(Resource::RelationNotRefOrCollection,
                                       sprintf(Resource::RelationNotRefOrCollection_Details,
                                               $sourceProperty));
        }

        $relationShip = $this->GetRelationShip($attributes["Relationship"],
                                               $attributes["ToRole"]);
        if($relationShip != '*')
        {
            throw new InvalidOperation(sprintf(Resource::AddLinkCollectionOnly,
                                               $sourceProperty),
                                       Resource::AddLinkCollectionOnly_Details);
        }
    }

    /**
     * To check whether creating  reference link between $relatedEnd::SourceResource
     * and $relatedEnd::TargetResource is valid based their current states.
     * [Note: Do not call this function from your application, it is used internally]
     *
     * @param $relatedEnd The RelatedEnd
     * @throws InvalidOperation
     */
    protected function ValidateSetLink($relatedEnd)
    {
        $sourceObject = $relatedEnd->GetSourceResource();
        $sourceProperty = $relatedEnd->GetSourceProperty();
        $targetObject = $relatedEnd->GetTargetResource();
        $sourceResourceBox = null;
        if (!$this->ObjectToResource->TryGetValue($sourceObject, $sourceResourceBox))
        {
            throw new InvalidOperation(Resource::EntityNotContained,
                                        Resource::EntityNotContained_Details);
        }

        $targetResourceBox = null;
        if((null != $targetObject) && !$this->ObjectToResource->TryGetValue($targetObject,
                                                                            $targetResourceBox))
        {
           throw new InvalidOperation(Resource::EntityNotContained,
                                        Resource::EntityNotContained_Details);
        }

        if (($sourceResourceBox->State == EntityStates::Deleted) ||
            (($targetResourceBox != null) &&
             ($targetResourceBox->State == EntityStates::Deleted)))
        {
            throw new InvalidOperation(Resource::NoRelationWithDeleteEnd,
                                       Resource::NoRelationWithDeleteEnd_Details);
        }

        try
        {
            $property = new ReflectionProperty($sourceObject, $sourceProperty);
        }
        catch(ReflectionException $ex)
        {
             throw new InvalidOperation(Resource::NoPropertyForTargetObject,
                                       sprintf(Resource::NoPropertyForTargetObject_Details,
                                               $sourceProperty));
        }

        $attributes = Utility::getAttributes($property);
        if(!isset($attributes['Relationship']) ||
           !isset($attributes['ToRole']) ||
           !isset($attributes['FromRole'])||
           !isset($attributes['Type']))
        {
            throw new InvalidOperation(Resource::NoRelationBetweenObjects, null);
        }

        if($attributes['Type'] != 'NavigationProperty')
        {
            throw new InvalidOperation(Resource::RelationNotRefOrCollection,
                                       sprintf(Resource::RelationNotRefOrCollection_Details,
                                               $sourceProperty));
        }

        $relationShip = $this->GetRelationShip($attributes['Relationship'],
                                               $attributes['ToRole']);
        if($relationShip != '0..1' && $relationShip != '1')
        {
            throw new InvalidOperation(sprintf(Resource::SetLinkReferenceOnly,
                                               $sourceProperty),
                                       Resource::SetLinkReferenceOnly_Details);
        }
    }

    /**
     * To check whether deleteing a link between $relatedEnd::SourceResource and
     * $relatedEnd::TargetResource is valid based their current states.
     * [Note: Do not call this function from your application, it is used internally]
     *
     * @param $relatedEnd The RelatedEnd
     * @throws InvalidOperation
     */
    protected function ValidateDeleteLink($relatedEnd)
    {
        $sourceObject = $relatedEnd->GetSourceResource();
        $sourceProperty = $relatedEnd->GetSourceProperty();
        $targetObject = $relatedEnd->GetTargetResource();
        $sourceResourceBox = null;
        if (!$this->ObjectToResource->TryGetValue($sourceObject, $sourceResourceBox))
        {
            throw new InvalidOperation(Resource::EntityNotContained,
                                        Resource::EntityNotContained_Details);
        }
        $targetResourceBox = null;
        if (!$this->ObjectToResource->TryGetValue($targetObject, $targetResourceBox))
        {
            throw new InvalidOperation(Resource::EntityNotContained,
                                        Resource::EntityNotContained_Details);
        }

        try
        {
            $property = new ReflectionProperty($sourceObject, $sourceProperty);
        }
        catch(ReflectionException $ex)
        {
             throw new InvalidOperation(Resource::NoPropertyForTargetObject,
                                       sprintf(Resource::NoPropertyForTargetObject_Details,
                                               $sourceProperty));
        }

        $attributes = Utility::getAttributes($property);
        if(!isset($attributes["Relationship"]) ||
           !isset($attributes["ToRole"]) ||
           !isset($attributes["FromRole"])||
           !isset($attributes["Type"]))
        {
            throw new InvalidOperation(Resource::NoRelationBetweenObjects, null);
        }

        if($attributes["Type"] != "NavigationProperty")
        {
            throw new InvalidOperation(Resource::RelationNotRefOrCollection,
                                       sprintf(Resource::RelationNotRefOrCollection_Details,
                                               $sourceProperty));
        }

        $relationShip = $this->GetRelationShip($attributes["Relationship"],
                                               $attributes["ToRole"]);
        if($relationShip != '*')
        {
            throw new InvalidOperation(sprintf(Resource::AddLinkCollectionOnly,
                                               $sourceProperty),
                                       Resource::AddLinkCollectionOnly_Details);
        }
    }

    /**
     * To detach all bindings created with $resourceBox::Resource as source object.
     * [Note: Do not call this function from your application, it is used internally]
     *
     * @param $resourceBox the ResourceBox
     */
    protected function DetachRelated($resourceBox)
    {
        $bindingValues = $this->Bindings->Values();
        foreach($bindingValues as $bindingValue)
        {
            if($resourceBox->IsRelatedEntity($bindingValue))
            {
                $this->DetachExistingLink($bindingValue);
            }
        }
    }

    /**
     * To remove the $relatedEnd from Binding dictionary.
     * [Note: Do not call this function from your application, it is used internally]
     *
     * @param $relatedEnd The RelatedEnd
     */
    protected function DetachExistingLink($relatedEnd)
    {
        if ($this->Bindings->Remove($relatedEnd))
        {
            $relatedEnd->State = EntityStates::Detached;
            $resourceBox = null;
            $this->ObjectToResource->TryGetValue($relatedEnd->getSourceResource(),
                                                 $resourceBox);
            $resourceBox->RelatedLinkCount--;
        }
    }

    /**
     * Used by SetLink API. If SetLink is already called to add link between source
     * and target, then this function returns the existing binding. If SetLink
     * is called between source and some other target then this function detach that link.
     * [Note: Do not call this function from your application, it is used internally]
     *
     * @param Object $source
     * @param string $sourceProperty
     * @param Object $target
     * @return RelatedEnd or null
     */
    protected function DetachReferenceLink($source, $sourceProperty, $target)
    {
        $bindingValues = $this->Bindings->Values();
        foreach($bindingValues as $relatedEnd)
        {
            if ($relatedEnd->GetSourceResource()->getObjectID() == $source->getObjectID() &&
                $relatedEnd->GetSourceProperty() == $sourceProperty)
            {
                if(((null == $target) && (null == $relatedEnd->GetTargetResource())) ||
                   ((null != $target) && (null != $relatedEnd->GetTargetResource())
                                      && ($target->getObjectID() == $relatedEnd->GetTargetResource()->getObjectID()))
                )
                {
                    return $relatedEnd;
                }
                $this->DetachExistingLink($relatedEnd);
            }
        }
        return null;
    }

    /**
     * To update the $resourceBox::Source object by parsing the atom XML in $str.
     * [Note: Do not call this function from your application, it is used internally]
     *
     * @param string $str
     * @param ResourceBox $resourceBox
     * @param string $content_type
     */
    public function LoadResourceBox($str, $resourceBox, $content_type)
    {
        $resource = $resourceBox->GetResource();
        $uri = null;
        $atomEntry = null;
        AtomParser::PopulateObject($str, $resource, $uri, $atomEntry);
        if(isset($uri))
        {
            $index = Utility::reverseFind($uri, '/');
            $editLink = substr($uri,$index + 1, strlen($uri) - $index);
            $resourceBox->Identity = $uri;
            $resourceBox->EditLink = $editLink;
        }

        //If $str represents content of entry of type Media then
        //popluate values specific to media entry
        $resourceBox->EditMediaLink  = $atomEntry->EditMediaLink;
        $resourceBox->MediaLinkEntry = $atomEntry->MediaLinkEntry;
        $resourceBox->StreamETag     = $atomEntry->StreamETag;
        $resourceBox->EntityETag     = $atomEntry->EntityETag;
        $resourceBox->StreamLink     = $atomEntry->MediaContentUri;
    }

    /**
     * Check whether an object with identity $atomEntry::Identity exists in the
     * context, if it exists return that object. If not, create the object for
     * that entity instance, add it to ObjectToResource dictionary and return
     * the object.
     * [Note: Do not call this function from your application, it is used internally]
     *
     * @param string $entityType Name of entity Type
     * @param AtomEntry $atomEntry AtomEntry
     * @return Object The object representing the entity instance
     * @throws InternalError
     */
    public function AddToObjectToResource($entityType, $atomEntry)
    {
        $uri = $atomEntry->Identity;
        if( array_key_exists($uri, $this->IdentityToResource))
        {
            return $this->IdentityToResource[$uri]->getResource();
        }

        try
        {
            $class = new ReflectionClass($entityType);
            $resource = $class->newInstance($uri);
            $index = Utility::reverseFind($uri, '/');
            $editLink = substr($uri,$index + 1, strlen($uri) - $index);
            $resourceBox = new Resourcebox($uri, $editLink, $resource);

            $resourceBox->EditMediaLink  = $atomEntry->EditMediaLink;
            $resourceBox->MediaLinkEntry = $atomEntry->MediaLinkEntry;
            $resourceBox->StreamETag     = $atomEntry->StreamETag;
            $resourceBox->EntityETag     = $atomEntry->EntityETag;
            $resourceBox->StreamLink     = $atomEntry->MediaContentUri;
            $resourceBox->State          = EntityStates::Unchanged;

            $this->ObjectToResource->Add($resource, $resourceBox);
            $this->IdentityToResource[$uri] = $resourceBox;
            return $resource;
        }
        catch (ReflectionException $ex)
        {
            throw new InternalError(Resource::InvalidEntityClassName .
                                    $entityType);
        }
    }

    /**
     * When user perfroms a query operation with $expand option or LoadProperty
     * method, then the binding between the SourceObject and Target object become
     * binding in the context. This function will add such a binding to Bindinds
     * dictionary if not exists.
     * [Note: Do not call this function from your application, it is used internally]
     *
     * @param Object $sourceObject
     * @param string $sourcePropertyName
     * @param Object $object
     */
    public function AddToBindings($sourceObject, $sourcePropertyName, $object)
    {
        $binding = new RelatedEnd($sourceObject, $sourcePropertyName, $object);
        if ($this->Bindings->ContainsKey($binding) == FALSE)
        {
            $binding->State = EntityStates::Unchanged;
            $this->Bindings->Add($binding, $binding);
        }
    }

    /**
     * Test the object $object is an object if not throws exception.
     * [Note: Do not call this function from your application, it is used internally]
     *
     * @param Object $object
     * @param string $from
     * @throws InvalidOperation
     */
    protected function ThrowExceptionIfNotValidObject($object, $from)
    {
        if(is_object($object))
        {
            if (is_a($object, 'Object'))
            {
                return;
            }
        }

        $message;
        $message_details = null;
        switch($from)
        {
            case 'AddObject':
                $message = Resource::AddInvalidObject;
                $message_details = Resource::AddInvalidObject_Details;
                break;
            case 'UpdateObject':
                $message = Resource::UpdateInvalidObject;
                $message_details = Resource::UpdateInvalidObject_Details;
                break;
            case 'DeleteObject':
                $message = Resource::DeleteInvalidObject;
                $message_details = Resource::DeleteInvalidObject_Details;
                break;
            case 'AddLink':
                $message = Resource::AddLinkInvalidObject;
                $message_details = Resource::AddLinkInvalidObject_Details;
                break;
            case 'SetLink':
                $message = Resource::SetLinkInvalidObject;
                $message_details = Resource::SetLinkInvalidObject_Details;
                break;
            case 'DeleteLink':
                $message = Resource::DeleteLinkInvalidObject;
                $message_details = Resource::DeleteLinkInvalidObject_Details;
                break;
            case 'LoadProperty':
                $message = Resource::LoadPropertyInvalidObject;
                $message_details = Resource::LoadPropertyInvalidObject_Details;
                break;
            case 'SetEntityHeaders':
                $message = Resource::SetEntityHeadersInvalidObject;
                $message_details = Resource::SetEntityHeadersInvalidObject_Details;
                break;
            default:
                $message = Resource::InvalidObject;
        }

        throw new InvalidOperation($message, $message_details);
    }

    /**
     * To add a custom header.
     *
     * @param string $headerName The custom header name
     * @param string $HeaderValue The custom header value
     */
    public function addHeader($headerName, $headerValue)
    {
        $this->CustomHeaders[$headerName] = $headerValue;
    }

    /**
     * To clear the array holding custom headers.
     */
    public function removeHeaders()
    {
        unset($this->CustomHeaders);
    }

    /**
     * To get Odata service uri.
     * [Note: Do not call this function from your application, it is used internally]
     *
     * @Return string
     */
    public function GetBaseUri()
    {
        return $this->_baseURI;
    }

    /**
     * To get Odata service uri with slash.
     *
     * @Return string
     */
    public function GetBaseUriWithSlash()
    {
        return $this->_baseUriWithSlash;
    }

   /**
    * To get Odata service uri with-out slash.
    *
    * @return string
    */
    public function GetBaseUriWithOutSlash()
    {
        return rtrim($this->_baseUriWithSlash, '/');
    }

    /**
     * To get the entity set name corrosponding to entity type.
     * [Note: Do not call this function from your application, it is used internally]
     *
     * @param string $entityType The Entity Type
     * @return string
     */
    public function GetEntitySetNameFromType($entityType)
    {
        $entitySet = isset($this->_entityType2Set[strtolower($entityType)])?
                           $this->_entityType2Set[strtolower($entityType)] :
                           $entityType;
        return $entitySet;
    }

    /**
     * To get the entity name corrosponding to entity set.
     * [Note: Do not call this function from your application, it is used internally]
     *
     * @param string $entitySet The Entity Set Name
     * @return string
     */
    public function GetEntityTypeNameFromSet($entitySet)
    {
        $entityType = isset($this->_entitySet2Type[strtolower($entitySet)])?
                            $this->_entitySet2Type[strtolower($entitySet)] :
                            $entitySet;
        return $entityType;
    }

    /**
     * To get relationship 0..1, 1 or *.
     * [Note: Do not call this function from your application, it is used internally]
     *
     * @param string $relationship The Name of association
     * @param string $fromOrToRole The Name of From or To Role
     * @return string
     * @throws InternalError
     */
    public function GetRelationShip($relationship, $fromOrToRole)
    {
         if(!isset($this->_association[$relationship]) ||
            !isset($this->_association[$relationship][$fromOrToRole]))
         {
            throw new InternalError("Invalid RelationShip ($relationship) :
                                     'From' or 'ToRole' ($fromOrToRole)");
         }

         return $this->_association[$relationship][$fromOrToRole];
    }

    /**
     * To get the URI that is used to return binary property data as a data stream.
     *
     * @param EntityObject $entity
     * @return uri
     * @throws InvalidOperation
     */
    public function GetReadStreamUri($entity)
    {
        $resourceBox = null;
        if (!$this->ObjectToResource->TryGetValue($entity, $resourceBox))
        {
            throw new InvalidOperation(Resource::EntityNotContained,
                                       Resource::EntityNotContained_Details);
        }

        return $resourceBox->GetMediaResourceUri($this->_baseUriWithSlash);
    }

    /**
     * Synchronously requests a data stream that contains the binary property of
     * requested Media Link Entry $entity. The $args argument can be null,
     * a string representing Accept message header or instance of DataServiceRequestArgs
     * class which contains settings for the HTTP request message (Slug, Accept,
     * Content-Type etc..).
     *
     * @param EntityObject $entity
     * @param $args null, string or object of DataServiceRequestArgs
     * @return DataServiceStreamResponse
     * @throws InvalidOperation, ODataServiceException
     */
    public function GetReadStream($entity, $args = null)
    {
        $args1 = new DataServiceRequestArgs();
        if($args == null)
        {
        }
        else if(is_string($args))
        {
            $args1->setAcceptContentType($args);
        }
        else if(is_object($args) &&
           is_a($args, 'DataServiceRequestArgs'))
        {
            $args1 = $args;
        }
        else
        {
            throw new InvalidOperation(Resource::InvalidArgumentForGetStream, null);
        }

        $resourceBox = null;
        if (!$this->ObjectToResource->TryGetValue($entity, $resourceBox))
        {
            throw new InvalidOperation(Resource::EntityNotContained,
                                       Resource::EntityNotContained_Details);
        }

        $mediaResourceUri = $resourceBox->GetMediaResourceUri($this->_baseUriWithSlash);
        if ($mediaResourceUri == null)
        {
            throw new InvalidOperation(Resource::EntityNotMediaLinkEntry, null);
        }

        $httpRequest = $this->CreateRequest($mediaResourceUri,
                                             HttpVerb::GET,
                                             true,
                                             Resource::Content_Type_ATOM,
                                             Resource::DataServiceVersion_3);
        $httpRequest->ApplyHeaders($args1->getHeaders());

        $isError = false;
        $innerException = '';
        $httpResponse = $this->ExecuteAndReturnResponse($httpRequest,
                                                    Resource::DataServiceVersion_3,
                                                    $isError,
                                                    $innerException);

        if($isError)
        {
             throw new ODataServiceException($innerException, '',
                                             $httpResponse->getHeaders(),
                                             $httpResponse->getCode());
        }

        return new DataServiceStreamResponse($httpResponse);
    }

    /**
     * To set a new data stream as the binary property of an entity, with the
     * specified settings in the request message.
     *
     * @param EntityObject $entity
     * @param BinaryStream $stream
     * @param boolean $closeStream
     * @param HttpRequestHeader::ContentType $contentType
     * @param HttpRequestHeader::Slug $slug
     * @throws InvalidOperation
     */
    public function SetSaveStream($entity, $stream, $closeStream, $contentType, $slug)
    {
        if(empty($contentType))
        {
            throw new InvalidOperation('SetSaveStream: The contentType' .
                                       Resource::ArgumentNotNull);
        }

        if(empty($slug))
        {
            throw new InvalidOperation('SetSaveStream: The slug' .
                                       Resource::ArgumentNotNull);
        }

        if(empty($stream))
        {
            throw new InvalidOperation('SetSaveStream: The stream' .
                                       Resource::ArgumentNotNull);
        }

        $resourceBox = null;
        if (!$this->ObjectToResource->TryGetValue($entity, $resourceBox))
        {
            throw new InvalidOperation(Resource::EntityNotContained,
                                       Resource::EntityNotContained_Details);
        }

        $args = new DataServiceRequestArgs();
        $args->setContentType($contentType);
        $args->setSlug($slug);

        $resourceBox->MediaLinkEntry = true;
        $resourceBox->SaveStream = new DataServiceSaveStream($stream, $args);
    }

    /**
     * To create an HttpRequest object with certain headers set based on the
     * parameters passed.
     * [Note: Do not call this function from your application, it is used internally]
     *
     * @param Uri $requestUri
     * @param HttpVerb::* $httpVerb
     * @param boolean $allowAnyType
     * @param string  $contentType
     * @param Resource::DataServiceVersion_* $dataServiceVersion
     * @return HttpRequest
     */
    public function CreateRequest($requestUri, $httpVerb,
                                   $allowAnyType,  $contentType,
                                   $dataServiceVersion)
    {

        $headers = array();

        if($this->UsePostTunneling &&
           $httpVerb != HttpVerb::POST &&
           $httpVerb != HttpVerb::GET)
        {
            $headers[HttpRequestHeader::XHTTPMethod] = $httpVerb;
            $httpVerb = HttpVerb::POST;
        }

        if($dataServiceVersion == null)
        {
            $dataServiceVersion = Resource::DataServiceVersion_1;
        }

        $headers[HttpRequestHeader::Accept] =  $allowAnyType ?
                                                '*/*' :
                                                'application/atom+xml,application/xml';
        $headers[HttpRequestHeader::AcceptCharset] = 'UTF-8';
        $headers['DataServiceVersion'] = $dataServiceVersion;
        $headers['MaxDataServiceVersion'] = Resource::DataServiceVersion_3;

        if($httpVerb !=  HttpVerb::GET)
        {
            $headers[HttpRequestHeader::ContentType] = $contentType;
        }

        $httpRequest = new HttpRequest($httpVerb, $requestUri,
                                       $this->Credential, $this->HttpProxy,
                                       $headers);
	return $httpRequest;
    }

    /**
     * To update the EditLink and Identity of ResourceBox representing the $entity
     * using $location, Also update the IdentityToResource Dictionary.
     * [Note: Do not call this function from your application, it is used internally]
     *
     * @param EntityObject $entity
     * @param Uri $location
     * @throws InternalError
     */
    public function AttachLocation($entity, $location)
    {
        if( array_key_exists($location, $this->IdentityToResource))
        {
           unset($this->IdentityToResource[$location]);
        }

        $resourceBox = null;
        if (!$this->ObjectToResource->TryGetValue($entity, $resourceBox))
        {
            throw new InternalError(Resource::AttachLocationFailedDescRetrieval);
        }

        $index = Utility::reverseFind($location, '/');
        $editLink = substr($location,$index + 1, strlen($location) - $index);
        $resourceBox->Identity = $location;
        $resourceBox->EditLink = $editLink;
        $this->IdentityToResource[$location] = $resourceBox;
    }

    /**
     * To register the call back to be invoked before sending request.
     *
     * @param string $function_name
     * @param object $class_instance
     */
    public function OnBeforeRequest($function_name, $class_instance)
    {
        $this->_onBefore_cb_instance	= $class_instance;
	$this->_onBefore_cb_function	= $function_name;
    }

    /**
     * To register call back to be invoked after receiving response.
     *
     * @param string $function_name
     * @param object $class_instance
     */
    public function OnAfterResponse($function_name, $class_instance)
    {
        $this->_onAfter_cb_instance	= $class_instance;
	$this->_onAfter_cb_function	= $function_name;
    }

    /**
     * To invoke client registered callback using OnBeforeCall.
     * [Note: Do not call this function from your application, it is used internally]
     *
     * @param HttpRequest $httpRequest
     */
    public function OnBeforeRequestInternal($httpRequest)
    {
        $this->InvokeCallBack($this->_onBefore_cb_instance,
                              $this->_onBefore_cb_function,
                              $httpRequest);
    }

    /**
     * To invoke client registered callback using OnAfterCall.
     * [Note: Do not call this function from your application, it is used internally]
     *
     * @param HttpResponse $httpResponse
     */
    public function OnAfterResponseInternal($httpResponse)
    {
        $this->InvokeCallBack($this->_onAfter_cb_instance,
                              $this->_onAfter_cb_function,
                              $httpResponse);
    }

    /**
     * To invoke the callback.
     * [Note: Do not call this function from your application, it is used internally]
     *
     * @param $instance
     * @param string $function
     * @param HttpRequest/HttpResponse $param
     * @throws InvalidOperation
     */
    protected function InvokeCallBack($instance, $function, $param)
    {
        try
    	{
            if($function)
            {
		if($instance)
		{
                    $class = new ReflectionClass(get_class($instance));
                    $method = $class->getMethod($function);
                    $method->Invoke($instance, $param);
		}
		else
		{
                    $func = new ReflectionFunction($function);
                    $func->Invoke($param);
		}
            }
    	}
    	catch(ReflectionException $exception)
    	{
    		throw new InvalidOperation($exception->getMessage());
    	}
    }
}
?>
