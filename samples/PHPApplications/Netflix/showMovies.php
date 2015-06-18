<?php
  session_start();
  if(key_exists("current", $_SESSION)) {
    function RunTime($runtime){
          $hours = intval($runtime / 3600);
          $minutes = ($runtime / 60) % 60;
          if($minutes < 10)
            $minutes = "0" . $minutes;

          return $hours . ":" . $minutes;
        }

        function SetWidth($rating){
        $imageWidth = 29;
        $newWidth = $imageWidth * $rating;

        return "width: " . $newWidth . "px;";
        }

    require_once 'netflix.php';

    $netflix = new NetflixCatalog();
    $settings = parse_ini_file("app.config", 1);
    if (!empty($settings["HTTP_PROXY_HOST"]))
    {
        $netflix->HttpProxy = new HttpProxy(
            $settings["HTTP_PROXY_HOST"],
            $settings["HTTP_PROXY_PORT"],
            $settings["HTTP_PROXY_USER_NAME"],
            $settings["HTTP_PROXY_PASSWORD"]);
    }
    $dsQueryContinuation = new DataServiceQueryContinuation($_SESSION["token"]);
    $movies = $netflix->Execute($dsQueryContinuation);

    echo '<table border="1px solid gray" bgcolor="FDF8E2" width="900px;" align="center" >';

    echo "<tr><td colspan='2'><div align='center'>";
    //if($_SESSION["token"] != null)
      echo "<input type='button' value='>>' style='float: right;' onclick='window.location=\"moviePaging.php?Next=true\";' />";
    if($_SESSION["current"] != 0)
      echo "<input type='button' value='<<' style='float: left;' onclick='window.location=\"moviePaging.php?Last=true\";' />";
    echo "<input type='button' value='Home' onclick='window.location=\"index.php\";' /></div></td></tr>";

    foreach($movies->Result as $movie){
      echo "\n      <tr>\n<td>\n<a href='$movie->Url'>\n<img alt='" . $movie->Name .
           "' src='" . $movie->BoxArt->LargeUrl . "'>\n</a>\n</td>" .
           "\n<td>\n<p>\n<span style='font-size: larger;'>" . $movie->Name . "\n</span> " .
           "\n<span style='font-size: smaller;'>($movie->ReleaseYear)\n</span>\n</p>" .
           "\n$movie->Synopsis\n<br />\n" .
           "Rating:\n<span style='background: url(\"images/spite_Punk_Star.jpg\") repeat-x; height: 29px; " .
           SetWidth($movie->AverageRating) . "'>&nbsp;</span>, Runtime: " . RunTime($movie->Runtime) . "\n</td>\n</tr>\n";
    }

    echo "</table>\n";
  }
  else {
    header( 'Location: /' );
    die();
  }
?>
