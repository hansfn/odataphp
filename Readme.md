Introduction
============

> The OData SDK for PHP enables PHP developers to access data services that 
> use the OData protocol. Detailed information on the OData protocol and 
> the other SDKs available can be found at https://www.odata.org/. 

This is an updated version of the OData SDK for PHP meant to work with OData version 3.0. 
The original version (dated Mar 15, 2010) only supported version 1.x and 2.0. 

The SDK was originally published on Codeplex, and an 
[archived version of the project is still available](https://archive.codeplex.com/?p=odataphp).

The motivation behind this work is to integrate data from Mediasite, a system used for video 
recordings at many universities in Norway (and the rest of the World), in other PHP applications
like Drupal.

The initial work was indirectly sponsored by [Molde University College](http://www.himolde.no/) 
since they pay my salary.

Features
========

This list is taken from the original project and not updated or verified:

- Support for all new OData protocol features (Projections, Server-Side paging, Blobs, RowCounter and Customizable Feeds)
- Support for Azure authentication
- Better programming model with APIs for all Query Options
- More command line options
- Additional samples

Try the toolkit using some of the OData Services available on the internet:

- Services.odata.org: http://services.odata.org/OData/OData.svc/

Installation Instructions
=========================
These instrcutions are for PHP 5, but should work for PHP 7 too.

The assumption is that PHP is already installed and configured on the machine where the OData SDK for PHP is installed.
The SDK does not have any dependency on the host OS so it can run on Windows, Linux or Mac OSX machines.
The instructions below are written for Debian (verified on Debian Jessie / 8.1).

1. Get the source either by dowloading a [release](https://github.com/hansfn/odataphp/releases) or cloning from git. 
   In the following we assume that the SDK is unpacked / cloned into ```/usr/local/lib/php5/odataphp```.
   
   Cloning from Git:
   
       mkdir -p /usr/local/lib/php5
       cd /usr/local/lib/php5
       git clone https://github.com/hansfn/odataphp.git
3. Add the path to the framework folder to the include_path directive in php.ini e.g.

        include_path = ".:/usr/local/lib/php5/odataphp/framework"
4. Create a variable called 'ODataphp_path' in the php.ini file and set it to the
   path where the OData SDK for PHP was installed (step 1).
   Open php.ini and search for 'Paths and Directories' section. Just below the definition 
   of 'include_path' directive, add the following two lines:

        ; OData SDK for PHP Library Path
        ODataphp_path = "/usr/local/lib/php5/odataphp/framework"
5. Make sure you that the curl, xml and xsl modules are installed and enabled in PHP. 
   In Debian the xml module is always included in PHP. The curl and xsl modules can be 
   installed (and enabled) using apt-get as follows,

        apt-get install php5-curl php5-xsl

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
