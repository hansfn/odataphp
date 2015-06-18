Installation Instructions
=========================

The assumption is that PHP is already installed and configured on the machine where the OData SDK for PHP is installed.
The SDK does not have any dependency on the host OS so it can run on Windows, Linux or Mac OSX machines.

1. Create a folder named, for instance, 'ODataphp' eg: C:\PHPLib\ODataphp

2. Copy the files and folders in the framework folder to the folder created above. Now your 'ODataphp' look like below:

C:\PHPLib\ODataphp
------------------------
	|
	|--- PHPDataSvcUtil.php
	|--- Azure
	|--- Common
	|--- Context
	|--- Credential
	|--- Exception
	|--- Extras
	|--- Interfaces
	|--- Parser
	|--- Resource
	|--- WebUtil

	
3. Add the path to the folder created in step 1 to the 'include_path' directive in php.ini
	e.g.
	include_path = ".;C:\PHPLib\ODataphp"

4. Create a variable called 'ODataphp_path' in the php.ini file and set it to the path where the OData toolkit for PHP was installed (step 1).
   Open php.ini and search for 'Paths and Directories' section. Just below the definition of 'include_path' directive, add the following two lines:
   ;OData toolkit for PHP Library Path
   ODataphp_path = "C:\PHPLib\ODataphp"
   
5. On Linux platform, make sure you have the php-xml module installed. This can be installed using yum as follows,
   yum install php-xml

6. Enable php_xsl.dll in php.ini. 
   Search for 'extension=php_xsl.dll' in the php.ini file and remove the semicolon (;) in front.      

7. Enable php_curl.dll in php.ini. 
   Search for 'extension=php_curl.dll' in the php.ini file and remove the semicolon (;) in front.      

       
Usage Instructions
==================
After the installation is completed, you can generate the proxy class for any OData Service
that you want to connect to. Documentation on how to use the OData toolkit for PHP can be found in the User Manual
document under the \docs directory.
     
Directorty Structure
====================

|-- PHPDataSvcUtil.php [Proxy generation tool]
|
|-- framework
        |
        |--Azure       [contain files for using OData toolkit library aganist Windows Azure tables]
        |
        |--Common      [contains commonly used class definition files for dictionary, collection, 
        |               guid, http proxy, reflection helper.Utility classes for Azure ACS and Azure
        |               Table authentication and xsl file for code generation]
        |
        |--Context     [Contains class definition files for context tracking, AtomPub generation,
        |               query and stream processing files]
        |
        |--Exception   [Contains class definition files for exceptions]
        |
        |--Extras      [Contains class definition files for OData Service editor]
        |
        |--Interfaces  [interface definitions]
        |
        |--Parser      [AtomPub parser]
        |
        |--Resource    [Resource file]
        |
        |--WebUtil     [Utility files for handling normal and batch http request-response]
        |
