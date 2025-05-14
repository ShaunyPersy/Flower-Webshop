<?php
    session_start();
    include("./functions.php");
    set_error_handler("errorHandler");
    
    $host = "localhost";
    $user = "Webuser";
    $password = "Lab2021";
    $database = "PlantShop";

    $link = mysqli_connect($host, $user, $password) or die ("Error: no connection can be made to the host");
    mysqli_select_db($link, $database) or die ("Error: the database could not be opened");
    
    try {
        if (isset($_GET["searchBar"]) && !empty($_GET["searchBar"])) {
            $term = "%" . htmlspecialchars($_GET["searchBar"]) . "%";
        
            $query = "SELECT * FROM plant WHERE plantName LIKE ?";
        
            $stmt = mysqli_prepare($link, $query);
            mysqli_stmt_bind_param($stmt, "s", $term);
            mysqli_stmt_execute($stmt);
        
            $result = mysqli_stmt_get_result($stmt);
        
            while ($row = mysqli_fetch_array($result)) {
                echo '<div class="newCard">
                    <a href="./information.php?plantID=' . $row["PlantID"] . '">
                        <h2>' . $row["PlantName"] . '</h2>
                        <img src="./img/user/' . $row["PlantPicture"] . '" alt="Product Photo"/>
                        <p>€' . $row["PlantPrice"] . '</p>
                    </a>
                </div>';
            }
            mysqli_stmt_close($stmt);
        }
        else {
            $query = "SELECT * FROM plant";
            $result = mysqli_query($link, $query);
        
            while ($row = mysqli_fetch_array($result)) {
                echo '<div class="newCard">
                    <a href="./information.php?plantID=' . $row["PlantID"] . '">
                        <h2>' . $row["PlantName"] . '</h2>
                        <img src="./img/user/' . $row["PlantPicture"] . '" alt="Product Photo"/>
                        <p>€' . $row["PlantPrice"] . '</p>
                    </a>
                </div>';
            }
        }
    } catch (Exception $e){
        echo "An unexpected error occurred.";
    }
?>