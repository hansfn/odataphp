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
class SaveResult
{
    /**
     * Reference to contextobject instance
     *
     * @var ObjectContext
     */
    protected $_context;

    /**
     * The list holding the merged result of values in ObjectToResource and
     * Bindings dictionaries of context object, only the entries with State
     * Unchanged will be loaded. For each entries in this list one changeset
     * (a MIME part of batchRequest or a non-batchRequest) will be genereated.
     *
     * @var ResourceBox[] and RelatedEnd[]
     */
    protected $_changedEntries;

    /**
     * The string holding batch boundary for the batch request
     * format will be batch_{guid}
     *
     * @var string
     */
    protected $_batchBoundary;

    /**
     * The string holding changeset boundary for the batch request
     * format will be changeset_{guid}
     *
     * @var string
     */
    protected $_changesetBoundry;

    /**
     * The string holding the body part of batch request.
     *
     * @var string
     */
    protected $_batchRequestBody;

    /**
     * The variable to hold HttpBatchResponse instance in batch-mode.
     *
     * @var HttpBatchResponse
     */
    protected $_httpBatchResponse;

    /**
     * The list holding the collection of http response for each chnageset
     * in the batch request.
     *
     * @var HttpResponse[]
     */
    protected $_httpResponses;

    /**
     * Used during Non-Batch mode updation.
     *
     * @var boolean
     */
    protected $_completed;

    /**
     * Used to identify the entry in the _changedEntries array which
     * is currenty processing during Non-Batch mode.
     *
     * @var int
     */
    protected $_entryIndex;

    /**
     * true if context is processing a stream which is set using SetSaveStream,
     * with PUT or POST operation.
     *
     * @var boolean
     */
    protected $_processingMediaLinkEntry;

    /**
     * true if context is processing a stream which is set using SetSaveStream,
     * with PUT operation.
     *
     * @var boolean
     */
    protected $_processingMediaLinkEntryPut;

    /**
     * The stream set by SetSaveStream.
     *
     * @var string
     */
    protected $_mediaResourceRequestStream;

    /**
     *
     * @var OperationResponse[]
     */
    protected $_operationResponses;

    /**
     *
     * @var array<int, HttpStatus>
     */
    protected $_changeOrderIDToHttpStatus;

     /**
      * Construct SaveResult
      *
      * @param ObjectContext $context
      * @param SaveChangesOption $saveChangesOptions
      */
    public function SaveResult($context, $saveChangesOptions)
    {
        $this->_context = $context;
        $mergedDictionary = Dictionary::Merge($this->_context->ObjectToResource,
                                        $this->_context->Bindings,
                                        "State", Entitystates::Unchanged,
                                        FALSE);
        $mergedDictionary->Sort("ChangeOrder");
        $this->_changedEntries =  $mergedDictionary->Values();
        $this->_batchBoundary = "batch_" . Guid::NewGuid();
        $this->_completed = false;
        $this->_entryIndex = -1;
        $this->_processingMediaLinkEntry = false;
        $this->_processingMediaLinkEntryPut = false;
        $this->_mediaResourceRequestStream = null;
        $this->_operationResponses =array();
        $this->_changeOrderIDToHttpStatus = array();

        if($saveChangesOptions == SaveChangesOptions::None)
        {
            //If savechange option is non-batch mode then we should retrive all
            //MLE resource boxes with state 'Unchanged' and Stream not equal to
            //null and add it to $this->_changedEntries.
            $entries = $this->_context->ObjectToResource->Values();
            foreach($entries as $entry)
            {
                if($entry->State == EntityStates::Unchanged &&
                   $entry->SaveStream != null)
                {
                    $this->_changedEntries[] = $entry;
                }
            }
        }
    }

    /*
     * To create and perform batch request from the changed entries list. For any
     * update operation on an entity (not binding) this function will use MERGE
     * if $replaceOnUpdateOption is false else PUT.
     *
     * @param boolean $replaceOnUpdateOption
     * @throws ODataServiceException
     */
    public function BatchRequest($replaceOnUpdateOption)
    {
        $this->_changesetBoundry =  "changeset_" . Guid::NewGuid();
        $changedEntriesCount = count($this->_changedEntries);
        if($changedEntriesCount > 0)
        {
            try
            {
                Utility::WriteLine($this->_batchRequestBody,
                                   "--" . $this->_batchBoundary);
                Utility::WriteLine($this->_batchRequestBody,
                                   "Content-Type: multipart/mixed; boundary=" .
                                   $this->_changesetBoundry);
                Utility::WriteLine($this->_batchRequestBody, null);
                for($i = 0; $i < $changedEntriesCount; $i++)
                {
                    Utility::WriteLine($this->_batchRequestBody,
                                       "--" . $this->_changesetBoundry);
                    $changesetHeader = $this->CreateChangeSetHeader($i, $replaceOnUpdateOption);
                    $changesetBody =$this->CreateChangeSetBody($i);
                    if ($changesetBody != null)
                    {
                        Utility::WriteLine($changesetHeader,
                                           "Content-Length: " . strlen($changesetBody));
                    }
                    Utility::WriteLine($changesetHeader, null);
                    $this->_batchRequestBody = $this->_batchRequestBody . $changesetHeader;
                    if($changesetBody != null)
                    {
                        $this->_batchRequestBody = $this->_batchRequestBody . $changesetBody;
                    }
                }
            }
            catch(InvalidOperation $exception)
            {
                //InvalidOperation Exception can be thrown only from the processing
                //logic of framework while building the batch request headers and
                //body, so no http repsonse headers, status code will be there.
                throw new ODataServiceException($exception->getError(),
                                                '',
                                                array(),
                                                null);
            }

            Utility::WriteLine($this->_batchRequestBody, "--" . $this->_changesetBoundry . "--");
            Utility::WriteLine($this->_batchRequestBody, "--" . $this->_batchBoundary . "--");
            $this->PerformBatchRequest();
            $this->EndBatchRequest();
        }
    }

    /**
     * To perform batch request
     *
     * @throws ODataServiceException
     */
    protected function PerformBatchRequest()
    {
        $credentialInHeaders = false;
        $uri = $this->_context->GetBaseUriWithSlash() . '$batch';
        $httpBatchRequest = new HttpBatchRequest($uri,
                                                 $this->_batchBoundary,
                                                 $this->_batchRequestBody,
                                                 $this->_context->Credential,
                                                 $this->_context->HttpProxy,
                                                 $this->_context->CustomHeaders,
                                                 $credentialInHeaders);
        try
        {
            //require a try-catch block to handle curl error due to curl_exec
            //failure (like failed to connect to host)and missing of changeset
            //boundary in batchresponse tested in BatchResponse::Create invoken
            //by HttpBatchRequest::GetResponse.
            $this->_context->OnBeforeRequestInternal($httpBatchRequest->GetRawHttpRequest());
            $this->_httpBatchResponse = $httpBatchRequest->GetResponse();
            $this->_context->OnAfterResponseInternal($this->_httpBatchResponse->GetAsHttpResponse());
        }
        catch(InvalidOperation $exception)
        {
           throw new ODataServiceException($exception->getError() .
                                           $exception->getDetailedError(),
                                           '',
                                           array(),
                                           null);
        }

        //Error check for batch Response ex:UnAuthorized
        if(! $this->_httpBatchResponse->IsError())
        {
            $this->_httpResponses = $this->_httpBatchResponse->GetSubBatchHttpResponses();
        }
        else
        {
            throw new ODataServiceException($this->_httpBatchResponse->GetMessage(),
                                            '',
                                            $this->_httpBatchResponse->GetHeaders(),
                                            $this->_httpBatchResponse->GetCode());
        }
    }

    /*
     *  To
     *  a. Check any error is returned by OData service (ex: ODataService
     *     version mismatch, if user try to add a record with existing key)
     *  b. Populate the entities created by user from the OData service response
     *  c. Update entity states and do clean up activity.
     *
     * @throws ODataServiceException
     */
    protected function EndBatchRequest()
    {
        $this->CheckForDataServiceVersion();
        $this->CheckForDataServiceError();
        $this->LoadResourceBoxes();

        $relatedEnds = $this->_context->Bindings->Values();
        foreach($relatedEnds as $relatedEnd)
        {
            if($relatedEnd->State == EntityStates::Deleted)
            {
                $this->_context->Bindings->Remove($relatedEnd);
            }

            if($relatedEnd->State == EntityStates::Modified ||
               $relatedEnd->State == EntityStates::Added)
            {
                $relatedEnd->State = EntityStates::Unchanged;
            }
        }

        $resourceBoxes = $this->_context->ObjectToResource->Values();
        foreach($resourceBoxes as $resourceBox)
        {
            if($resourceBox->State == EntityStates::Deleted)
            {
                if(null != $resourceBox->Identity)
                {
                    unset($this->_context->IdentityToResource[$resourceBox->Identity]);
                }
                $this->_context->ObjectToResource->Remove($resourceBox->GetResource());
            }
            if($resourceBox->State == EntityStates::Modified)
            {
                $resourceBox->State = EntityStates::Unchanged;
            }
        }
    }

    /*
     * To check the version mismatch and throws ODataServiceException in case of
     * version mismatch.
     *
     * @throws ODataServiceException
     */
    protected function CheckForDataServiceVersion()
    {
        foreach($this->_httpResponses as $httpResponse)
        {
            $headers = $httpResponse->getHeaders();
            if(isset($headers['Dataserviceversion']) &&
            ((int)$headers['Dataserviceversion'] > (int)Resource::MaxDataServiceVersion))
            {
                throw new ODataServiceException(Resource::VersionMisMatch .
                                                $headers['Dataserviceversion'],
                                                '',
                                                array(),
                                                null);
            }
        }
    }

    /*
     * To check OData service response for any error and throws ODataServiceException
     * in case of error. For example if Client try to add an entity with a key which
     * is already existing in the OData Service, then OData Service will throw
     * 'Violation of PRIMARY KEY constraint' error.
     *
     * @throws ODataServiceException
     */
    protected function CheckForDataServiceError()
    {
        if(count($this->_httpResponses) == 1)
        {
            $httpResponse = $this->_httpResponses[0];
            if($httpResponse->isError())
            {
                $headers = $httpResponse->getHeaders();
                $content_type = isset($headers['Content-type']) ? $headers['Content-type']: "";
                throw new ODataServiceException($httpResponse->getBody(),
                                                $content_type,
                                                $headers,
                                                $httpResponse->getCode());
            }
        }
    }

    /**
     * Load the resource boxes holding the entity instances added by client, from
     * corrosponding response from OData service.
     *
     */
    protected function LoadResourceBoxes()
    {
        foreach($this->_changedEntries as $changedEntry)
        {
            if(($changedEntry->IsResource()) &&
               ($changedEntry->State == EntityStates::Added))
            {
                $this->LoadResourceBox($changedEntry);
            }
        }
    }

    /**
     * Load a resourcebox's entity instance in added state from OData service response.
     *
     * @param ResourceBox $resourceBox
     */
    protected function LoadResourceBox($resourceBox)
    {
         $Content_ID = $resourceBox->ChangeOrder;
         $str = $this->GetBodyByContentID($Content_ID, $content_type);
         if($str == null)
         {
            return;
         }

        $this->_context->LoadResourceBox($str, $resourceBox, $content_type);
        $resourceBox->State = EntityStates::Unchanged;
    }

     /**
      * To retrieve http response body corrosponding to a specific content-id
      *
      * @param int $Content_ID
      * @param string [out] $content_type
      * @return string or null
      */
    protected function GetBodyByContentID($Content_ID, &$content_type)
    {
        foreach($this->_httpResponses as $httpResponse)
        {
            $headers = $httpResponse->getHeaders();
            if($headers['Content-id'] == $Content_ID)
            {
                if(isset($headers['Content-type']))
                {
                    $content_type = $headers['Content-type'];
                }

                return $httpResponse->getBody();
            }
        }

        return null;
    }

    /**
     * To creates changeset body (MIME part body) for
     * a changeset that will become a part batchrequest.
     *
     * @param int $index
     * @return string returns body part of one MIME part
     * @throws InvalidOperation
     */
    protected function CreateChangeSetBody($index)
    {
        $changesetBody;
        $entry = $this->_changedEntries[$index];
        if($entry->IsResource())
        {
            $changesetBody = $this->CreateChangeSetBodyForResource($entry, false);
        }
        else
        {
            $changesetBody = $this->CreateChangesetBodyForBinding($entry, true);
        }

        return $changesetBody;
    }

     /**
      * To create changeset body for an entity in ATOMPub format.
      *
      * @param ResourceBox $resourceBox
      * @param boolean $newline
      * @return string
      * @throws InvalidOperation, InternalError
      */
    protected function CreateChangeSetBodyForResource($resourceBox, $newline)
    {
        $syndicationUpdated = false;
        if(EntityStates::Deleted == $resourceBox->State)
        {
            return null;
        }

        if(EntityStates::Added != $resourceBox->State &&
            EntityStates::Modified != $resourceBox->State)
        {
            throw new InternalError(Resource::UnexpectdEntityState);
        }

        $changesetBodyForResource = null;
        Utility::WriteLine($changesetBodyForResource, "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>");
        Utility::WriteLine($changesetBodyForResource, "<entry xmlns:d=\"http://schemas.microsoft.com/ado/2007/08/dataservices\" xmlns:m=\"http://schemas.microsoft.com/ado/2007/08/dataservices/metadata\" xmlns=\"http://www.w3.org/2005/Atom\">");

        $object = $resourceBox->getResource();
        $entityName = get_class($object);
        $type = ClientType::Create($entityName);
        $nullCFProperties = array();
        $CF_SyndicationXML = null;
        $CF_NonSyndicationXML = null;

        $this->HandleNCharProperties($type, $object);

        //Check this Client Type has Syndication or Custom Property mapping, if so
        //build the syndication XML/Custom XML.
        if($type->hasEPM())
        {
            $epmProperties = $type->getRawSortedEPMProperties();
            $FC_Synd_dom = new DOMDocument();
            $FC_NonSynd_dom = new DOMDocument();
            foreach($epmProperties as $epmProperty)
            {
                $synd = false;
                if($epmProperty->hasEPM($synd))
                {
                    $propertyName = $epmProperty->getName();
                    $attributes = $epmProperty->getAttributes();
                    $refProperty = new ReflectionProperty($object, $propertyName);
                    $propertyValue = $refProperty->getValue($object);

                    if($synd)
                    {
                        if($attributes['FC_TargetPath'] == 'SyndicationUpdated')
                        {
                            $syndicationUpdated = true;
                            $propertyValue = Utility::TimeInISO8601();
                        }

                        if(is_null($propertyValue))
                        {
                            $nullCFProperties[] = $propertyName;
                        }

                        $path = SyndicationItemProperty::GetSyndicationItemPathNoNS($attributes['FC_TargetPath']);
                        XMLBuilder::BuildDOMFromPath($FC_Synd_dom,
                                                     $path,
                                                     $propertyValue,
                                                     '',
                                                     '',
                                                     false);
                    }
                    else
                    {
                        if(is_null($propertyValue))
                        {
                            $nullCFProperties[] = $propertyName;
                        }

                        XMLBuilder::BuildDOMFromPath($FC_NonSynd_dom,
                                                     $attributes['FC_TargetPath'],
                                                     $propertyValue,
                                                     $attributes['FC_NsPrefix'],
                                                     $attributes['FC_NsUri'],
                                                     true);
                    }
                }
            }

            $CF_SyndicationXML = trim(str_replace('<?xml version="1.0"?>', '',
                                                  $FC_Synd_dom->SaveXML()));
            $CF_NonSyndicationXML = trim(str_replace('<?xml version="1.0"?>', '',
                                                     $FC_NonSynd_dom->SaveXML()));
        }


        if(!empty($CF_SyndicationXML))
        {
            Utility::WriteLine($changesetBodyForResource, $CF_SyndicationXML);
            if(!$syndicationUpdated)
            {
                Utility::WriteLine($changesetBodyForResource, '<updated>' .
                                                              Utility::TimeInISO8601() .
                                                              '</updated>');
            }
        }
        else
        {
            Utility::WriteLine($changesetBodyForResource, '<title />');
            Utility::WriteLine($changesetBodyForResource, '<author>');
            Utility::WriteLine($changesetBodyForResource, '<name />');
            Utility::WriteLine($changesetBodyForResource, '</author>');
            Utility::WriteLine($changesetBodyForResource, '<updated>' .
                                                          Utility::TimeInISO8601() .
                                                          '</updated>');
        }

        if( EntityStates::Modified == $resourceBox->State)
        {
            $editLinkUri = $resourceBox->Identity;
            Utility::WriteLine($changesetBodyForResource, '<id>' .
                                                          $editLinkUri .
                                                          '</id>');

            //While modifying, key properties cannot be null
            $keyPropertyNames = $type->geyKeyProperties();
            foreach($keyPropertyNames as $keyPropertyName)
            {
                $prop = new ReflectionProperty($object, $keyPropertyName);
                $propertyValue = $prop->getValue($object);
                if(empty($propertyValue) && !$this->_processingMediaLinkEntry)
                {
                    throw new InvalidOperation(Resource::NullValueNotAllowedForKey .
                                               $keyPropertyName);
                }
            }
        }
        else
        {
            Utility::WriteLine($changesetBodyForResource, '<id />');
        }

        if(!$resourceBox->MediaLinkEntry)
        {
            Utility::WriteLine($changesetBodyForResource,
                               "<content type=\"application/xml\">");
        }

        Utility::WriteLine($changesetBodyForResource, '<m:properties>');

        $nonEpmProperties = $type->getRawNonEPMProperties(true);
        foreach($nonEpmProperties as $nonEpmProperty)
        {

            $propertyName = $nonEpmProperty->getName();
            $refProperty = new ReflectionProperty($object, $propertyName);
            $propertyValue = $refProperty->getValue($object);

            $body = $this->CheckAndCreateChangeSetBodyPartForComplexType($nonEpmProperty, $propertyValue);
            if($body)
            {
                Utility::WriteLine($changesetBodyForResource, $body);
                continue;
            }

            $property = "";
            if(empty($propertyValue) || is_null($propertyValue))
            {
                Utility::GetPropertyType($refProperty, $notNullable);
                if(!$notNullable)
                {
                    $property = "<d:" . $propertyName . " " . "m:null=\"true\" />";
                }
                else
                {
                    //ex: In the case of OrderID, the ID is a autonumber, so user will not
                    //specify this value, since its a nonnullable value we cant set null=true
                    //property, in this case the correct property node should be
                    //<d:OrderID">0</d:OrderID">, but instead of this we can also achieve the
                    //same effect by not adding the property itself in xml
                    continue;
                }
            }
            else
            {
                $attributes = $nonEpmProperty->getAttributes();
                $edmType = '';
                if(isset($attributes['EdmType']) &&
                   $attributes['EdmType'] != 'Edm.String')
                {
                    $edmType = ' m:type="' . $attributes['EdmType'] . '"';
                }

                $property = '<d:' . $propertyName . "$edmType>" .
                            $propertyValue .
                            '</d:' . $propertyName . '>';
            }

            Utility::WriteLine($changesetBodyForResource, $property);
        }

        //Check for CF properties for which user has set null value. In this
        //case these properties should be added in the content section.
        foreach($nullCFProperties as $nullCFProperty)
        {
            $refProperty = new ReflectionProperty($object, $propertyName);
            $property = "<d:" . $nullCFProperty . " " . "m:null=\"true\" />";
            Utility::WriteLine($changesetBodyForResource, $property);
        }

        Utility::WriteLine($changesetBodyForResource, '</m:properties>');
        if(!$resourceBox->MediaLinkEntry)
        {
            Utility::WriteLine($changesetBodyForResource, '</content>');
        }

        //append CF Cutom XML
        if(!empty($CF_NonSyndicationXML))
        {
            Utility::WriteLine($changesetBodyForResource, $CF_NonSyndicationXML);
        }

        Utility::WriteLine($changesetBodyForResource, '</entry>');
        if($newline)
        {
            Utility::WriteLine($changesetBodyForResource, null);
        }

        return $changesetBodyForResource;
    }

    /**
     *
     * @param ClientType::Property $property
     * @param Object $object reference to the complex property of entity
     */
    protected function CheckAndCreateChangeSetBodyPartForComplexType($property, $object)
    {
        $propertyNameCT = $property->getName();
        $propertyAttributes = $property->getAttributes();
        $complexBody = null;
        $index = 0;

        //Now check for complex type. If type not start with 'Edm.'
        //it can be a complex type.
        if(isset($propertyAttributes['EdmType']) &&
            ($index = strpos($propertyAttributes['EdmType'], 'Edm.')) !== 0)

        {
            $complexBody = '<d:' . $propertyNameCT . ' m:type="' .
                            $propertyAttributes['EdmType'] . '">' . "\n";
            $type = ClientType::Create($propertyNameCT);
            $nonEpmProperties = $type->getRawNonEPMProperties(true);
            foreach($nonEpmProperties as $nonEpmProperty)
            {
                $propertyName = $nonEpmProperty->getName();
                $refProperty = new ReflectionProperty($object, $propertyName);
                $propertyValue = $refProperty->getValue($object);
                $property = null;
                if(empty($propertyValue) || is_null($propertyValue))
                {
                    Utility::GetPropertyType($refProperty, $notNullable);
                    if(!$notNullable)
                    {
                        $property = "<d:" . $propertyName . " " . "m:null=\"true\" />";
                    }
                    else
                    {
                        continue;
                    }
                }
                else
                {
                    $attributes = $nonEpmProperty->getAttributes();
                    $edmType = '';
                    if(isset($attributes['EdmType']) &&
                    $attributes['EdmType'] != 'Edm.String')
                    {
                        $edmType = ' m:type="' . $attributes['EdmType'] . '"';
                    }

                    $property = '<d:' . $propertyName . "$edmType>" .
                                $propertyValue .
                                '</d:' . $propertyName . '>';
                }

                if(isset($property))
                {
                    Utility::WriteLine($complexBody, $property);
                }
            }
        }

        if($complexBody)
        {
            $complexBody .=  '</d:' . $propertyNameCT . '>';
        }

        return $complexBody;
    }

     /**
      * To create changeset body for a binding in ATOMPub format.
      *
      * @param RelatedEnd $binding
      * @param boolean $newline
      * @return string
      */
    protected function CreateChangesetBodyForBinding($binding, $newline)
    {
        if ((EntityStates::Added != $binding->State) &&
            (EntityStates::Modified !=  $binding->State))
        {
            return null;
        }

        //In the case of SetLink target can be null to indicate DELETE operation
        if(null == $binding->GetTargetResource())
        {
            return null;
        }

        $changesetBodyForBinding = null;
        $targetObjectUri = null;
        $targetResourcebox = null;
        $this->_context->ObjectToResource->TryGetValue($binding->GetTargetResource(), $targetResourcebox);
        if($targetResourcebox->Identity != null)
        {
            $targetObjectUri = $targetResourcebox->Identity;
        }
        else
        {
            $targetObjectUri = "$" . $targetResourcebox->ChangeOrder;
        }

        Utility::WriteLine($changesetBodyForBinding,
                           "<?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\"?>");
        $changesetBodyForBinding = $changesetBodyForBinding .
                                   "<uri xmlns=\"http://schemas.microsoft.com/ado/2007/08/dataservices/metadata\">";
        $changesetBodyForBinding = $changesetBodyForBinding . $targetObjectUri . "</uri>";
        if($newline)
        {
            Utility::WriteLine($changesetBodyForBinding, null);
        }

        return $changesetBodyForBinding;
    }

     /**
      * To creates changeset header (MIME part header) for a changeset that
      * will become a part batchrequest.
      *
      * @param int $index
      * @param boolean $replaceOnUpdateOption
      * @return string
      */
    public function CreateChangeSetHeader($index, $replaceOnUpdateOption)
    {
        $changesetHeader = null;
        $entry = $this->_changedEntries[$index];
        if($entry->IsResource())
        {
            $changesetHeader = $this->CreateChangeSetHeaderForResource($entry, $replaceOnUpdateOption);
        }
        else
        {
            $changesetHeader = $this->CreateChangesetHeaderForBinding($entry, true);
        }
        return $changesetHeader;
    }

     /**
      * To create changeset header for an entity in batch mode.
      *
      * @param Resource $resourceBox
      * @param boolean $replaceOnUpdateOption
      * @return string
      */
    public function CreateChangeSetHeaderForResource($resourceBox, $replaceOnUpdateOption)
    {
         $entityHttpMethod = $this->GetEntityHttpMethod($resourceBox->State,
                                                        $replaceOnUpdateOption);
         $changesetHeaderForResource = null;
         $resourceUri = $resourceBox->GetResourceUri($this->_context->GetBaseUriWithSlash());
         $this->WriteOperationRequestHeaders($changesetHeaderForResource,
                                      $entityHttpMethod,
                                      $resourceUri);
         Utility::WriteLine($changesetHeaderForResource,
                            "Content-ID: " . $resourceBox->ChangeOrder);
         Utility::WriteLine($changesetHeaderForResource,
                            "Accept: " . $this->_context->Accept);
         //If user specified any header specific to this resource using
         //SetEntityHeader then write those headers.
         if(count($resourceBox->Headers) != 0)
         {
            foreach($resourceBox->Headers as $httpHeadrName => $httpHeaderValue)
            {
                Utility::WriteLine($changesetHeaderForResource,
                                   $httpHeadrName . ": " . $httpHeaderValue);
            }
         }

         //In case of PUT, DELETE or MERGE operation, if the entity supports
         //etag then we need to write If-Match header
         if($entityHttpMethod != HttpVerb::POST &&
            isset($resourceBox->EntityETag))
         {
            Utility::WriteLine($changesetHeaderForResource,
                               "If-Match: " . $resourceBox->EntityETag);
         }

         if (EntityStates::Deleted != $resourceBox->State)
         {
            Utility::WriteLine($changesetHeaderForResource, "Content-Type: " .
                                                            "application/atom+xml" .
                                                            ";" .
                                                            "type=entry");
         }

         return $changesetHeaderForResource;
    }

     /**
      * To create changeset header for binding in batch mode.
      *
      * @param RelatedEnd $binding
      * @return string
      */
    protected function CreateChangesetHeaderForBinding($binding)
    {
        $changesetHeaderForBinding = null;
        $uri = $this->CreateRequestRelativeUri($binding);
        $sourceResourceBox = null;
        $this->_context->ObjectToResource->TryGetValue($binding->GetSourceResource(),
                                                       $sourceResourceBox);
        $absoluteUri = null;
        if (null != $sourceResourceBox->Identity)
        {
            $absoluteUri = $sourceResourceBox->GetResourceUri($this->_context->GetBaseUriWithSlash());
        }
        else
        {
            $absoluteUri = "$" . $sourceResourceBox->ChangeOrder;
        }

        $absoluteUri = $absoluteUri . "/" . $uri;
        $this->WriteOperationRequestHeaders($changesetHeaderForBinding,
                                            $this->GetBindingHttpMethod($binding),
                                            $absoluteUri);
        Utility::WriteLine($changesetHeaderForBinding,
                           "DataServiceVersion: 1.0;NetFx");
        Utility::WriteLine($changesetHeaderForBinding,
                           "Accept: " . $this->_context->Accept);
        Utility::WriteLine($changesetHeaderForBinding,
                           "Content-ID: " . $binding->ChangeOrder);
        if ((null != $binding->GetTargetResource())&&
            ((EntityStates::Added == $binding->State) ||
            (EntityStates::Modified == $binding->State)))
        {
           Utility::WriteLine($changesetHeaderForBinding,
                              "Content-Type: application/xml");
        }

        return $changesetHeaderForBinding;
    }

     /**
      * To create relative URI for binding operation based on binding state.
      *
      * @param RelatedEnd $binding
      * @return Uri
      */
    protected function CreateRequestRelativeUri($binding)
    {
        $property = new ReflectionProperty($binding->GetSourceResource(),
                                           $binding->GetSourceProperty());
        $attributes = Utility::getAttributes($property);
        $relationShip = $this->_context->GetRelationShip($attributes["Relationship"],
                                                         $attributes["ToRole"]);
        $Iscollection = ($relationShip != "0..1" && $relationShip != "1")? true: false;

        if ($Iscollection && EntityStates::Added != $binding->State)
        {
            $targetResourceBox = null;
            $this->_context->ObjectToResource->TryGetValue($binding->GetTargetResource(),
                                                           $targetResourceBox);
            $editLinkUri = $this->GenerateEditLinkUri($this->_context->GetBaseUriWithSlash(),
                                                      $targetResourceBox->GetResource(),
                                                      true);
            return "$" . "links/" . $editLinkUri;
        }

        return "$" . "links/" . $binding->GetSourceProperty();
    }

     /**
      * To create EditLink URI for an entity hold by the ResourceBox $resource.
      *
      * @param Uri $baseUriWithSlash
      * @param ResourceBox $resource
      * @param boolean $isRelative
      * @return Uri
      */
    protected function GenerateEditLinkUri($baseUriWithSlash, $resource, $isRelative)
    {
        $editLinkUri = '';
        if(!$isRelative)
        {
            $editLinkUri = $baseUriWithSlash;
        }

        $editLinkUri = $editLinkUri . Utility::getUri($resource);
        return $editLinkUri;
    }

     /**
      * To append operation request headers to $outVal.
      *
      * @param string [out] $outVal
      * @param HttpVerb $methodName
      * @param Uri $uri
      */
    protected function WriteOperationRequestHeaders(&$outVal, $methodName, $uri)
    {
        Utility::WriteLine($outVal, "Content-Type: application/http");
        Utility::WriteLine($outVal, "Content-Transfer-Encoding: binary");
        Utility::WriteLine($outVal, null);
        Utility::WriteLine($outVal, $methodName . " " . $uri . " " . "HTTP/1.1");
    }

     /**
      * To get HTTP Verb to be used for an entity instance based on the state of the
      * ResourceBox object holding the entity instance.
      *
      * @param EntityStates $state
      * @param boolean $replaceOnUpdateOption
      * @return HttpVerb
      * @throws InternalError
      */
    protected function GetEntityHttpMethod($state, $replaceOnUpdateOption)
    {
        if($state == EntityStates::Added)
        {
            return HttpVerb::POST;
        }

        if($state == EntityStates::Deleted)
        {
            return HttpVerb::DELETE;
        }

        if($state == EntityStates::Modified)
        {
            if($replaceOnUpdateOption)
            {
                return HttpVerb::PUT;
            }

            return HttpVerb::MERGE;
        }

        throw new InternalError(Resource::InvalidEntityState);
    }

     /**
      * To get HTTP Verb to be used for a binding based on the state of the
      * RelatedEnd object holding the relationship.
      *
      * @param RelatedEnd $binding
      * @return HttpVerb
      */
    public function GetBindingHttpMethod($binding)
    {
        $property = new ReflectionProperty($binding->GetSourceResource(),
                                           $binding->GetSourceProperty());
        $attributes = Utility::getAttributes($property);
        $relationShip = $this->_context->GetRelationShip($attributes["Relationship"],
                                                         $attributes["ToRole"]);
        //SetLink
        if($relationShip == '0..1' || $relationShip == '1')
        {
            //SetLink with target null
            if($binding->GetTargetResource() == null)
            {
                return HttpVerb::DELETE;
            }

            return HttpVerb::PUT;
        }
        //DeleteLink
        if (EntityStates::Deleted == $binding->State)
        {
            return HttpVerb::DELETE;
        }
        //AddLink
        return HttpVerb::POST;
    }

     /**
      * To perform chnage set requests in non-batch mode. For any update operation
      * on an entity (not binding) this function will use MERGE if $replaceOnUpdateOption
      * is false else PUT.
      *
      * @param boolean $replaceOnUpdateOption
      * @return DataServiceResponse
      */
    public function NonBatchRequest($replaceOnUpdateOption)
    {
        $headers = array();
        $code = null;

        do
        {
            $headers = array();
            $code = null;

            try
            {
                $httpRequest = $this->CreateRequestHeaderForSingleChange($replaceOnUpdateOption);

                if($httpRequest != null ||
                   $this->_entryIndex < count($this->_changedEntries))
                {
                    $contentStream = $this->CreateRequestBodyForSingleChange($this->_entryIndex);
                    if (($contentStream != null) &&
                        (($stream = $contentStream->getStream()) != null))
                    {
                        $httpMethod = $httpRequest->getMethod();

                        if (($contentStream->IsKnownMemoryStream()) &&
                            ($httpMethod == HttpVerb::POST))
                        {
                           //$httpRequest->ApplyHeaders(array(HttpRequestHeader::ContentLength => strlen($stream)));
                        }

                        if($httpMethod == HttpVerb::POST ||
                           $httpMethod == HttpVerb::MERGE)
                        {
                            $httpRequest->setPostBody($stream);
                        }
                        else if($httpMethod == HttpVerb::PUT)
                        {
                            $httpRequest->setPutBody($stream);
                        }
                    }

                    $this->_context->OnBeforeRequestInternal($httpRequest);
                    //HttpRequest::GetResponse can throw InvalidOperation exception
                    //if the curl_exec fails (ex: could not connect to host)
                    //the below catch blcok will catch that exception and
                    //re-throw it as ODataServiceExcpetion.
                    $httpRawResponse = $httpRequest->GetResponse();
                    $httpResponse = HttpResponse::fromString($httpRawResponse);
                    $this->_context->OnAfterResponseInternal($httpResponse);

                    if($httpResponse->isError())
                    {
                        $headers = $httpResponse->getHeaders();
                        $code = $httpResponse->getCode();
                        $httpException = $this->getHttpException($httpResponse);
                        throw new InvalidOperation($httpException);
                    }

                    $httpCode = $httpResponse->getCode();
                    $this->UpdateChangeOrderIDToHttpStatus($httpCode);
                    $this->_operationResponses[] = new OperationResponse($httpResponse->getHeaders(),
                                                                         '',
                                                                         $httpCode);
                    $this->HandleOperationResponse($httpResponse);
                }
                else
                {
                    $this->_completed = true;
                    $this->EndNonBatchRequest();
                }

            }catch(InvalidOperation $exception)
            {
                $this->EndNonBatchRequest();
                throw new ODataServiceException($exception->getError() .
                                                $exception->getDetailedError(),
                                                '',
                                                $headers,
                                                $code);
            }
        }while(!$this->_completed);

        return new DataServiceResponse(array(), '', $this->_operationResponses, false);
    }

    /**
     * To create an HttpRequest object with required headers set for currenlty
     * selected (identified by $this->_entryIndex)entry (Resource or Binding).
     * The headers will be set based on the type of operation to be performed
     * on the selected entry (POST, PUT, MERGE or DELETE).
     * Based on the scenario [type of entry (Resource or Binding) and status of
     * associated stream (if exists, for Resource only)], this function will
     * call appropriate functions to handle specfic senario header generation.
     *
     * @param boolean $replaceOnUpdateOption
     * @return HttpRequest
     */
    protected function CreateRequestHeaderForSingleChange($replaceOnUpdateOption)
    {
        if(!$this->_processingMediaLinkEntry)
        {
            $this->_entryIndex++;
        }
        else
        {
            $resourceBox = $this->_changedEntries[$this->_entryIndex];
            if($this->_processingMediaLinkEntryPut &&
               $resourceBox->State == EntityStates::Unchanged)
            {
                $this->_entryIndex++;
            }

            $this->_processingMediaLinkEntry = false;
            $this->_processingMediaLinkEntryPut = false;
            $resourceBox->SaveStream = null;
        }

        if($this->_entryIndex >= count($this->_changedEntries))
        {
            return null;
        }

        $descriptor = $this->_changedEntries[$this->_entryIndex];
        if(!$descriptor->IsResource())
        {
            return $this->CreateRequestHeaderForBinding($descriptor);
        }

        if(($descriptor->State == EntityStates::Unchanged ||
            $descriptor->State == EntityStates::Modified) &&
            (($request = $this->CheckAndProcessMediaEntryPut($descriptor)) != null))
        {
                $this->_processingMediaLinkEntry = true;
                $this->_processingMediaLinkEntryPut = true;
                return $request;
        }

        if(($descriptor->State == EntityStates::Added) &&
            (($request = $this->CheckAndProcessMediaEntryPost($descriptor)) != null))
        {
                $this->_processingMediaLinkEntry = true;
                $this->_processingMediaLinkEntryPut = false;
                return $request;
        }

        return $this->CreateRequestHeaderForResource($descriptor, $replaceOnUpdateOption);
    }

    /**
     * To create a ContentStream object which holds a stream that will become
     * the body of the HttpRequest for currenlty selected entry (Resource or Binding).
     * Based on the scenario [type of entry (Resource or Binding) and status of
     * associated stream (if exists, for Resource only)], this function prepare
     * and returns approperiate stream.
     *
     * @param int $index
     * @return ContentStream
     */
    protected function CreateRequestBodyForSingleChange($index)
    {
        $descriptor = $this->_changedEntries[$index];
        if($descriptor->IsResource())
        {
            if ($this->_processingMediaLinkEntry)
            {
                return new ContentStream($this->_mediaResourceRequestStream, false);
            }
            return new ContentStream($this->CreateRequestBodyForResource($descriptor), true);
        }

        if ((EntityStates::Added != $descriptor->State) &&
             ((EntityStates::Modified != $descriptor->State) ||
              ($descriptor->GetTargetResource() == null)))
        {
            return null;
        }

        return new ContentStream($this->CreateRequestBodyForBinding($descriptor), true);
    }

     /**
      * To create a HttpRequest object with required headers set for entity in
      * the $resourceBox. This is used in non-batch mode.
      *
      * @param ResourceBox $resourceBox
      * @param boolean $replaceOnUpdateOption
      * @return HttpRequest
      */
    protected function CreateRequestHeaderForResource($resourceBox, $replaceOnUpdateOption)
    {

        $entityHttpMethod = $this->GetEntityHttpMethod($resourceBox->State, $replaceOnUpdateOption);
        $resourceUri = $resourceBox->GetResourceUri($this->_context->GetBaseUriWithSlash());
        //Hack: If we are using Windows Auth, with PUT operation, then Curl will throw the error
        //"necessary data rewind wasn't possible in ..", so fix is use POST Tunneling for this
        //request and after creating the request reset it to default value.
        $usePostTuneling = $this->_context->UsePostTunneling;

        if((isset($this->_context->Credential)) &&
           ($this->_context->Credential->getCredentialType() == CredentialType::WINDOWS) &&
           ($entityHttpMethod == HttpVerb::PUT))
        {
            $this->_context->UsePostTunneling = true;
        }

        $request = $this->_context->CreateRequest($resourceUri,
                                               $entityHttpMethod,
                                               false,
                                               "application/atom+xml",
                                               Resource::DataServiceVersion_1);

        //If user specified any header specific to this resource using SetEntityHeader
        //then set those headers
        if(count($resourceBox->Headers) != 0)
        {
           $request->ApplyHeaders($resourceBox->Headers);
        }

        //In the case of PUT, MERGE  or DELETE operations If the entity has
        //etag associated with it then If-Match header is required
        if($entityHttpMethod != HttpVerb::POST && isset($resourceBox->EntityETag))
        {
            $headers = array('If-Match' => $resourceBox->EntityETag);
            $request->ApplyHeaders($headers);
        }

        $this->_context->UsePostTunneling  = $usePostTuneling;
        return $request;
    }


    /**
     * To create a HttpRequest object with required headers set for the
     * relationship in the $binding. This is used in non-batch mode.
     *
     * @param RelatedEnd $binding
     * @return HttpRequest
     */
    protected function CreateRequestHeaderForBinding($binding)
    {
        $sourceResourceBox = null;
        $this->_context->ObjectToResource->TryGetValue($binding->GetSourceResource(),
                                                       $sourceResourceBox);
        $targetResourceBox = null;
        if($binding->GetTargetResource() != null)
        {
            $this->_context->ObjectToResource->TryGetValue($binding->GetTargetResource(),
                                                           $targetResourceBox);
        }

        if($sourceResourceBox->Identity == null)
        {
            throw new InvalidOperation(Resource::LinkResourceInsertFailure);
        }

        if(($targetResourceBox != null) && ($targetResourceBox->Identity == null))
        {
            throw new InvalidOperation(Resource::LinkResourceInsertFailure);
        }

        return $this->_context->CreateRequest($this->CreateRequestUri($sourceResourceBox, $binding),
                                               $this->GetBindingHttpMethod($binding),
                                               false,
                                               "application/xml",
                                               Resource::DataServiceVersion_1);
    }

     /**
      * To create the changeset body for an entity in ATOMPub. This is used
      * in non-batch mode.
      *
      * @param ResourceBox $resourceBox
      * @return string
      */
    protected function CreateRequestBodyForResource($resourceBox)
    {
        return $this->CreateChangeSetBodyForResource($resourceBox, false);
    }

     /**
      * To create the changeset body for a relationship in ATOMPub. This is used
      * in non-batch mode.
      *
      * @param RelatedEnd $binding
      * @return string
      */
    protected function CreateRequestBodyForBinding($binding)
    {
        return $this->CreateChangesetBodyForBinding($binding, true);
    }

    /**
     * to check whether the resource represented  by the $resourceBox has any
     * BLOB associated with it, which is to be saved using HTTP PUT.
     * HTTP PUT will be used to save a BLOB when the assoicated MLE (resource)
     * is already exists in the OData service, which means state of the resource
     *  will be unchanged or modified.
     *
     * @param ResourceBox $resourceBox
     * @Return HttpRequest
     * @throws InvalidOperation
     */
    protected function CheckAndProcessMediaEntryPut($resourceBox)
    {
        if($resourceBox->SaveStream == null)
        {
            return null;
        }

        $editMediaResourceUri = $resourceBox->GetEditMediaResourceUri($this->_context->GetBaseUriWithSlash());
        if($editMediaResourceUri == null)
        {
            throw new InvalidOperation(Resource::SetSaveStreamWithoutEditMediaLink);
        }

        //Hack: If we are using Windows Auth, with PUT operation, then Curl will throw the error
        //"necessary data rewind wasn't possible in ..", so fix is use POST Tunneling for this
        //request and after creating the request reset it to default value.
        $usePostTuneling = $this->_context->UsePostTunneling;
        $this->_context->UsePostTunneling = true;
        $mediaResourceRequest = $this->CreateMediaResourceRequest($editMediaResourceUri,
                                                                  HttpVerb::PUT);
        $this->_context->UsePostTunneling = $usePostTuneling;
        $this->SetupMediaResourceRequest($mediaResourceRequest, $resourceBox);
        //TODO: Add E-Tag
        return $mediaResourceRequest;
    }

    /**
     * To check whether the resource represented  by the $resourceBox has any
     * BLOB associated with it, which is to be saved using HTTP POST.
     * HTTP POST will be used to save a BLOB when the assoicated MLE (resource)
     * is not exists in the OData service and which is just added in the context
     * by the client application, which means state of the MLE (resource) will
     * be added.
     *
     * @param ResourceBox $resourceBox
     * @Return HttpRequest
     */
    protected function CheckAndProcessMediaEntryPost($resourceBox)
    {
        if (!$resourceBox->MediaLinkEntry)
        {
            return null;
        }

        if($resourceBox->SaveStream == null)
        {
            throw new InvalidOperation(Resource::MLEWithoutSaveStream);
        }

        $mediaResourceRequest = $this->CreateMediaResourceRequest($resourceBox->GetResourceUri($this->_context->GetBaseUriWithSlash()),
                                                                  HttpVerb::POST);
        $this->SetupMediaResourceRequest($mediaResourceRequest, $resourceBox);
        $resourceBox->State = EntityStates::Modified;
        return $mediaResourceRequest;
    }

    /**
     * To create a HttpRequest with required headers set for a media resource
     * (BLOB) request (POST or PUT).
     *
     * @param Uri $requestUri
     * @param HttpVerb $method
     * @Return HttpRequest
     */
    protected function CreateMediaResourceRequest($requestUri, $method)
    {
        $mediaResourceRequest = $this->_context->CreateRequest($requestUri,
                                                                $method,
                                                                false,
                                                                '*/*',
                                                                Resource::DataServiceVersion_1);
        $mediaResourceRequest->ApplyHeaders(array('Content-Type' => '*/*'));
        return $mediaResourceRequest;
    }

    /**
     * To set the _mediaResourceRequestStream member variable with the stream
     * specified by user through SetSaveStream. Also sets header of HttpRequest
     * (for media resource POST or PUT) with one passed by user through SetSaveStream.
     *
     * @param HttpRequest $mediaResourceRequest
     * @param ResourceBox $resourcBox
     */
    protected function SetupMediaResourceRequest(&$mediaResourceRequest,
                                                 $resourcBox)
    {
        $this->_mediaResourceRequestStream = $resourcBox->SaveStream->getStream();
        $mediaResourceRequest->ApplyHeaders($resourcBox->SaveStream->getArgs()->getHeaders());
    }

     /**
      * To create the Uri to be used for a binding operation (AddLink, SetLink
      * or DeleteLink). For example if both Customer (with id 'ALKFI')and
      * Order (1234)exists in OData service and in context, then Uri will be:
      * _http://dataservice/Customers('ALKFI')/$links/Orders(1234)
      * if only Customer exists in the data service and context and Order is just
      * added in the context then Uri will be:
      * _http://dataservice/Customers('ALKFI')/$links/Orders
      *
      * @param ResourceBox $sourceResourceBox
      * @param RelatedEnd $binding
      * @return Uri
      */
    protected function CreateRequestUri($sourceResourceBox, $binding)
    {
        return Utility::CreateUri($sourceResourceBox->GetResourceUri($this->_context->GetBaseUriWithSlash()),
                              $this->CreateRequestRelativeUri($binding));
    }

    /**
     * To update the _changeOrderIDToHttpStatus array, which hold the status of
     * each change request in non-batch mode. This array will be used to update
     * the state of all resources in the context once all changes are done.
     * Note that we are skipping the case of BLOB PUT, this because BLOB put
     * will happen in two cases:
     * 1. MLE (resource) associated with the BLOB is in unchanged state
     *      (In this case resource dont have change order id).
     * 2. MLE (resource) associated with the BLOB is in modified state
     *      (In this case there will be another request generated for the
     *       MLE so skip the case of BLOB).
     *
     * @param HttpVerb $httpCode
     */
    protected function UpdateChangeOrderIDToHttpStatus($httpCode)
    {
        if(!($this->_processingMediaLinkEntry &&
             $this->_processingMediaLinkEntryPut))
        {
            $resourceBox = $this->_changedEntries[$this->_entryIndex];
            $changeOrder = $resourceBox->ChangeOrder;
            if( array_key_exists($changeOrder, $this->_changeOrderIDToHttpStatus))
            {
                unset($this->_changeOrderIDToHttpStatus[$changeOrder]);
            }

            $this->_changeOrderIDToHttpStatus[$changeOrder] = $httpCode;
        }
    }

    /**
     * To handle the HttpResponse object of a HttpRequest POST request in
     * non-batching mode. This function will invoke ObjectContext::AttachLocation
     * which uses the value of HttpHeader with key 'Location' to set the Identity
     * and EditLink of current entry (ResourceBox) under process. Also populate the
     * entity instance with OData service returned values.
     *
     * @param HttpResponse $httpResponse
     */
    protected function HandleOperationResponse($httpResponse)
    {
         $resourceBox = $this->_changedEntries[$this->_entryIndex];
         if ($resourceBox->IsResource())
         {
            $headers = $httpResponse->getHeaders();
            //Handle the POST Operation Response.
            //SDK will fire POST operation in three cases
            //1. AddLink [we will skip this case]
            //2. AddObject [$resourceBox->State == EntityStates::Added]
            //3. SetSaveStream on an entity which is just added using AddObject
            //   In this case the CheckAndProcessMediaEntryPost will set the state
            //   of the object to Modified.
            //   [($resourceBox->State == EntityStates::Modified) &&
            //      $this->_processingMediaLinkEntry) &&
            //     !$this->_processingMediaLinkEntryPut)]
            if (($resourceBox->State == EntityStates::Added) ||
                ((($resourceBox->State == EntityStates::Modified) &&
                  $this->_processingMediaLinkEntry) &&
                 !$this->_processingMediaLinkEntryPut))
            {
                $resourceBox->EntityETag = AtomParser::GetEntityEtag($httpResponse->getBody());;
                $location = isset($headers[HttpRequestHeader::Location])?
                                  $headers[HttpRequestHeader::Location] :
                                  null;
                if ($httpResponse->isSuccessful())
                {
                    if ($location == null)
                    {
                        throw new ODataServiceException(Resource::NoLocationHeader,
                                                        '',
                                                        array(),
                                                        null);
                    }
                    $this->_context->AttachLocation($resourceBox->getResource(),
                                                    $location);

                    if($resourceBox->State == EntityStates::Added)
                    {
                        $atomEntry = null;
                        AtomParser::PopulateObject($httpResponse->getBody(),
                                                    $resourceBox->getResource(),
                                                    $uri, $atomEntry);
                    }
                    else
                    {
                        //After the POST operation for a media, state of corrosponding entity will be
                        //updated to Modified [earlier it will be Added] in CheckAndProcessMediaEntryPost
                        //function. So next will be a MERGE request, while generating body for this
                        //MERGE operation, the function CreateChangeSetBodyForResource will throw error
                        //if any of the Key field is null. So update the Key fields.
                        AtomParser::PopulateMediaEntryKeyFields($httpResponse->getBody(),
                                                    $resourceBox->getResource());

                    }
                }
            }

            if ($this->_processingMediaLinkEntry &&
                !$httpResponse->isSuccessful())
            {
                $this->_processingMediaLinkEntry = false;
                if ($this->_processingMediaLinkEntryPut)
                {
                    $resourceBox->State = EntityStates::Added;
                    $this->_processingMediaLinkEntryPut = false;
                }
            }
        }
    }

    /**
     * To update the State of all entity instances and relationships (ResourceBox and
     * RelatedEnd).
     *
     */
    protected function EndNonBatchRequest()
    {
        $this->UpdateEntriesState($this->_context->ObjectToResource->Values());
        $this->UpdateEntriesState($this->_context->Bindings->Values());
    }

    /**
     * To update the State of all entity instances (ResourceBox and Bindings) based
     * on the Http response status from the OData service for each entity, which
     * we have already stored in _changeOrderIDToHttpStatus array.
     *
     * @param Dictionary $entries
     */
    protected function UpdateEntriesState($entries)
    {
        foreach($entries as $entry)
        {
            if($entry->State != EntityStates::Unchanged)
            {
                $changeOrder = $entry->ChangeOrder;
                $httpCode = isset($this->_changeOrderIDToHttpStatus[$changeOrder]) ?
                                $this->_changeOrderIDToHttpStatus[$changeOrder] :
                                null;
                if(!empty($httpCode) && Utility::HttpSuccessCode($httpCode))
                {
                    if($entry->State == EntityStates::Deleted)
                    {
                        if($entry->IsResource())
                        {
                            if(null != $entry->Identity)
                            {
                                unset($this->_context->IdentityToResource[$entry->Identity]);
                            }
                            $this->_context->ObjectToResource->Remove($entry->GetResource());
                        }
                        else
                        {
                            $this->_context->Bindings->Remove($entry);
                        }
                    }

                    if($entry->State == EntityStates::Modified ||
                       $entry->State == EntityStates::Added)
                    {
                        $entry->State = EntityStates::Unchanged;
                    }
                }
            }
        }
    }

     /**
      * Retrive http exception from HttpResponse object.
      *
      * @param HttpResponse $httpResponse
      * @return string
      */
    protected function getHttpException($httpResponse)
    {
        $exception = '';
        $headers = $httpResponse->getHeaders();
        if(isset($headers['Content-type']))
        {
            if(strpos(strtolower($headers['Content-type']),
               strtolower(Resource::Content_Type_ATOM)) !== FALSE)
            {
                $exception = $httpResponse->getMessage();
            }
            else
            {
                $exception = $httpResponse->getBody();
            }
        }
        else
        {
            $exception = $httpResponse->getMessage();
        }

        return $exception;
    }

     /**
      * To pad the nchar properties with spaces at the right end if its size is
      * less than the maximum size defined for the property.
      *
      * @param ClientType $type
      * @param Object $object
      */
    protected function HandleNCharProperties($type, $object)
    {
        $properties = $type->getRawProperties();
        foreach($properties as $property)
        {
            $attributes = $property->getAttributes();

            if((isset($attributes['EdmType'])) &&
               (isset($attributes['FixedLength'])) &&
               ($attributes['EdmType'] == 'Edm.String') &&
               ($attributes['FixedLength'] == 'true'))
           {
               $refProperty = new ReflectionProperty($object, $property->getName());
               $propertyValue = $refProperty->getValue($object);
               if(is_null($propertyValue))
               {
                   continue;
               }

               $currentLength = strlen($propertyValue);
               $padLength = $attributes['MaxLength'] - $currentLength;

               if($padLength > 0)
               {
                   $propertyValue = str_pad($propertyValue, $currentLength + $padLength);
                   $refProperty->setValue($object, $propertyValue);
               }
           }
        }
    }
}
?>