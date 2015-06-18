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
class ContentStream
{
    /**
     *
     * @var <boolean>
     */
    protected $_isKnownMemoryStream;

    /**
     *
     * @var <text/binary>
     */
    protected $_stream;

    /**
     * To create a ContentStream instance.
     *
     * @param <text/binary> $stream
     * @param <boolean> $isKnownMemoryStream
     */
    public function ContentStream($stream, $isKnownMemoryStream)
    {
        $this->_stream = $stream;
        $this->_isKnownMemoryStream = $isKnownMemoryStream;
    }

    /**
     * To get the associated stream.
     *
     * @return <text/binary>
     */
    public function getStream()
    {
        return $this->_stream;
    }

    /**
     * To check the type of associated stream. This function returns true if the
     * stream is text, false if the stream is binary.
     *
     * @return <boolean>
     */
    public function IsKnownMemoryStream()
    {
        return $this->_isKnownMemoryStream;
    }
}
?>
