Introduction
============

This is an updated version of the OData SDK for PHP (published on https://odataphp.codeplex.com/) 
meant to work with OData version 3.0. The original version (dated Mar 15, 2010) only supported 
version 1.x and 2.0. In addition this version is developed on Linux; no more Windows problems ;-)

The motivation behind this work is to integrate data from Mediasite, a system used for video 
recordings at many universities in Norway (and the rest of the World), in other PHP applications
like Drupal.

The work is indirectly sponsored by [Molde University College](http://www.himolde.no/) since 
they pay my salary.

Installation Instructions
=========================

The assumption is that PHP is already installed and configured on the machine where the OData SDK for PHP is installed.
The SDK does not have any dependency on the host OS so it can run on Windows, Linux or Mac OSX machines.
The instructions below are written for Debian (verified for on Jessie / 8.1).

1. Create a folder that should contian the SDK, for instance, ```/usr/local/lib/php5/odataphp```.
2. Copy the all files and folders in the SDK to the folder created above. 
   In particular, the folder ```/usr/local/lib/php5/odataphp/framework``` should have the 
   content seen at bottom of this file (in the directory structure section).
3. Add the path to the framework folder (created in step 1/2) to the 'include_path' directive in php.ini e.g.

        ```
        include_path = ".:/usr/local/lib/php5/odataphp/framework"
        ```
4. Create a variable called 'ODataphp_path' in the php.ini file and set it to the
   path where the OData SDK for PHP was installed (step 1).
   Open php.ini and search for 'Paths and Directories' section. Just below the definition 
   of 'include_path' directive, add the following two lines:

        ```
        ; OData SDK for PHP Library Path
        ODataphp_path = "/usr/local/lib/php5/odataphp/framework"
        ```
5. Make sure you that the curl, xml and xsl modules are installed and enabled in PHP. 
   In Deian the xml module is always included in PHP. The curl and xsl modules can be 
   installed (and enabled) using apt-get as follows,

        ```
        apt-get install php5-curl php5-xsl
        ```
       
Usage Instructions
==================
After the installation is completed, you can generate the proxy class for any OData Service
that you want to connect to. Documentation on how to generate the proxy class and use the SDK
can be found in the User Manual document under the docs directory, 
e.g. ```/usr/local/lib/php5/odataphp/doc/User_Guide.htm```.

In addition it is probably useful to look at the samples located in ```/usr/local/lib/php5/odataphp/samples```.
     
Directorty Structure for the framework folder
=============================================

```
|- PHPDataSvcUtil.php [Proxy generation tool]
|
|-- Azure       [contain files for using OData toolkit library aganist Windows Azure tables]
|
|-- Common      [contains commonly used class definition files for dictionary, collection, 
|               guid, http proxy, reflection helper.Utility classes for Azure ACS and Azure
|               Table authentication and xsl file for code generation]
|
|-- Context     [Contains class definition files for context tracking, AtomPub generation,
|               query and stream processing files]
|
|-- Exception   [Contains class definition files for exceptions]
|
|-- Extras      [Contains class definition files for OData Service editor]
|
|-- Interfaces  [interface definitions]
|
|-- Parser      [AtomPub parser]
|
|-- Resource    [Resource file]
|
|-- WebUtil     [Utility files for handling normal and batch http request-response]
|
```
