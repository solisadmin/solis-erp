<?php

try {

    //=-================== CATCH ALL WARNINGS INTO ERROR TRAP =======================
    set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
        // error was suppressed with the @-operator
        if (0 === error_reporting()) {
            return false;
        }
        throw new Exception($errstr . " " . $errno);
    });

    session_start();
    
    if (!isset($_SESSION["solis_userid"])) {
        throw new Exception("NO LOG IN!");
    }
    
    if (!isset($_POST["t"])) {
        throw new Exception("NO TOKEN");
    }
    if ($_POST["t"] != $_SESSION["token"]) {
        throw new Exception("INVALID TOKEN");
    }
    
    if (!isset($_POST["uid"])) {
        throw new Exception("INVALID ID");
    }
    
    $id = $_POST["uid"];

    require_once("../../connector/pdo_connect_main.php");
    require_once("../../utils/utilities.php");

    $con = pdo_con();

    $stmt = $con->prepare("UPDATE tbluser SET status='DEACTIVATED' WHERE id IN ($id)");
    $stmt->execute(array(":date_activated"=>date("Y-m-d H:i:s")));

    echo json_encode(array("OUTCOME" => "OK"));
} catch (Exception $ex) {
    die(json_encode(array("OUTCOME" => "ERROR: " . $ex->getMessage())));
}
?>
