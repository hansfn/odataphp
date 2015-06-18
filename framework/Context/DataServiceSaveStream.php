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
class DataServiceSaveStream
{
    /**
     *
     * @var Stream
     */
    protected $_stream;

    /**
     *
     * @var DataServiceRequestArgs
     */
    protected $_args;

    /**
     * Construct DataServiceSaveStream instance.
     *
     * @param Stream $stream
     * @param DataServiceRequestArgs $args
     */
    public function DataServiceSaveStream($stream, $args)
    {
        $this->_stream = $stream;
        $this->_args = $args;
    }

    /**
     * To get the stream.
     *
     * @return Stream
     */
    public function getStream()
    {
        return $this->_stream;
    }

    /**
     *
     * To get associated DataServiceRequestArgs object which contains the value
     * of headers 'Accept', Content-Type
     *
     * @return DataServiceRequestArgs
     */
    public function getArgs()
    {
        return $this->_args;
    }
}
?>