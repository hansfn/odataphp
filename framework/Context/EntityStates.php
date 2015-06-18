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
 * defines constants which is used to represents state of entity instances.
 */
class EntityStates
{
    /**
     * Used to indicate a new entity has been added (using AddObject) or a new link
     * has been created (using AddLink).
     */

    const Added = '1';

    /**
     * Used to indicate an entity has been deleted (using DeleteObject) or a link
     * has been deleted (using DeleteLink).
     */

    const Deleted = '2';

    /**
     * Used to indicate a link has been detached (when we delete an object using
     * DeleteObject all assoicated links added using AddLink should be released
     * and its status should be set to Detached)
     */

    const Detached = '3';

    /**
     * Used to indicate an entity has been modified (using UpdateObject).
     */

    const Modified = '4';

    /**
     * Used to indicate the entity has not yet changed (when we perform any query
     * execution the resultant entities will be in  Unchanged state).
     */
    const Unchanged = '5';
}
?>
