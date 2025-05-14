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
    
        $query = "SELECT * FROM user WHERE userName LIKE ?";
    
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "s", $term);
        mysqli_stmt_execute($stmt);
    
        $result = mysqli_stmt_get_result($stmt);

        while ($row = mysqli_fetch_array($result)){
            $userID = $row["UserID"];

            echo '<div class="users">
                <img src="./img/trashIcon.png" alt="remove icon" title="remove" onclick="confirm(\'' . $userID . '\')"/>
                <img src="./img/settingsIcon.png" alt="settings icon" title="settings" onclick="settings(\'' . $userID . '\')"/>
                <form id="formSettings' . $userID . '" action="' . $_SERVER["PHP_SELF"] . '" method="post" onsubmit="return validateSave(\'' . $userID . '\')">
                    <input type="text" name="userCard' . $userID . '" id="userCard' . $userID . '" value="' . $row["UserName"] . '" readonly/>
                    <input type="text" name="privilegeCard' . $userID . '" id="privilegeCard' . $userID . '" value="' . $row["UserType"] . '" readonly/>
                </form>
            </div>';
        }
        mysqli_stmt_close($stmt);
    } else {
        $query = "SELECT * FROM user";
        $result = mysqli_query($link, $query);

        while ($row = mysqli_fetch_array($result)){
            $userID = $row["UserID"];

            echo '<div class="users">
                <img src="./img/trashIcon.png" alt="remove icon" title="remove" onclick="confirm(\'' . $userID . '\')"/>
                <img src="./img/settingsIcon.png" alt="settings icon" title="settings" onclick="settings(\'' . $userID . '\')"/>
                <form id="formSettings' . $userID . '" action="' . $_SERVER["PHP_SELF"] . '" method="post" onsubmit="return validateSave(\'' . $userID . '\')">
                    <input type="text" name="userCard' . $userID . '" id="userCard' . $userID . '" value="' . $row["UserName"] . '" readonly/>
                    <input type="text" name="privilegeCard' . $userID . '" id="privilegeCard' . $userID . '" value="' . $row["UserType"] . '" readonly/>
                </form>
            </div>';
        }
    }
?>