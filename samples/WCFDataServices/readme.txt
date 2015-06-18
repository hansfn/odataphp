Setup Instructions
==================

    The samples\SimpleApplication, samples\VideoGameStoreApplication and samples\WCFDataServiceEditor samples uses the
    following WCF Data Services.

    1. NorthWindDataServices.
            Used by Sample1.php to Sample10.php and Sample13.php applications in the SimpleApplication directory.
            Used by WCFDataServiceEditor application.
    2. ACSNorthWindDataServices.
            Used by the Sample11.php and Sample12.php applications in the the SimpleApplication directory.
    3. VideoGameStoreDataServices.
            Used by the VideoGameStoreApplication application.

    The following section decsribes how to configure the above WCF Services.

Steps to configure sample WCF Service
=====================================

[A] Install the NorthWind and VideoGameStore databases.
    --------------------------------------------------
    Step1 :  Install SQL Server Express 2008 [http://www.microsoft.com/downloads/details.aspx?displaylang=en&FamilyID=7522a683-4cb2-454e-b908-e805e9bd4e28]
    Step2 :  Extract the NorthWind_Data.zip and VideoGameStore_Data.zip (located under WCFDataServices\databases) into somehwhere in your disk.
             ex: D:\WCFServiceDataBases\NORTWND.MDF, VideoGameStore_Data.MDF
    Step3 :  Open 'SQL Server Management Studio'
    Step4 :  In 'Object Explorer', Right click on 'Databases' and select 'Attach...', this will bring up 'Attach Databases'
             Dialog box.
    Step5 :  Under 'Databases to Attach', click on 'Add..', this will bring up 'Locate Databases Files' browse dialog box
    Step6 :  Browse and Select NORTWND.MDF, then click 'OK', this will create a database with name 'NorthWind'
    Step7 :  Repeat the Steps4 to 6 for attaching 'VideoGameStore_Data.MDF' file.
    Step8 :  Drill down to 'Object Explorer' => 'Security' => 'Logins', right click and select 'New Login...'
    Step9 :  In the 'Login- New' window, Click on 'Search..', this will bring up 'Select User or Group' dialog box.
    Step10:  search for 'NETWORK SERVICE'and Click 'OK'.
    Step11:  Click 'OK' in 'Login - New' window.
    Step12:  From the 'Object Explorer', drill-down to Databases => NorthWind => Security => Users, right click and select 'New User...'
             this will bringup 'Database User - New' dialog box.
    Step13:  Give username as 'NETWORK_USER', click on '...', give name as 'NETWORK SERVICE' and click 'OK'
    Step14:  In the 'Database user - New' dialog box under 'Database Role MemberShip', select the following options:
                    db_owner
                    db_datareader
                    db_datawriter
                    db_accessadmin

              Click 'OK'
    Step15:  Repeat the steps from 8 to 14 for 'VideoGameStore' database.

[B] Host WCFServices on IIS
    -----------------------
    Step1 :  Open IIS Manager [Start => Programs => Administrative Tools => Internet Information Service (IIS) Manager].
    Step2 :  Drill-down to Connections => Site, right click and select 'Add Web Site...'
    Step3 :  Give site name as 'WCFServices' and physical path as path to ..\Samples\WCFDataServices directory
             Give port as 13985 [This port is used by all sample php applications]
    Step4 :  From the Web-Brower, browse to 'http://localhost:13985/'
             This will give link to three services

                Northwind DataService
                Northwind DataService [Access to this service requires ACS authentication]
                Video Game DataService

    Note: The build.cmd script can be used to build the solutions for the samples, if you do not have VS 2008 installed.               This script requires .NET Framework 3.5 to be installed.

[C] Running WCFServices in Visual Studio (alternative for Step B)
    ------------------------------------
    Step1 : Open/Compile the WCFDataServices project in the samples\WCFDataServices directory.
    Step2 : You can see three svc files (NorthwindDataService.svc, VideoGameStoreDataService.svc and ACSNorthwindDataService.svc)
    Step3 : Right click on either NorthwindDataService.svc or VideoGameStoreDataService.svc, select 'View in Browser'
            Note: you cannot browse to ACSNorthwindDataService.svc as it requires ACS authentication.

    IMPORTANT: Note that for Visual Studio users, the port has been set to '22973' in SLN file, so before running the
    sample applications change the port hardcoded in sample applications from '13985' to '22973'



