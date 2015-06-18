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
 * This class is used to hold object-Value pair. This Dictionary
 * requires object to be derived from Object class. Which cause
 * object to have unique ObjectID.
 */
class Dictionary
{
    /**
     * Array to hold the dictionary entries as Key-Value Pair
     *
     * Dictionary::Key => Guid
     * Dictionary::Value => Entry(user_key, user_value)
     */
    protected $_entries = array();

    /**
     * To add an entry to dictionary.
     *
     * @param <object> $key The key object, Must be derived from Object
     * @param <object> $value The value object
     * @throws Exception
     */
    public function Add($key, $value)
    {
        $objectID = $key->getObjectID();
        if(array_key_exists($objectID, $this->_entries))
        {
            throw new Exception("Key already exists");
        }

        $this->_entries[$objectID] = new Pair($key, $value);
    }

    /**
     * To remove an entry from dictionary.
     *
     * @param <object> $key The key object, Must be derived from Object
     * @returns <bool> TRUE: if $key exists, FALSE: if $key not exists
     *
     */
    public function Remove($key)
    {
        $objectID = $key->getObjectID();
        if(array_key_exists($objectID, $this->_entries))
        {
            unset($this->_entries[$objectID]);
            return TRUE;
        }

        return FALSE;
    }

    /**
     * To remove all entries from dictionary.
     *
     */
    public function RemoveAll()
    {
        foreach($this->_entries as $key => $value)
        {
            unset($this->_entries[$key]);
        }
    }

    /**
     * To check a particular key exists in the dictionary.
     *
     * @param <object> $key
     */
    public function ContainsKey($key)
    {
        $objectID = $key->getObjectID();
        if(array_key_exists($objectID, $this->_entries))
        {
            return TRUE;
        }

        return FALSE;
    }

    /*
     * To retrives collection of value objects.
     *
     * @Returns <Object list>
     */
    public function Values()
    {
        $values = array();
        foreach($this->_entries as $key => $value)
        {
            $values[] = $value->entry2;
        }

        return $values;
    }

    /*
     * To retrives collection of key objects.
     *
     * @Returns <Object list>
     */
    public function Keys()
    {
        $keys = array();
        foreach($this->_entries as $key => $value)
        {
            $keys[] = $value->entry1;
        }

        return $keys;
    }

    /**
     * To get number of Key:Value pair in the Dictionary.
     *
     * @Return<int>
     */
    public function Count()
    {
        return count($this->_entries);
    }

    /*
     * To retrives value corrosponding to a key object.
     *
     * @param <object> $key The key object, Must be derived from Object
     * @param <object> $value [OUT] The value object, will conatins the value
     * corrosponding to key on return
     * @Returns <bool> TRUE if $key exists, FALSE if $key not exists
     */
    public function TryGetValue($key, &$value)
    {
        $objectID = $key->getObjectID();
        if(array_key_exists($objectID, $this->_entries))
        {
            $value = $this->_entries[$objectID]->entry2;
            return TRUE;
        }

        $value = null;
        return FALSE;
    }

    /* To Sort the dictionary 'values' based on any property of class
     * representing the value object.
     *
     * @param <string> $propertyName The name of property used for sorting
     * @Returns No return value
     */
    public function Sort($propertyName)
    {
        $sortArray = array();
        foreach($this->_entries as $key => $value)
        {
            $property = new ReflectionProperty($value->entry2, $propertyName);
            $sortArray[$key] = $property->getValue($value->entry2);
        }

        asort($sortArray);
        $newEntries = array();
        foreach($sortArray as $key => $value)
        {
            $newEntries[$key] = $this->_entries[$key];
        }

        unset($this->_entries);
        $this->_entries = $newEntries;
    }

    /*
     * To get the index of a key in the dictionary.
     *
     * @param <object> $key The key whose index to be returned
     * Returns <integer> index: if $key exists, -1: if $key not exists
     */
    public function FindEntry($key)
    {
        $index = -1;
        $objectID = $key->getObjectID();
        foreach($this->_entries as $key => $value)
        {
            $index++;
            if($key == $objectID)
            {
                return $index;
            }
        }

        return -1;
    }

    /*
     * To get a value based on index.
     *
     * @param <int> $index The index of value to be returned
     * @Returns <object> if index is with in the range.
     * @throws Exception
     */
    public function GetAt($index)
    {
        if($index < 0 || $index >= count($this->_entries))
        {
            throw new Exception("index out of boundary");
        }

        $i = 0;
        foreach($this->_entries as $key => $value)
        {
            if($i == $index)
            {
                return $value->entry2;
            }

            $i++;
        }
    }

    /**
     * This function will merge two dictionaries. If $propertyName and $propertyValue
     * specified then the merged result will only contains <key Value> pairs, where
     * value of $propertyName of each Value will be equal or not equal to $propertyValue
     * based on $condition.
     *
     * @param <Dictionary> $dictionary1
     * @param <Dictionary> $dictionary2
     * @param <string> propertyName
     * @param <anyType> $propertyValue
     * @patam <bool> $condition
     */
    public static function Merge($dictionary1, $dictionary2,
                                 $propertyName = null, $propertyValue = null,
                                 $condition = TRUE)
    {
        $mergedDictionary = new Dictionary();
        foreach($dictionary1->_entries as $key => $value)
        {
            if(($propertyName != null) &&
               (!Dictionary::canAdd($value->entry2, $propertyName, $propertyValue, $condition)))
            {
                continue;
            }

            $mergedDictionary->Add($value->entry1, $value->entry2);
        }

        foreach($dictionary2->_entries as $key => $value)
        {
            if(($propertyName != null) &&
               (!Dictionary::canAdd($value->entry2, $propertyName, $propertyValue, $condition)))
            {
                continue;
            }

            $mergedDictionary->Add($value->entry1, $value->entry2);
        }

        return $mergedDictionary;
    }

    /**
     * To testsa value can be added to merged dictionary.
     *
     * @param <anyType> value
     * @param <string> propertyName
     * @param <anyType> $propertyValue
     * @patam <bool> $condition
     */
    protected static function canAdd($value, $propertyName, $propertyValue, $condition)
    {
        $property = new ReflectionProperty($value, $propertyName);
        if((($condition == TRUE) &&
           ($property->getValue($value) != $propertyValue)) ||
           (($condition == FALSE) &&
           ($property->getValue($value) == $propertyValue)))
           {
            return FALSE;
           }

         return TRUE;
    }
}

/**
  * Used by Dictionary to store user passed  key-value pair as value in the
  * internal array.
  *
  */
class Pair
{
    /**
     *
     * @var <Object>
     */
    public $entry1;

    /**
     *
     * @var <Object>
     */
    public $entry2;

    /**
     * Construct a Pair instance.
     *
     * @param <Object> $entry1
     * @param <Object> $entry2
     */
    public function Pair($entry1, $entry2)
    {
        $this->entry1 = $entry1;
        $this->entry2 = $entry2;
    }
}
?>