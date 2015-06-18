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
class Utility
{
    protected static $ACCEPT_TYPE  = "Accept: application/json";
    protected static $CONTENT_TYPE = "Content-Type: application/json";
    protected static $REQUEST_GET  = "GET";

    /**
     * This function is to retrive Entity name from
     * type property.
     * @param string $type The value of Type property of __metadata.
     * @return string $entityName The entity Name
     */
    public static function getEntityNameFromType($type)
    {
        // TBD: Take last occurrence of '.', but we should use type 2 name mapping
        $pos = strrpos($type, ".");
        $entityName = substr($type, $pos + 1);
        return $entityName;
    }

    /**
     * This function is to retrive Property Name from
     * raw property returned by the reflection method
     * getProperties.
     * @param string $rawProperty The raw property
     * string returned by getProperties.
     * @return string $propertyName The Property Name.
     */
    public static function getPropertyName($rawProperty)
    {
        $pos = strpos($rawProperty, "$");
        $tmp  = substr($rawProperty, $pos + 1);
        $pos = strpos($tmp, " ");
        $propertyName = substr($tmp, 0, $pos);
        return $propertyName;
    }

    /**
     * Function to find the index of last occurance of a
     * character in a string.
     * @param string $string The string to be serached.
     * @param char $char The character to be serached.
     * @return index of $char in $string, -1 if not found.
     */
    public static function reverseFind($string, $char)
    {
        if (($len = strlen($string)) == 0)
            return -1;

        $revString = strrev($string);
        if ($revString[0] == $char)
        {
            return ($len - 1);
        }

        $index = strpos($revString, $char);
        if ($index != FALSE)
        {
            return ($len - $index - 1);
        }

        return -1;
    }

    /**
     * Function to decode unicode characters in json response.
     * @param string $jsonStr The json string to be decoded.
     * @return string $jsonStr The decoded string, which can
     * render by browser.
     */
    public static function decodeJSONSpecialChar($jsonStr)
    {
        while ($pos = strpos($jsonStr, "\u"))
        {
            $hex = substr($jsonStr, $pos + 2, 4);
            $char = hexdec($hex);
            switch ($char)
            {
                case "\"":
                    $char = "";
                    break;

                default:
                    break;
            }

            $search = "\u". $hex;
            $replace = "&#".$char.";";
            $jsonStr = str_replace($search, $replace, $jsonStr, $count);
        }

        // Replace escaped single quote
        $jsonStr = str_replace("\'", "'", $jsonStr, $count);

        // Return utf8 string
        return utf8_decode($jsonStr);
    }

    /**
     * Function to encode the special characters in a string to
     * json format.
     * @param string $jsonStr The string to be encoded.
     * @return string $jsonStr The encoded json string.
     */
    public static function encodeJSONSpecialChar($jsonStr)
    {
       while ($pos = strpos($jsonStr, "&#"))
       {
            $pos  = strpos($jsonStr, "&#");
            $pos2 = strpos($jsonStr, ";",$pos);
            $char = substr($jsonStr, $pos + 2, $pos2 - $pos - 2);
            $hex  = dechex($char);
            $pad  = '';
            for ($i = strlen($hex); $i < 4; $i++)
            {
                $pad = $pad . "0";
            }

            $search = "&#" . $char . ";";
            $replace = "\u" . $pad. $hex;
            $jsonStr = str_replace($search, $replace, $jsonStr, $count);
        }

        $trans = Utility::get_html_json_translation_table();
        $jsonStr = str_replace(array_keys($trans), $trans, $jsonStr);
        return $jsonStr;
    }

    public static function getProperties($entityName, &$PropertyArray)
    {
        $class = new ReflectionClass($entityName);
        $properties = $class->getProperties();
        foreach ($properties as $property)
        {
            $pos1 = strpos($property, "$");
            $tmp  = substr($property, $pos1+1);
            $pos2 = strpos($tmp, " ");
            $PropertyArray[] = substr($tmp, 0, $pos2);
        }
    }

     /**
     * Function to get value of a property in a associative array
     * corresponding to the JSON response.
     * @param array $phpNativeJSON The JSON associative array.
     * @return string $propertyName The property whose value
     * to be retrived from $phpNativeJSON.
     * @return string The value of property $propertyName.
     */
    public static function getValueFromJSONResponse($phpNativeJSON, $propertyName)
    {
        return  $phpNativeJSON["d"][$propertyName];
    }

    /**
     * Function to create an instance of an entitiy.
     * @param string $entry Name of entity class.
     * @param string $url URL to entity instance in data service.
     * @return $object The entity instance.
     */
    public static function getEntity($entry, $url)
    {
        $class = new ReflectionClass($entry);
        if ($class->isInstantiable())
        {
            $object = $class->newInstance($url);
        }

        return $object;
    }

    /**
     * Function to convert json date to php date.
     * @param string $date Date in json format.
     * @return string Date in php format.
     */
    public static function jsonDateToPhpDate($date)
    {
        $pos  = strpos($date, "(");
        $date = substr($date, $pos + 1);
        $pos  = strpos($date, ")");
        $date = substr($date, 0, $pos - 3);
        $day  = date('d', $date);
        $month = date('m', $date);
        $year  = date('Y', $date);
        return  $year . "/" . $month . "/". $day;
    }

    /**
     * Function to check any entity key of an entity
     * instance is empty or not.
     * @param $object The entity instance.
     * @return TRUE if any of the entity key is empty.
     * FALSE if none of the entity key is empty.
     */
    public static function isEntityKeyEmpty($object)
    {
        foreach ($object->getEntityKeys() as $entityKey)
        {
            $prop = new ReflectionProperty($object, $entityKey);
            $keyVal = $prop->getValue($object);
            if (empty($keyVal))
            {
                return TRUE;
            }
        }
        return FALSE;
    }

   /**
     * Function to make a uri corrosponding to an entity instance.
     * @param $object The entity instance.
     * @return string $uri The uri of entity instance in data service
     * corrosponding to $object.
     */
    public static function getUri($object)
    {
        $className = get_class($object);
        $keyQuery = "";
        foreach ($object->getEntityKeys() as $entityKey)
        {
            $keyQuery = $keyQuery . $entityKey;
            $keyQuery = $keyQuery . "=";
            $prop = new ReflectionProperty($object, $entityKey);
            $keyVal = $prop->getValue($object);

            if (Utility::ContainAttribute($prop->getDocComment(), 'Edm.String') == FALSE)
            {
                $keyQuery = $keyQuery
                          . str_replace(' ', "%20", $keyVal)
                          . ",";
            }
            else
            {
                $keyQuery = $keyQuery
                          . "'"
                          . str_replace(' ', "%20", $keyVal)
                          . "'"
                          . ",";
            }
        }
        $keyQuery[(strlen($keyQuery) - 1)] = ")";
        $uri = $className . "(" . $keyQuery;
        return $uri;
    }

    /**
     * Function to check an attribute is present in the comment.
     * @param string $docComment The comment section of any property.
     * @param string $attribute The attribute to be searched.
     * @return bool TRUE If attribute $attribute is present in the
     * comment section.
     * FALSE if attribute is not present in the comment.
     */
    public static function ContainAttribute($docComment, $attribute)
    {
        if (strpos($docComment, $attribute) == FALSE)
        {
            return FALSE;
        }

        return TRUE;
    }

    /**
     * Function to convert an edm type to json type.
     * @param ReflectionProperty $prop.
     * @param $object The entity instance.
     * @return string $retVal The converted property.
     */
    public static function edmToJSONType($prop, $object)
    {
        $retVal = $prop->getValue($object);
        if (Utility::ContainAttribute($prop->getDocComment(), 'Edm.Binary'))
        {
            $retVal = base64_encode($retVal);
        }
        else
        {
            $retVal = str_replace("'", "\'", $retVal);
        }
        return $retVal;
    }

    /**
     * This function will return a mapping table for
     * conversion of special characters in html to json
     */
    function get_html_json_translation_table()
    {
        $allEntities = Utility::get_html_translation_table_();
        $specialEntities = get_html_translation_table(HTML_SPECIALCHARS, ENT_NOQUOTES);
        $noTags = array_diff($allEntities, $specialEntities);
        $keys = array();
        $values = array();
        foreach ($noTags as $char => $entity)
        {
             $values[] = "\u00" . dechex(ord($char));
        }

        $trans = array_combine(array_keys($noTags), $values);
        return $trans;
    }

    /**
     * This function is a wrapper over get_html_translation_table
     * and also add some special character which are not in the
     * default translation table.
     */
    function get_html_translation_table_()
    {
        $trans = get_html_translation_table(HTML_ENTITIES, ENT_NOQUOTES);
        $trans[chr(128)] = '&euro;';
        $trans[chr(130)] = '&sbquo;';    // Single Low-9 Quotation Mark
        $trans[chr(131)] = '&fnof;';    // Latin Small Letter F With Hook
        $trans[chr(132)] = '&bdquo;';    // Double Low-9 Quotation Mark
        $trans[chr(133)] = '&hellip;';    // Horizontal Ellipsis
        $trans[chr(134)] = '&dagger;';    // Dagger
        $trans[chr(135)] = '&Dagger;';    // Double Dagger
        $trans[chr(136)] = '&circ;';    // Modifier Letter Circumflex Accent
        $trans[chr(137)] = '&permil;';    // Per Mille Sign
        $trans[chr(138)] = '&Scaron;';    // Latin Capital Letter S With Caron
        $trans[chr(139)] = '&lsaquo;';    // Single Left-Pointing Angle Quotation Mark
        $trans[chr(140)] = '&OElig;    ';    // Latin Capital Ligature OE
        $trans[chr(145)] = '&lsquo;';    // Left Single Quotation Mark
        $trans[chr(146)] = '&rsquo;';    // Right Single Quotation Mark
        $trans[chr(147)] = '&ldquo;';    // Left Double Quotation Mark
        $trans[chr(148)] = '&rdquo;';    // Right Double Quotation Mark
        $trans[chr(149)] = '&bull;';    // Bullet
        $trans[chr(150)] = '&ndash;';    // En Dash
        $trans[chr(151)] = '&mdash;';    // Em Dash
        $trans[chr(152)] = '&tilde;';    // Small Tilde
        $trans[chr(153)] = '&trade;';    // Trade Mark Sign
        $trans[chr(154)] = '&scaron;';    // Latin Small Letter S With Caron
        $trans[chr(155)] = '&rsaquo;';    // Single Right-Pointing Angle Quotation Mark
        $trans[chr(156)] = '&oelig;';    // Latin Small Ligature OE
        $trans[chr(159)] = '&Yuml;';    // Latin Capital Letter Y With Diaeresis
        ksort($trans);
        return $trans;
    }

    /**
     *@param <string by reference> $string
     *@param <string> $value
     *Merge $value with $string and append a newline at the end
     */
    public function WriteLine(&$string, $value)
    {
        if($value != null)
        {
            $string = $string . $value;
        }
        $string = $string . "\n";
    }

    public function GetPropertyType($property, &$notNullable)
    {
        $type = "";
        $notNullable = false;

        if (Utility::ContainAttribute($property->getDocComment(), 'EntityProperty'))
        {
            if(Utility::ContainAttribute($property->getDocComment(), 'NotNullable'))
            {
                $notNullable = true;
            }

            if(Utility::ContainAttribute($property->getDocComment(), 'Edm.String'))
            {
                $type = "Edm.String";
            }
            else if(Utility::ContainAttribute($property->getDocComment(), 'Edm.Int32'))
            {
                $type = "Edm.Int32";
            }
            else if(Utility::ContainAttribute($property->getDocComment(), 'Edm.Binary'))
            {
                $type = "Edm.Binary";
            }
            else if(Utility::ContainAttribute($property->getDocComment(), 'Edm.DateTime'))
            {
                $type = "Edm.DateTime";
            }
            else if(Utility::ContainAttribute($property->getDocComment(), 'Edm.Decimal'))
            {
                $type = "Edm.Decimal";
            }
            else if(Utility::ContainAttribute($property->getDocComment(), 'Edm.Int16'))
            {
                $type = "Edm.Int16";
            }
            else if(Utility::ContainAttribute($property->getDocComment(), 'Edm.Boolean'))
            {
                $type = "Edm.Boolean";
            }
            else if(Utility::ContainAttribute($property->getDocComment(), 'Edm.Decimal'))
            {
                $type = "Edm.Decimal";
            }
        }

        return $type;
    }

    /**
     *@param <stringe> $uri
     *@return string The entity set name.
     *This function will retrive the entity set name from
     *a url which of the  format
     *http://host/service.svc/EntitySet(KeyValue)
     *
     */
    public static function GetEntitySetFromUrl($uri)
    {
        $openBracePos = strpos($uri, '(');
        if( $openBracePos === FALSE)
        {
            throw new ODataServiceException(Resource::ExpectedOpenBraceNotFound,
                                            '',
                                            array(),
                                            null);
        }

        $slashPos = strpos($uri, '/');
        if($slashPos === FALSE)
        {
            return substr($uri, 0, $openBracePos);
        }

        if($slashPos > $openBracePos)
        {
            return substr($uri, 0, $openBracePos);
        }

        for($i = $slashPos + 1; $i < $openBracePos; $i++)
        {
            if($uri[$i] == '/')
                $slashPos = $i;
        }

        return substr($uri, $slashPos + 1, $openBracePos - $slashPos - 1);
    }

    /**
     *
     * @param <ReflectionProperty or ReflectionClass> $typeInstance
     * @return <array> name-value pair of attributes
     */
    public static function  getAttributes($typeInstance)
    {
        $AttributeCollection = array();
        $rawAttributes = $typeInstance->getDocComment();
        $attributes = explode("\n", $rawAttributes);
        foreach ($attributes as $attribute)
        {
            $i = strpos($attribute, "@");
            if ($i !== FALSE)
            {
                $j = strpos($attribute, ":");
                $key = substr($attribute, $i + 1, $j - $i - 1);
                $value = trim(substr($attribute, $j+1));
                $key = trim($key);
                if (isset($AttributeCollection[$key]))
                {
                    if (! is_array($AttributeCollection[$key]))
                    {
                        $AttributeCollection[$key] = array($AttributeCollection[$key]);
                    }
                    $AttributeCollection[$key][] = $value;
                }
                else
                {
                    $AttributeCollection[$key] = $value;
                }
            }
        }

        return $AttributeCollection;
    }

    public static function IsAbsoluteUrl($url)
    {
        $parts = parse_url($url);
        if(isset($parts['scheme']) && isset($parts['host']))
        {
            return true;
        }
        return false;
    }

    public static function CreateUri($baseUri, $requestUri)
    {
        if(!isset($requestUri) || $requestUri == '')
        {
            throw new Exception('Utility::CreateUri The requestUri argument cannot be null');
        }
        if(Utility::IsAbsoluteUrl($requestUri))
        {
            return $requestUri;
        }

        return( rtrim($baseUri, "/") . "/" . ltrim($requestUri, "/"));
    }

    public static function TimeInISO8601()
    {
        $time = time();
        return date('Y-m-d', $time) . 'T' . gmdate("H:i:s", $time) .'Z';
    }

    public static function HttpSuccessCode($httpCode)
    {
        if(!empty($httpCode))
        {
            $restype = floor($httpCode / 100);
            return ($restype == 2 || $restype == 1);
        }

        throw new Exception('Utility::HttpSuccessCode The httpCode argument cannot be null');
    }
}
?>