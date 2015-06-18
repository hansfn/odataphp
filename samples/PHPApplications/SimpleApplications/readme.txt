Setup Instructions
==================
    This folder contain collection of simple PHP applications, which shows how to use OData Toolkit for PHP library
    to peform various operations aganist WCF NorthWind and ACSNorthWind data services.

    In order to use these samples, you need to host the sample WCF services in /Samples/WCFDataServices (see /Samples/WCFDataServices/readme.txt) 

Short Description of Sample Application Files
=============================================

    Sample1.php :  List all cutomer's ID in NorthWind DB with USA as Country using Server Side Paging and print the number of coustmers using inline count
    Sample2.php :  List all cutomer's ID in NorthWind DB with USA as Country and associated Order's ID using expand option and Server Side paging
    Sample3.php :  List all cutomer's ID in NorthWind DB with USA as Country and associated Order's ID using LoadProperty and Server Side paging
    Sample4.php :  Usage of Row Count
    Sample5.php :  Add a customer entity to Northwind DB with ID 'CHAN9' and CompanyName as 'Channel9'
    Sample6.php :  Update the CompanyName of customer entity added in Sample5 to 'Channel8'
    Sample7.php :  Delete the customer entity with ID 'CHAN9'
    Sample8.php :  Add a link between a customer entity and Order entity
    Sample9.php :  Delete the link added in Sample8 between a customer entity and Order entity
    Sample10.php:  Add a customer entity to Northwind DB with ID 'CHAN9' and CompanyName as 'Channel9', update the ComapnyName to 'channel8' and delete the customer
    Sample11.php:  This application retrieve and display information of a customer with ID ALFKI from a service which requires ACS authentication. This application
                   register a callback function using 'OnBeforeRequest'  API, so that before sending any request to the service this call-back will be invoked. From this call-back, application will
                   connect to ACS to retrieve the acs token, uses this token connects to the service.
    Sample12.php:  This application is same as sample11 but it uses 'ACSCredential' class instead of call-back mechanism.
    Sample13.php:  This application shows how to use projection feature.

    NorthWindEntities.php: The proxy file generated using PHPDataSvcUtil.php for WCF NorthWind Data Service.

    ACSNorthWindEntities.php: The proxy file generated using PHPDataSvcUtil.php for WCF NorthWind Data Service which requires ACS authentication.

    ACSSettingsForProxyGeneration.ini: The command-line proxy generation configuration file for generating ACSNorthWindEntities.php
    e.g. C:\PHPLib\ODataphp>php PHPDataSvcUtil.php /config=%your path to simpleapplications directory/ACSSettingsForProxyGeneration.ini

