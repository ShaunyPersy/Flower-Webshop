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

    if (isset($_POST["addCart"])) {
        $newPlantID = $_POST['plantID'];
        $newAmount = $_POST["amount"];
    
        $index = array_search($newPlantID, $_SESSION["products"]);
    
        if ($index !== false) {
            $_SESSION["amounts"][$index] += $newAmount;
        } else {
            array_push($_SESSION["products"], $newPlantID);
            array_push($_SESSION["amounts"], $newAmount);
        }
    
        header("Location: ./shop.php");
        exit;
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Plant Information</title>
        <meta charset="utf-8"/>
        <link href="./css/reset.css" rel="stylesheet"/>
        <link href="./css/general.css" rel="stylesheet"/>
        <link href="./css/information.css" rel="stylesheet"/>
    </head>
    <body>
        <div id="wrapper">
            <nav>
                <ul>
                    <li><a href="./index.php">Home</a></li>
                    <li><a class="active" href="./shop.php">Shop</a></li>
                    <li><a href="./cart.php">Cart</a></li>
                    <li><a href="./contact.php">Contact</a></li>
                    
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
            
                <?php
                    $plantID = $_GET["plantID"];

                    $query = "SELECT plantName, plantDescription, plantCare, plantPrice, plantPicture, plantQuantityInStock FROM plant WHERE plantID = ?";

                    $stmt = mysqli_prepare($link, $query);
                    mysqli_stmt_bind_param($stmt, "i", $plantID);
                    mysqli_stmt_execute($stmt);

                    mysqli_stmt_bind_result($stmt, $plantName, $plantDesc, $plantCare, $plantPrice, $plantPic, $plantQuantity);
                    mysqli_stmt_fetch($stmt);
                    mysqli_stmt_close($stmt);

                    $categoryID = selectDB($link, "categoryID", "plantCategory", "plantID", "i", $plantID);

                    $query = " SELECT categoryName, categoryDescription FROM category WHERE categoryID = ?";
                    $stmt = mysqli_prepare($link, $query);
                    mysqli_stmt_bind_param($stmt, "i", $categoryID);
                    mysqli_stmt_execute($stmt);

                    mysqli_stmt_bind_result($stmt, $categoryName, $categoryDesc);
                    mysqli_stmt_fetch($stmt);
                    mysqli_stmt_close($stmt);

                    echo '<div id="field">
                            <h1>' . $plantName . '</h1>
                            <img id="flowerPic" src="./img/user/' . $plantPic . '" alt="plant photo"/>
                            
                            <div id="textField">
                                <p class="middle">' . $plantDesc . '</p>
                                <p class="middle"><strong>€' . $plantPrice . '</strong></p>

                                <div id="dropDowns">
                                    <p>Care</p>
                                    <div id="dropDownCare" class="dropDown">
                                        <p>' . $plantCare . '</p>
                                    </div>

                                    <p>' . $categoryName . '</p>
                                    <div id="dropDownCategory" class="dropDown">
                                        <p>' . $categoryDesc . '</p>
                                    </div>
                                </div>
                            </div>';

                    if(!empty($userName)){
                        if ($plantQuantity == 0){
                            echo '<h2>OUT OF STOCK</h2></div>';
                        } else {
                            echo '<div id="center">
                                    <form method="post" action="./information.php?plantID=' . $plantID . '">
                                        <input type="number" name="amount" value="1" min="1" max="'. $plantQuantity .'"/>
                                        <input type="hidden" name="plantID" value="' . $plantID . '"/>
                                        <input type="submit" name="addCart"/>
                                    </form>
                                </div>
                            </div>';

                            echo '<div id="userReview">
                                <form method="post" action="' . $_SERVER["PHP_SELF"] .'?plantID='. $plantID .'">
                                    <input type="text" name="review"/>
                                    <input type="submit" name="submitReview" value="submit Review"/>
                                </form>
                            </div>';
                        }
                    }

                    $query = "SELECT customerID, reviewText FROM review WHERE plantID = ?";

                    $stmt = mysqli_prepare($link, $query);
                    mysqli_stmt_bind_param($stmt, "i", $plantID);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_bind_result($stmt, $customerID, $reviewText);
                    
                    $customerIDs = array();
                    $reviewTexts = array();
                    $userIDs = array();

                    while($row = mysqli_stmt_fetch($stmt)){
                        $customerIDs[] = $customerID;
                        $reviewTexts[] = $reviewText;
                    }
                    mysqli_stmt_close($stmt);

                    foreach($customerIDs as $customerID){
                        $query = "SELECT userID FROM customer WHERE customerID = ?";

                        $stmt = mysqli_prepare($link, $query);
                        mysqli_stmt_bind_param($stmt, "i", $customerID);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_bind_result($stmt, $userID);

                        while($row = mysqli_stmt_fetch($stmt)){
                            $userIDs[] = $userID;
                        }
                        mysqli_stmt_close($stmt);
                    }

                    foreach ($userIDs as $userID) {
                        $query = "SELECT userName FROM user WHERE userID = ?";

                        $stmt = mysqli_prepare($link, $query);
                        mysqli_stmt_bind_param($stmt, "i", $userID);
                        mysqli_stmt_execute($stmt);
                        mysqli_stmt_bind_result($stmt, $userName);

                        while($row = mysqli_stmt_fetch($stmt)){
                            $userNames[] = $userName;
                        }
                        mysqli_stmt_close($stmt);
                    }

                    for($i = 0; $i < count($customerIDs); $i++){
                        echo '<div class="reviews"><p>'. $userNames[$i] .'</p><hr/>
                            <p>'. $reviewTexts[$i] .'</p></div>';
                    }

                if(isset($_POST["submitReview"])){
                    $userName = $_SESSION['user'];

                    $review = sanitize($_POST["review"], $link);
                    $userID = selectDB($link, "userID", "user", "userName", "s", $userName);
                    $customerID = selectDB($link, "customerID", "customer", "userID", "i", $userID);
            
                    $query = "INSERT INTO review (plantID, customerID, reviewText)
                    VALUES (?,?,?)";

                    $stmt = mysqli_prepare($link, $query);
                    mysqli_stmt_bind_param($stmt, "iis", $plantID, $customerID, $review);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);

                    header("Location: ./information.php?plantID=" . $plantID);
                    exit;
                }
                ?>

            <button id="back"><a href="./shop.php">Back</a></button>
        </div>
    </body>
</html>