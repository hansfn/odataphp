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
class WCFDataServicesEntity
{
    protected $_containerScriptName;
    protected $_uri = "";
    protected $_query = "";
    protected $_http_proxy_hostObject;
    protected $_pageSize;
    protected $_enablePaging;

    /**
     * Constructs a new WCFDataServicesEntity object.
     * @param string $className The Container class Name.
     * @param string $uri The data service url.
     * @param string $query The query to execute.
     */
    public function  __construct($containerScriptName, $className, $enablePaging, $pageSize, $uri, $query, $proxy ="", $port = "", $userName = "", $password = "")
    {
        $this->_containerScriptName = $containerScriptName;
        $this->_uri = $uri;
        $this->_query = $query;
        $this->_pageSize = $pageSize;
        $this->_enablePaging = $enablePaging;
        $class = new ReflectionClass($className);
        $this->_proxyObject = $class->newInstance($this->_uri);
        if (strlen($proxy) != 0)
        {
            $this->_proxyObject->HttpProxy = new HttpProxy($proxy, $port);
        }
        if (!empty($userName) && !(empty($password)))
        {
            $this->_proxyObject->Credential = new Credential($userName, $password);
        }
    }

    /**
     * Function to setup the data editor table.
     * This function will display the result of
     * user suppiled query, if user not supplied
     * any query then this function will display
     * all entities in the data service.
     */
    public function display()
    {
        if ($this->_query == '')
        {
            WCFDataServicesEntity::displayEntities();
        }
        else
        {
            WCFDataServicesEntity::displayEntityData();
        }
    }

    /**
     * Fucntion to display result of query.
     */
    protected function displayEntityData()
    {

        $this->_query = str_replace("\'", "'", $this->_query);
        $queryToRun = $this->_query;
        $pagingSection = '<table border="0" align="center" CELLSPACING="15">';
        $nextSkip = null;
        $canPage = false;
        if($this->_enablePaging && (isset($_REQUEST['pagingAllowed']) &&
                                    $_REQUEST['pagingAllowed'] == 'true'))
        {
            $canPage = true;
            $skip = 0;
            if(isset($_REQUEST['skip']))
            {
                $skip = $_REQUEST['skip'];
            }

            $parts = parse_url($queryToRun);
            if(isset($parts['query']))
            {
                $queryToRun .= '&$top='.$this->_pageSize.'&$skip=' . $skip;
            }
            else
            {
                $queryToRun .= '?$top='.$this->_pageSize.'&$skip=' . $skip;
            }

            $nextSkip = $skip + $this->_pageSize;
            if($nextSkip != $this->_pageSize)
            {
                $prev = $skip - $this->_pageSize;
                $pagingSection .= "<td><a href=\"" . $this->_containerScriptName
                                    . "?query="
                                    . $this->_query
                                    . "&serviceUri="
                                    . $this->_uri
                                    . '&skip=' . $prev
                                    . '&pagingAllowed=true'
                                    . "\">Prev</a></td>";
            }
        }

        $response = $this->_proxyObject->Execute($queryToRun);
        $resultSet = $response->Result;
        echo "<br><br><table style=\"border: thin solid #C0C0C0;\" border=\"1\">";
        if (count($resultSet) > 0)
        {
            $propertyArray = WCFDataServicesEntity::getProperties($resultSet[0]);
            $this->displayHeader($propertyArray, $resultSet[0]);
            foreach ($resultSet as $result)
            {
                echo "<tr style=\"font-family: Calibri; "
                     . "background-color: #CCFFFF\">";
                WCFDataServicesEntity::getDetailButtonText($result);
                foreach ($propertyArray as $property)
                {
                    $prop = new ReflectionProperty($result, $property);
                    $propertyAttributes = Utility::getAttributes($prop);
                    if ($propertyAttributes['Type'] == 'NavigationProperty')
                    {
                        $pagingAllowed = 'pagingAllowed=true';
                        $relationShip = $this->_proxyObject->GetRelationShip($propertyAttributes["Relationship"],
                                                               $propertyAttributes["ToRole"]);
                        if($relationShip != '*')
                        {
                            $pagingAllowed = 'pagingAllowed=false';
                        }

                        $skip = null;
                        if(isset($_REQUEST['skip']))
                        {
                            $skip = '&skip=' . $_REQUEST['skip'];
                        }

                        $pagingAllowedWhileAttaching = null;
                        if(isset($_GET['pagingAllowed']))
                        {
                            $pagingAllowedWhileAttaching =
                                '&pagingAllowed=' . $_GET['pagingAllowed'];
                        }

                        echo "<td>";
                        $relatedLinks = $result->getRelatedLinks();
                        $finalQuery = $relatedLinks[$property];
                        $finalQuery = str_replace("%20", '',$finalQuery);
                        echo "<a href=\"" . $this->_containerScriptName . "?query="
                             . $finalQuery
                             . '&' . $pagingAllowed
                             . $skip
                             . "&serviceUri="
                             . $this->_uri
                             ."\">"
                             . $property
                             . "</a>";
                        echo "<br><a href=\"" . $this->_containerScriptName . "?query="
                             . $this->_query
                             . $pagingAllowedWhileAttaching
                             . $skip
                             . "&serviceUri="
                             . $this->_uri
                             . "&Type="
                             .  $property
                             . "&AttachTo="
                             . $finalQuery
                             . "\">  Add Link </a>";
                        echo "</td>";
                    }
                    else
                    {
                        $propertyAttributes = Utility::getAttributes($prop);
                        if(isset($propertyAttributes['EdmType']) &&
                        ($index = strpos($propertyAttributes['EdmType'], 'Edm.')) !== 0)

                        {
                            $value = $prop->getValue($result);
                            $type = ClientType::Create(get_class($value));
                            $nonEpmProperties = $type->getRawNonEPMProperties(true);
                            echo '<td><table style="border: thin solid #C0C0C0;" border="1">';
                            foreach($nonEpmProperties as $nonEpmProperty)
                            {
                                $propertyName = $nonEpmProperty->getName();
                                $refProperty = new ReflectionProperty($value, $propertyName);
                                $propertyValue = $refProperty->getValue($value);
                                echo '<tr><td>';
                                echo $propertyValue;
                                echo '</td></tr>';
                            }
                            echo '</table></td>';
                        }
                        else
                        {
                            if (Utility::ContainAttribute($prop->getDocComment(),
                                                     'Binary'))
                            {
                                // TODO: Display image in the cell
                                echo "<td>Image</td>";
                            }
                            else
                            {
                                $value = $prop->getValue($result);
                                if ($value == '')
                                {
                                    $value = 'null';
                                }
                                echo "<td>"
                                     . $value
                                    . "</td>";
                            }
                        }
                    }
                }
                echo "</tr>";
            }

            if($canPage)
            {
                 $pagingSection .= "<td><a href=\"" . $this->_containerScriptName
                                    . "?query="
                                    . $this->_query
                                    . "&serviceUri="
                                    . $this->_uri
                                    . '&skip=' . $nextSkip
                                    . '&pagingAllowed=true'
                                    . "\">Next</a></td><tr/></table>";
            }
        }

        if($canPage)
        {
            echo $pagingSection;
        }
        echo "</table><br><br>";
    }

    /**
     * Fucntion to display two header rows of data editor table.
     * First row displays the name of entity which has been selected
     * by the query and second row displays all property name.
     * @param array $propertyArray The collection of Property Names.
     * @param $obj The entity instance.
     */
    protected function displayHeader($propertyArray, $obj)
    {
        $pagingAllowed = null;
        if($this->_enablePaging && isset($_REQUEST['pagingAllowed']))
        {
            $pagingAllowed = "<input type=\"hidden\" name=\"pagingAllowed\" value=\"".$_REQUEST['pagingAllowed']."\">";
        }

        $skip = null;
        if(isset($_REQUEST['skip']))
        {
            $skip = "<input type=\"hidden\" name=\"skip\" value=\"".$_REQUEST['skip']."\">";
        }

        echo "<tr align=\"center\" style=\"font-family: Calibri; "
             ."background-color: #97CC00\">";
        echo "<td>";
        $queryString = "?query="
                     . $this->_query
                     . "&serviceUri="
                     . $this->_uri
                     . "&Type="
                     . get_class($obj);
        echo "<form action=\"" . $this->_containerScriptName . ""
             . $queryString
             . "\" method=\"post\">";
        echo "<input id=\"btnAdd\" type=\"submit\" name=\"btnAdd\" "
             . "value=\"Add Record\"/>";
        echo $pagingAllowed;
        echo $skip;
        echo "</form>";
        echo "</td>";
        echo "<td colspan=\""
             . count($propertyArray)
             . "\">"
             . get_class($obj)
             . "</td>";
        echo "</tr>";
        echo "<tr style=\"font-family: Calibri; "
             . "background-color: #99CCFF\"><td></td>";

        foreach ($propertyArray as $property)
        {
            echo "<td>" . $property . "</td>";
        }
        echo "</tr>";
    }

    /**
     * Fucntion to display all entities in the data service.
     */
    protected function displayEntities()
    {
        $enities = $this->_proxyObject->getEntities();
        echo "<br><br><table align=\"center\" style=\"font-family: Calibri; "
             . "width: 95%\">";
        echo "<tr><td align=\"center\" style=\"font-family: Calibri; "
             . "background-color: #97CC00\">Entities</td></tr>";
        foreach ($enities as $entity)
        {
            echo "<tr ><td style=\"font-family: Calibri; "
                 . "background-color: #99CCFF\" border=\"1\">";
            echo "<a href=\"" . $this->_containerScriptName . "?query="
                 . $entity
                 . '&pagingAllowed=true'
                 . "&serviceUri="
                 . $this->_uri
                 . "\">"
                 . $entity . "</a>";
            echo "</td></tr>";
        }
        echo "</table><br><br>";
    }

    /**
     * Helper function to get all property names
     * of an entity instance.
     * @param $obj The entity instance whose
     * properties are to be fetched.
     * @return array $propertyArray The collection of
     * properties.
     */
    protected function getProperties($obj)
    {
        $class = new ReflectionClass(get_class($obj));
        $properties = $class->getProperties();
        $propertyArray = array();

        foreach ($properties as $property)
        {
            $pos1 = strpos($property, "$");
            $tmp  = substr($property, $pos1 + 1);
            $pos2 = strpos($tmp, " ");
            $propertyName = substr($tmp, 0, $pos2);
            if ($propertyName != '_entityMap'
                && $propertyName != '_entityKey'
                && $propertyName != '_baseURI'
                && $propertyName != '_relLinks'
                && $propertyName != '_objectID')
            {
                $propertyArray[] = $propertyName;
            }
        }
        return $propertyArray;
    }

    /**
     * Helper function generate navigation query
     * @param $obj The instance of navigation object.
     * @return string $returnQuery the query representing
     * the navigation object.
     */
    protected function formQueryForNavigationProperty($obj)
    {
        $entityKey = $obj->entityKey[0];
        $prop = new ReflectionProperty($obj, $entityKey);
        $value = $prop->getValue($obj);
        $returnQuery = "";
        $edmType = Utility::GetPropertyType($prop, $notNullable);
        if(strcmp($edmType, "Edm.String") == 0)
        {
            $returnQuery = $this->_query . "('" . $value . "')/";
        }
        else
        {
            $returnQuery = $this->_query . "(" . $value . ")/";
        }
        return $returnQuery;
    }

    protected function getDetailButtonText($obj)
    {
        $pagingAllowed = null;
        if($this->_enablePaging && isset($_REQUEST['pagingAllowed']))
        {
            $pagingAllowed = '&pagingAllowed=' . $_REQUEST['pagingAllowed'];
        }

        $skip = null;
        if(isset($_REQUEST['skip']))
        {
            $skip = '&skip=' . $_REQUEST['skip'];
        }

        $keyQuery = "(";
        foreach ($obj->getEntityKeys() as $entityKey)
        {
            $keyQuery = $keyQuery.$entityKey;
            $keyQuery = $keyQuery."=";
            $prop = new ReflectionProperty($obj, $entityKey);
            $keyVal = rtrim($prop->getValue($obj));

            $edmType = Utility::GetPropertyType($prop, $notNullable);
            if(strcmp($edmType, "Edm.String") == 0)
            {
                $keyQuery = $keyQuery."'".str_replace(' ', "%20",$keyVal)."'".",";
            }
            else
            {
                $keyQuery = $keyQuery.str_replace(' ', "%20",$keyVal).",";
            }
        }

        $entityType = get_class($obj);
        $entitySetName = $this->_proxyObject->GetEntitySetNameFromType($entityType);

        $keyQuery[(strlen($keyQuery) - 1)] =")";
        $queryString = "?query="
                     . $this->_query
                     . "&serviceUri="
                     . $this->_uri
                     . "&changeId="
                     . $keyQuery
                     . "&Type="
                     . $entitySetName
                     . $pagingAllowed
                     . $skip;
        echo "<form action=\"" . $this->_containerScriptName . ""
             . $queryString
             . "\" method=\"post\">";
        echo "<td style=\"width=70\">"
             . "<input id=\"btnDetail\" type=\"submit\" "
             . "name=\"btnDetail\" value=\"Detail\"/>"
             .  "</td>";
        echo "</form>";
    }

    /**
     * Function to display entity details in edit mode.
     * @param string $id The entitiy key.
     * @param string $type The entity name.
     */
    public function displayDetails($id, $type)
    {
        $pagingAllowed = null;
        if($this->_enablePaging && isset($_REQUEST['pagingAllowed']))
        {
            $pagingAllowed = "<input type=\"hidden\" name=\"pagingAllowed\" value=\"".$_REQUEST['pagingAllowed']."\">";
        }

        $skip = null;
        if(isset($_REQUEST['skip']))
        {
            $skip = "<input type=\"hidden\" name=\"skip\" value=\"".$_REQUEST['skip']."\">";
        }

        $response = $this->_proxyObject->Execute($type . $id);
        $resultSet = $response->Result;
        echo "<table style=\"border: thin solid #C0C0C0;\" border=\"1\">";
        echo "<form action=\"" . $this->_containerScriptName . "?query=". $this->_query
             . "&serviceUri="
             . $this->_uri
             . "&Type="
             . $type
             . "\" method=\"post\">";
        echo "<tr align=\"center\" style=\"font-family: Calibri; "
             ."background-color: #97CC00\">"
             . "<td Colspan =\"2\">"
             . get_class($resultSet[0])
             . "[" . $id ."]"
             . "</td></tr>";
        echo "<tr style=\"font-family: Calibri; background-color: #99CCFF\">"
             ."<td>Field</td>"
             ."<td>Value</td>"
             ."</tr>";
        $propertyArray = WCFDataServicesEntity::getProperties($resultSet[0]);
        foreach ($propertyArray as $property)
        {
            echo "<tr style=\"font-family: Calibri; background-color: #CCFFFF\">";
            $prop = new ReflectionProperty($resultSet[0], $property);
            if (Utility::ContainAttribute($prop->getDocComment(),
                                         'NavigationProperty'))
            {
            }
            else
            {
                $value = $prop->getValue($resultSet[0]);
                if (Utility::ContainAttribute($prop->getDocComment(),
                                             'NotNullable'))
                {
                    echo "<td style=\"width=150pt\">" . $property . "*</td>";
                }
                else
                {
                    echo "<td style=\"width=150pt\">" . $property . "</td>";
                }
                echo "<td><input size = \"150\" name=\""
                     . $property
                     . "\" type=\"text\" value=\""
                     . $value
                     . "\"/></td>";
            }
            echo "</tr>";
        }
        echo "<tr>";
        echo "<td style=\"width=70\"><input id=\"btnUpdate\" "
             ."type=\"submit\" name=\"btnUpdate\" value=\"Update\"/></td>";
        echo "<td style=\"width=70\"><input id=\"btnDelete\" "
             ."type=\"submit\" name=\"btnDelete\" value=\"Delete\"/></td>";
        echo "</tr>";
        echo "<input type=\"hidden\" name=\"editLink\" value=\"".$id."\">";
        echo $pagingAllowed;
        echo $skip;
        echo "</form>";
        echo "</table>";
    }

    /**
     * Wrapper function over ObjectContext::UpdateObject.
     * @param array $fields The array holding form datas.
     * @param string $type The entity name
     */
    public function Update($fields, $type)
    {
        $object = $this->getObject($type, $fields);
        $this->_proxyObject->UpdateObject($object);
        $this->_proxyObject->SaveChanges();
    }

    /**
     * Wrapper function over ObjectContext::DeleteObject.
     * @param array $fields The array holding form datas.
     * @param string $type The entity name
     */
    public function Delete($fields, $type)
    {
        $object = $this->getObject($type, $fields);
        $this->_proxyObject->DeleteObject($object);
        $this->_proxyObject->SaveChanges();
    }

    /**
     * Function to create an instance of entitiy
     * and populate the object with form data.
     * @param array $fields The array holding form datas.
     * @param string $type The entity name.
     * @return $object New entity instance of tyoe $type
     * which will be initialized with form data.
     */
    protected function getObject($type, $fields = array())
    {
        $object;
        if(isset($fields["editLink"]))
        {
            $editLink = str_replace("\'", "'", $fields["editLink"]);
            $query = $type . $editLink;
            $response = $this->_proxyObject->Execute($query);
            $source = $response->Result;
            $object = $source[0];
            unset($fields["editLink"]);
        }
        else
        {
            $class = new ReflectionClass($type);
            $object = $class->newInstance($this->_uri);
        }

        foreach ($fields as $key=>$value)
        {
            if ($key != 'btnUpdate'
                && $key != 'btnDelete'
                && $key != 'btnSave'
                && $key != 'btnAddLink'
                && $key != 'pagingAllowed'
                && $key != 'skip')
            {
                $prop = new ReflectionProperty($object, $key);
                $prop->setValue($object, $value);
            }
        }
        return $object;
    }

    /**
     * Function to form to add new entity instance.
     * @param string $type The entitiy name.
     */
    public function displayAdd($type)
    {
        $pagingAllowed = null;
        if($this->_enablePaging && isset($_REQUEST['pagingAllowed']))
        {
            $pagingAllowed = "<input type=\"hidden\" name=\"pagingAllowed\" value=\"".$_REQUEST['pagingAllowed']."\">";
        }

        $skip = null;
        if(isset($_REQUEST['skip']))
        {
            $skip = "<input type=\"hidden\" name=\"skip\" value=\"".$_REQUEST['skip']."\">";
        }

        $object = $this->getObject($type);
        echo "<table style=\"border: thin solid #C0C0C0;\" border=\"1\">";
        echo "<form action=\"" . $this->_containerScriptName . "?query=". $this->_query
             . "&serviceUri="
             . $this->_uri
             . "&Type="
             . $type
             . "\" method=\"post\">";
        echo "<tr align=\"center\" style=\"font-family: Calibri; "
             . "background-color: #97CC00\">"
             . "<td Colspan =\"2\">"
             . get_class($object)
             . "</td></tr>";
        echo "<tr style=\"font-family: Calibri; background-color: #99CCFF\">"
             ."<td>Field</td><td>Value</td></tr>";

        $propertyArray = WCFDataServicesEntity::getProperties($object);
        foreach ($propertyArray as $property)
        {
            echo "<tr style=\"font-family: Calibri; "
                 . "background-color: #CCFFFF\">";
            $prop = new ReflectionProperty($object, $property);
            if (Utility::ContainAttribute($prop->getDocComment(),
                                         'NavigationProperty'))
            {
            }
            else
            {
                if (Utility::ContainAttribute($prop->getDocComment(),
                                             'NotNullable'))
                {
                    echo "<td style=\"width=150pt\">" . $property . "*</td>";
                }
                else
                {
                    echo "<td style=\"width=150pt\">" . $property . "</td>";
                }
                echo "<td ><input size = \"150\" name=\""
                     . $property
                     . "\" type=\"text\" /></td>";
            }
            echo "</tr>";
        }
        echo "<tr>";
        echo $pagingAllowed;
        echo $skip;
        echo "<td colspan=\"2\" style=\"width=70\">"
             . "<input id=\"btnSave\" type=\"submit\" "
             . "name=\"btnSave\" value=\"Save\"/></td>";
        echo "</tr>";
        echo "</form>";
        echo "</table>";
    }

    /**
     * Function to display addlink button, and to
     * generate form to handle addlink.
     * @param string $type The entitiy name.
     * @param string $attachedTo The navigation path
     */
    public function displayAddLink($type, $attachedTo ='')
    {
        $pagingAllowed = null;
        if($this->_enablePaging && isset($_REQUEST['pagingAllowed']))
        {
            $pagingAllowed = "<input type=\"hidden\" name=\"pagingAllowed\" value=\"".$_REQUEST['pagingAllowed']."\">";
        }

        $skip = null;
        if(isset($_REQUEST['skip']))
        {
            $skip = "<input type=\"hidden\" name=\"skip\" value=\"".$_REQUEST['skip']."\">";
        }

        echo $attachedTo."<br>";
        $pos = Utility::reverseFind($attachedTo, '/');
        if ($pos != FALSE)
        {
            $attachedTo = substr($attachedTo, 0, $pos);
        }
        $object = $this->getObject($type);
        echo "<table style=\"border: thin solid #C0C0C0;\" border=\"1\">";
        echo "<form action=\"" . $this->_containerScriptName . "?query=". $this->_query
             . "&serviceUri="
             . $this->_uri
             . "&Type="
             . $type
             .  "&AttachQuery="
             . $attachedTo
             .  "\" method=\"post\">"
             . $pagingAllowed
             . $skip;
        echo "<tr align=\"center\" style=\"font-family: Calibri; "
             ."background-color: #97CC00\">"
             . "<td Colspan =\"2\">"
             . get_class($object)
             . "</td></tr>";
        echo "<tr style=\"font-family: Calibri; "
             ."background-color: #99CCFF\">"
             ."<td>Field</td>"
             ."<td>Value</td></tr>";
        foreach ($object->getEntityKeys() as $key)
        {
            echo "<tr style=\"font-family: Calibri; "
                 . "background-color: #CCFFFF\">";
            echo "<td style=\"width=175pt\">"
                 . $key
                 . "*</td>";
            echo "<td ><input size = \"125\" name=\""
                 . $key
                 . "\" type=\"text\" /></td>";
            echo "</tr>";
        }
        echo "<tr>";
        echo "<td colspan=\"2\" style=\"width=70\"><input id=\"btnAddLink\""
             ." type=\"submit\" name=\"btnAddLink\" value=\"Save\"/></td>";
        echo "</tr>";
        echo "</form>";
        echo "</table>";
    }

    /**
     * Wrapper function over ObjectContext::AddObject.
     * @param array $fields The array holding form datas.
     * @param string $type The entity name
     */
    public function Insert($fields, $type)
    {
        $object = $this->getObject($type, $fields);
        $this->_proxyObject->AddObject($type, $object);
        $this->_proxyObject->SaveChanges();
    }

    /**
     * Wrapper function over ObjectContext::AddLink.
     * @param array $fields The array holding form datas.
     * @param string $type The entity name of target Object.
     * @param string $attachTo The query to get source Object.
     */
    public function AddLink($fields, $type, $attachTo)
    {
        $keyQuery = $type. "(";
        foreach ($fields as $key=>$value)
        {
            if ($key != 'btnUpdate'
                && $key != 'btnDelete'
                && $key != 'btnSave'
                && $key != 'btnAddLink'
                && $key != 'skip'
                && $key != 'pagingAllowed'
                )
            {
                $keyQuery = $keyQuery . $key . "=";
                if (is_numeric($value))
                {
                    $keyQuery = $keyQuery
                          . str_replace(' ', "%20", $value)
                          . ",";
                }
                else
                {
                    $keyQuery = $keyQuery
                          . "'"
                          . str_replace(' ', "%20", $value)
                          . "'"
                          . ",";
                }
            }
        }
        $keyQuery[(strlen($keyQuery) - 1)] =")";
        $response1 = $this->_proxyObject->Execute($attachTo);
        $response2 = $this->_proxyObject->Execute($keyQuery);
        $source = $response1->Result;
        $target = $response2->Result;
        $this->_proxyObject->AddLink($source[0], $type, $target[0]);
        $this->_proxyObject->SaveChanges();
    }
}
?>
