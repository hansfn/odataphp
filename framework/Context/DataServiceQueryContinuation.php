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
class DataServiceQueryContinuation
{
    /**
     * next link uri to the next set of entities.
     *
     * @var <Uri>
     */
    protected $_nextLinkUri;

    /**
     * Construct DataServiceQueryContinuation instance.
     *
     * @param Uri $nextLinkUri
     */
    public function DataServiceQueryContinuation($nextLinkUri)
    {
        $this->_nextLinkUri = $nextLinkUri;
    }

    /**
     * To get the nextlink uri.
     *
     * @return Uri
     */
    public function ToString()
    {
	return $this->_nextLinkUri;
    }

    /**
     * To get the nextlink uri.
     *
     * @return Uri
     */
    public function getNextLinkUri()
    {
	return $this->_nextLinkUri;
    }

    /**
     * To create a QueryComponent from _nextLinkUri.
     *
     * @return QueryComponents
     */
    public function CreateQueryComponents()
    {
        return new QueryComponents($this->_nextLinkUri, null);
    }
}
?>
