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
class Guid
{
    /**
     * Generate a unique id using MD5 algorithm
     *
     * @Return <string>
     */
    public static function NewGuid()
    {
        $rawId = strtolower(md5(uniqid(rand(), true)));
        return substr($rawId, 0, 8).'-'
                        .substr($rawId, 8, 4).'-'
                        .substr($rawId,12, 4).'-'
                        .substr($rawId,16, 4).'-'
                        .substr($rawId,20,12);
    }
};
?>