<?php
session_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!--
Copyright (c) 2009, Persistent Systems Limited

Redistribution and use, with or without modification, are permitted 
provided that the following  conditions are met:
- Redistributions of source code must retain the above copyright notice, 
this list of conditions and the following disclaimer.
- Neither the name of Persistent Systems Limited nor the names of its contributors 
may be used to endorse or promote products derived from this software 
without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, 
THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR 
PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR 
CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, 
EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, 
PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; 
OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, 
WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR 
OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, 
EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
-->

<!--
Home page for VideoGame Data Service Client.
-->
<?php
// Include service url definiation
require_once 'urldef.php';

?>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <style type="text/CSS">
            .borderimage
            {
                border:3px solid black;
            }
            
            .borderimageSelected
            {
                border:3px solid yellow;
            }

            button
            {
                background-color: rgb(186,85,211);
                border-style : solid;
                border-left-color : rgb(238,130,238);
                border-top-color : rgb(238,130,238);
                border-right-color : rgb(128,0,128);
                border-bottom-color : rgb(128,0,128);
                color : rgb(250,250,226)
            }
            div
            {
                scrollbar-face-color:#000000;
                scrollbar-shadow-color:#632984;
                scrollbar-highlight-color:#632984;
                scrollbar-3dlight-color:#130919;
                scrollbar-darkshadow-color:#130919;
                scrollbar-track-color:#130919;
                scrollbar-arrow-color:#C2A2DA
            }
        </style>

        <script type="text/JavaScript">
            // Show border around image
            function borderImage(imageObject, color)
            {
                if (document.all || document.getElementById)
                {                
                    imageObject.style.borderColor = color;
                }
            }

            function showPressed(ID)
            {
                document.getElementById(ID).style.borderLeftColor = "rgb(128,0,128)";
                document.getElementById(ID).style.borderTopColor = "rgb(128,0,128)";
                document.getElementById(ID).style.borderRightColor = "rgb(238,130,238)";
                document.getElementById(ID).style.borderBottomColor = "rgb(238,130,238)";
            }
            function unPressed(ID)
            {
                document.getElementById(ID).style.borderLeftColor = "rgb(238,130,238)";
                document.getElementById(ID).style.borderTopColor = "rgb(238,130,238)";
                document.getElementById(ID).style.borderRightColor = "rgb(128,0,128)";
                document.getElementById(ID).style.borderBottomColor = "rgb(128,0,128)";
            }

            function SaveGameProduct()
            {
                var url = "SaveGameProduct.php";

                element = document.getElementById('id');
                productID = element.value;

                element = document.getElementById('Description');
                description = element.innerHTML;
                description = decodeSpecialChars(description);

                element = document.getElementById('SelectRating');
                selectRating =  element.options[element.selectedIndex].value;
                selectRating = selectRating.replace(/\+/g, "%2B");

                element = document.getElementById('SelectGenre');
                selectGenre =  element.options[element.selectedIndex].value;

                element = document.getElementById('Text3');
                developer =  element.value;

                element = document.getElementById('Text2');
                listPrice =  element.value;

                element = document.getElementById('Text1');
                releaseDate =  element.value;

                var params = "ProductID="+ productID + "&Description="+description+"&Rating="+selectRating+"&Genre="+selectGenre+"&Developer="+developer+"&ListPrice="+listPrice+"&ReleaseDate="+releaseDate;

                httpObject = getHTTPObject();
                if (window.ActiveXObject) 
                {
                    httpObject.open("POST", url, false);
                }
                else if (window.XMLHttpRequest) 
                {
                    httpObject.open("POST", url, true);
                }
                
                httpObject.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                httpObject.setRequestHeader("Content-length", params.length);
                httpObject.setRequestHeader("Connection", "close");

                httpObject.onreadystatechange = function() 
                {
                    if (httpObject.readyState == 4 && httpObject.status == 200) 
                    {
                        if (httpObject.responseText == "success") 
                        {
                            alert("Updated!");
                        }
                        else 
                        {
                            alert(httpObject.responseText);
                        }

                    }
                }
                httpObject.send(params);
            }
            
            function SetBorder(id, index)
            {
                var imageid = 'image_' + id;
                if (document.all || document.getElementById)
                {
                    borderImage(document.getElementById(imageid), "yellow");
                    if (oldImageObject != null)
                    {
                        borderImage(document.getElementById(imageid), "black");
                    }
                }                
                document.getElementById('scrollDiv').scrollTop = index*150; 
            }
            
            var id;
            var oldImageObject;
            function LoadGame(newImageObject, txt)
            {
                if (document.all || document.getElementById)
                {
                    borderImage(newImageObject, "yellow");
                    if (oldImageObject != null)
                    {
                        borderImage(oldImageObject, "black");
                    }
                }
                
                productID = document.getElementById('id');
                productID.value = txt;

                httpObject = getHTTPObject();
                if (httpObject != null)
                {
                    query = "GetProduct.php" + "?" + "ProductID=" + txt +"&" + "PropertyName=" + "ProductName";
                    if (window.ActiveXObject) 
                    {
                        httpObject.open("GET", query, false);
                    }
                    else if (window.XMLHttpRequest) 
                    {
                        httpObject.open("GET", query, true);
                    }
                    id = txt;
                    oldImageObject = newImageObject;
                    httpObject.onreadystatechange = setProductName;
                    httpObject.send(null);
                }
                else
                {
                    alert("Could not load game!");
                }
            }

            function setProductName()
            {
                if (httpObject.readyState == 4)
                {
                    refreshCache();
                    productName = document.getElementById('ProductName');
                    productName.innerHTML = httpObject.responseText;
                    query = "GetProduct.php" + "?" + "ProductID=" + id +"&" + "PropertyName=" + "ProductDescription";
                    if (window.ActiveXObject) 
                    {
                        httpObject.open("GET", query, false);
                    }
                    else if (window.XMLHttpRequest) 
                    {
                        httpObject.open("GET", query, true);
                    }
                    httpObject.onreadystatechange = setDescription;
                    httpObject.send(null);
                }
            }

            function setDescription()
            {
                if (httpObject.readyState == 4)
                {
                    refreshCache();
                    description = document.getElementById('Description');
                    description.innerHTML = trim(httpObject.responseText);
                    query = "GetProduct.php" + "?" + "ProductID=" + id + "&" + "PropertyName=" + "ListPrice";
                    if (window.ActiveXObject) 
                    {
                        httpObject.open("GET", query, false);
                    }
                    else if (window.XMLHttpRequest) 
                    {
                        httpObject.open("GET", query, true);
                    }
                    httpObject.onreadystatechange = setListPrice;
                    httpObject.send(null);
                }
            }

            function setListPrice()
            {
                if (httpObject.readyState == 4)
                {
                    refreshCache();
                    ListPrice = document.getElementById('Text2');
                    ListPrice.value = "$" + trim(httpObject.responseText);
                    query = "GetGame.php" + "?" + "ProductID=" + id +"&" + "PropertyName=" + "Rating";
                    if (window.ActiveXObject) 
                    {
                        httpObject.open("GET", query, false);
                    }
                    else if (window.XMLHttpRequest) 
                    {
                        httpObject.open("GET", query, true);
                    }
                    httpObject.onreadystatechange = setRating;
                    httpObject.send(null);
                }

            }

            function setRating()
            {
                if (httpObject.readyState == 4)
                {
                    refreshCache();
                    SelectRating = document.getElementById('SelectRating');
                    selVal = httpObject.responseText;
                    for (var i = 0; i < SelectRating.options.length; i++)
                    {
                        decoded = SelectRating.options[i].value.replace(/%20/g, " ");
                        if (trim(decoded) == trim(selVal))
                        {
                            SelectRating.options[i].selected= true;
                            break;
                        }
                    }
                    query = "GetGame.php" + "?" + "ProductID=" + id +"&" + "PropertyName=" + "Genre";
                    if (window.ActiveXObject) 
                    {
                        httpObject.open("GET", query, false);
                    }
                    else if (window.XMLHttpRequest) 
                    {
                        httpObject.open("GET", query, true);
                    }
                    httpObject.onreadystatechange = setGenre;
                    httpObject.send(null);
                }
            }

            function setGenre()
            {
                if (httpObject.readyState == 4)
                {
                    refreshCache();
                    SelectGenre = document.getElementById('SelectGenre');
                    selVal = httpObject.responseText;
                    for (var i = 0; i < SelectGenre.options.length; i++)
                    {
                        decoded = SelectGenre.options[i].value.replace(/%20/g, " ");
                        if (trim(decoded) == trim(selVal))
                        {
                            SelectGenre.options[i].selected= true;
                            break;
                        }
                    }
                    
                    query = "GetGame.php" + "?" + "ProductID=" + id +"&" + "PropertyName=" + "Developer";
                    if (window.ActiveXObject) 
                    {
                        httpObject.open("GET", query, false);
                    }
                    else if (window.XMLHttpRequest) 
                    {
                        httpObject.open("GET", query, true);
                    }
                    httpObject.onreadystatechange = setDeveloper;
                    httpObject.send(null);
                }
            }

            function setDeveloper()
            {
                if (httpObject.readyState == 4)
                {
                    refreshCache();
                    Developer = document.getElementById('Text3');
                    Developer.value = trim(httpObject.responseText);

                    query = "GetProduct.php" + "?" + "ProductID=" + id +"&" + "PropertyName=" + "ReleaseDate";
                    if (window.ActiveXObject) 
                    {
                        httpObject.open("GET", query, false);
                    }
                    else if (window.XMLHttpRequest) 
                    {
                        httpObject.open("GET", query, true);
                    }
                    httpObject.onreadystatechange = setReleaseDate;
                    httpObject.send(null);
                }
            }

            function setReleaseDate()
            {
                if (httpObject.readyState == 4)
                {
                    refreshCache();
                    ReleaseDate = document.getElementById('Text1');
                    ReleaseDate.value = trim(httpObject.responseText);

                    query = "GetProduct.php" + "?" + "ProductID=" + id +"&" + "PropertyName=" + "ProductImageUrl";
                    if (window.ActiveXObject) 
                    {
                        httpObject.open("GET", query, false);
                    }
                    else if (window.XMLHttpRequest) 
                    {
                        httpObject.open("GET", query, true);
                    }
                    httpObject.onreadystatechange = setProductIamge;
                    httpObject.send(null);
                }
            }

            function setProductIamge()
            {
                if (httpObject.readyState == 4)
                {                    
                    document.getElementById('gameicon').src = trim(httpObject.responseText);
                }
            }
            
            function trim(str, chars) 
            {
                return ltrim(rtrim(str, chars), chars);
            }

            function ltrim(str, chars) 
            {
                chars = chars || "\\s";
                return str.replace(new RegExp("^[" + chars + "]+", "g"), "");
            }

            function rtrim(str, chars) 
            {
                chars = chars || "\\s";
                return str.replace(new RegExp("[" + chars + "]+$", "g"), "");
            }

            function getHTTPObject()
            {
                if (window.ActiveXObject) 
                {
                    return new ActiveXObject("Microsoft.XMLHTTP");
                }
                else if (window.XMLHttpRequest) 
                {
                    return new XMLHttpRequest();
                }
                else 
                {
                    alert("Your browser does not support AJAX!");
                    return null;
                }
            }

            function refreshCache()
            {
                if (!httpObject.getResponseHeader("Date")) 
                {
                    var cached = httpObject;
                    httpObject =  getHTTPObject();
                    var ifModifiedSince = cached.getResponseHeader("Last-Modified");
                    ifModifiedSince = (ifModifiedSince) ?
                        ifModifiedSince : new Date(0); // January 1, 1970
                    if (window.ActiveXObject) 
                    {
                        httpObject.open("GET", query, false);
                    }
                    else if (window.XMLHttpRequest) 
                    {
                        httpObject.open("GET", query, true);
                    }
                    httpObject.setRequestHeader("If-Modified-Since", ifModifiedSince);
                    httpObject.send("");
                    if (httpObject.status == 304) 
                    {
                        httpObject = cached;
                    }
                }
            }

            function decodeSpecialChars(str)
            {
                var histogram = {};
                histogram   = get_html_translation_table('HTML_ENTITIES', 'ENT_NOQUOTES');

                for (symbol in histogram) 
                {
                    dec = ord(symbol);
                    hex = "\\u00" + dec.toString(16);
                    str = str.replace( new RegExp(symbol, "g" ), hex);;
                }
                
                return str;
            }

            function get_html_translation_table(table, quote_style) 
            {
                var entities = {}, histogram = {}, decimal = 0, symbol = '';
                var constMappingTable = {}, constMappingQuoteStyle = {};
                var useTable = {}, useQuoteStyle = {};

                useTable      = (table ? table.toUpperCase() : 'HTML_SPECIALCHARS');
                useQuoteStyle = (quote_style ? quote_style.toUpperCase() : 'ENT_COMPAT');

                // Translate arguments
                constMappingTable[0]      = 'HTML_SPECIALCHARS';
                constMappingTable[1]      = 'HTML_ENTITIES';
                constMappingQuoteStyle[0] = 'ENT_NOQUOTES';
                constMappingQuoteStyle[2] = 'ENT_COMPAT';
                constMappingQuoteStyle[3] = 'ENT_QUOTES';

                // Map numbers to strings for compatibilty with PHP constants
                if (!isNaN(useTable)) 
                {
                    useTable = constMappingTable[useTable];
                }
                if (!isNaN(useQuoteStyle)) 
                {
                    useQuoteStyle = constMappingQuoteStyle[useQuoteStyle];
                }

                if (useQuoteStyle != 'ENT_NOQUOTES') 
                {
                    entities['34'] = '&quot;';
                }

                if (useQuoteStyle == 'ENT_QUOTES') 
                {
                    entities['39'] = '&#039;';
                }

                if (useTable == 'HTML_SPECIALCHARS') 
                {
                    // ascii decimals for better compatibility
                    entities['38'] = '&amp;';
                    entities['60'] = '&lt;';
                    entities['62'] = '&gt;';
                } 
                else if (useTable == 'HTML_ENTITIES') 
                {
                    // ascii decimals for better compatibility
                    entities['38']  = '&amp;';
                    entities['60']  = '&lt;';
                    entities['62']  = '&gt;';
                    entities['160'] = '&nbsp;';
                    entities['161'] = '&iexcl;';
                    entities['162'] = '&cent;';
                    entities['163'] = '&pound;';
                    entities['164'] = '&curren;';
                    entities['165'] = '&yen;';
                    entities['166'] = '&brvbar;';
                    entities['167'] = '&sect;';
                    entities['168'] = '&uml;';
                    entities['169'] = '&copy;';
                    entities['170'] = '&ordf;';
                    entities['171'] = '&laquo;';
                    entities['172'] = '&not;';
                    entities['173'] = '&shy;';
                    entities['174'] = '&reg;';
                    entities['175'] = '&macr;';
                    entities['176'] = '&deg;';
                    entities['177'] = '&plusmn;';
                    entities['178'] = '&sup2;';
                    entities['179'] = '&sup3;';
                    entities['180'] = '&acute;';
                    entities['181'] = '&micro;';
                    entities['182'] = '&para;';
                    entities['183'] = '&middot;';
                    entities['184'] = '&cedil;';
                    entities['185'] = '&sup1;';
                    entities['186'] = '&ordm;';
                    entities['187'] = '&raquo;';
                    entities['188'] = '&frac14;';
                    entities['189'] = '&frac12;';
                    entities['190'] = '&frac34;';
                    entities['191'] = '&iquest;';
                    entities['192'] = '&Agrave;';
                    entities['193'] = '&Aacute;';
                    entities['194'] = '&Acirc;';
                    entities['195'] = '&Atilde;';
                    entities['196'] = '&Auml;';
                    entities['197'] = '&Aring;';
                    entities['198'] = '&AElig;';
                    entities['199'] = '&Ccedil;';
                    entities['200'] = '&Egrave;';
                    entities['201'] = '&Eacute;';
                    entities['202'] = '&Ecirc;';
                    entities['203'] = '&Euml;';
                    entities['204'] = '&Igrave;';
                    entities['205'] = '&Iacute;';
                    entities['206'] = '&Icirc;';
                    entities['207'] = '&Iuml;';
                    entities['208'] = '&ETH;';
                    entities['209'] = '&Ntilde;';
                    entities['210'] = '&Ograve;';
                    entities['211'] = '&Oacute;';
                    entities['212'] = '&Ocirc;';
                    entities['213'] = '&Otilde;';
                    entities['214'] = '&Ouml;';
                    entities['215'] = '&times;';
                    entities['216'] = '&Oslash;';
                    entities['217'] = '&Ugrave;';
                    entities['218'] = '&Uacute;';
                    entities['219'] = '&Ucirc;';
                    entities['220'] = '&Uuml;';
                    entities['221'] = '&Yacute;';
                    entities['222'] = '&THORN;';
                    entities['223'] = '&szlig;';
                    entities['224'] = '&agrave;';
                    entities['225'] = '&aacute;';
                    entities['226'] = '&acirc;';
                    entities['227'] = '&atilde;';
                    entities['228'] = '&auml;';
                    entities['229'] = '&aring;';
                    entities['230'] = '&aelig;';
                    entities['231'] = '&ccedil;';
                    entities['232'] = '&egrave;';
                    entities['233'] = '&eacute;';
                    entities['234'] = '&ecirc;';
                    entities['235'] = '&euml;';
                    entities['236'] = '&igrave;';
                    entities['237'] = '&iacute;';
                    entities['238'] = '&icirc;';
                    entities['239'] = '&iuml;';
                    entities['240'] = '&eth;';
                    entities['241'] = '&ntilde;';
                    entities['242'] = '&ograve;';
                    entities['243'] = '&oacute;';
                    entities['244'] = '&ocirc;';
                    entities['245'] = '&otilde;';
                    entities['246'] = '&ouml;';
                    entities['247'] = '&divide;';
                    entities['248'] = '&oslash;';
                    entities['249'] = '&ugrave;';
                    entities['250'] = '&uacute;';
                    entities['251'] = '&ucirc;';
                    entities['252'] = '&uuml;';
                    entities['253'] = '&yacute;';
                    entities['254'] = '&thorn;';
                    entities['255'] = '&yuml;';
                } 
                else 
                {
                    throw Error("Table: "+useTable+' not supported');
                    return false;
                }

                // ascii decimals to real symbols
                for (decimal in entities) 
                {
                    symbol = String.fromCharCode(decimal)
                    histogram[symbol] = entities[decimal];
                }

                return histogram;
            }

            function ord( string ) 
            {
                return (string+'').charCodeAt(0);
            }
        </script>        
        <title>Video Game Store</title>
    </head>

    <?php
    require_once 'DisplayResult.php';
    ?>

    <body style="left: 100px; width: 600px; position: relative;
          height: 600px; background-color:#000000">
        <div style="width: 800px; position: relative; height: 600px; left: 100px;">
            <table style="width: 100%">
                <tr>
                    <td style="height: 21px; width: 60%; color: #ff6666; font-family: Curlz MT; font-size: 32pt">
                        VIDEO GAME STORE
                    </td>
                    <td style="height: 21px; width: 30%">
                    </td>
                </tr>
                <tr style="height: 90%">
                    <td colspan="3" style="border-right: #ff9966 thin solid; border-top: #ff9966 thin solid;
                        border-left: #ff9966 thin solid; border-bottom: #ff9966 thin solid; height: 100%">
                        <table>
                            <tr style="height: 500px;">
                                <td style="width: 175px; height: 480px;">
                                    <div id="scrollDiv" style="height: 100%; border-right: #ff9966 1px solid; border-top: #ff9966 1px solid;
                                         border-left: #ff9966 1px solid; border-bottom: #ff9966 1px solid; overflow: auto;">
                                        <?php
                                        $id = -1;
                                        $displayResult = new DisplayResult(VIDEO_GAME_STORE_SERVICE_URL);
                                        $resultRetrieved = FALSE;
                                        if (isset($_GET['operation'])) 
                                        {
                                            if (isset($_GET['id'])) 
                                            {
                                                $id = $_GET['id'];
                                                $_SESSION['resultSet'] = $displayResult->Save($id, $_POST['Description'], $_POST['Rating'],
                                                    $_POST['Genre'], $_POST['Developer'], $_POST['ListPrice'], $_POST['ReleaseDate']);
                                                $displayResult->DisplayImages($id);
                                                $resultRetrieved = TRUE;
                                            }
                                        }

                                        if ($resultRetrieved == FALSE) 
                                        {
                                            if (isset($_GET['id'])) 
                                            {
                                                $id = $_GET['id'];
                                            }
                                            
                                            $_SESSION['resultSet'] = $displayResult->DisplayImages($id);
                                        }
                                        ?>
                                    </div>
                                    <?php
                                        if (isset($_GET['id'])) 
                                        {
                                            $id = $_GET['id'];

                                            $index = $_GET['index'];
                                    ?>
                                            <script type="text/JavaScript">
                                            <?php echo "SetBorder(".$id.",".$index.")";?>
                                            </script>
                                    <?php
                                        }
                                        else
                                        {
                                            // Default index
                                            $index = 0;
                                        }
                                    ?>
                                </td>
                                <td style="height: 470px; width: 10px">
                                </td>
                                <td style="height: 480px; width: 600px">
                                    <div style="height: 100%; border-right: #ff9966 1px solid; border-top: #ff9966 1px solid;
                                         border-left: #ff9966 1px solid; border-bottom: #ff9966 1px solid; overflow: auto;">
                                        <table style="height: 98%; width: 98%">
                                            <tr>
                                                <form action="<?php echo "index.php?operation=save&id=" .$_SESSION['resultSet']->ProductID."&index=".$index; ?>" method="post" >
                                                    <input type="hidden" name="id" id="id" value="<?php echo $_SESSION['resultSet']->ProductID;?>"/>
                                                    <td style="width: 60%">
                                                        <table style="width: 100%">
                                                            <tr>
                                                                <td name="ProductName" id="ProductName" style="height: 25px; width: 50%; color: #ff9966; font-size: 18pt; vertical-align: top;
                                                                    font-family: Calibri" align="center">
                                                                        <?php echo $_SESSION['resultSet']->ProductName ?>
                                                                </td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td style="height: 125px; width: 50%; color: #ff9966; font-size: 14pt; vertical-align: top;
                                                                    font-family: Calibri">
                                                                    Description<br />
                                                                    <textarea name="Description" id="Description" cols="50" rows="6" 
                                                                        style="background-color: #ffdead; font-size: 13pt;font-family: Calibri;"><?php 
                                                                        echo ltrim($_SESSION['resultSet']->ProductDescription); ?></textarea>
                                                                </td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td style="height: 25px; width: 50%; color: #ff9966; font-size: 13pt; ;font-family: Calibri; vertical-align: top;
                                                                    font-family: Calibri">
                                                                    Rating<br />
                                                                        <?php DisplayResult::displayRatingDropDown($_SESSION['resultSet']->Game[0]->Rating);
                                                                    ?>
                                                                </td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td style="height: 25px; width: 50%; color: #ff9966; font-size: 14pt; vertical-align: top;
                                                                    font-family: Calibri">
                                                                    Genre<br />
                                                                        <?php DisplayResult::displayGenreDropDown($_SESSION['resultSet']->Game[0]->Genre);
                                                                    ?>
                                                                </td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td style="height: 25px; width: 50%; color: #ff9966; font-size: 14pt; vertical-align: top;
                                                                    font-family: Calibri">
                                                                    Developer<br />
                                                                    <input id="Text3" name="Developer" type="text" style="width: 100%; background-color: #ffdead;font-size: 12pt; ;font-family: Calibri;"  value="<?php echo $_SESSION['resultSet']->Game[0]->Developer ?>"/>
                                                                </td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td style="height: 25px; width: 50%; color: #ff9966; font-size: 14pt; vertical-align: top;
                                                                    font-family: Calibri">
                                                                    List Price<br />
                                                                    <input id="Text2" name="ListPrice" type="text" style="width: 100%; background-color: #ffdead;font-size: 12pt; ;font-family: Calibri;" value="<?php echo "$" .$_SESSION['resultSet']->ListPrice ?>"/>
                                                                </td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td style="height: 24px; width: 50%; color: #ff9966; font-size: 14pt; vertical-align: top;
                                                                    font-family: Calibri">
                                                                    Release Date<br />
                                                                    <input id="Text1" name="ReleaseDate" type="text" style="width: 100%; background-color: #ffdead;font-size: 12pt; ;font-family: Calibri;" value="<?php echo DisplayResult::getFormatedDate($_SESSION['resultSet']->ReleaseDate) ?>"/>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                    <td style="width: 10%"></td>
                                                    <td style="height: 100%; width: 25%">
                                                        <table style="height: 100%; width: 100%">
                                                            <tr>
                                                                <td style="height: 10%; width: 100%;">
                                                                </td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td style="height: 80%; width: 100%; vertical-align: top;" align="center">
                                                                    <img id="gameicon" src="<?php echo $_SESSION['resultSet']->ProductImageUrl ?>" alt="1" />
                                                                </td>
                                                            </tr>
                                                            
                                                            <tr>
                                                                <td align="right">
                                                                    <button type="submit" name="btnSave" onmousedown="showPressed('btnSave')" onmouseup="unPressed('btnSave')" >
                                                                    <!--<button type="button" name="btnSave" onmousedown="showPressed('btnSave')" onmouseup="unPressed('btnSave')" 
                                                                             onclick="SaveGameProduct()" />-->
                                                                            <b>Save</b></button>                                                                                                                   
                                                                </td>
                                                            </tr>
                                                        </table>
                                                    </td>
                                                </form>
                                            </tr>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    </body>
</html>
