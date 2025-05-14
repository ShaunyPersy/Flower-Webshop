<?php
    session_start();
    include("./functions.php");
    set_error_handler("errorHandler");

    if (isset($_SESSION["user"])){
        $userName = $_SESSION["user"];
    } else {
        header("Location: login.php");
        exit;
    }

    if (isset($_SESSION["userType"])){
        $userType = $_SESSION["userType"];

        if ($userType === "regular") {
            errorFile($userName, " tried accessing the admin pages\n");
            header("Location: index.php");
            exit;
        }
    }
    
    $host = "localhost";
    $user = "Webuser";
    $password = "Lab2021";
    $database = "PlantShop";

    $link = mysqli_connect($host, $user, $password) or die ("Error: no connection can be made to the host");
    mysqli_select_db($link, $database) or die ("Error: the database could not be opened");

    if(isset($_POST["submitSignUp"])){
        $userName = sanitize($_POST["user"], $link);
        $pass = sanitize($_POST["pass"], $link);

        $checkUserName = selectDB($link, "userName", "user", "userName", "s", $userName);
    
        if (!empty($result)) {          //DUPLICATE USER
            header("Location: login.php?error=5"); 
            exit;
        }
        else {                         //ADD USER
            $firstName = sanitize($_POST["firstName"], $link);
            $lastName = sanitize($_POST["lastName"], $link);
            $add = sanitize($_POST["address"], $link);
            $email = sanitize($_POST["email"], $link);
            $privilege = $_POST["privilege"];

            $hashedPass = password_hash($pass, PASSWORD_DEFAULT);

            $query = "INSERT INTO user (userName, userPassword, userType)
                    VALUES (?, ?, ?)";
            
            $stmt = mysqli_prepare($link, $query);
            mysqli_stmt_bind_param($stmt, "sss", $userName, $hashedPass, $privilege);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $userID = selectDB($link, "userID", "user", "userName", "s", $userName);

            $query = "INSERT INTO customer (userID, customerFirstName, customerLastName, customerAddress, customerEmail)
                    VALUES (?,?,?,?,?)";

            $stmt = mysqli_prepare($link, $query);
            mysqli_stmt_bind_param($stmt, "issss", $userID, $firstName, $lastName, $add, $email);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $_SESSION["user"] = $userName;
            header("Location: users.php");
            exit;
        }
    }

    if(isset($_POST["removeUserID"])) {
        $removeUserID = $_POST["removeUserID"];

        if($_POST["choice"] == "Yes") {
            deleteDB($link, "review", "customerID", "s", $removeUserID);
            
            $query = "SELECT orderID FROM `order` WHERE customerID = ?";

            $stmt = mysqli_prepare($link, $query);
            mysqli_stmt_bind_param($stmt, "i", $removeUserID);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);

            $orderIDs = array();
                
            while ($row = mysqli_fetch_array($result)){
                $orderIDs[] = $row["orderID"];
            }

            mysqli_stmt_close($stmt);

            foreach ($orderIDs as $orderID){
                deleteDB($link, "orderLine", "orderID", "s", $orderID);
                deleteDB($link, "payment", "orderID", "s", $orderID);
                deleteDB($link, "`order`", "orderID", "s", $orderID);
            }

            deleteDB($link, "customer", "userID", "s", $removeUserID);
            deleteDB($link, "user", "userID", "s", $removeUserID);
        }

        header("Location: ./users.php");
        exit;
    }

    foreach($_POST as $key => $value) {
        if(strpos($key, "submitSave") !== false) {
            $userID = str_replace("submitSave", "",  $key);

            $userName = sanitize($_POST["userCard" . $userID], $link);
            $userType = sanitize($_POST["privilegeCard" . $userID], $link);

            $query = "SELECT userName, userType FROM user WHERE userName = ? AND userType = ?";

            $stmt = mysqli_prepare($link, $query);
            mysqli_stmt_bind_param($stmt, "ss", $userName, $userType);
            mysqli_stmt_execute($stmt);

            mysqli_stmt_bind_result($stmt, $checkUserName, $checkUserType);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);

            if ($userName != $checkUserName || $userType != $checkUserType){
                $query = "UPDATE user SET userName = ?, userType = ? WHERE userID = ?";

                $stmt = mysqli_prepare($link, $query);
                mysqli_stmt_bind_param($stmt, "ssi", $userName, $userType, $userID);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                header("Location: ./users.php");
                exit;
            }
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Users</title>
        <meta charset="utf-8"/>
        <link href="./css/reset.css" rel="stylesheet"/>
        <link href="./css/general.css" rel="stylesheet"/>
        <link href="./css/users.css" rel="stylesheet"/>
        <script>
            function addToggle(name) {
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

            function validateAdd() {
                var customer = document.getElementById("privilege1").checked;
                var admin = document.getElementById("privilege2").checked;  

                var firstName = document.getElementById("firstName").value;
                var lastName = document.getElementById("lastName").value;
                var address = document.getElementById("address").value;
                var email = document.getElementById("email").value;
                var username = document.getElementById("user").value;
                var pass = document.getElementById("pass").value;
                var confirmPass = document.getElementById("confirmPass").value;

                var error = document.getElementById("error2");

                if (!customer && !admin || firstName == "" || lastName == "" ||address == "" ||email == "" || username == "" || pass == "" || confirmPass == "" )
                {
                    error.innerHTML = "Fill in all fields!";
                    return false;
                }
                else if (confirmPass !== pass)
                {
                    error.innerHTML = "Wrong password!";
                    return false;
                }
                return true;
            }
            
            function confirmRemove(userID){
                var confirm = document.getElementById("confirm");

                var computedStyle = window.getComputedStyle(confirm);
                var displayValue = computedStyle.getPropertyValue("display");

                if (displayValue === "none" || displayValue === "") {
                    confirm.style.display = "block";
                    document.getElementById("removeUserID").value = userID;
                } else {
                    confirm.style.display = "none";
                }
            }

            function validateSave(userID) {
                var user = document.getElementById("userCard" + userID).value;
                var privInput = document.getElementById("privilegeCard" + userID).value;
                var error = document.getElementById("error");

                var priv = privInput.toLowerCase();

                if(user === "" || priv === ""){
                    location.href = "./users.php?error=1";
                    return false;
                } else if (priv !== "regular" && priv !== "admin") {
                    location.href = "./users.php?error=2"
                    return false;
                }
            }

            function settings(userID){ 
                var save = document.getElementById("submitSave" + userID);

                var computedStyle = window.getComputedStyle(save);
                var displayValue = computedStyle.getPropertyValue("display");

                if (displayValue === "none" || displayValue === "") {
                    save.style.display = "block";
                    document.getElementById("userCard" + userID).removeAttribute('readonly');
                    document.getElementById("privilegeCard" + userID).removeAttribute('readonly');
                } else {
                    save.style.display = "none";
                    document.getElementById("userCard" + userID).setAttribute('readonly', true);
                    document.getElementById("privilegeCard" + userID).setAttribute('readonly', true);
                }
            }

            function start() {
                xhr = new XMLHttpRequest();
                if (xhr != null) {
                    var searchString = document.getElementById("searchBar").value;
                    var url = "usersProcess.php?searchBar=" + searchString;

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
                    <li><a class="active" href="./users.php">Users</a></li>
                    <li><a href="./products.php">Products</a></li>
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

            <h1>Users</h1>
            <hr/>

            <form id="searchBarForm">
                <input type="text" id="searchBar" name="search" placeholder="Search User" onkeyup="start();"/>
            </form>

            <p id="error" class="error">
                <?php
                    if(isset($_GET["error"])){
                        $error = $_GET["error"];
                
                        if ($error == 1) {
                            echo "Please fill in all fields!";
                        } else {
                            echo "Please pick regular or admin!";
                        }
                    }
                ?>
            </p>
            
            <div id="Field">
                <?php
                    $query = "SELECT * FROM user";
                    $result = mysqli_query($link, $query);
                    
                    while ($row = mysqli_fetch_array($result)){
                        $userID = $row["UserID"];
            
                        echo '<div class="users">
                            <img src="./img/trashIcon.png" alt="remove icon" title="remove" onclick="confirmRemove(\'' . $userID . '\')"/>
                            <img src="./img/settingsIcon.png" alt="settings icon" title="settings" onclick="settings(\'' . $userID . '\')"/>
                            <form id="formSettings' . $userID . '" action="' . $_SERVER["PHP_SELF"] . '" method="post" onsubmit="return validateSave(\'' . $userID . '\')">
                                <input type="text" name="userCard' . $userID . '" id="userCard' . $userID . '" value="' . $row["UserName"] . '" readonly/>
                                <input type="text" name="privilegeCard' . $userID . '" id="privilegeCard' . $userID . '" value="' . $row["UserType"] . '" readonly/>
                                <input type="submit" name="submitSave' . $userID . '" id="submitSave' . $userID . '" class="hideSave" value="Save"/>
                            </form>
                        </div>';
                    }
                ?>
            </div>

            <div id="bttn">
                <button class="add" onclick="addToggle('newUser')">add user</button>
            </div>

            <div class="newCard" id="newUser">
                <form class="addForm" method="post" action="<?php echo($_SERVER["PHP_SELF"]); ?>" onsubmit="return validateAdd()">
                    <label for="privilege">Privilege:</label>
                    <div id="privilege">
                        <p>
                            <input type="radio" name="privilege" id="privilege1" value="customer"/>
                            <label for="privilege1">Customer</label>
                        </p>
                        <p>
                            <input type="radio" name="privilege" id="privilege2" value="admin"/>
                            <label for="privilege2">Admin</label>
                        </p>
                    </div>
    
                    <p>
                        <label for="firstName">First Name:</label>
                        <input type="text" name="firstName" id="firstName"/>
                    </p>
    
                    <p>
                        <label for="lastName">Last Name:</label>
                        <input type="text" name="lastName" id="lastName"/>
                    </p>
    
                    <p>
                        <label for="address">Address:</label>
                        <input type="text" name="address" id="address"/>
                    </p>
    
                    <p>
                        <label for="email">E-mail:</label>
                        <input type="email" name="email" id="email"/>
                    </p>
    
                    <div id="user">
                        <p>
                            <label for="user">Username:</label>
                            <input type="text" name="user" id="user"/>
                        </p>
    
                        <p>
                            <label for="pass">Password:</label>
                            <input type="password" name="pass" id="pass"/>
                        </p>
    
                        <p>
                            <label for="confirmPass">Confirm Password:</label>
                            <input type="password" name="confirmPass" id="confirmPass"/>
                        </p>
                    </div>
    
                    <p class="error" id="error2"></p>
                    <input class="add" type="submit" name="submitSignUp" value="Sign Up"/>
                </form>
                <button class="back" onclick="addToggle('newUser')">Back</button>
            </div>

            <div id="confirm">
                <form action="<?php echo($_SERVER["PHP_SELF"]); ?>" method="post" id="form">
                    <p>Are you sure you want to remove this user?</p>
                    <p>
                        <input type="hidden" name="removeUserID" id="removeUserID"/>

                        <label for="choice1">Yes</label>
                        <input type="radio" name="choice" id="choice1" value="Yes"/>

                        <label for="choice2">No</label>
                        <input type="radio" name="choice" id="choice2" value="No"/>

                        <input type="submit" name="submitConfirm" value="submit"/>
                    </p>
                </form>
            </div>
                    
            <footer>
    
            </footer>
        </div>
    </body>
</html>