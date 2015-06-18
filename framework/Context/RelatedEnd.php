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
 * To represent class which holds the bindings in the context.
 */
class RelatedEnd extends Entity
{
    /**
     * To hold the source of object of binding
     */
    protected $_sourceResource;

    /**
     * To hold the source property in Source Object
     */
    protected $_sourceProperty;

    /**
     * To hold the target of object of binding
     */
    protected $_targetResource;

    /**
     * To get the source resource.
     *
     * @Returns Object
     */
    public function GetSourceResource()
    {
        return $this->_sourceResource;
    }

    /**
     * To get source property.
     *
     * @Returns string
     */
    public function GetSourceProperty()
    {
        return $this->_sourceProperty;
    }

    /**
     * To get target resource.
     *
     * @Returns Object
     */
    public function GetTargetResource()
    {
        return $this->_targetResource;
    }

    /**
     * Constructs a new RelatedEnd Object.
     *
     * @param Object $sourceResource
     * @param string $sourceProperty
     * @param Object $targetResource
     */
    public function RelatedEnd($sourceResource, $sourceProperty, $targetResource)
    {
        $this->_sourceResource = $sourceResource;
        $this->_sourceProperty = $sourceProperty;
        $this->_targetResource = $targetResource;
    }

    /**
     * @Returns FALSE always
     */
    public function IsResource()
    {
        return FALSE;
    }

    /**
     * To check the equality of two RelatedEnd instances.
     *
     * @param RelatedEnd $relatedEnd1
     * @param RelatedEnd $relatedEnd2
     * @Return bool if both ends are equal else false
     */
    public static function Equals($relatedEnd1, $relatedEnd2)
    {
        $targetObjectID1 = $targetObjectID2 = '00000000-0000-0000-0000-000000000000';
        if($relatedEnd1->GetTargetResource() !=null)
        {
            $targetObjectID1 = $relatedEnd1->GetTargetResource()->getObjectID();
        }

        if($relatedEnd2->GetTargetResource() !=null)
        {
            $targetObjectID2 = $relatedEnd2->GetTargetResource()->getObjectID();
        }

        if (($relatedEnd1->GetSourceResource()->getObjectID() == $relatedEnd2->GetSourceResource()->getObjectID()) &&
            ($targetObjectID1 == $targetObjectID2) &&
            ($relatedEnd1->GetSourceProperty() == $relatedEnd2->GetSourceProperty()))
        {
            return TRUE;
        }
        return FALSE;
    }

    /**
     * To get a unique id associated with this RelatedEnd.
     *
     * @Returns UniqueID
     */
    public function getObjectID()
    {
        $targetObjectID = '00000000-0000-0000-0000-000000000000';
        if($this->_targetResource != null)
        {
            $targetObjectID = $this->_targetResource->getObjectID();
        }

        return $this->_sourceResource->getObjectID() . "_" . $this->_sourceProperty . "_" . $targetObjectID;
    }
}
?>