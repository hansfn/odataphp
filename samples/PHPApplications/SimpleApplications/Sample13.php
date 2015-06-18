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



echo "<h3>Sample13: Shows how to use projection through select query option</h3>";
try
{
     $svc = new NorthwindEntities();
     $query = $svc->Customers()->filter("Country eq 'USA'")
                               ->Select('CustomerID,CompanyName');
     $customersResponse = $query->Execute();
     foreach($customersResponse->Result as $customer)
     {
         echo "CustomerID:" . $customer->CustomerID . "<br/>";
         echo "Company Name:" . $customer->CompanyName . "<br/>";
         echo "Country:" .$customer->Country . "(This will be null as we selected only CustomerID and CompanyName)" . "<br/>";
         echo "----------" . "<br/>";
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
?>
