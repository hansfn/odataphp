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
  * This file shows the usage of WCFDataServicesEditor
  * class by using NorthWind WCF Data Service.
  */                    
require_once 'NorthwindEntities.php';
require_once 'Extras/WCFDataServicesEditor.php';

/*
 * URL for Northwind WCF Data Service
 */
define("NORTHWIND_SERVICE_URL", "http://localhost:13985/NorthWindDataService.svc");

try 
{
    $editor = new WCFDataServicesEditor("./index.php", "NorthwindEntities", true, 4, NORTHWIND_SERVICE_URL);
    $editor->show();  
} 
catch (ODataServiceException $e)
{
    echo "Error:" . $e->getError() . "<br>" . "Detailed Error:" . $e->getDetailedError();
}
catch(DataServiceRequestException $ex)
{
    echo 'Error while running the query:' . $ex->Response->getQuery();
    echo $ex->Response->getError();
}
catch(InvalidOperation $ex)
{
    echo $ex->getError();
}
?>
         
