Setup Instructions
==================
 1. Before you can use this application, you must complete the OData SDK for PHP setup as explained in the User Guide document in \docs or framework\readme.txt.
        
 2. This application uses VideoGameStore Data Service, for hosting this service see the /Samples/WCFDataServices/readme.txt

 3. Check the urldef.php file to make sure the VIDEO_GAME_SERVICE_URL constant is defined with the correct
    URL of the service deployed using Visual Studio. This urldef.php is shared by all php scripts in the application.

 5. Deploy the VideoGameStore web application on a web server and navigate to the index.php page.
  
  
Short Description of files
==========================
 1. index.php : This is the home page of VideoGameStore application.
 2. DisplayResult.php : This file is used for retrieving/Modifying VideoGameStore data.
 3. urldef.php: Defines VIDEO_GAME_SERVICE_URL that points to the underlying WCF Data Service.
 4. SaveGameProduct.php: Updates product information submitted by user to the database .