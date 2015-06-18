<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Netflix - oData</title>
  </head>
  <body bgcolor="b9090b">
      <?php
        require_once 'netflix.php';
        session_start();

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
          $token = null;

          if(key_exists("Last", $_GET)) {
            $_SESSION["current"] = $_SESSION["current"] - 1;
          }
          else if(key_exists("Next", $_GET)) {
            $_SESSION["current"] = $_SESSION["current"] + 1;
          }
          else {
            $_SESSION["current"] = 0;
          }
          $skip = 5 * $_SESSION["current"];
          $query = $netflix->Titles()->Filter("Type eq 'Movie'")->Top(5)->Skip($skip);
          $token = $query->RequestUri();
          $_SESSION["token"] = $token;
        ?>

    <div id="Loading" align="center" />
      <?php
        }
        catch(DataServiceRequestException $e){
          echo "Message: " . $e->getTraceAsString();
        }
      ?>
    </body>
    <script type="text/javascript">
      function onStartup() {
        xmlhttp = new XMLHttpRequest();
        xmlhttp.onreadystatechange = function() {
          if(xmlhttp.readyState == 4 && xmlhttp.status == 200) {
            Loading.innerHTML = xmlhttp.responseText;
          }
          else{
            Loading.innerHTML = "<img alt='Loading' src='images/loading.gif' />";
          }
        }
        xmlhttp.open("GET","showMovies.php",true);
        xmlhttp.send(null);
      }

      onStartup();
    </script>
</html>
