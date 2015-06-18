<?php
    $simpleApps = array();
    $simpleApps['RunSimpleSample.php?sampleFileName=./SimpleApplications/Sample1.php'] = "Sample1: List all cutomer's ID in NorthWind DB with USA as Country using Server Side Paging and print the number of customers using inline count";
    $simpleApps['RunSimpleSample.php?sampleFileName=./SimpleApplications/Sample2.php'] = "Sample2: List all cutomer's ID in NorthWind DB with USA as Country and associated Order's ID using expand option and Server Side paging";
    $simpleApps['RunSimpleSample.php?sampleFileName=./SimpleApplications/Sample3.php'] = "Sample3: List all cutomer's ID in NorthWind DB with USA as Country and associated Order's ID using LoadProperty and Server Side paging";
    $simpleApps['RunSimpleSample.php?sampleFileName=./SimpleApplications/Sample4.php'] = "Sample4: Usage of Row Count";
    $simpleApps['RunSimpleSample.php?sampleFileName=./SimpleApplications/Sample5.php'] = "Sample5: Add a customer entity to Northwind DB with ID 'CHAN9' and CompanyName as 'Channel9'";
    $simpleApps['RunSimpleSample.php?sampleFileName=./SimpleApplications/Sample6.php'] = "Sample6: Update the ComapnyName of customer entity added in Sample5 to 'Channel8'";
    $simpleApps['RunSimpleSample.php?sampleFileName=./SimpleApplications/Sample7.php'] = "Sample7: Delete the customer entity with ID 'CHAN9'";
    $simpleApps['RunSimpleSample.php?sampleFileName=./SimpleApplications/Sample8.php'] = "Sample8: Add a link between a customer entity and Order entity";
    $simpleApps['RunSimpleSample.php?sampleFileName=./SimpleApplications/Sample9.php'] = "Sample9: Delete the link added in Sample8 between a customer entity and Order entity";
    $simpleApps['RunSimpleSample.php?sampleFileName=./SimpleApplications/Sample10.php'] = "Sample10: SaveChanges() API  has been used to add a Customers entity, update CompanyName and Delete the same customer";
  $simpleApps['RunSimpleSample.php?sampleFileName=./SimpleApplications/Sample11.php'] = "Sample11: Shows how to use register call-back feature to access service which requires ACS authentication.";
  $simpleApps['RunSimpleSample.php?sampleFileName=./SimpleApplications/Sample12.php'] = "Sample12: Shows how to use the toolkit's ACSCredential class  to access service which requires ACS authentication.";
  $simpleApps['RunSimpleSample.php?sampleFileName=./SimpleApplications/Sample13.php'] = "Sample13: Shows how to use projection through select query option.";
?>

<h4>Simple PHP Applications</h4>
<p>Following simple applications demonstrates usage of OData Toolkit for PHP. Click <a href="http://localhost:13985/NorthwindDataService.svc/">here</a> to view underlying WCF Data Service.</p>
<ul>
<?php
    foreach ($simpleApps as $key => $value)
    {
?>      
    <li><a href="<?php echo $key; ?>"><?php echo $value; ?></a></li>
<?php
    }
?>
</ul>
<br/>
        
<h4>WCF Data Services Editor Sample</h4>
<p>Click <a href="WCFDataServicesEditor/index.php">here</a> to display the WCF Data Services
Editor for <a href="http://localhost:13985/NorthWindDataService.svc/">Northwind WCF Data Service.</a>
</p>
<h4>Video Game Store Application</h4>
<p>Click <a href="VideoGameStoreApplication/index.php">here</a> to view the Video Game Store Application 
for <a href="http://localhost:13985/VideoGameStoreDataService.svc/">Video Game WCF Data Service.</a>
</p>
<h4>Netflix catalog Sample Application</h4>
<p>Click <a href="netflix/index.php">here</a> for the Netflix catalog sample application.
Simple browser App for <a href="http://odata.netflix.com/catalog">Netflix Catalog OData Service</a>
</p>
<br/>
