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
        die("NO LOG IN!");
    }

    if (!isset($_POST["t"])) {
        die("INVALID TOKEN");
    }
    if (!isset($_POST["params"])) {
        die("INVALID PARAMETERS");
    }
    
    if (!isset($_POST["spo_params"])) {
        die("INVALID SPO PARAMETERS");
    }

    if ($_POST["t"] != $_SESSION["token"]) {
        //die("INVALID TOKEN");
    }

    require_once("../../connector/pdo_connect_main.php");
    require_once("./_rates_calculator.php");
    require_once("./_rates_get_contract.php");
    require_once("../hotelspecialoffers/_spo.php");
    require_once("../hotelspecialoffers/_spo_taxcommi.php");
    require_once("../hotelcontracts/_contract_capacityarr.php");
    require_once("../hotelcontracts/_contract_exchangerates.php");
    require_once("../hotelcontracts/_contract_calculatesp.php");
    require_once("../hotelcontracts/_contract_taxcommi.php");
    require_once("../hotelcontracts/_contract_combinations_rooms.php");
    require_once("../../globalvars/globalvars.php");
    require_once("../../utils/utilities.php");
    
    
    $con = pdo_con();
    $arr_main_params = json_decode($_POST["params"],true);
    $arr_spo_params = json_decode($_POST["spo_params"],true);
    $arr_params = array_merge($arr_main_params,$arr_spo_params);
        
    $outcome = _rates_calculator($con, $arr_params);
    
    
    $arr_params["max_pax"] = $arr_params["spo_party_pax"];
    $arr_params["booking_date"] = $arr_params["spo_booking_date"];
    $arr_params["travel_date"] = $arr_params["spo_travel_date"];
    $arr_params["wedding_interested"] = $arr_params["spo_chk_is_wedding"];
    $arr_params["suppmealplan"] = $arr_params["supp_mealplan"];
    $arr_params["arr_adults"] = $arr_params["adults"];
    $arr_params["arr_children"] = $arr_params["children"];
    
    $test = array();
    //$test = _rates_calculator_reservation_get_cost_claim($con, 5, $arr_params);
    //$test = _rates_calculator_reservation_get_ad_ch_categories($con, 5, $arr_params);

    echo json_encode(array("OUTCOME" => "OK", "RESULT" => $outcome, "TEST"=>$test));
} catch (Exception $ex) {
    die(json_encode(array("OUTCOME" => "ERROR: " . $ex->getMessage())));
}
?>

