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

/**
  *
  * This is a sample application to show how to delete an existing link between
  * Customers entity instance and Orders Entity Instance.
  */
require_once 'NorthwindEntities.php';
require_once 'urldef.php';

echo "<h3>Sample9: Delete the link added in Sample8 between a customer entity and Order entity</h3>";

try
{    
    $svc = new NorthwindEntities(NORTHWIND_SERVICE_URL);
    $response = $svc->Customers()
                    ->filter("CustomerID eq 'CHAN9'")
                    ->Expand('Orders')
                    ->Execute();
    if(count($response->Result))
    {
        foreach($response->Result as $customer)
        {
            foreach($customer->Orders as $order)
            {
                $svc->DeleteLink($customer, "Orders", $order);
                $svc->DeleteObject($order);
            }
        
            $svc->DeleteObject($customer);
            $svc->SaveChanges();
        }
        echo "All Order Links associated with customer with ID CHAN9 has been deleted, also the orders and customer has been deleted";
    }
    else
    {
        echo "Customer with CustomerID CHAN9 not found";
    }
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
catch(InvalidOperation $e)
{
    echo $e->getError();
}
?>