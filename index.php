<?php
    session_start();
    include("./functions.php");
    set_error_handler("errorHandler");
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Home</title>
        <meta charset="utf-8"/>
        <link href="./css/reset.css" rel="stylesheet"/>
        <link href="./css/general.css" rel="stylesheet"/>
        <link href="./css/index.css" rel="stylesheet"/>
    </head>
    <body>
        <div id="wrapper">
            <nav>
                <ul>
                    <li><a class="active" href="./index.php">Home</a></li>
                    <li><a href="./shop.php">Shop</a></li>
                    <?php
                        if(isset($_SESSION["user"])){
                            echo '<li><a href="./cart.php">Cart</a></li>';
                        }
                    ?>
                    <li><a href="./contact.php">Contact</a></li>

                    <?php
                        if (isset($_SESSION["user"])){
                            $userName = $_SESSION["user"];
                            $userType = $_SESSION["userType"];

                            if(strcmp($userType, "admin") === 0){
                                echo '<li><a>View</a>
                                    <ul>
                                        <li><a class="active" href="./index.php">Customer</a></li>
                                        <li><a href="./users.php">Admin</a></li>
                                    </ul>
                                </li>';
                            }

                            echo '<li><a class="login">' . $userName . '</a>
                                <ul>
                                    <li><a href="./logout.php">Logout</a></li>
                                </ul>
                            </li>';
                        }
                        else {
                            echo '<li><a class="login" href="./login.php">Login</a></li>';
                        }
                    ?>
                </ul>
            </nav>
    
            <img src="./img/banner.jpg" alt="banner"/>
    
            <h1>Petals Paradise</h1>
            <hr/>
    
            <div id="text">
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
                <button><a href="./shop.php">Start Shopping</a></button>
            </div>

            <div id="pics">
                <img src="./img/slider1.jpg" alt="picture"/>
                <img id="middle" src="./img/slider2.jpg" alt="picture"/>
                <img src="./img/slider3.jpg" alt="picture"/>
            </div>
        </div>
    </body>
</html>