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
  * This file is used to save the Game/Product data
  */
require_once 'VideoGameStoreEntities.php';

// Include service url definiation
require_once 'urldef.php';

$proxy = new VideoGameStoreEntities(VIDEO_GAME_STORE_SERVICE_URL);
$success = false;

$product = new Product;
$product->ProductID = $_POST['ProductID'];
$product->ProductDescription = $_POST['Description'];
$product->ListPrice = str_replace('$', '', $_POST['ListPrice']);
$product->ReleaseDate = $_POST['ReleaseDate'];

try 
{
    if ($proxy->UpdateObject($product)) 
    {
        $success = true;     
    }
} 
catch (ADODotNETDataServicesException $e)
{   
    $error1 = $e->getError();
    $derror1 = $e->getDetailedError();
}

$game = new Game;
$game->ProductID = $_POST['ProductID'];
$game->Rating = $_POST['Rating'];
$game->Genre = $_POST['Genre'];
$game->Developer = $_POST['Developer'];
try 
{
    if ($proxy->UpdateObject($game)) 
    {
         $success = true;
    }       
} 
catch (ADODotNETDataServicesException $e)
{
    $error2 = $e->getError();
    $derror2 = $e->getDetailedError();
}

if ($success) 
{
    echo "success";
} 
else 
{
    echo $error1 . $derror1 . $error2 . $derror2;
}
?>
