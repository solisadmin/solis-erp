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
        throw new Exception("INVALID TOKEN");
    }
    if ($_POST["t"] != $_SESSION["token"]) {
        throw new Exception("INVALID TOKEN");
    }

    if (!isset($_POST["cid"])) {
        throw new Exception("INVALID CONTRACT ID");
    }

    if (!isset($_POST["hid"])) {
        throw new Exception("INVALID HOTEL ID");
    }

    require_once("../../connector/pdo_connect_main.php");
    require_once("./_contract_exchangerates.php");


    $contractid = $_POST["cid"];

    $con = pdo_con();

    $arr_rates = _contract_exchangerates($con, $contractid);

    echo json_encode(array("OUTCOME" => "OK", "EXCHANGE_RATES" => $arr_rates));
} catch (Exception $ex) {

    die(json_encode(array("OUTCOME" => "ERROR: " . $ex->getMessage())));
}
?>
