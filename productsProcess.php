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

    if (isset($_GET["searchBar"]) && !empty($_GET["searchBar"])) {
        $term = "%" . htmlspecialchars($_GET["searchBar"]) . "%";
    
        $query = "SELECT * FROM plant WHERE plantName LIKE ?";
    
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "s", $term);
        mysqli_stmt_execute($stmt);
    
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_array($result)){
            $plantID = $row["PlantID"];

            echo '<div id="plant" class="users">
            <img src="./img/trashIcon.png" alt="remove icon" title="remove" onclick="confirm(\'' . $plantID . '\')"/>
            <img src="./img/settingsIcon.png" alt="settings icon" title="settings" onclick="settings(\'' . $plantID . '\')"/>
            <form id="formSettings' . $plantID . '" action="' . $_SERVER["PHP_SELF"] . '" method="post" onsubmit="return validateSave(\'' . $plantID . '\')" enctype="multipart/form-data">
                <img class="flowerPic" src="./img/user/' . $row["PlantPicture"] . '" alt="flower picture"/>
                <input type="text" name="plantName' . $plantID . '" id="plantName' . $plantID . '" value="' . $row["PlantName"] . '" readonly/>
                <div id="hide' . $plantID . '" class="hide" style="display: none;">
                    <input type="text" name="plantDesc' . $plantID . '" value="' . $row["PlantDescription"] . '"/>
                    <input type="text" name="plantCare' . $plantID . '" value="' . $row["PlantCare"] . '"/>
                    <input type="text" name="plantPrice' . $plantID . '" value="' . $row["PlantPrice"] . '"/>
                    <input type="text" name="plantQIS' . $plantID . '" value="' . $row["PlantQuantityInStock"] . '"/>
                    <input type="file" name="pic' . $plantID . '" accept="image/*"/>
                </div>
            </form></div>';
        }
    } else {
        $query = "SELECT * FROM plant";
        $result = mysqli_query($link, $query);

        while ($row = mysqli_fetch_array($result)){
            $plantID = $row["PlantID"];

            echo '<div id="plant" class="users">
            <img src="./img/trashIcon.png" alt="remove icon" title="remove" onclick="confirm(\'' . $plantID . '\')"/>
            <img src="./img/settingsIcon.png" alt="settings icon" title="settings" onclick="settings(\'' . $plantID . '\')"/>
            <form id="formSettings' . $plantID . '" action="' . $_SERVER["PHP_SELF"] . '" method="post" onsubmit="return validateSave(\'' . $plantID . '\')" enctype="multipart/form-data">
                <img class="flowerPic" src="./img/user/' . $row["PlantPicture"] . '" alt="flower picture"/>
                <input type="text" name="plantName' . $plantID . '" id="plantName' . $plantID . '" value="' . $row["PlantName"] . '" readonly/>
                <div id="hide' . $plantID . '" class="hide" style="display: none;">
                    <input type="text" name="plantDesc' . $plantID . '" value="' . $row["PlantDescription"] . '"/>
                    <input type="text" name="plantCare' . $plantID . '" value="' . $row["PlantCare"] . '"/>
                    <input type="text" name="plantPrice' . $plantID . '" value="' . $row["PlantPrice"] . '"/>
                    <input type="text" name="plantQIS' . $plantID . '" value="' . $row["PlantQuantityInStock"] . '"/>
                    <input type="file" name="pic' . $plantID . '" accept="image/*"/>
                </div>
            </form></div>';
        }
    }
?>