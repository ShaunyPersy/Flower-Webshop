<?php
    function sanitize($var, $link)
    {
        $sanVar = htmlspecialchars($var);
        $extraSanVar = mysqli_real_escape_string($link, $sanVar);

        return $extraSanVar;
    }

    function selectDB($link, $col, $table, $condition, $paramType, $bind){
        $query = "SELECT $col FROM $table WHERE $condition = ?";

        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, $paramType, $bind);
        mysqli_stmt_execute($stmt);

        mysqli_stmt_bind_result($stmt, $result);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        return $result;
    }

    function deleteDB($link, $table, $condition, $paramType, $bind){
        $query = "DELETE FROM $table WHERE $condition = ?";

        $stmt = mysqli_prepare($link, $query);
        mysqli_stmt_bind_param($stmt, $paramType, $bind);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    function errorHandler($errno, $errMsg, $errFile, $errLine) {
        $error = "[ " . $errno . " ]: ";
        $error .= $errMsg;
        $error .= " in file " . $errFile;
        $error .= " on line " . $errLine ."\n";
    
        echo $error;
        exit();
    }  

    function exceptionHandler($exception) {
        echo "An exception occurred: " . $exception->getMessage();
    }

    function errorFile($userName, $string){
        $file =@ fopen("./logFile.txt", "a") or die("Unable to open file");
        fwrite($file, "[". date("Y/m/d H:i:s") . "]" . $userName . $string);
        fclose($file);
    }
?>