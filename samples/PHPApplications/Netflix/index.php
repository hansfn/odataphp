<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Netflix - oData</title>
        <script type="text/javascript" >
          function searchByGenre() {
            if(searchForm.Genre.value != 'Undecided')
            {
              params = "Type=Genre&Genre=" + searchForm.Genre.value;
              search(params);
            }
          }

          function searchByLanguage() {
            if(searchForm.Language.value != 'Undecided')
            {
              params = "Type=Language&Language=" + searchForm.Language.value;
              search(params);
            }
          }

          function searchByName() {
            params = "Type=Name&Name=" + searchForm.Name.value;
            search(params);
          }

          function search(params) {
            xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
              if(xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                Results.innerHTML = xmlhttp.responseText;
              }
              else{
                Results.innerHTML = "<img alt='Loading' src='images/loading.gif' align='center' />";
              }
            }
            xmlhttp.open("POST","search.php",true);
            xmlhttp.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
            xmlhttp.send(params);
            clearSearch();
          }

          function getPage(page) {
            xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
              if(xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                Results.innerHTML = xmlhttp.responseText;
              }
              else{
                Results.innerHTML = "<img alt='Loading' src='images/loading.gif' align='center' />";
              }
            }
            xmlhttp.open("GET","" + page + ".php",true);
            xmlhttp.send(null);
          }

          function clearSearch() {
            searchForm.Genre.selectedIndex = 0
            searchForm.Language.selectedIndex = 0
            searchForm.Name.value = '';
          }

        </script>
    </head>
    <body bgcolor="b9090b">
      <form name="searchForm" method="POST" action="search.php">
      <?php
        require_once 'netflix.php';
        session_start();
        $_SESSION["token"] = null;

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

        $queryGenres = $netflix->Genres();
        $queryLanguages = $netflix->Languages();
        
        $_SESSION["current"] = 0;

        function GetGenres($query, $netflix){
          $genres = $query->Execute();

          echo "\n<option value='Undecided'>Undecided</option>";
          $token = null;
          do
          {
            if($token != null)
              $genres = $netflix->Execute($token);

            foreach($genres->Result as $genre){
              echo "\n<option value='" . $genre->Name . "'>$genre->Name</option>";
            }
          }while(($token = $genres->GetContinuation()) != null);
        }

        function GetLanguages($query, $netflix){
          $languages = $query->Execute();

          echo "\n<option value='Undecided'>Undecided</option>";
          $token = null;
          do
          {
            if($token != null)
              $languages = $netflix->Execute($token);

            foreach($languages->Result as $language){
              echo "\n<option value='" . $language->Name . "'>$language->Name</option>";
            }
          }while(($token = $languages->GetContinuation()) != null);
        }

        ?>
      <table border="1px solid gray" bgcolor="FDF8E2" width="900px;" align="center">
        <tr><td colspan="3" align="center">Search Movies</td></tr>
        <tr>
          <td width="50px;">Genre</td>
          <td><select name="Genre" onchange="searchByGenre()"><?php GetGenres($queryGenres, $netflix) ?></select></td>
        </tr>
        <tr>
          <td width="50px;">Language</td>
          <td><select name="Language"  onchange="searchByLanguage()"><?php GetLanguages($queryLanguages, $netflix) ?></select></td>
        </tr>
        <tr>
          <td width="50px;">Name</td>
          <td><input type="text" name="Name" /><input type="button" value="Search" onclick="searchByName()" /></td>
        </tr>
        <tr>
          <td width="50px;">Browse</td>
          <td colspan="2"><a href="javascript:void(0)" onclick="getPage('movies')">Movies</a>
            <br /><a href="javascript:void(0)" onclick="getPage('seasons')">Seasons</a>
            <br /><a href="moviePaging.php" >Paging Example</a></td>
        </tr>
      </table>
        <input type="hidden" name="Type" />
      <?php
        }
        catch(Exception $e){
          echo $e->getMessage();
        }
      ?>
        </form>
      <div id="Results" align="center" />
    </body>
</html>