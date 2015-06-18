<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Netflix - oData</title>
  </head>
  <body bgcolor="b9090b">
      <?php
        require_once 'netflix.php';

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

        try {
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
        $query = $netflix->Titles()->Filter("Type eq 'Season'")->OrderBy("Name");
        $movies = $query->Execute();

        ?>
    <table border="1px solid gray" bgcolor="FDF8E2" width="900px;" align="center">
<?php
        foreach($movies->Result as $movie){
        echo "      <tr>\n<td>\n<a href='$movie->Url'>\n<img alt='" . $movie->Name .
                "' src='" . $movie->BoxArt->LargeUrl . "'>\n</a>\n</td>" .
                "\n<td>\n<p>\n<span style='font-size: larger;'>" . $movie->Name . "\n</span> " .
                "\n<span style='font-size: smaller;'>($movie->ReleaseYear)\n</span>\n</p>" .
                "\n$movie->Synopsis\n<br />\n" .
                "Rating:\n<span style='background: url(\"images/spite_Punk_Star.jpg\") repeat-x; height: 29px; " .
                SetWidth($movie->AverageRating) . "'>&nbsp;</span>, Runtime: " . RunTime($movie->Runtime) . "\n</td>\n</tr>\n";
        }
      ?>
      </table>
      <?php
        }
        catch(Exception $e){
          echo $e->getMessage();
        }
      ?>
    </body>
</html>
