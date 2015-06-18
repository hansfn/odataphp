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

require_once 'WCFDataServicesEntity.php';

/*
 * @copyright  Copyright (c) 2010, Persistent Systems Limited (http://www.persistentsys.com)
 * @license    http://odataphp.codeplex.com/license
 */
class WCFDataServicesEditor
{
    protected $_containerName;
    protected $_containerScriptName;
    protected $_http_proxy_host;
    protected $_http_proxy_port;
    protected $_userName;
    protected $_password;
    protected $_pageSize;
    protected $_enablePaging;


    /**
     * Constructs a new WCFDataServicesEditor object.
     * @param string $containerScriptName The Container PHP Script Name.
     * @param string $containerName The Container class Name.
     * @param string $serviceUri WCF Data Service URI.
     * @param string $http_proxy_host HTTP Proxy host name.
     * @param string $http_proxy_port HTTP Proxy port number.
     * @param string $userName user name for authenticating WCF Data Service.
     * @param string $password password for authenticating WCF Data Service.
     */
    public function WCFDataServicesEditor($containerScriptName, $containerName, $enablePaging = false, $pageSize = 5, $serviceUri = "",
        $http_proxy_host = "", $http_proxy_port = "", $userName = "", $password = "")
    {
        $this->_containerScriptName = $containerScriptName;
        $this->_containerName = $containerName;
        $this->_serviceUri = $serviceUri;
        $this->_proxy = $http_proxy_host;
        $this->_port = $http_proxy_port;
        $this->_userName = $userName;
        $this->_password = $password;
        $this->_pageSize = $pageSize;
        $this->_enablePaging = $enablePaging;
    }

    /**
     * This function displays the editor
     */
    public function show()
    {
?>
<html>
    <head>
    </head>
    <body>
        <table style="border: thin solid #C0C0C0; width:100%" align="left" >
            <form action="<?php  echo $this->_containerScriptName; ?>" method="post">
                <tr>
                     <td style="font-family: calibri; font-size: medium;">
                        WCF Data Service : <a href="<?php  echo $this->_serviceUri; ?>"><?php  echo $this->_serviceUri; ?></a>
                        <input type="hidden" name="txtUri" value="<?php  echo $this->_serviceUri; ?>" />
                    </td>
                </tr>
                <tr>
                    <td style="font-family: calibri; font-size: medium;">
                        Query &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:
                        <input style="width:550pt;" id="txtQuery" type="text" name="txtQuery" value="<?php if(isset($_POST['txtQuery']) == 1) echo str_replace("\'", "'", $_POST['txtQuery']); elseif(isset($_GET['query']) == 1) echo str_replace("\'", "'", $_GET['query']);?>"/>
                        <input id="btnResult" type="submit" name="btnResult" value="Submit Query" />
                    </td>
                </tr>
            </form>
            <tr>
                <td colspan="3" style="width: 100%;">
                    <div>
                    <?php
                        $serviceUri = '';
                        $query = '';

                        if (isset($_GET['serviceUri']))
                        {
                            $serviceUri = $_GET['serviceUri'];
                        }

                        if (isset($_GET['query']))
                        {
                            $query = $_GET['query'];
                        }

                        if (isset($_POST['btnResult']) == 1)
                        {
                            $serviceUri = $_POST['txtUri'];
                            $query = $_POST['txtQuery'];
                        }

                        if ($serviceUri != '' || $query != '')
                        {
                            $getResult = new WCFDataServicesEntity($this->_containerScriptName, $this->_containerName, $this->_enablePaging, $this->_pageSize, $serviceUri, $query,
                                             $this->_proxy, $this->_port, $this->_userName, $this->_password);
                            if (isset($_POST['btnDelete']))
                            {
                                $getResult->Delete($_POST, $_GET['Type']);
                            }
                            else if (isset($_POST['btnUpdate']))
                            {
                                $getResult->Update($_POST, $_GET['Type']);
                            }
                            else if (isset($_POST['btnSave']))
                            {
                                $getResult->Insert($_POST, $_GET['Type']);
                            }
                            else if (isset($_POST['btnAddLink']))
                            {
                                $attachQuery = str_replace("\'", "'", $_GET['AttachQuery']);
                                $getResult->AddLink($_POST, $_GET['Type'], $attachQuery);
                            }
                            $getResult->display();

                            if (isset($_POST['btnDetail']))
                            {
                                if (isset($_GET['changeId']))
                                {
                                    $changeId = str_replace("\'", "'", $_GET['changeId']);
                                    $getResult->displayDetails($changeId, $_GET['Type']);
                                }
                            }
                            else if (isset($_POST['btnAdd']))
                            {
                                $getResult->displayAdd($_GET['Type']);
                            }
                            else if (isset($_GET['AttachTo']))
                            {
                                $attachTo = str_replace("\'", "'", $_GET['AttachTo']);
                                $getResult->displayAddLink($_GET['Type'], $attachTo);
                            }
                        }
                        else
                        {
                            $getResult = new WCFDataServicesEntity($this->_containerScriptName, $this->_containerName, $this->_enablePaging, $this->_pageSize, $this->_serviceUri, '',
                                             $this->_proxy, $this->_port, $this->_userName, $this->_password);
                            $getResult->display();
                        }
                        ?>
                    </div>
                </td>
            </tr>
        </table>
    </body>
</html>
<?php
    }
}
?>
