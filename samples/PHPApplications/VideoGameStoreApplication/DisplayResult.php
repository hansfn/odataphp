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
  * This file contains the definition of DisplayResult class.
  */ 
  
require_once 'VideoGameStoreEntities.php';

/**
  * The DisplayResult Class.
  *
  * @copyright  2008 Persistent System Limited
  */
class DisplayResult 
{
    /**
     * Variable to hold proxy instance.
     */
    protected $_obj;
    
    /**
     * This variable will hold collection of top 25
     * products
     */
    protected $_resultSet;
    
    /**
     * The array holding rating collection
     */
    protected static $_ratingArray = array('E (Everyone)',
                                          'E10 (Everyone 10+)',
                                          'T (Teen)',
                                          'M (Mature)',
                                          'RP (Rating Pending)');
    
    /**
     * The array holding genre collection
     */
    protected static $_genreArray = array('Action',
                                         'Adventure',
                                         'Card and Board',
                                         'Coin-Op Classics',
                                         'Compilations',
                                         'Console Classics',
                                         'Family',
                                         'Family Games',
                                         'Fighting',
                                         'Music',
                                         'Platform',
                                         'Puzzle and Word',
                                         'Racing',
                                         'Roleplaying',
                                         'RPG',
                                         'Shooter',
                                         'Simulation',
                                         'Sports',
                                         'Sports Games',
                                         'Strategy',
                                         'Strategy and Sims',
                                         'Xbox LIVE Arcade',
                                         'Xbox Originals' );

    /**
     * Constructs a new DisplayResult object.
     * @param string $uri uri of the video game service
     */
    public function __construct($uri)
    {
        $this->_obj = new VideoGameStoreEntities($uri);
        try
        {
			$response  = $this->_obj->Execute("Product?\$top=25");
			$this->_resultSet = $response->Result;   
		}
		catch (ADODotNETDataServicesException $e) 
		{
			echo "Error:" . $e->getError() . "<br>" . "Detailed Error:" . $e->getDetailedError();
			exit(1);
		}
    }

    /**
     * Constructs a new DisplayResult object.
     * @param int $Id The Id of product
     * @return string $returnValue The product object with
     * ProductID $id
     */
    public function DisplayImages($Id = -1)
    {
        $returnValue;
        $found = FALSE;
        echo "<table style=\"width:90%\">";
        $i = 0;
        foreach ($this->_resultSet as $product) 
        {
            if (strpos($product->ProductImageUrl, "noimage.jpg") == FALSE) 
            {
                if ($product->ProductID == $Id) 
                {
                    $returnValue = $product;
                } 
                else if ($found == FALSE) 
                {                    
                    $returnValue = $product;
                }
                echo "<tr align=\"center\" style=\"width: 100%; height: 150px\"><td>";               
                /*
                echo "<input class=\"borderimage\" src=\"" 
                     . $product->ProductImageUrl
                     . "\"type=\"image\" onclick=\"LoadGame(this, ".$product->ProductID . ")\"";                
                echo "/>";
                */
                echo "<form method=\"POST\" action=index.php?id="
                     . $product->ProductID . "&index=" . $i . ">";
                echo "<input class=\"borderimage\" id=\"image_".$product->ProductID."\" src=\"" 
                     . $product->ProductImageUrl
                     . "\"type=\"image\"\"/>";
                echo "</form>";  
                echo "</td></tr>";
                $found = TRUE;
                $i++;
            }
        }
        echo "</table>";
        $this->_obj->LoadProperty($returnValue, 'Game');
        return $returnValue;
    }

    /**
     * Function to display rating drop-down.
     * @param string $rating The rating to be selected.
     */
    public static function displayRatingDropDown($rating)
    {
        echo "<select name=\"Rating\" id=\"SelectRating\" "
              . "style=\"width: 100%;background-color: #ffdead;"
              . " font-size: 12pt; ;font-family: Calibri;\">";
        foreach (DisplayResult::$_ratingArray as $rate) 
        {
            if ($rate == $rating) 
            {
                echo "<option value="
                        . str_replace(' ', '%20', $rate)
                        . " Selected>" . $rate
                        . "</option>";
            } 
            else 
            {
                echo "<option value=" 
                     . str_replace(' ', '%20', $rate)
                     . ">"
                     . $rate
                     . "</option>";
            }
        }
        echo  "</select>";
    }

    /**
     * Function to display Genre drop-down.
     * @param string $genre The genre to be selected.
     */
    public static function displayGenreDropDown($genre) 
    {
        echo "<select  name=\"Genre\" id=\"SelectGenre\" "
             . "style=\"width: 100%; background-color: #ffdead;"
             . "font-size: 12pt; ;font-family: Calibri;\">";
        foreach (DisplayResult::$_genreArray as $gen) 
        {
            if ($gen == $genre) 
            {
                echo "<option value=" 
                     . str_replace(' ', '%20', $gen)
                     . " Selected>"
                     . $gen
                     . "</option>";
            } 
            else 
            {
                echo "<option value=" 
                     . str_replace(' ', '%20', $gen)
                     . ">"
                     . $gen
                     . "</option>";
            }
        }
        echo  "</select>";
    }

    /**
     * Function to format the date.
     * @param string $date The date to be formatted.
     * @return string $date The formatted date.
     */
    public static function getFormatedDate($date)
    {
        $pos = strpos($date, "T");
        if ($pos != 0) 
        {
            return substr($date, 0, $pos);
        }
        return $date;
    }

    /**
     * Function to save user changes
     * @param string $id The Product key
     * @param string $desc The Product Description
     * @param string $rating The Game rating
     * @param string $genre The Game genere
     * @param string $developer The Game Developer
     * @param string $listPrice The Product Price
     * @param string $releaseDate The Product Release date
     */
    public function Save($id, $desc, $rating, $genre, $developer, $listPrice, $releaseDate)
    {	
		try
		{	
			$response = $this->_obj->Execute("Product(" . $id . ")" );
			$products = $response->Result;
			foreach ($products as $product) 
			{
				$product->ProductDescription = $desc;
				$product->ListPrice = str_replace('$', '', $listPrice);
				$product->ReleaseDate = $releaseDate;
				$product->ProductVersion = $product->ProductVersion;
				$this->_obj->UpdateObject($product);
			}
			$this->_obj->SaveChanges();        
			$response = $this->_obj->Execute("Game?\$filter=ProductID eq " . $id );;
			$games = $response->Result;
			foreach ($games as $game) 
			{
				$game->Rating = str_replace('%20', ' ', $rating);
				$game->Genre = str_replace('%20', ' ', $genre);
				$game->Developer = $developer;
				$game->GameVersion = $game->GameVersion;
				$this->_obj->UpdateObject($game);
			}
			$this->_obj->SaveChanges();
			$products[0]->Game = $games;
			return $products[0];
        }
        catch(ADODotNETDataServicesException $e)
        {
			 echo "Error: " . $e->getError() . "<br>" . "Detailed Error: " . $e->getDetailedError();
        }
    }
}
?>
