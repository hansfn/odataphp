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
class Collection
{
    /**
     * the associative array.
     *
     * @var <array>
     */
    protected $_keyValPairs;

    /**
     * Construct Collection instance.
     *
     */
    public function Collection()
    {
        $this->_keyValPairs = array();
    }

    /**
     * To add a new key-value pair to the collection. if an item with key exists
     * then overwrite it.
     *
     * @param <string> $key
     * @param <string> $value
     */
    public function Add($key, $value)
    {
        $this->Remove($key);
        $this->_keyValPairs[$key] = $value;
    }

    /**
     * To remove a key-value pair from the collection identified by $key.
     *
     * @param <string> $key
     */
    public function Remove($key)
    {
        $actualKey = null;
        if($this->_key($key, $actualKey))
        {
            unset($this->_keyValPairs[$actualKey]);
        }
    }

    /**
     * To get a value from the collection identified by $key.
     *
     * @param <string> $key
     * @param <string> [out] $value
     * @return <boolean>
     */
    public function TryGetValue($key, &$value)
    {
        $actualKey = null;
        if($this->_key($key, $actualKey))
        {
            $value = $this->_keyValPairs[$actualKey];
            return true;
        }

        $value = null;
        return false;
    }

    /**
     * To check an item with specific keys exists in the collection.
     *
     * @param <type> $key
     * @return <bool>
     */
    public function HasKey($key)
    {
        $actualKey = null;
        return $this->_key($key, $actualKey);
    }

    /**
     * To get all keys as an array.
     *
     * @retrun <array>
     */
    public function GetAllKeys()
    {
        return array_keys($this->_keyValPairs);
    }

    /**
     * Return the collection as key-value array.
     *
     * @return <array>
     */
    public function GetAll()
    {
        return $this->_keyValPairs;
    }

    /**
     * To merge the contents of $srcArray with the collection.
     *
     * @param <array> $srcArray
     */
    public function CopyFrom($srcArray)
    {
        foreach ($srcArray as $key => $value)
        {
            $this->Add($key, $value);
        }
    }

    /**
     * To clear the collection.
     *
     */
    public function Clear()
    {
        unset($this->_keyValPairs);
        $this->_keyValPairs = array();
    }

    /**
     * To perfrom a case-insensitive search on collection.
     *
     * @param <string> $cikey
     * @param <string> [out] $actualKey
     * @return <boolean>
     */
    protected function _key($cikey, &$actualKey)
    {
        foreach($this->_keyValPairs as $key => $value)
        {
            if(strcasecmp($key, $cikey) == 0)
            {
                $actualKey = $key;
                return true;
            }
        }

        return false;
    }
}
?>