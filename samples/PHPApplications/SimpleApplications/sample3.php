<?php
/**
  *
  * Copyright (c) 2009, Persistent Systems Limited
  *
  * Redistribution and use, with or without modification, are permitted
  *  provided that the following  conditions are met:
  *   - Redistributions of source code must retain the above copyright notice,
  *     this list of conditions and the following disclaimer.
  *   - Neither the name of Persistent Systems Limited nor the names of its contributors
  *     may be used to endorse or promote products derived from this software
  *     without specific prior written permission.
  *
  * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
  * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
  * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
  * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
  * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
  * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
  * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS;
  * OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
  * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
  * OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
  * EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
  */
require_once 'NorthwindEntities.php';
require_once 'urldef.php';

echo "<h3>Sample3: List all cutomer's ID in NorthWind DB with USA as Country and associated Order's ID using LoadProperty and Server Side paging</h3>";

try
{
     $svc = new NorthwindEntities(NORTHWIND_SERVICE_URL);

     $query = $svc->Customers()->filter("Country eq 'USA'");
     $customerResponse = $query->Execute();

     $nextCustomerToken = null;
     do
     {
        if($nextCustomerToken != null)
        {
            $customerResponse = $svc->Execute($nextCustomerToken);
        }

        foreach($customerResponse->Result as $customer)
        {
             echo '<br/>CustomerID: ' . $customer->CustomerID . "<br/>";
             $nextOrderToken = null;

             echo "<br/>Associated Orders <br/>";
             echo "-----------------------<br/>";
             do
             {
                $ordersResponse = $svc->LoadProperty($customer, 'Orders', $nextOrderToken);

                foreach($customer->Orders as $order)
                {
                    echo "     " . $order->OrderID . "<br/>";
                }      
             }while(($nextOrderToken = $ordersResponse->GetContinuation()) != null);             
        }

     }while(($nextCustomerToken = $customerResponse->GetContinuation()) != null);

}
catch(DataServiceRequestException $ex)
{
        echo 'Error: while running the query ' . $ex->Response->getQuery();
        echo "<br/>";
        echo $ex->Response->getError();
}
catch (ODataServiceException $e)
{
    echo "Error:" . $e->getError() . "<br>" . "Detailed Error:" . $e->getDetailedError();
}
?>
