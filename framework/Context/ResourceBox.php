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
 * To represent class whose instance holds entity instance and its related
 * information in the context.
 */
class ResourceBox extends Entity
{

   /**
    * This variable will hold the uri to the resource if resource is existing
    * ex: http://server/service.svc/Customer(CustomerID='CHA25')
    * else null (just added)
    */
    public $EditLink;

   /**
    * This variable will hold the relative uri of the resource
    * If the resource is just added in the context then this variable
    * contains EntityName
    * ex: Customers
    * If the resource is already existing then this variable holds the unique id
    * in the data service
    * ex: Customers(CustomerID='CHA12')
    */
    public $Identity;

   /**
    * Number of related links. ie number of elements in the Bindings dictionary
    * in which resource participate as SourceObject
    */
    public $RelatedLinkCount;

    /**
     * The entity instance
     */
    protected $_resource;

    /**
     *
     * @var Uri The uri taken from href attribute of edit-media link node
     * Populated from AtomEntry::EditMediaLink
     */
    public $EditMediaLink;

    /**
     *
     * @var bool True if associated resource is a Media Link Entry
     * Populated from AtomEntry::MediaLinkEntry
     */
    public $MediaLinkEntry;

    /**
     *
     * @var string The etag value taken from m:etag attribute of edit-media link node
     * Populated from AtomEntry::StreamETag
     */
    public $StreamETag;

    /**
     *
     * @var string The etag value taken from m:etag attribute of entry node
     * Populated from AtomEntry::EntityTag
     */
    public $EntityTag;

    /**
     *
     * @var Uri The uri taken from href attribute of Content node
     * Populated from AtomEntry::MediaContentUri
     */
    public $StreamLink;

    /**
     *
     * @var DataServiceSaveStream This object holds stream and
     * headers to be send for PUTing or POSTing of BLOBs
     */
    public $SaveStream;

    /**
     *
     * @var array To hold http header specific for an entity
     * ex: Slug
     */
    public $Headers;

    /**
     * @Returns Object
     * Get method for the entity instance
     */
    public function getResource()
    {
        return $this->_resource;
    }

    /**
     * To construct a ResourceBox instance
     *@param Uri $identity
     *@param string $editLink
     *@param Object $resource
     */
    public function ResourceBox($identity, $editLink, $resource)
    {
        $this->Identity = $identity;
        $this->EditLink = $editLink;
        $this->_resource = $resource;
        $this->EditMediaLink = null;
        $this->MediaLinkEntry = false;
        $this->StreamETag = null;
        $this->StreamLink = null;
        $this->SaveStream = null;
        $this->Headers = array();
    }

    /**
     * @Returns TRUE always.
     */
    public function IsResource()
    {
        return TRUE;
    }

    /**
     * Construct and return uri points to the actual entity instance in the
     * OData service. If resource is just added to context this this will point
     * to the entity.
     *
     *@param Uri $baseUriWithSlash
     *@Returns Uri
     */
    public function GetResourceUri($baseUriWithSlash)
    {
        return $baseUriWithSlash . $this->EditLink;
    }

    /**
     *To check current resource is participating in the binding represented by
     *$related
     *
     *@param RelatedEnd $related
     *@Return bool
     */
    public function IsRelatedEntity($related)
    {
        if ($this->_resource->getObjectID() == $related->GetSourceResource()->getObjectID())
        {
            return TRUE;
        }

        return FALSE;
    }

    /**
     * To get the media resource uri if exists.
     *
     * @param Uri $baseUriWithSlash The service uri
     * @return Uri The media resource uri if it exists
     */
    public function GetMediaResourceUri($baseUriWithSlash)
    {
        if($this->StreamLink != null)
        {
            //Seems the StreamLink value ie value of href attribute of Content
            //node is absolute, if its relative we should append the
            //baseurl (after removing .svc part) with StreamLink
            return $this->StreamLink;
        }

        return null;
    }

    /**
     * To get the Edit-Media Resource Uri.
     *
     * @param Uri $baseUriWithSlash
     * @return Uri
     */
    public function GetEditMediaResourceUri($baseUriWithSlash)
    {
        if($this->EditMediaLink != null)
        {
            return Utility::CreateUri($baseUriWithSlash, $this->EditMediaLink);
        }

        return null;
    }
}
?>