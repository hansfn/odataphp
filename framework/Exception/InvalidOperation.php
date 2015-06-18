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
 * Represents invalid operation performed by the client application during tracking.
 */
class InvalidOperation extends Exception
{
    /**
     *
     * @var string
     */
    protected $_error;

    /**
     *
     * @var string
     */
    protected $_detailedError;

    /**
     * Create an InvalidOperation instance.
     *
     * @param string $error
     * @param string $detailedError
     */
    public function InvalidOperation($error, $detailedError = null)
    {
        $this->_error = $error;
        $this->_detailedError = $detailedError;
    }

    /**
     * To get the error string.
     *
     * @return string
     */
    public function getError()
    {
        return $this->_error;
    }

    /**
     * To get the detailed error string.
     *
     * @return string
     */
    public function getDetailedError()
    {
        return $this->_detailedError;
    }
}
?>
