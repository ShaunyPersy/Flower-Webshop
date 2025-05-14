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
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Home</title>
        <meta charset="utf-8"/>
        <link href="./css/reset.css" rel="stylesheet"/>
        <link href="./css/general.css" rel="stylesheet"/>
        <link href="./css/index.css" rel="stylesheet"/>
        <link href="./css/signUp.css" rel="stylesheet"/>
        <script>
            function validateSignUp() {
                var firstName = document.getElementById("firstName").value;
                var lastName = document.getElementById("lastName").value;
                var address = document.getElementById("address").value;
                var email = document.getElementById("email").value;
                var username = document.getElementById("user").value;
                var pass = document.getElementById("pass").value;
                var confirmPass = document.getElementById("confirmPass").value;

                if (firstName == "" || lastName == "" ||address == "" ||email == "" || username == "" || pass == "" || confirmPass == "" )
                {
                    window.location.href = "signUp.php?error=3";
                    return false;
                }
                else if (confirmPass !== pass)
                {
                    window.location.href = "signUp.php?error=4";
                    return false;
                }
                return true;
            }
        </script>
    </head>
    <body>
        <div id="wrapper">
            <div id="signUp">
                <form method="post" action="<?php echo($_SERVER["PHP_SELF"]); ?>" onsubmit="return validateSignUp()">
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
                            <label for="username">Username:</label>
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

                    <p id="error2" class="error">
                        <?php
                            if (isset($_GET["error"])){
                                $error = $_GET["error"];

                                if ($error == 3) {
                                    echo "Please fill in all fields!";
                                }
                                else if ($error == 4) {
                                    echo "Wrong password!";
                                }
                                else if ($error == 5) {
                                    echo "Username already exists";
                                }
                            }
                        ?>
                    </p>
                    <input type="submit" name="submitSignUp" value="Sign Up"/>
                </form>
            </div>

            <?php 
                if(isset($_POST["submitSignUp"]))
                {
                    $userName = sanitize($_POST["user"], $link);
                    $pass = sanitize($_POST["pass"], $link);

                    $checkUserName = selectDB($link, "userName", "user", "userName", "s", $userName);
                
                    if (!empty($checkUserName)) {          //DUPLICATE USER
                        header("Location: signUp.php?error=5"); 
                        exit;
                    }
                    else {                         //ADD USER
                        $firstName = sanitize($_POST["firstName"], $link);
                        $lastName = sanitize($_POST["lastName"], $link);
                        $add = sanitize($_POST["address"], $link);
                        $email = sanitize($_POST["email"], $link);

                        $hashedPass = password_hash($pass, PASSWORD_DEFAULT);

                        $query = "INSERT INTO user (userName, userPassword, userType)
                                VALUES (?, ?, 'regular')";
                        
                        $stmt = mysqli_prepare($link, $query);
                        mysqli_stmt_bind_param($stmt, "ss", $userName, $hashedPass);
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
                        $_SESSION["userType"] = "regular";

                        header("Location: index.php");
                        exit;
                    }
                }   
            ?>

            <div id="bttn">
                <a id="link" href="./login.php"><button id="back">Back</button></a>
            </div>
        </div>
    </body>
</html>