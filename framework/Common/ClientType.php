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
class ClientType
{
    protected $_attributes;
    protected $_properties;
    protected $_navigationProperties;
    protected $_hasEPM;
    protected $_sortedEPMProperties;
    protected static $_cache = array();

    /**
     *
     * @param <string> $type
     * Constructor
     */
    public function ClientType($type)
    {
        $this->_attributes = array();
        $this->_properties =array();
        $this->_navigationProperties = array();
        $this->_sortedEPMProperties = array();
        $targetPathToCount = array();

        try
        {
            $rClass = new ReflectionClass($type);
            $this->_hasEPM = false;
            $this->_attributes = Utility::getAttributes($rClass);
            $sourceProperty = null;
            if(array_key_exists('FC_SourcePath', $this->_attributes))
            {
                $this->_hasEPM = true;
                $sourceProperty = $this->_attributes['FC_SourcePath'];
            }

            $properties = $rClass->getProperties();

            foreach ($properties as $property)
            {
                if($property->isPublic())
                {
                    $attributes = Utility::getAttributes($property);
                    $propertyName = $property->name;

                    if(($sourceProperty != null) &&
                       ($sourceProperty == $propertyName))
                    {
                        ValidateEPMAttributes($this->_attributes, $attributes, $sourceProperty, false);
                    }

                    if(isset($attributes['Type']) && $attributes['Type'] == 'EntityProperty')
                    {
                        $propertyObj = new Property($propertyName, $attributes);
                        $this->_properties[$propertyName] = $propertyObj;
                        if ($propertyObj->hasEPM($syn))
                        {
                            $this->_hasEPM = true;
                            $attrs = $propertyObj->getAttributes();
                            if($syn)
                            {
                                $targetPath = SyndicationItemProperty::GetSyndicationItemPathNoNS($attrs['FC_TargetPath']);
                            }
                            else
                            {
                                $targetPath = $attrs['FC_TargetPathNS'];
                                if(isset($attrs['NodeAttribute']))
                                {
                                    $targetPath .= '/@' . $attrs['NodeAttribute'];
                                }
                            }

                            $targetPathToCount[$targetPath] = substr_count($targetPath, "/");
                        }
                    }
                    else if(isset($attributes['Type']) && $attributes['Type'] == 'NavigationProperty')
                    {
                        $this->_navigationProperties[ $propertyName] = new Property($propertyName, $attributes);
                    }
                }
            }

            asort($targetPathToCount);
            $properties = $this->_properties;
            foreach($targetPathToCount as $key => $value)
            {
                foreach($properties as $property)
                {
                    $syn = false;
                    $targetPath = null;
                    if ($property->hasEPM($syn))
                    {
                        $attrs = $property->getAttributes();
                        if($syn)
                        {
                            $targetPath = SyndicationItemProperty::GetSyndicationItemPathNoNS($attrs['FC_TargetPath']);
                        }
                        else
                        {
                            $targetPath = $attrs['FC_TargetPathNS'];
                            if(isset($attrs['NodeAttribute']))
                            {
                                $targetPath .= '/@' . $attrs['NodeAttribute'];
                            }
                        }

                        if($key == $targetPath)
                        {
                                $this->_sortedEPMProperties[] = $property;
                        }
                     }
                 }
            }
        }
        catch(ReflectionException $exception)
        {
           throw new InvalidOperation('ReflectionException in ClientType constructor');
        }
    }

    /**
     *
     * @param <string> $type
     * @return <ClientType object>
     * Create and returns ClientType object for an entity $type.
     */
    public static function Create($type)
    {
        if(isset(self::$_cache[$type]))
        {
            return self::$_cache[$type];
        }

        self::$_cache[$type] = new ClientType($type);
        return self::$_cache[$type];
    }

    /**
     *
     * @return <array<string>>
     * Returns name of all entity properties of the entity represented by
     * this instance of ClientType. Note that this function returns all the
     * properties with @Type attribute equal to 'EntityProperty'
     */
    public function getProperties()
    {
        return array_keys($this->_properties);
    }

    /**
     *
     * @return <array<PropertyObject>>
     * Returns Entity Property Objects which holds information about
     * property of the entity represented by this instance of ClientType.
     * Note that this function returns all the property Objects with
     * @Type attribute equal to 'EntityProperty'
     */
    public function getRawProperties()
    {
        return array_values($this->_properties);
    }

    /**
     *
     * @return <array<PropertyObject>>
     * Returns EPM Property Objects which holds information about property
     * with EPM, of the entity represented by this instance of ClientType.
     * Note that this function returns all the property Objects with
     * @Type attribute equal to 'EntityProperty' and FC_TargetPath, FC_KeepInContent,
     * FC_ContentType, FC_NsPrefix, FC_NsUri attributes set
     */
    public function getRawEPMProperties()
    {
        $result = array();
        $rawProperties = $this->getRawProperties();
        foreach($rawProperties as $rawProperty)
        {
            if($rawProperty->hasEPM($synd))
            {
                $result[] = $rawProperty;
            }
        }
        return $result;
    }

    /**
     *
     * @return <array<PropertyObject>>
     * Returns EPM Property Objects [which are sorted by number of slashes in the
     * FC_TargetPath attribute], of the entity represented by this instance of ClientType.
     * Note that this function returns all the property Objects with
     * @Type attribute equal to 'EntityProperty' and FC_TargetPath, FC_KeepInContent,
     * FC_ContentType, FC_NsPrefix, FC_NsUri attributes set
     */
    public function getRawSortedEPMProperties()
    {
        return  $this->_sortedEPMProperties;
    }

    /**
     *
     * @param <boolean> $retrunKeepInContentProperties
     * @return <array<PropertyObject>>
     * Returns Non-EPM Property Objects. If $retrunKeepInContentProperties is true
     * the returned collection includes EPM property with FC_KeepInContent true.
     */
    public function getRawNonEPMProperties($retrunKeepInContentProperties = false)
    {
        $result = array();
        $rawProperties = $this->getRawProperties();
        foreach($rawProperties as $rawProperty)
        {
            if(!$rawProperty->hasEPM($synd))
            {
                $result[] = $rawProperty;
            }
            else
            {
               $attributes = $rawProperty->getAttributes();
               if($retrunKeepInContentProperties &&
                  isset($attributes['FC_KeepInContent']) &&
                  $attributes['FC_KeepInContent'] == 'true')
               {
                   $result[] = $rawProperty;
               }

            }
        }
        return $result;
    }

    /**
     *
     * @return <array<string>>
     * Returns name of all Navigation properties of the entity represented by
     * this instance of ClientType. Note that this function returns all the
     * properties with @Type attribute equal to 'NavigationProperty'
     */
    public function getNavigationProperties()
    {
        return array_keys($this->_navigationProperties);
    }

    /**
     *
     * @return <array<PropertyObject>>
     * Returns Property Objects which holds information about Navigation
     * property of the entity represented by this instance of ClientType.
     * Note that this function returns all the property Objects with
     * @Type attribute equal to 'NavigationProperty'
     */
    public function getRawNavigationProperties()
    {
        return array_values($this->_navigationProperties);
    }

    /**
     *
     * @return <array<string>>
     * Returns all entity and navigation properties
     */
    public function getAllProperties()
    {
        return array_merge(array_keys($this->_properties),
                           array_keys($this->_navigationProperties));
    }

    /**
     *
     * @return <array<string>>
     * Returns Key Properties of entity represented by this instance of ClientType.
     */
    public function geyKeyProperties()
    {
         if(isset($this->_attributes['key']))
         {
            if(!is_array($this->_attributes['key']))
            {
                return array($this->_attributes['key']);
            }
            return $this->_attributes['key'];
         }
         return array();
    }

    /**
     *
     * @param  <string> $propertyName
     * @return <array<name => value>>
     * Returns attribute of a entity property.
     */
    public function getPrpopertyAttributes($propertyName)
    {
        if(array_key_exists($propertyName, $this->_properties))
        {
            return $this->_properties[$propertyName]->getAttributes();
        }
        throw new InvalidOperation("ClientType::getPrpopertyAttributes PropertyName not exists");
    }

    /**
     *
     * @param <string> $propertyName
     * @return <array<name => value>>
     * Returns attribute of a navigation property.
     */
    public function getNavigationPropertyAttributes($propertyName)
    {
        if(array_key_exists($propertyName, $this->_navigationProperties))
        {
            return $this->_navigationProperties[$propertyName]->getAttributes();
        }
        throw new InvalidOperation("ClientType::getPrpopertyAttributes PropertyName not exists");
    }

    /**
     *
     * @return <array<name => value>>
     * Returns attributes of the entity represented by this instance of
     * ClientType
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }

    /**
     *
     * @return <boolean>
     * Return true if the the entity represented by this instance of ClientType
     * has any EPM property else false.
     */
    public function hasEPM()
    {
        return $this->_hasEPM;
    }
}

/**
 * Property Class.
 */
class Property
{
    protected $_attributes;
    protected $_name;
    protected $_hasEPM;
    protected $_isSyndication;

    /**
     *
     * @param <string> $name
     * @param <array<key => value>> $attributes
     * Constructor
     */
    public function Property($name, $attributes)
    {
        $this->_isSyndication = $this->_hasEPM = false;
        $this->_name = $name;
        $this->_attributes = $attributes;

        if(array_key_exists('FC_TargetPath', $this->_attributes))
        {
            ValidateEPMAttributes($this->_attributes,
                                  $this->_attributes,
                                  $this->_name,
                                  true);
            $this->_hasEPM = true;
            $this->_isSyndication = SyndicationItemProperty::IsSyndicationItem($this->_attributes['FC_TargetPath']);
            if(!$this->_isSyndication)
            {
                $attribute = null;
                $segments = explode("/", $this->_attributes['FC_TargetPath']);
                $segment_count = count($segments);
                if($segments[$segment_count - 1][0] == '@')
                {
                    $attribute = ltrim($segments[$segment_count - 1], '@');
                    $this->_attributes['NodeAttribute'] = $attribute;
                    unset($segments[$segment_count - 1]);
                    $segment_count--;
                }

                $FC_TargetPathWithNS = null;
                $nsPrefix = $this->_attributes['FC_NsPrefix'];
                $FC_TargetPathWithNS = $nsPrefix . ":" . $segments[0];
                for($i=1; $i<$segment_count; $i++)
                {
                    $FC_TargetPathWithNS = $FC_TargetPathWithNS .
                                           "/" .
                                           $nsPrefix .
                                           ":" .
                                           $segments[$i];
                }
                $this->_attributes['FC_TargetPathNS'] = $FC_TargetPathWithNS;
            }
         }
    }

    /**
     *
     * @return <string>
     * Returns Property Name
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     *
     * @return <array<key => value>>
     * Returns attributes of property
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }

    /**
     *
     * @param <boolean> [out] $isSyndication
     * @return <boolean>
     * Returns true if the Property has EPM, also [out] argument will indicate
     * the EPM type Syndication or Custom.
     */
    public function hasEPM(&$isSyndication)
    {
        $isSyndication = $this->_isSyndication;
        return $this->_hasEPM;
    }
}

/**
 * class XMLBuilder
 *
 */
class XMLBuilder
{
     /**
      *
      * @param <DOMDocument> $dom
      * @param <string> $path
      * @param <string> $val
      * @param <string> $nsPrefix
      * @param <Uri> $nsUrl
      * @param <boolean> $considerNS
      *
      * This function will build xml from path for example:
      * $path = 'Node1/Node2'
      * BuildDOMFromPath($dom, $path, 'Hello', 'm', 'http://host/mns', true);
      * $path = 'Node1/Node2/Node3/@attr
      * BuildDOMFromPath($dom, $path, 'world', 'm', 'http://host/mns', true);
      *
      * Will build an XML as follows ($dom->SaveXML())
      * <m:Node1>
      *     <m:Node2>
      *         <m:Node3 m:attr="world"/>
      *     Hello
      *     </m:Node2>
      * <m:Node1>
      */
     public static function BuildDOMFromPath($dom, $path, $val, $nsPrefix, $nsUrl, $considerNS)
     {
        $data2 = self::ValidatePathThrowErrorIfInvalid($path, $query, $nsPrefix, $considerNS, $attribute);

        $count = count($data2);
        $keys = array_keys($data2);
        $lastKey = $keys[$count-1];
        $xPath = new DOMXPath($dom);
        if($considerNS)
        {
            $xPath->registerNamespace($nsPrefix, $nsUrl);
        }
        $nodes = $xPath->query($lastKey);

        if($nodes->length == 0)
        {
            $lastKey1 = ltrim($lastKey, "/");
            $domNode;
            if($lastKey == $query && $attribute == null)
            {
                if($considerNS)
                {
                    $domNode = new DOMElement($lastKey1, $val, $nsUrl);
                }
                else
                {
                    $domNode = new DOMElement($lastKey1, $val);
                }

                $domNode = $dom->appendChild($domNode);
            }
            else
            {
                if($considerNS)
                {
                   $domNode = new DOMElement($lastKey1, '', $nsUrl);
                }
                else
                {
                   $domNode = new DOMElement($lastKey1, '');
                }

                $domNode = $dom->appendChild($domNode);
            }
        }

        $flag = false;
        foreach($data2 as $key => $value)
        {
            if($flag)
            {
                break;
            }

            $nodes = $xPath->query($key);
            if($nodes->length == 0)
            {
                continue;
            }
            else
            {
                $newNode = $nodes->item(0);
                $i = 0;
                $count = count($value);
                foreach($value as $key1 => $value1)
                {
                    $i++;
                    $domNode;

                    if($i == $count && $attribute == null)
                    {
                        if($considerNS)
                        {
                            $domNode = new DOMElement($value1, $val, $nsUrl);
                        }
                        else
                        {
                           $domNode = new DOMElement($value1, $val);
                        }

                        $newNode = $newNode->appendChild($domNode);
                    }
                    else
                    {
                        if($considerNS)
                        {
                            $domNode = new DOMElement($value1, '', $nsUrl);
                        }
                        else
                        {
                           $domNode = new DOMElement($value1, '');
                        }

                        $newNode = $newNode->appendChild($domNode);
                    }
                }

                $flag = true;
            }
        }

        if($attribute != null)
        {
            $xPath = new DOMXPath($dom);
            if($considerNS)
            {
                $xPath->registerNamespace($nsPrefix, $nsUrl);
            }

            $nodes = $xPath->query($query);
            $nodes->item(0)->setAttribute($attribute, $val);
        }
    }

    /**
     *
     * @param <string> $path
     * @param <string> $key
     * @param <string> $nsPrefix
     * @param <boolean> $considerNS
     * @param <string> [out] $attribute
     * This function build an associate array from path and set $attribute to
     * the attribute if path contains @ in the last segment.
     *
     * $path = 'A/B/C/D/@attr' This path will return the array as follows:
     * array('A/B/C/D' => array(),
     *       'A/B/C' => array(D),
     *       'A/B' => array(B,C),
     *       'A' => array(B,C,D))
     *  and $attribute = attr
     */
     public static function ValidatePathThrowErrorIfInvalid($path, &$key, $nsPrefix, $considerNS, &$attribute)
     {
        if(empty($path))
        {
            throw new InternalError('Path cannot be empty');
        }

        $attribute = null;
        $segments = explode("/", $path);
        $segment_count = count($segments);
        if($segments[$segment_count - 1][0] == '@')
        {
            $attribute;
            if($considerNS)
            {
                $attribute = $nsPrefix . ":" . ltrim($segments[$segment_count - 1], '@');
            }
            else
            {
                $attribute = ltrim($segments[$segment_count - 1], '@');
            }
            unset($segments[$segment_count - 1]);
            $segment_count--;
        }

        $result = array();
        $key = null;
        for($i = 0; $i < $segment_count; $i++)
        {
            if($considerNS)
            {
                $key = $key . "/" . $nsPrefix . ":". $segments[$i];
            }
            else
            {
                 $key = $key . "/" . $segments[$i];
            }

            $result[$key] = array();
            for($j = $i+1; $j < $segment_count; $j++)
            {
                $val;
                if($considerNS)
                {
                    $val = $nsPrefix . ":" . $segments[$j];
                }
                else
                {
                    $val = $segments[$j];
                }

                $result[$key][] = $val;
            }
        }

        return array_reverse($result);
    }
}

/**
 * class SyndicationTextContentKind
 */
class SyndicationTextContentKind
{
    const Plaintext = 'text';
    const Html = 'html';
    const Xhtml = 'xhtml';
}

/**
 * class SyndicationItemProperty
 */
class SyndicationItemProperty
{
     protected static $_syndicationItemToPath = array('SyndicationAuthorEmail' => array('default:author/default:email', 'author/email'),
                                                      'SyndicationAuthorName' => array('default:author/default:name', 'author/name'),
                                                      'SyndicationAuthorUri' => array('default:author/default:uri', 'author/uri'),
                                                      'SyndicationContributorEmail' => array('default:contributor/default:email', 'contributor/email'),
                                                      'SyndicationContributorName' => array('default:contributor/default:name', 'contributor/name'),
                                                      'SyndicationContributorUri' => array('default:contributor/default:uri', 'contributor/uri'),
                                                      'SyndicationPublished' => array('default:published', 'published'),
                                                      'SyndicationRights' => array('default:rights', 'rights'),
                                                      'SyndicationSummary' => array('default:summary', 'summary'),
                                                      'SyndicationTitle' => array('default:title', 'title'),
                                                      'SyndicationUpdated' => array('default:updated', 'updated'));

     /**
      *
      * @param <string> $item
      * @return <boolean>
      * Returns true if $item is a SyndicationType else false.
      */
     public static function IsSyndicationItem($item)
     {
          return isset(self::$_syndicationItemToPath[$item]);
     }

     /**
      *
      * @param <String> $item
      * @return <string>
      * Returns the XPath with namespace that can be used to query the SyndicationItem $item
      */
     public static function GetSyndicationItemPathwithNS($item)
     {
         if(self::IsSyndicationItem($item))
         {
             return self::$_syndicationItemToPath[$item][0];
         }

         return null;
     }

     /**
      *
      * @param <String> $item
      * @return <string>
      * Returns the XPath without namespace that can be used to query the SyndicationItem $item
      */
     public static function GetSyndicationItemPathNoNS($item)
     {
         if(self::IsSyndicationItem($item))
         {
             return self::$_syndicationItemToPath[$item][1];
         }

         return null;
     }
}

/**
 *
 * @param <array<key => value>> $srcAttributes
 * @param <array<key => value>> $tarAttributes
 * @param <string> $propertyName
 * @param <boolean> $validateOnly
 *
 * This function will validate the EPM attributes in the $srcAttributes collection
 * of the property $propertyName. If the $validateOnly is false then this function
 * will copy the EPM attributes from $srcAttributes to tarAttributes
 */
function ValidateEPMAttributes($srcAttributes, &$tarAttributes, $propertyName, $validateOnly)
{
      if(!isset($srcAttributes['FC_TargetPath']))
      {
            throw new InvalidOperation(Resource::FCTargetPathMissing . $propertyName);
      }


      if(!isset($srcAttributes['FC_KeepInContent']))
      {
            throw new InvalidOperation(Resource::FCKeepInContentMissing . $propertyName);
      }

      if(!$validateOnly)
      {
        $tarAttributes['FC_TargetPath'] = $srcAttributes['FC_TargetPath'];
        $tarAttributes['FC_KeepInContent'] = $srcAttributes['FC_KeepInContent'];
      }

      if(SyndicationItemProperty::IsSyndicationItem($srcAttributes['FC_TargetPath']))
      {
            if(!isset($srcAttributes['FC_ContentKind']))
            {
                throw new InvalidOperation(Resource::FCContentKindMissing . $propertyName);
            }

            if(!$validateOnly)
            {
                $tarAttributes['FC_ContentKind'] = $srcAttributes['FC_ContentKind'];
            }
      }
      else
      {
            if(!isset($srcAttributes['FC_NsPrefix']))
            {
                throw new InvalidOperation(Resource::FCNsPrefixMissing . $propertyName);
            }


            if(!isset($srcAttributes['FC_NsUri']))
            {
                throw new InvalidOperation(Resource::FCNsUriMissing . $propertyName);
            }

            if(!$validateOnly)
            {
                $tarAttributes['FC_NsPrefix'] = $srcAttributes['FC_NsPrefix'];
                $tarAttributes['FC_NsUri'] = $srcAttributes['FC_NsUri'];
            }
     }
}
?>