<?php
/**   
  *
  * Copyright (c) 2010, Persistent Systems Limited
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
  * This is a sample application to Execute a query against NorthWind data service
  * that will fetch customer entity set with Country property equal to 'UK' and to
  * display the returned entity set in browser. 
  */
$currentTab = "Demo";
$contentFileName = dirname(__FILE__) . $_GET['sampleFileName'];
$templateFilename =  dirname(__FILE__) . './templates/1_column_template.php';
$long_page = true;

if (is_file($contentFileName)) 
{
    ob_start();
    include $contentFileName;
    $page_content = ob_get_contents();
    $page_content = $page_content . GetFileContent($contentFileName);
    ob_end_clean();

    include $templateFilename;
}

/**
  *
  * This function gets source code of specific file
  */
function GetFileContent($fileName)
{
    $fileContent = '<br/><br/><div id="source_code_collapsed" style="display:block">';
    $fileContent = $fileContent . '<a href="javascript:swap(\'source_code\')">Click to view the source code</a>';
    $fileContent = $fileContent . '</div>';
    $fileContent = $fileContent . '<div id="source_code_expanded" style="display:none;color:green">';
    $fileContent = $fileContent . '<a href="javascript:swap(\'source_code\')">Click to hide the source code</a><br/>';
    $fileContent = $fileContent .  str_replace("\n", "<br/>", file_get_contents($fileName), $count) . '</div>';

    return $fileContent;
}
?>
