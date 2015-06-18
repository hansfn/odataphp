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
class Resource
{
    //definition of all erro messages thrown by context tracking and request generation logic
    const AddInvalidObject = 'Trying to Add Invalid Object';
    const AddInvalidObject_Details = 'Client is trying to add an object which is not an instance of an entity';
    const UpdateInvalidObject = 'Trying to Update Invalid Object';
    const UpdateInvalidObject_Details = 'Client is trying to update an object which is not an instance of an entity';
    const DeleteInvalidObject = 'Trying to Delete Invalid Object';
    const DeleteInvalidObject_Details = 'Client is trying to delete an object which is not an instance of an entity';
    const AddLinkInvalidObject = 'Trying to Add Link between Objects, where one or both objects are invalid';
    const AddLinkInvalidObject_Details = 'Client is trying to create link between objects, where one or both of the objects are not entity instances';
    const SetLinkInvalidObject = 'Trying to Set Link between Objects, where one or both objects are invalid';
    const SetLinkInvalidObject_Details = 'Client is trying to set link between objects, where one or both of the objects are not entity instances';
    const DeleteLinkInvalidObject = 'Trying to Delete Link between Objects, where one or both objects are invalid';
    const DeleteLinkInvalidObject_Details = 'Client is trying to delete link between objects, where one or both of the objects are not entity instances';
    const LoadPropertyInvalidObject = 'Trying to call LoadProperty in an invalid object';
    const LoadPropertyInvalidObject_Details = 'Client is trying to perform eager loading on an object, which is not an instance of entity';
    const SetEntityHeadersInvalidObject = 'Trying to set header for Invalid Object';
    const SetEntityHeadersInvalidObject_Details = 'Client is trying to set header for an object which is not an instance of an entity';
    const InvalidObject = 'Object is not valid';
    const EntityAlreadyContained = 'The context is already tracking the entity.';
    const EntityAlreadyContained_Details = 'Client is trying to add an entity which is already in added state';
    const EntityNotContained = 'The context is not currently tracking the entity.';
    const EntityNotContained_Details = 'Client is trying to perform an operation (UpdateObject, DeleteObject, AddLink, DeleteLink, SetLink SetEntityHeaders LoadProperty, GetReadStreamUri or GetReadStream) on an entity which requires entity to be tracked';
    const NoRelationWithDeleteEnd = 'One or both of the ends of the relationship is in the deleted state.';
    const NoRelationWithDeleteEnd_Details = 'Client is trying to perform an operation (AddLink or SetLink) where one or both of the entities participating in the relationship are in deleted state';
    const RelationAlreadyContained = 'The context is already tracking the relationship.';
    const RelationAlreadyContained_Details = 'Client is trying to create a link between two entites which already has an existing relationship in context';
    const NoRelationWithInsertEnd = 'One or both of the ends of the relationship is in the added state.';
    const NoRelationWithInsertEnd_Details = 'Client is trying to delete link between two entities where one or both of the entities are in added state or there is no existing relationship between the entities';
    const NoPropertyForTargetObject = 'Source Object does not contain a property which represents the target object';
    const NoPropertyForTargetObject_Details = 'Client is trying to perfrom an operation (AddLink, SetLink or DeleteLink) where the source object does not have a property "%s" representing target object';
    const NoRelationBetweenObjects = 'AddLink, SetLink or DeleteLink will work only if source object entity type has a relationship with target object entity type';
    const RelationNotRefOrCollection = "The sourceProperty is not a reference or collection of the target's object type.";
    const RelationNotRefOrCollection_Details = 'The AddLink, SetLink or DeleteLink operations can be performed only if the source object\'s property "%s" is a collection or reference type (Navigation property)';
    const SetLinkReferenceOnly = 'SetLink method works only when the source object\'s property "%s" is not a collection.';
    const SetLinkReferenceOnly_Details = 'SetLink requires relationship from source object to destination object of type \'many to zero or one\' or \'one to zero or one\'';
    const AddLinkCollectionOnly = 'AddLink and DeleteLink methods works only when the source object\'s property "%s" is a collection.';
    const AddLinkCollectionOnly_Details = 'AddLink or DeleteLink requires relationship from source object to destination object of type \'one to many\'';
    const CountNotPresent = 'Count value is not part of the response stream';
    const MissingEditMediaLinkInResponseBody = 'Error processing response stream. Missing href attribute in the edit-Media link element in the response';
    const ExpectedEmptyMediaLinkEntryContent = 'Error processing response stream. The ATOM content element is expected to be empty if it has a source attribute.';
    const ExpectedValidHttpResponse = 'DataServiceStreamResponse constructor requires valid HttpResponse object';
    const InvalidArgumentForGetStream = 'Second argument of GetStream API should be null, string or object of type DataServiceRequestArgs';
    const EntityNotMediaLinkEntry = 'GetReadStream API requires the specified entity to represent a Media Link Entry';
    const SetSaveStreamWithoutEditMediaLink = 'The binary property on the entity cannot be modified as a stream because the corresponding entry in the response does not have an edit-media link. Ensure that the entity has a binary property that is accessible as a stream in the data model.';
    const LinkResourceInsertFailure = 'One of the link\'s resources failed to insert.';
    const MLEWithoutSaveStream = 'Media Link Entry, but no save stream was set for the entity';
    const ArgumentNotNull = ' Argument cannot be null';
    const NoLocationHeader = 'The response to this POST request did not contain a \'location\' header. That is not supported by this client.';
    const InvalidSaveChangesOptions = 'The specified SaveChangesOptions is not valid, use SaveChangesOptions::Batch or SaveChangesOptions::None';
    const NullValueNotAllowedForKey = "The serialized resource has a null value in key member, Null values are not supported in key members - [Check you have set value of any key memeber to null by mistake or if you have used select query option, make sure this member variable is selected] Key Name: ";
    const NoLoadWithInsertEnd = 'The context cannot load the related collection or reference for objects in the added state';
    const NoLoadWithUnknownProperty = 'The context cannot load the related collection or reference to the unknown property - ';
    const AttachLocationFailedDescRetrieval = 'InternalError: AttachLocation Failed to retrieve the descriptor';
    const UnexpectdEntityState = 'Unexpected Entity State while trying to generate changeset body for resource';
    const InvalidEntityClassName = 'Failed to find entity class with name -';
    const InvalidExecuteArg = 'Execute API receives only uri or DataServiceQueryContinuation';
    const NoEmptyQueryOption = 'Error in DataService Query: Can\'t add empty Query option';
    const ReservedCharNotAllowed = 'Error in DataService Query: Can\'t add query option because it begins with reserved character \'$\' - ';
    const NoDuplicateOption = 'Error in DataService Query: Can\'t add duplicate query option - ';
    const NoCountAndInLineCount = 'Cannot add count option to the resource set because it would conflict with existing count options';
    const CollectionNotBelongsToQueryResponse = 'GetContinuation API: The collection is not belonging to the QueryOperationResponse';
    const FCTargetPathMissing = 'Invalid Proxy File failed to retrieve \'FC_TargetPath\' for the property - ';
    const FCKeepInContentMissing = 'Invalid Proxy File failed to retrieve \'FC_KeepInContent\' for the property - ';
    const FCContentKindMissing = 'Invalid Proxy File failed to retrieve \'FC_ContentKind\' for the property - ';
    const FCNsPrefixMissing = 'Invalid Proxy File failed to retrieve \'FC_NsPrefix\' for the property - ';
    const FCNsUriMissing = 'Invalid Proxy File failed to retrieve \'FC_NsUri\' for the property - ';
    const EntityHeaderCannotAppy = 'Entity header can be applied only if entity is in added or modified state';
    const EntityHeaderOnlyArray = 'Second argument to SetEntityHeader must be an array';
    const InvalidEntityState = 'Invalid entity state while generating HTTP method';
    const InvalidHttpVerbForSetPostBody = 'SetPostBody requires http method to be POST or MERGE';
    const InvalidBatchResponseNoCSBoundary = 'Batch Response is Invalid. Missing changeset boundary value';
    const ExpectedOpenBraceNotFound = 'Expected Open brace is not found in the url';
    const XMLWithoutFeedorEntry = 'XML without feed or entry node, which is not valid ATOMPub XML';
    //definition for data service specific headers
    const MaxDataServiceVersion = '2.0';
    const DefaultDataServiceVersion = '1.0';
    const DataServiceVersion_1 = '1.0';
    const DataServiceVersion_2 = '2.0';
    const USER_AGENT = 'OData Service';
    //definition of possible Accept and Content-Types headers
    const Accept_ATOM = 'application/atom+xml,application/xml';
    const Content_Type_ATOM = 'application/atom+xml,application/xml';
    const AZURE_API_VERSION = '2009-04-14';
    const DefaultSaveChangesOptions = SaveChangesOptions::Batch;
    //error message for version mismatch
    const VersionMisMatch = 'Response version mismatch. Client Library Expect version 2.0, but service returns response with version ';
}
?>