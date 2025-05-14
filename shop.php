<?php
    session_start();
    include("./functions.php");

    $host = "localhost";
    $user = "Webuser";
    $password = "Lab2021";
    $database = "PlantShop";

    $link = mysqli_connect($host, $user, $password) or die ("Error: no connection can be made to the host");
    mysqli_select_db($link, $database) or die ("Error: the database could not be opened");

    if(isset($_POST["filterApply"])){
        $category = $_POST["category"];
        $min = $_POST["min"];
        $max = $_POST["max"];
    }

    if(isset($_POST["removeFilter"])){
        $_POST["category"] = null;
        $min = $_POST["min"] = null;
        $_POST["max"] = null;
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Shop</title>
        <meta charset="utf-8"/>
        <link href="./css/reset.css" rel="stylesheet"/>
        <link href="./css/general.css" rel="stylesheet"/>
        <link href="./css/shop.css" rel="stylesheet"/>
        <script>
            function start() {
                xhr = new XMLHttpRequest();
                if (xhr != null) {
                    var searchString = document.getElementById("searchBar").value;

                    var url = "shopProcess.php?searchBar=" + searchString;

                    xhr.onreadystatechange=showResult;
                    xhr.open("GET", url, true);
                    xhr.send(null);
                }
            }

            function showResult() {
                var output = document.getElementById("field");

                if (xhr.readyState == 4 && xhr.status == 200) {
                    if(xhr.responseText) {
                        output.innerHTML = xhr.responseText;
                    }
                }
            }

            function showFilters(){
                var filterSection = document.getElementById("filter");

                if (filterSection.style.display === "block") {
                    filterSection.style.display = "none";
                } else {
                    filterSection.style.display = "block";
                }
            }
        </script>
    </head>
    <body>
        <div id="wrapper">
            <nav>
                <ul>
                    <li><a href="./index.php">Home</a></li>
                    <li><a class="active" href="./shop.php">Shop</a></li>
                    <?php
                        if(isset($_SESSION["user"])){
                            echo '<li><a href="./cart.php">Cart</a></li>';
                        }
                    ?>
                    <li><a href="./contact.php">Contact</a></li>
                    
                    <?php
                        if (isset($_SESSION["user"])){
                            $user = $_SESSION["user"];
                            $userType = $_SESSION["userType"];

                            if(strcmp($userType, "admin") === 0){
                                echo '<li><a>View</a>
                                    <ul class="dropDown">
                                        <li><a class="active" href="./index.php">Customer</a></li>
                                        <li><a href="./users.php">Admin</a></li>
                                    </ul>
                                </li>';
                            }

                           echo '<li><a class="login">' . $user . '</a>' .
                            '<ul class="dropDown"><li><a href="./logout.php">Logout</a></li></ul></li>';
                        }
                        else {
                            echo '<li><a class="login" href="./login.php">Login</a></li>';
                        }
                    ?>
                </ul>
            </nav>

            <div id="header">
                <form id="searchBarForm">
                    <input type="text" name="searchBar" id="searchBar" placeholder="search Product" onkeyup="start();"/>
                    <div id="filterBttn" onclick="showFilters()">
                        <p>Filters</p>
                    </div>
                </form>
            </div>

            <div id="filter">
                <form id="filterForm" method="post" action="<?php echo($_SERVER["PHP_SELF"]); ?>">
                    <h2>Filters</h2>
                    <p>
                        <label for="categories">Categories: </label>
                        <select name="category">
                            <option value="">Select Category</option>
                            <?php
                                $query = "SELECT categoryName FROM category";
                                $result = mysqli_query($link, $query);

                                while ($row = mysqli_fetch_array($result)){
                                    $categoryName = $row['categoryName'];
                                    $selected = ($category === $categoryName) ? 'selected' : '';

                                    echo '<option value="' . $categoryName . '" ' . $selected . '>'. $categoryName .'</option>';
                                }
                            ?>
                        </select>
                    </p>

                    <p>Price:</p>
                    <p>
                        <label for="min">Min</label>
                        <input type="number" name="min" min="0" value="<?php echo isset($min) ? $min : ''; ?>"/>
                        -
                        <label for="max">Max</label>
                        <input type="number" name="max" value="<?php echo isset($max) ? $max : ''; ?>"/>
                    </p>
                    
                    <p>
                        <input type="submit" value="apply" name="filterApply"/>
                        <input type="submit" value="remove" name="removeFilter"/>
                    </p>
                </form>
            </div>

            <div id="field">
                <?php
                    if(isset($_POST["filterApply"])){
                        if ($category !== "") {
                            $categoryID = selectDB($link, "categoryID", "category", "categoryName", "s", $category);
                        
                            $query = "SELECT plantID FROM plantCategory WHERE categoryID = ?";
                
                            $stmt = mysqli_prepare($link, $query);
                            mysqli_stmt_bind_param($stmt, "i", $categoryID);
                            mysqli_stmt_execute($stmt);
                        
                            $plantIDs = mysqli_stmt_get_result($stmt);
                            $filteredPlantIDs = array();
                
                            while ($row = mysqli_fetch_array($plantIDs)){
                                $filteredPlantIDs[] = $row["plantID"];
                            }
                
                            mysqli_stmt_close($stmt);
                
                            foreach ($filteredPlantIDs as $plantID) {
                                $query = "SELECT plantName, plantPicture, plantPrice FROM plant WHERE plantID = ?";

                                if($min !== "" && $max !== ""){
                                    $query .= " AND plantPrice BETWEEN ? AND ?";
                                    $stmt = mysqli_prepare($link, $query);
                                    mysqli_stmt_bind_param($stmt, "iii", $plantID, $min, $max);
                                }
                                else {
                                    $stmt = mysqli_prepare($link, $query);
                                    mysqli_stmt_bind_param($stmt, "i", $plantID);
                                }

                                mysqli_stmt_execute($stmt);
                            
                                mysqli_stmt_bind_result($stmt, $plantName, $plantPic, $plantPrice);

                                if (mysqli_stmt_fetch($stmt)) {
                                    echo '<div class="newCard">
                                        <a href="./information.php?plantID=' . $plantID . '">
                                            <h2>' . $plantName . '</h2>
                                            <img src="./img/user/' . $plantPic . '" alt="Product Photo"/>
                                            <p>€' . $plantPrice . '</p>
                                        </a>
                                    </div>';
                                }
                                
                                mysqli_stmt_close($stmt);
                            }
                        }
                        else {
                            $query = "SELECT * FROM plant WHERE plantPrice BETWEEN ? AND ?";

                            $stmt = mysqli_prepare($link, $query);
                            mysqli_stmt_bind_param($stmt, "ii", $min, $max);
                            mysqli_stmt_execute($stmt);

                            $result = mysqli_stmt_get_result($stmt);

                            while ($row = mysqli_fetch_array($result)) {
                                echo '<div class="newCard">
                                        <a href="./information.php?plantID=' . $row["PlantID"] . '">
                                            <h2>' . $row["PlantName"] . '</h2>
                                            <img src="./img/user/' . $row["PlantPicture"] . '" alt="Product Photo"/>
                                            <p>€' . $row["PlantPrice"] . '</p>
                                        </a>
                                </div>';
                            }
                        }
                    } else {
                        $query = "SELECT * FROM plant";
                        $result = mysqli_query($link, $query);
                    
                        while ($row = mysqli_fetch_array($result)) {
                            echo '<div class="newCard">
                                        <a href="./information.php?plantID=' . $row["PlantID"] . '">
                                            <h2>' . $row["PlantName"] . '</h2>
                                            <img src="./img/user/' . $row["PlantPicture"] . '" alt="Product Photo"/>
                                            <p>€' .  $row["PlantPrice"] . '</p>
                                        </a>
                                 </div>';
                        }
                    }
                ?>
            </div>
            
            <footer>
    
            </footer>
        </div>
    </body>
</html>

<?php
    set_error_handler("errorHandler");
?>