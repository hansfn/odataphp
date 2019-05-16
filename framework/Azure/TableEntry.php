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
class TableEntry extends BaseObject
{
    protected $_baseURI;
    protected $_obectID;
    protected $_relLinks  = array();

    /**
     * @Type:EntityProperty
     */
    public $PartitionKey;

    /**
     * @Type:EntityProperty
     */
    public $RowKey;

     /**
     * @Type:EntityProperty
     */
    public $Timestamp;

    /**
     *
     * @param <Uri> $uri
     */
    public function __construct($uri="")
    {
        $this->_baseURI = $uri;
        $this->Timestamp = '1900-01-01T00:00:00';
        $this->_objectID = Guid::NewGuid();
    }

    /**
     *
     * @return <GUID>
     */
    public function getObjectID()
    {
        return $this->_objectID;
    }

    /**
     *
     * @param <array> $relLinks
     */
    public function setRelatedLinks($relLinks)
    {
        $this->_relLinks = $relLinks;
    }
}
?>