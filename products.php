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

     function getCategory($link) {
        $query = "SELECT * FROM category";
        $result = mysqli_query($link, $query);

        while($row = mysqli_fetch_array($result)) {
            echo "<option value=\"". $row["CategoryName"] . "\">". $row["CategoryName"] . "</option>";
        }
     }
 
    if (isset($_SESSION["user"])){
        $userName = $_SESSION["user"];
    } else {
        header("Location: login.php");
        exit;
    }

    if (isset($_SESSION["userType"])){
        $userType = $_SESSION["userType"];

        if ($userType === "regular") {
            header("Location: index.php");
            exit;
        }
    }

     if(isset($_POST["submitSignUp"])) {
        $plantName = sanitize($_POST["name"], $link); 
        $plantDesc = sanitize($_POST["desc"], $link);
        $plantCare = sanitize($_POST["care"], $link);
        $plantPrice = sanitize($_POST["price"], $link);
        $plantQIS = sanitize($_POST["QIS"], $link);

        $plantCategory = $_POST["category"];

        $_FILES["pic"]["name"] = $plantName . "IMG.jpg";

        $targetDir = "./img/user/";
        $targetFile = $targetDir . basename($_FILES["pic"]["name"]);

        move_uploaded_file($_FILES["pic"]["tmp_name"], $targetFile);

        $query = "INSERT INTO plant (plantName, plantDescription, plantCare, plantPrice, plantQuantityInStock, plantPicture)
                VALUES (?,?,?,?,?, ?)";
        
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "sssiis", $plantName, $plantDesc, $plantCare, $plantPrice, $plantQIS, $_FILES["pic"]["name"]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $categoryID = selectDB($link, "categoryID", "category", "categoryName", "s", $plantCategory);
        $plantID = selectDB($link, "plantID", "plant", "plantName", "s", $plantName);

        $query = "INSERT INTO plantcategory (plantID, categoryID)
            VALUES (?,?)";

        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "ii", $plantID, $categoryID);
        mysqli_stmt_execute($stmt);

        header("Location: ./products.php");
        exit;
     }

     if(isset($_POST["submitCategory"])) {
        $categoryName = sanitize($_POST["categoryName"], $link);
        $categoryDesc = sanitize($_POST["categoryDesc"], $link);

        $query = "INSERT INTO category (categoryName, categoryDescription) VALUES (?,?)";

        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "ss", $categoryName, $categoryDesc);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: ./products.php");
        exit;
     }

     if(isset($_POST["submitConfirm"])) {
        $removePlantID = $_POST["removePlantID"];

        if($_POST["choice"] == "Yes") {
            $file = selectDB($link, "plantPicture", "plant", "plantID", "i", $removePlantID);

            unlink("./img/user/" . $file);

            deleteDB($link, "review", "plantID", "s", $removePlantID);
            deleteDB($link, "plantCategory", "plantID", "s", $removePlantID);
            deleteDB($link, "orderLine", "plantID", "s", $removePlantID);
            deleteDB($link, "plant", "plantID", "s", $removePlantID);
        }

        header("Location: ./products.php");
        exit;
    }

    $update = false;

    foreach($_POST as $key => $value) {
        if(strpos($key, "submitSave") !== false) {
            $plantID = str_replace("submitSave", "",  $key);

            $plantName = sanitize($_POST["plantName" . $plantID], $link);
            $plantDesc = sanitize($_POST["plantDesc" . $plantID], $link);
            $plantCare = sanitize($_POST["plantCare" . $plantID], $link);
            $plantPrice = sanitize($_POST["plantPrice" . $plantID], $link);
            $plantQIS = sanitize($_POST["plantQIS" . $plantID], $link);

            $plantPic = "pic" . $plantID;

            if ($_FILES[$plantPic]["size"] > 0) {
                $_FILES[$plantPic]["name"] = $plantName . "IMG.jpg";
            
                $targetDir = "./img/user/";
                $targetFile = $targetDir . basename($_FILES[$plantPic]["name"]);
            
                move_uploaded_file($_FILES[$plantPic]["tmp_name"], $targetFile);
            
                $query = "UPDATE plant SET plantName = ?, plantDescription = ?, plantCare = ?, plantPrice= ?, plantQuantityInStock = ?, plantPicture = ? WHERE plantID = ?";
                $stmt = mysqli_prepare($link, $query);
                mysqli_stmt_bind_param($stmt, "sssiisi", $plantName, $plantDesc, $plantCare, $plantPrice, $plantQIS, $_FILES[$plantPic]["name"], $plantID);
            } else {
                $query = "UPDATE plant SET plantName = ?, plantDescription = ?, plantCare = ?, plantPrice= ?, plantQuantityInStock = ? WHERE plantID = ?";
                $stmt = mysqli_prepare($link, $query);
                mysqli_stmt_bind_param($stmt, "sssiis", $plantName, $plantDesc, $plantCare, $plantPrice, $plantQIS, $plantID);
            }

            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $update = true;
        }
    }

    if($update){
        header("Location: ./products.php");
        exit;
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Products</title>
        <meta charset="utf-8"/>
        <link href="./css/reset.css" rel="stylesheet"/>
        <link href="./css/general.css" rel="stylesheet"/>
        <link href="./css/users.css" rel="stylesheet"/>
        <script>
            function addToggle(name){
                var bttn = document.getElementById("bttn");
                var element = document.getElementById(name);

                //directly using style.display did not work
                var computedStyle = window.getComputedStyle(element);
                var displayValue = computedStyle.getPropertyValue("display");

                if (displayValue === "none" || displayValue === "") {
                    bttn.style.display = "none";
                    element.style.display = "block";
                } else {
                    bttn.style.display = "block";
                    element.style.display = "none";
                }
            }

            function confirmRemove(plantID){
                var confirm = document.getElementById("confirm");

                var computedStyle = window.getComputedStyle(confirm);
                var displayValue = computedStyle.getPropertyValue("display");

                if (displayValue === "none" || displayValue === "") {
                    confirm.style.display = "block";
                    document.getElementById("removePlantID").value = plantID;
                } else {
                    confirm.style.display = "none";
                }
            }

            function settings(plantID){
                var hide = document.getElementById("hide" + plantID);

                if(hide.style.display == "none"){
                    document.getElementById("plantName" + plantID).removeAttribute('readonly');
                    hide.style.display = "block";
                } else {
                    document.getElementById("plantName" + plantID).setAttribute('readonly', true);
                    hide.style.display = "none";
                }
            }

            function start() {
                xhr = new XMLHttpRequest();
                if (xhr != null) {
                    var searchString = document.getElementById("searchBar").value;
                    var url = "productsProcess.php?searchBar=" + searchString;

                    xhr.onreadystatechange=showResult;
                    xhr.open("GET", url, true);
                    xhr.send(null);
                }
            }

            function showResult() {
                var output = document.getElementById("Field");

                if (xhr.readyState == 4 && xhr.status == 200) {
                    if(xhr.responseText) {
                        output.innerHTML = xhr.responseText;
                    }
                }
            }
        </script>
    </head>
    <body>
        <div id="wrapper">
            <nav>
                <ul>
                    <li><a href="./users.php">Users</a></li>
                    <li><a class="active" href="./products.php">Products</a></li>
                    <li><a>View</a>
                        <ul class="dropDown">
                            <li><a href="./index.php">Customer</a></li>
                            <li><a class="active" href="./users.php">Admin</a></li>
                        </ul>
                    </li>
                    <li><a class="login" href="#">Admin</a>
                        <ul>
                            <li><a href="./logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </nav>

            <h1>Products</h1>
            <hr/>

            <form id="searchBarForm">
                <input type="text" id="searchBar" name="search" placeholder="Search Product" onkeyup="start();"/>
            </form>
            
            <div id="Field">
                <?php
                    $query = "SELECT * FROM plant";
                    $result = mysqli_query($link, $query);

                    while ($row = mysqli_fetch_array($result)){
                        $plantID = $row["PlantID"];

                        echo '<div class="plants" class="users">
                        <img src="./img/trashIcon.png" alt="remove icon" title="remove" onclick="confirmRemove(\'' . $plantID . '\')"/>
                        <img src="./img/settingsIcon.png" alt="settings icon" title="settings" onclick="settings(\'' . $plantID . '\')"/>
                        <form id="formSettings' . $plantID . '" action="' . $_SERVER["PHP_SELF"] . '" method="post" enctype="multipart/form-data">
                            <img class="flowerPic" src="./img/user/' . $row["PlantPicture"] . '" alt="flower picture"/>
                            <input type="text" name="plantName' . $plantID . '" id="plantName' . $plantID . '" value="' . $row["PlantName"] . '" readonly/>
                            <div id="hide' . $plantID . '" class="hide" style="display: none;">
                                <input type="text" name="plantDesc' . $plantID . '" value="' . $row["PlantDescription"] . '"/>
                                <input type="text" name="plantCare' . $plantID . '" value="' . $row["PlantCare"] . '"/>
                                <input type="text" name="plantPrice' . $plantID . '" value="' . $row["PlantPrice"] . '"/>
                                <input type="text" name="plantQIS' . $plantID . '" value="' . $row["PlantQuantityInStock"] . '"/>
                                <input type="file" name="pic' . $plantID . '" accept="image/*"/ value="' . $row["PlantPicture"] . '"/>
                                <input type="submit" name="submitSave' . $plantID . '" value="Save"/>
                            </div>
                        </form></div>';
                    }
                ?>
            </div>

            <div id="confirm">
                <form action="<?php echo($_SERVER["PHP_SELF"]); ?>" method="post" id="form">
                    <p>Are you sure you want to remove this plant?</p>
                    <p>
                        <input type="hidden" name="removePlantID" id="removePlantID"/>

                        <label for="choice1">Yes</label>
                        <input type="radio" name="choice" id="choice1" value="Yes"/>

                        <label for="choice2">No</label>
                        <input type="radio" name="choice" id="choice2" value="No"/>

                        <input type="submit" name="submitConfirm" value="submit"/>
                    </p>
                </form>
            </div>

            <div id="bttn">
                <button class="add" onclick="addToggle('newUser')">add product</button>
                <button class="add" onclick="addToggle('newCategory')">add category</button>
            </div>

            <div class="newCard" id="newUser">
                <form class="addForm" method="post" action="<?php echo($_SERVER["PHP_SELF"]); ?>" onsubmit="return validateAdd()" enctype="multipart/form-data">
                    <p>
                        <label for="name">Name:</label>
                        <input type="text" name="name" id="name"/>
                    </p>
    
                    <p>
                        <label for="desc">Description:</label>
                        <input type="text" name="desc" id="desc"/>
                    </p>

                    <p>
                        <label for="category">Category:</label>
                        <select name="category">
                            <?php
                                getCategory($link);
                            ?>
                        </select>
                    </p>

                    <p>
                        <label for="care">Plant Care:</label>
                        <input type="text" name="care" id="care"/>
                    </p>
    
                    <p>
                        <label for="price">Price</label>
                        <input type="text" name="price" id="price"/>
                    </p>
    
                    <p>
                        <label for="QIS">Quantity in stock:</label>
                        <input type="text" name="QIS" id="QIS"/>
                    </p>

                    <p>
                        <label for="pic">Picture:</label>
                        <input type="file" name="pic" id="pic" accept="image/*"/>
                    </p>
    
                    <p class="error" id="error2"></p>
                    <input class="add" type="submit" name="submitSignUp" value="Add"/>
                </form>
                <button class="back" onclick="addToggle('newUser')">Back</button>
            </div>

            <div class="newCard" id="newCategory">
                <form class="addForm" method="post" action="<?php echo($_SERVER["PHP_SELF"]); ?>">
                    <p>
                        <label for="categoryName">Name:</label>
                        <input type="text" name="categoryName"/>
                    </p>

                    <p>
                        <label for="categoryDesc">Description:</label>
                        <textarea name="categoryDesc"></textarea>
                    </p>
                    <input class="add" type="submit" name="submitCategory"/>
                </form>
                <button class="back" onclick="addToggle('newCategory')">Back</button>
            </div>
        </div>
    </body>
</html>