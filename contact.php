<?php
    session_start();
    include("./functions.php");
    set_error_handler("errorHandler");
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Contact</title>
        <meta charset="utf-8"/>
        <link href="./css/reset.css" rel="stylesheet"/>
        <link href="./css/general.css" rel="stylesheet"/>
        <link href="./css/contact.css" rel="stylesheet"/>
    </head>
    <body>
        <div id="wrapper">
            <nav>
                <ul>
                    <li><a href="./index.php">Home</a></li>
                    <li><a href="./shop.php">Shop</a></li>
                    <?php
                        if(isset($_SESSION["user"])){
                            echo '<li><a href="./cart.php">Cart</a></li>';
                        }
                    ?>
                    <li><a class="active" href="./contact.php">Contact</a></li>

                    <?php
                        if (isset($_SESSION["user"])){
                            $userName = $_SESSION["user"];
                            $userType = $_SESSION["userType"];

                            if(strcmp($userType, "admin") === 0){
                                echo '<li><a>View</a>
                                    <ul class="dropDown">
                                        <li><a class="active" href="./index.php">Customer</a></li>
                                        <li><a href="./users.php">Admin</a></li>
                                    </ul>
                                </li>';
                            }
                            
                            echo '<li><a class="login">' . $userName . '</a>' .
                            '<ul class="dropDown"><li><a href="./logout.php">Logout</a></li></ul></li>';
                        }
                        else {
                            echo '<li><a class="login" href="./login.php">Login</a></li>';
                        }
                    ?>
                </ul>
            </nav>

            <div id="container">
                <img src="./img/icon2.png" alt="icon"/>

                <div id="right">
                    <div id="text">
                        <p>petalparadise@hotmail.com</p>
                        <p>999-999-999-999</p>
                        <p>Straatnaam 15, 290 Stad</p>
                    </div>
            
                    <div id="signUp">
                        <p>Sign up for monthly news&excl;</p>
                        <form method="post" action="#"> <!--Not functional-->
                            <input type="text" name="signUp" placeholder="enter email"/>
                            <input type="submit" name="submit" value="Sign Up"/>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>