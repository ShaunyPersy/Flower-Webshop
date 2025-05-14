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
        <title>Login</title>
        <meta charset="utf-8"/>
        <link href="./css/reset.css" rel="stylesheet"/>
        <link href="./css/general.css" rel="stylesheet"/>
        <link href="./css/login.css" rel="stylesheet"/>
        <script>
            function validateForm() {
                var user = document.getElementById("username").value;
                var pass = document.getElementById("password").value;

                if(user === "" || pass === "")
                {
                    window.location.href = "login.php?error=1";
                    return false;
                }
                return true;
            }

            function goToForm() {
                window.location.href = "./signUp.php";
            }

        </script>
    </head>
    <body>
        <div id="wrapper">
            <div id="login">
                <form method="post" action="<?php echo($_SERVER["PHP_SELF"]);?>" onsubmit="return validateForm()">
                    <img src="./img/icon.png" alt="icon"/>
                    <input type="text" id="username" name="username" placeholder="username"/>
                    <input type="password" id="password" name="password" placeholder="password"/>
                    
                    <p id="error1" class="error">
                        <?php
                            if (isset($_GET["error"]))
                            {
                                $error = $_GET["error"];

                                if ($error == 1){
                                    echo "Please fill in all fields!";
                                }
                                else if ($error == 2) {
                                    echo "Wrong username or password!";
                                }
                            }
                        ?>
                    </p>
                    <input type="submit" name="submitLogin" id="submitLogin" value="Login"/>
                </form>
            </div>

            <?php
                if (isset($_POST["submitLogin"]))
                {
                    $userName = sanitize($_POST["username"], $link);
                    $pass = sanitize($_POST["password"], $link);

                    $checkUserName = selectDB($link, "userName", "user", "userName", "s", $userName);

                    if ($checkUserName !== null)
                    {
                        $hashedPass = selectDB($link, "userPassword", "user", "userName", "s", $userName);

                        if ($hashedPass !== null && password_verify($pass, $hashedPass))
                        {
                            $userType = selectDB($link, "userType", "user", "userName", "s", $userName);

                            $_SESSION["user"] = $userName;
                            $_SESSION["userType"] = $userType;

                            $_SESSION["products"] = array();
                            $_SESSION["amounts"] = array();

                            if ($userType !== "admin"){
                                header("Location: index.php");
                                exit;
                            } else {
                                header("Location: users.php");
                                exit;
                            }
                        }
                        else {
                            header("Location: login.php?error=2");
                            exit;
                        }
                    }
                    else {
                        header("Location: login.php?error=2");
                        exit;
                    }
                }
            ?>

            <div id="bttns">
                <div id="register">
                    <a id="create" onclick="goToForm()">Create a new account</a>
                </div>
    
                <a id="link" href="./index.php"><button id="back">Back</button></a>
            </div>

            <footer>
    
            </footer>
        </div>
    </body>
</html>

<?php 
    mysqli_close($link);
?>