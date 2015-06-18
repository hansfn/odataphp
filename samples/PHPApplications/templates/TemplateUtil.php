<?php
function ShowContentUsingTemplate($currentTab, $contentFileName, $templateFilename, $long_page)
{
    if (is_file($contentFileName)) 
    {
        ob_start();
        include $contentFileName;
        $page_content = ob_get_contents();
        ob_end_clean();

        include $templateFilename;
    }
}
?>
