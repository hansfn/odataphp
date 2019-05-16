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
 * Base class for the classes whose instance needs to be stored in
 * Dictionary class (Common/Dictionary.php).
 */
abstract class BaseObject
{
    /**
     * To hold unique id (GUID).
     *
     */
    protected $_objectID;

   /**
    * Abstract function to be implemented by classes derived from this class.
    *
    */
    public abstract function getObjectID();
}
?>