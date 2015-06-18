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
  * This is asample application to show how to use LoadProperty function to
  * retrive Orders entity set associated to each customer entity instance.
  */
require_once 'NorthwindEntities.php';
require_once 'urldef.php';

echo "<h3>Sample10: Add a customer entity to Northwind DB with ID 'CHAN9' and CompanyName as 'Channel9', update the ComapnyName to 'channel8' and delete the customer</h3>";

try
{
    $svc = new NorthwindEntities(NORTHWIND_SERVICE_URL);

    $customer = Customers::CreateCustomers("CHAN9", "channel9");
    $svc->AddToCustomers($customer);
    $customer->CompanyName = "Channel8";
    $svc->UpdateObject($customer);
    $svc->DeleteObject($customer);
    $svc->SaveChanges();
    echo "User asked for adding Customer with CustomerID CHAN9 and CompanyName channe9, update CompanyName to channel8 then to delete the  customer (Since this will not affect the data service this queries wont be fired it will handled by context itself)";
  }
  catch (ADODotNETDataServicesException $e)
  {
    echo "Error:" . $e->getError() . "<br>" . "Detailed Error:" . $e->getDetailedError();
  }
?>
