<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
    <title>OData toolkit for PHP  for WCF Data Services</title>
    <link href="styles/styles.css" rel="stylesheet" type="text/css" />
</head>

<body>
    <!-- Javascript to collapse/hide contents  -->
    <script type="text/javascript">
        function swap(listIdPrefix) 
        {
            collapsedList = document.getElementById(listIdPrefix + "_collapsed");
            expandedList = document.getElementById(listIdPrefix + "_expanded");
            if (collapsedList.style.display == "block") 
            {
                collapsedList.style.display = "none";
                expandedList.style.display = "block";
            } 
            else 
            {
                collapsedList.style.display = "block";
                expandedList.style.display = "none";
            }
        } 
    </script>

    <!-- This Div is for style only, it should contain NO content -->
    <div id="backgroundStyle">
    &nbsp;</div>
    
    <!-- Wrapper for all page content -->
    <div id="pageBox">
        <!-- Header -->
        <div id="header">
            <span class="title">OData Toolkit for PHP</span>
            <div id="navigationLinks">
                <ul>
                    <?php
                    $links = array();
                    $links['Overview'] = 'index.php';
                    $links['Demo'] = 'Demo.php';
                    $links['Download'] = 'http://odataphp.codeplex.com/Release/ProjectReleases.aspx';
                    
                    foreach ($links as $key => $value)
					{
						?>
						<li <?php if ($key == $currentTab) { echo 'class=current';} ?> >
							<a class="navLinks" href="<?php echo $value; ?>"><?php echo $key; ?></a>
						</li>
						<?php
					}
                    ?>
                </ul>
            </div>
        </div>
        
        <!-- Section Navigation -->
        <div id="sectionHeader">
            <h2></h2>
        </div>
        
        <!-- Main Content -->
        <div id="content">
            <?php
                if ($long_page)
                {
                    echo '<a name="top"></a>';
                }
                echo $page_content;
                if ($long_page)
                {
                    echo '<!-- Link to top of page -->';
                    echo '<p><a href="#top">Top of Page</a></p>';
                }
            ?>
        </div>
    </div>
</body>

</html>
