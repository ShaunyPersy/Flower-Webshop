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

    if(isset($_POST["emptyCart"]))
    {
        $_SESSION["products"] = (array) null;
        $_SESSION["amounts"] = (array) null;
    }
    else if(isset($_POST["submitPay"])){
        $userID = selectDB($link, "userID", "user", "userName", "s", $_SESSION["user"]);
        $customerID = selectDB($link, "customerID", "customer", "userID", "i", $userID);

        $date = date("Y-m-d");
        $totalAmount = $_POST["totalAmount"];
        $status = "Paid";
        $paymentMethod = "Bancontact";

        $query = "INSERT INTO `order` (customerID, orderDate, orderStatus, orderTotalAmount, orderPaymentMethod)
        VALUES (?,?,?,?,?)";

        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "issis", $customerID, $date, $status, $totalAmount, $paymentMethod);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $query = "SELECT orderID FROM `order` WHERE customerID = ? ORDER BY orderID DESC LIMIT 1"; //get newest order
        
        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "i", $customerID);
        mysqli_stmt_execute($stmt);

        mysqli_stmt_bind_result($stmt, $orderID);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        $products = $_SESSION["products"];
        $amounts = $_SESSION["amounts"];

        for($i = 0; $i < count($products); $i++){
            $query = "INSERT INTO orderline (orderID, plantID, orderLineQuantity)
            VALUES (?,?,?)";

            $stmt = mysqli_prepare($link, $query);
            mysqli_stmt_bind_param($stmt, "iii", $orderID, $products[$i], $amounts[$i]);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            $plantInStock = selectDB($link, "plantQuantityInStock", "plant", "plantID", "i", $products[$i]);

            $leftOverAmount = $plantInStock - $amounts[$i];

            $query = "UPDATE plant SET plantQuantityInStock = ? WHERE plantID = ?";

            $stmt = mysqli_prepare($link, $query);
            mysqli_stmt_bind_param($stmt, "ii", $leftOverAmount, $products[$i]);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }

        $paymentStatus = "Paid";

        $query = "INSERT INTO payment (orderID, paymentDate, paymentStatus)
            VALUES (?,?,?)";

        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, "iss", $orderID, $date, $paymentStatus);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        header("Location: ./cart.php?status=paid");
        exit;
    }

    if (!isset($_SESSION["user"])) {
        header("Location: ./login.php");
        exit;
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Cart</title>
        <meta charset="utf-8"/>
        <link href="./css/reset.css" rel="stylesheet"/>
        <link href="./css/general.css" rel="stylesheet"/>
        <link href="./css/cart.css" rel="stylesheet"/>
        <script>
            function showPayment(){
                document.getElementById("order").style.display = "block";
                document.getElementById("emptyCart").style.display = "none";
                document.getElementById("submitOrder").style.display = "none";
            }

            function checkPayment() {
                var pay = document.getElementById("pay").value;
                var error = document.getElementById("error1");

                if(pay.toLowerCase() !== "pay") {
                    error.innerHTML = "You need to confirm the payment with 'pay'";
                    return false;
                }

                if (document.querySelectorAll('#cart table tr').length <= 2){
                    error.innerHTML = "Your cart is empty";
                    return false;
                }

                return true;
            }
        </script>
    </head>
    <body>
        <div id="wrapper">
            <nav>
                <ul>
                    <li><a href="./index.php">Home</a></li>
                    <li><a href="./shop.php">Shop</a></li>
                    <li><a class="active" href="./cart.php">Cart</a></li>
                    <li><a href="./contact.php">Contact</a></li>

                    <?php
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
                    ?>
                </ul>
            </nav>

            <p>
                <?php
                   if(isset($_GET["status"])){
                        echo '<p id="paid">Ordered, check your mail for more information.</p>';

                        $_SESSION["products"] = (array) null;
                        $_SESSION["amounts"] = (array) null;
                   } 
                ?>
            </p>

            <div id="cart">
                <h2>Cart</h2>
                <hr/>

                <table>
                    <tr>
                        <th>Product</th>
                        <th>Amount</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>

                    <?php
                        $products = $_SESSION["products"];
                        $amounts = $_SESSION["amounts"];
                        $endTotal = 0;

                        for($i = 0; $i < count($products); $i++) {
                            $query = "SELECT plantName, plantPrice FROM plant WHERE plantID = ?";

                            $stmt = mysqli_prepare($link, $query);
                            mysqli_stmt_bind_param($stmt, "i", $products[$i]);
                            mysqli_execute($stmt);

                            mysqli_stmt_bind_result($stmt, $plantName, $plantPrice);
                            mysqli_stmt_fetch($stmt);
                            mysqli_stmt_close($stmt);

                            $sum = $amounts[$i] * $plantPrice;
                            $endTotal += $sum;

                            echo '<tr>
                                    <td>' . $plantName . '</td>
                                    <td>' . $amounts[$i] . '</td>
                                    <td>€ ' . $plantPrice . '</td>
                                    <td>€ ' . $sum . '</td>
                                </tr>';
                        }
                    ?>

                    <tr class="endTotal">
                        <td colspan="3">Total:</td>
                        <td>€
                        
                        <?php
                            echo $endTotal;
                        ?>

                        </td>
                    </tr>
                </table>

                <form id="empty" method="post" action="<?php echo($_SERVER["PHP_SELF"]); ?>">
                    <input type="submit" id="emptyCart" name="emptyCart" value="Empty"/>
                </form>

                <button id="submitOrder" onclick="showPayment()">Order</button>

                <form id="order" method="post" action="<?php echo($_SERVER["PHP_SELF"]); ?>" onsubmit="return checkPayment()">
                    <input type="hidden" name="totalAmount" value="<?php echo $endTotal; ?>"/>
                
                    <p>Payment:</p>
                    <input type="text" id="pay" placeholder="type 'pay' to finish payment"/>
                    <input type="submit" name="submitPay" value="pay"/>
                    <p class="error" id="error1"></p>
                </form>
            </div>
        </div>
    </body>
</html>