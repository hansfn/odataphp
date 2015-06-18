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

require_once 'WebUtil/Microsoft_Http_Response.php';
require_once 'WebUtil/HttpResponse.php';
require_once 'Common/ACSUtil.php';

/*
 * @copyright  Copyright (c) 2010, Persistent Systems Limited (http://www.persistentsys.com)
 * @license    http://odataphp.codeplex.com/license
 */
try
{
    $util = new PHPSvcUtil($argv);
    $util->generateProxy();
    $options = $util->getOptions();
    echo "\n" . 'Done: OData Service Proxy File \'' . $options['/out_filename'] .
         '\' generated at ' . $options['/out_dir'] . "\n";
}
catch (Exception $e)
{
    $util->showUsageAndExit($e->getMessage());
}

class PHPSvcUtil
{
    protected $_validOptions = array('/config', '/uri', '/metadata', '/out', '/u', '/p', '/sn', '/at', '/auth', '/ph', '/pp', '/pu', '/ppwd', '/ups');
    protected $_auths = array('windows', 'acs');
    protected $_cmdArgs;
    protected $_options;
    protected $_metadataDoc;
    protected static $namespaces = array(
                                    'EDM_2007_05' => 'http://schemas.microsoft.com/ado/2007/05/edm',
                                    'EDM_2006_04' => 'http://schemas.microsoft.com/ado/2006/04/edm',
                                    'EDM_2008_09' => 'http://schemas.microsoft.com/ado/2008/09/edm'
                                 );
    protected static $QUERY_EDM_2007_05_ENTITYCONTAINER = '//EDM_2007_05:EntityContainer';
    protected static $QUERY_EDM_2006_04_ENTITYCONTAINER = '//EDM_2006_04:EntityContainer';
    protected static $QUERY_EDM_2008_09_ENTITYCONTAINER = '//EDM_2008_09:EntityContainer';
    protected static $_messages = array(
                            'ServicePath_Not_Set' => "The configuration option 'ODataphp_path' is not set in the php.ini file, Please refer installation instructions for fix this issue",
                            'Request_Error' => 'Request for metadata failed; ',
                            'Cannot_Repeat_Option' => 'Option cannot be repeated: ',
                            'Invalid_Path_Usage' => "Using '/uri' and '/metadata' together not allowed",
                            'Missing_Service_Path' => "Valid OData service uri or service metadata file is required",
                            'Auth_Option_Missing1' => 'Using authentication type \'windows\' requires /u and /p to be present',
                            'Auth_Option_Missing2' => 'Using authentication type \'acs\' requires /u /p /sn and /at to be present',
                            'Invalid_Auth_Type' => 'value of auth option is not valid',
                            'Invalid_Proxy_Option' => 'Using \'/ph\' requires \'/pp\' to be present',
                            'Invalid_Option_Format' => 'Make sure the format of all commandline options are \'parameter=value\'',
                            'Invalid_Config_File' => 'The configuration file is not valid',
                            'Invalid_Config_File_Path' => 'The configuration file not found'
                            );
     protected static $default_proxy_file = 'proxy.php';
     protected static $CONTENT_TYPE_ATOM = 'application/atom+xml,application/xml';

    /**
     * Construct PHPSvUtil instance.
     *
     * @param array $options
     */
    public function PHPSvcUtil($options)
    {
        unset($options[0]);
        $this->_cmdArgs = $options;
    }

    /**
     *
     * @return array
     * Retruns options (command line and additional options)
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Generate the proxy class
     *
     */
    public function generateProxy()
    {
        $this->_validateAndBuidOptions();

        $xsl_path = get_cfg_var('ODataphp_path');

        if (strlen($xsl_path) == 0)
        {
            throw new Exception(self::$_messages['ServicePath_Not_Set']);
        }

        $xsl_path = $xsl_path . "/" . "Common/WCFDataServices2PHPProxy.xsl";
        $xslDoc = new DOMDocument();
        $xslDoc->load($xsl_path);

        $proc = new XSLTProcessor();
        $proc->importStylesheet($xslDoc);
        $proc->setParameter('', 'DefaultServiceURI',
                            $this->_options['/uri_withoutSlash']);

        $this->_metadataDoc = new DOMDocument();
        if(!empty($this->_options['/metadata']))
        {
            $this->_metadataDoc->load($this->_options['/metadata']);
        }
        else
        {
            $metadata = $this->_getMetaDataOverCurl();
            $this->_metadataDoc->loadXML($metadata);
        }

        $proc->transformToURI($this->_metadataDoc,
                              $this->_options['/out_dir'] .
                              "\\" . $this->_getFileName());
    }

    /**
     * To retrive the service metadata using Curl.
     *
     * @return string
     */
    protected function _getMetaDataOverCurl()
    {
        $curlHandle = curl_init();
        curl_setopt($curlHandle, CURLOPT_URL,
                    $this->_options['/uri_withoutSlash'] .
                    '/' .
                    '$metadata');
        curl_setopt($curlHandle, CURLOPT_HEADER, true);
        curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curlHandle, CURLOPT_SSL_VERIFYPEER, false);
        
        if(isset($this->_options['/auth']))
        {
            switch($this->_options['/auth'])
            {
                case 'windows':
                    curl_setopt($curlHandle, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                    curl_setopt($curlHandle, CURLOPT_USERPWD, $this->_options['/u'] .
                                                          ":" .
                                                          $this->_options['/p']);
                break;

                case 'acs':
                    try
                    {
                        $proxy = null;

                        if(isset($this->_options['/ph']))
                        {
                            $proxy = new HttpProxy($this->_options['/ph'],
                                                   $this->_options['/pp'],
                                                   $this->_options['/pu'],
                                                   $this->_options['/ppwd']);
                        }

                        $acsutil = new ACSUtil($this->_options['/sn'],
                           $this->_options['/u'],
                           $this->_options['/p'],
                           $this->_options['/at'],
                           array(),
                           $proxy);
                       $token = $acsutil->GetACSToken();
                       $authHeaderValue = 'WRAP access_token="' .
                                          urldecode($token) . '"';
                       curl_setopt($curlHandle, CURLOPT_HTTPHEADER,
                                   array('authorization: '. $authHeaderValue));
                    }
                    catch(ACSUtilException $exception)
                    {
                        $error = str_replace("<br/>", "\n", $exception->getError());
                        throw new Exception($error);
                    }

                    break;
            }
        }

        if(isset($this->_options['/ph']) &&
           $this->_options['/ups'] == 'yes')
        {
            curl_setopt($curlHandle, CURLOPT_PROXY,
                        $this->_options['/ph'] . ":" . $this->_options['/pp']);

            if(isset($this->_options['/pu']))
            {
            	curl_setopt($curlHandle, CURLOPT_PROXYUSERPWD,
                            $this->_options['/pu'] . ":" . $this->_options['/ppwd']);
                curl_setopt($curlHandle, CURLOPT_HTTPPROXYTUNNEL, 1);
            }
        }

        $httpRawResponse = curl_exec($curlHandle);
        
        if (!$httpRawResponse)
        {
            throw new Exception(self::$_messages['Request_Error'] .
                                 curl_error($curlHandle));
        }

        $httpResponse = HttpResponse::fromString($httpRawResponse);
        if($httpResponse->isError())
        {
            $exception = 'Message:' . $httpResponse->getMessage();
            $exception .= "\n\n";
            $exception .= $httpResponse->getBody();
            throw new Exception($exception);
        }

        return($httpResponse->getBody());
    }

    /**
     * To get the name of the proxy class.
     * Name of the proxy class can be:
     *   a. Return user specified proxy file name using /out parameter
     *   b. If user not specified any file name then return EntitiyContainer
     *          name as proxy file name.
     *   c. If the above two fails then return default proxy file name
     *
     * @return string
     */
    protected function _getFileName()
    {
        if(!empty($this->_options['/out_filename']))
        {
            return $this->_options['/out_filename'];
        }

        $containerName = null;
        $xPath = new DOMXPath($this->_metadataDoc);
        foreach (self::$namespaces as $prefix => $namespaceURI)
        {
            $xPath->registerNamespace($prefix, $namespaceURI);
        }

        $entityContainers = $xPath->query(self::$QUERY_EDM_2007_05_ENTITYCONTAINER);
        if($entityContainers->length)
        {
            $containerName = $this->_getAttribute($entityContainers->item(0),
                                                  'Name');
        }
        else
        {
            $entityContainers = null;
            $entityContainers = $xPath->query(self::$QUERY_EDM_2006_04_ENTITYCONTAINER);
            if($entityContainers->length)
            {
                $containerName = $this->_getAttribute($entityContainers->item(0),
                                                      'Name');
            }
            else
            {
                $entityContainers = null;
                $entityContainers = $xPath->query(self::$QUERY_EDM_2008_09_ENTITYCONTAINER);
                if($entityContainers->length)
                {
                    $containerName = $this->_getAttribute($entityContainers->item(0),
                                                          'Name');
                }
            }
        }

        if($containerName)
        {
            $this->_options['/out_filename'] = $containerName . '.php';
        }
        else
        {
            $this->_options['/out_filename'] = self::$default_proxy_file;
        }

        return $this->_options['/out_filename'];
    }

    /**
     * To get value of the attrribute '$attributeName' in the DOMNode '$node'
     * if attribute not found return null.
     *
     * @param DOMNode $node
     * @param string $attributeName
     * @return string
     */
    protected function _getAttribute($node, $attributeName)
    {
        $attributes = $node->attributes;
        foreach ($attributes as $attribute)
        {
            if ($attribute->nodeName == $attributeName)
            {
                return $attribute->value;
            }
        }

        return null;
    }

    /**
     * To display message, usage and exit
     *
     * @param string $message
     */
    public function showUsageAndExit($message = null, $inConfig = false)
    {
        echo "Description:\n\n";
        echo "The Proxy Generation Tool can used in two ways, either you can specify required options in a confiuration file or pass required options as commandline arguments\n\n";
        echo "Usage:\n\n";
        echo "php PHPDataSvcUtil.php /config:<config file>";
        echo "\n\n";
        echo "php PHPDataSvcUtil.php /uri=<data service Uri> | /metadata=<service metadata file> [/out=<output file path>] [/auth=windows|acs /u=username /p=password [/sn=servicenamespace /at=applies_to] ] [/ph=proxy-host /pp=proxy-port [/pu=proxy-user /ppwd=proxy-password]]\n";
        echo "\n\n Parameters:";
        echo "\n  /config";
        echo "\n                    <file>";
        echo "\n                    Configuration file";
        echo "\n  /uri";
        echo "\n                    OData Service Uri";
        echo "\n  /metadata";
        echo "\n                    <file>";
        echo "\n                    Path to OData Service metadata file";
        echo "\n";
        echo "\n                    * Either /uri or /metadata is required";
        echo "\n";
        echo "\n  /out";
        echo "\n                    <file>|<dir>";
        echo "\n                    Target Path (default:Current directory)";
        echo "\n  /auth";
        echo "\n                    windows|acs";
        echo "\n                    Authentication type required to access the OData Service";
        echo "\n  /u";
        echo "\n                    User name (Required for windows|acs authentication)";
        echo "\n                    * domain\username: If authentication type is 'windows'";
        echo "\n                    * scope: If authentication type is 'acs'";
        echo "\n  /p";
        echo "\n                    Password (Required for windows|acs authentication)";
        echo "\n                    * Windows password: If authentication type is 'windows'";
        echo "\n                    * issuer-key: If authentication type is 'acs'";
        echo "\n  /sn";
        echo "\n                    ACS Service namespace (Required for acs authentication)";
        echo "\n  /at";
        echo "\n                    Applies To (Required for acs authentication)";
        echo "\n  /ph";
        echo "\n                    Http Proxy Host";
        echo "\n  /pp";
        echo "\n                    Http Proxy Port";
        echo "\n";
        echo "\n                    * /ph and /pp are required if you are running behind a http proxy";
        echo "\n  /pu";
        echo "\n                    Http Proxy user name";
        echo "\n  /ppwd";
        echo "\n                    Http Proxy password";
        echo "\n";
        echo "\n                    * If proxy server requires credentials";
        echo "\n /ups";
        echo "\n                    yes|no";
        echo "\n                    Use Proxy for Service request";
        print "\n\n                    * By default the user specified proxy settings will be used
                      while requesting metadata from OData Service. If you are using
                      ACS auth and access to your service not require any proxy settings
                      (e.g. service running locally) then set this flag to no, /ups=no";
        echo "\n";
        $m = '';
        if($inConfig) { $m = ' {Check your configuration file}';}
        echo $message? "\nError:" . $message . $m . "!\n\n" : "\n";
        exit;

    }

    /**
     * Validate the commandline arguments and return the options and additional
     * details as array of key value pair.
     *
     * @return array
     */
    protected function _validateAndBuidOptions()
    {
        $inConfig = false;

        $this->_options = array();

        if(count($this->_cmdArgs) == 0)
        {
            $this->showUsageAndExit();
        }

        //If one arg check its config path
        if(count($this->_cmdArgs) == 1)
        {
             $pieces = explode('=', $this->_cmdArgs[1], 2);

             if(empty($pieces[0]) || empty($pieces[1]))
             {
                $this->showUsageAndExit(self::$_messages['Invalid_Option_Format']);
             }

             if($pieces[0] == '/config')
             {
                if(!file_exists($pieces[1]))
                {
                    $this->showUsageAndExit(self::$_messages['Invalid_Config_File_Path']);
                }

                $options = @parse_ini_file($pieces[1]);

                if($options !== FALSE)
                {
                    unset($this->_cmdArgs);
                    //ups option is yes/no type, pares_ini_file return it as
                    //1/null so process it here.
                    if(array_key_exists('/ups', $options))
                    {
                        if($options['/ups'] == '')
                            $options['/ups'] = 'no';
                        else
                            $options['/ups'] = 'yes';
                    }

                    foreach($options as $key => $value)
                    {
                        $this->_cmdArgs[] = $key . '=' . $value;
                    }

                    $inConfig = true;
                }
                else
                {
                    $this->showUsageAndExit(self::$_messages['Invalid_Config_File']);
                }
             }
        }

        foreach($this->_cmdArgs as $option)
        {
            //$option = strtolower($option);
            $pieces = explode('=', $option, 2);

            if(empty($pieces[0]) || empty($pieces[1]))
            {
                $this->showUsageAndExit(self::$_messages['Invalid_Option_Format'],
                                        $inConfig);
            }

            if(!in_array($pieces[0], $this->_validOptions))
            {
                $this->showUsageAndExit("The option '$pieces[0]', is not valid",
                                        $inConfig);
            }

            if(array_key_exists($pieces[0], $this->_options))
            {
                $this->showUsageAndExit(self::$_messages['Cannot_Repeat_Option'] . $pieces[0],
                                        $inConfig);
            }

            if(($pieces[0] == '/uri' && array_key_exists('/metadata', $this->_options)) ||
               ($pieces[0] == '/metadata' && array_key_exists('/uri', $this->_options)))
            {
                $this->showUsageAndExit(self::$_messages['Invalid_Path_Usage'], $inConfig);
            }

            $this->_options[$pieces[0]] = $pieces[1];
        }

        if(!array_key_exists('/uri', $this->_options) &&
           !array_key_exists('/metadata', $this->_options))
        {
             $this->showUsageAndExit(self::$_messages['Missing_Service_Path'],
                                     $inConfig);
        }

        if(isset($this->_options['/auth']) &&
           !in_array($this->_options['/auth'], $this->_auths))
        {
            $this->showUsageAndExit(self::$_messages['Invalid_Auth_Type'],
                                    $inConfig);
        }

        if(isset($this->_options['/auth']))
        {
            switch($this->_options['/auth'])
            {
                case 'windows':
                    if(!isset($this->_options['/u']) ||
                       !isset($this->_options['/p']))
                    {
                        $this->showUsageAndExit(self::$_messages['Auth_Option_Missing1'],
                                                $inConfig);
                    }
                    break;
                case 'acs':
                    if(!isset($this->_options['/u']) || !isset($this->_options['/p']) ||
                       !isset($this->_options['/sn']) || !isset($this->_options['/at']))
                    {
                        $this->showUsageAndExit(self::$_messages['Auth_Option_Missing2'],
                                                $inConfig);
                    }
                    break;
            }
        }

        if(isset($this->_options['/ph']) && !isset($this->_options['/pp']))
        {
            $this->showUsageAndExit(self::$_messages['Invalid_Proxy_Option'],
                                    $inConfig);
        }

        if(!isset($this->_options['/pu']))
        {
            $this->_options['/pu'] = null;
            $this->_options['/ppwd'] = null;
        }

        if(!array_key_exists('/out', $this->_options))
        {
            $this->_options['/out'] = '.';
        }

        $this->_options['/metadata'] = isset($this->_options['/metadata']) ?
                                       $this->_options['/metadata'] : null;
        $this->_options['/uri'] = isset($this->_options['/uri']) ?
                                  $this->_options['/uri'] : null;
        $this->_options['/uri_withoutSlash'] = null;
        $this->_options['/out_filename'] = null;

        $path_parts = pathinfo($this->_options['/out']);
        $this->_options['/out_dir'] = rtrim($path_parts['dirname'], "\\");
        if(isset($path_parts['extension']) && !empty($path_parts['extension']))
        {
            $this->_options['/out_filename'] = $path_parts['basename'];
        }
        else
        {
            if($path_parts['basename'] != '.')
                $this->_options['/out_dir'] .= "\\" . $path_parts['basename'];
        }

        if(!empty($this->_options['/uri']))
        {
            $this->_options['/uri_withoutSlash'] = rtrim($this->_options['/uri'], "/");
            $this->_options['/uri'] = $this->_options['/uri_withoutSlash'] . '/';
        }

        if(!isset($this->_options['/ups']))
        {
            $this->_options['/ups'] = 'yes';
        }
    }
}
?>