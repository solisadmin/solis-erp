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
    //================================================================================


    session_start();
    
    if (!isset($_SESSION["solis_userid"])) {
        throw new Exception("NO LOG IN!");
    }
    
    if (!isset($_GET["t"])) {
        throw new Exception("INVALID TOKEN");
    }
    if ($_GET["t"] != $_SESSION["token"]) {
        throw new Exception("INVALID TOKEN");
    }
    
    if (!isset($_GET["id_booking_activity"])) {
        throw new Exception("INVALID ID". $_GET["id_booking_activity"]);
    }
    
    $id_booking_activity = $_GET["id_booking_activity"];
    $id_booking = $_GET["id_booking"];
	$id_user = $_SESSION["solis_userid"];
	$uname = $_SESSION["solis_username"];
	$log_status = "DELETE";
    
    require_once("../../connector/pdo_connect_main.php");
    require_once("../../utils/utilities.php");

    $con = pdo_con();
	
    // BOOKING ACTIVITY DETAILS
	$qryBookingActivity = $con->prepare("SELECT *
                                        FROM booking_activity BA
                                        WHERE BA.id_booking_activity = :id_booking_activity
                                        AND BA.active = 1");
	$qryBookingActivity->execute(array(":id_booking_activity"=>$id_booking_activity));
    $row_count_c = $qryBookingActivity->rowCount();

    if ($row_count_c > 0) {
        while ($row = $qryBookingActivity->fetch(PDO::FETCH_ASSOC)) 
        {
            $d_booking_activity = $row['id_booking_activity'];
            $id_booking = $row['id_booking'];
            $activity_service_paid_by = $row['activity_service_paid_by'];
            $id_tour_operator = $row['id_tour_operator'];
            $id_client = $row['id_client'];
            $activity_date = $row['activity_date'];
            $activity_time = $row['activity_time'];
            $activity_booking_date = $row['activity_booking_date'];
            $id_product = $row['id_product'];
            $id_product_service = $row['id_product_service'];
            $activity_name = $row['activity_name'];
            $activity_duration = $row['activity_duration'];
            $id_hotel = $row['id_hotel'];
            $hotelname = $row['hotelname'];
            $activity_pickup_time = $row['activity_pickup_time'];
            $activity_adult_amt = $row['activity_adult_amt'];
            $activity_teen_amt = $row['activity_teen_amt'];
            $activity_child_amt = $row['activity_child_amt'];
            $activity_infant_amt = $row['activity_infant_amt'];
            $activity_infant_amt = $row['activity_infant_amt'];
            $activity_total_pax = $row['activity_total_pax'];
            $id_product_service_claim = $row['id_product_service_claim'];
            $id_product_service_claim_cur = $row['id_product_service_claim_cur'];
            $id_dept = $row['id_dept'];
            $activity_claim_dept = $row['activity_claim_dept'];
            $activity_charge = $row['activity_charge'];
            $activity_adult_claim = $row['activity_adult_claim'];
            $activity_teen_claim = $row['activity_teen_claim'];
            $activity_child_claim = $row['activity_child_claim'];
            $activity_infant_claim = $row['activity_infant_claim'];
            $activity_total_claim = $row['activity_total_claim'];
            $id_product_service_cost = $row['id_product_service_cost'];
            $id_product_service_cost_cur = $row['id_product_service_cost_cur'];
            $activity_adult_cost = $row['activity_adult_cost'];
            $activity_teen_cost = $row['activity_teen_cost'];
            $activity_child_cost = $row['activity_child_cost'];
            $activity_infant_cost = $row['activity_infant_cost'];
            $activity_total_cost = $row['activity_total_cost'];
            $activity_rebate_type = $row['activity_rebate_type'];
            $activity_rebate_approve_by = $row['activity_rebate_approve_by'];
            $activity_discount_percentage = $row['activity_discount_percentage'];
            $activity_adult_claim_after_disc = $row['activity_adult_claim_after_disc'];
            $activity_teen_claim_after_disc = $row['activity_teen_claim_after_disc'];
            $activity_child_claim_after_disc = $row['activity_child_claim_after_disc'];
            $activity_infant_claim_after_disc = $row['activity_infant_claim_after_disc'];
            $activity_total_claim_after_disc = $row['activity_total_claim_after_disc'];
            $activity_client_room_no = $row['activity_client_room_no'];
            $id_language = $row['id_language'];
            $activity_remarks = $row['activity_remarks'];
            $activity_internal_remarks = $row['activity_internal_remarks'];
            $activity_status = $row['activity_status'];
            $id_rep = $row['id_rep'];
            $activity_voucher_no = $row['activity_voucher_no'];
            $activity_remarks = $row['activity_remarks'];
            $activity_internal_remarks = $row['activity_internal_remarks'];
            $activity_status = $row['activity_status'];
            $activity_close = $row['activity_close'];
            $activity_close_on = $row['activity_close_on'];
        }    
	
        $qryBookingActivityDelete = $con->prepare("UPDATE booking_activity SET active=0 WHERE id_booking_activity = :id_booking_activity");
        $qryBookingActivityDelete->execute(array(":id_booking_activity"=>$id_booking_activity));
	
        // BOOKING ATCIVITY CLIENT
        $qryBookingActivityClient = $con->prepare("SELECT * 
                                        FROM booking_activity_client
                                        WHERE id_booking_activity = :id_booking_activity
                                        AND active = 1");
        $qryBookingActivityClient->execute(array(":id_booking_activity"=>$id_booking_activity));
        $row_count_c = $qryBookingActivityClient->rowCount();
        $activity_clients= '';
        if ($row_count_c > 0) 
        {
            while ($row = $qryBookingActivityClient->fetch(PDO::FETCH_ASSOC)) 
            {
            $activity_clients .= $row['id_client'].',';
            }    
        }
        else
        {
             $activity_clients = '0';
        }
        
        $qryBookingActivityClientDelete = $con->prepare("UPDATE booking_activity_client SET active=0 WHERE id_booking_activity = :id_booking_activity");
        $qryBookingActivityClientDelete->execute(array(":id_booking_activity"=>$id_booking_activity));
    
        // BOOKING ACTIVITY LOG
	   $qryBookingActivityDeleteLog = $con->prepare("INSERT INTO booking_activity_log
            (
                    id_booking_activity,
                    id_booking,
                    activity_service_paid_by,
                    id_tour_operator,
                    id_client,
                    activity_date,
                    activity_time,
                    activity_booking_date,
                    id_product,
                    id_product_service,
                    activity_name,
                    activity_duration,
                    id_hotel,
                    hotelname,
                    activity_pickup_time,
                    activity_clients,
                    activity_adult_amt,
                    activity_teen_amt,
                    activity_child_amt,
                    activity_infant_amt,
                    activity_total_pax,
                    id_product_service_claim,
                    id_product_service_claim_cur,
                    id_dept,
                    activity_claim_dept,
                    activity_charge,
                    activity_adult_claim,
                    activity_teen_claim,
                    activity_child_claim,
                    activity_infant_claim,
                    activity_total_claim,
                    id_product_service_cost,
                    id_product_service_cost_cur,
                    activity_adult_cost,
                    activity_teen_cost,
                    activity_child_cost,
                    activity_infant_cost,
                    activity_total_cost,
                    activity_rebate_type,
                    activity_rebate_approve_by,
                    activity_discount_percentage,
                    activity_adult_claim_after_disc,
                    activity_teen_claim_after_disc,
                    activity_child_claim_after_disc,
                    activity_infant_claim_after_disc,
                    activity_total_claim_after_disc,
                    activity_client_room_no,
                    id_language,
                    activity_voucher_no,
                    activity_remarks,
                    activity_internal_remarks,
                    activity_status,
                    id_rep,
                    id_user,
                    uname,
                    log_status
			)
			VALUES
			(
                    :id_booking_activity,
                    :id_booking,
                    :activity_service_paid_by,
                    :id_tour_operator,
                    :id_client,
                    :activity_date,
                    :activity_time,
                    :activity_booking_date,
                    :id_product,
                    :id_product_service,
                    :activity_name,
                    :activity_duration,
                    :id_hotel,
                    :hotelname,
                    :activity_pickup_time,
                    :activity_clients,
                    :activity_adult_amt,
                    :activity_teen_amt,
                    :activity_child_amt,
                    :activity_infant_amt,
                    :activity_total_pax,
                    :id_product_service_claim,
                    :id_product_service_claim_cur,
                    :id_dept,
                    :activity_claim_dept,
                    :activity_charge,
                    :activity_adult_claim,
                    :activity_teen_claim,
                    :activity_child_claim,
                    :activity_infant_claim,
                    :activity_total_claim,
                    :id_product_service_cost,
                    :id_product_service_cost_cur,
                    :activity_adult_cost,
                    :activity_teen_cost,
                    :activity_child_cost,
                    :activity_infant_cost,
                    :activity_total_cost,
                    :activity_rebate_type,
                    :activity_rebate_approve_by,
                    :activity_discount_percentage,
                    :activity_adult_claim_after_disc,
                    :activity_teen_claim_after_disc,
                    :activity_child_claim_after_disc,
                    :activity_infant_claim_after_disc,
                    :activity_total_claim_after_disc,
                    :activity_client_room_no,
                    :id_language,
                    :activity_voucher_no,
                    :activity_remarks,
                    :activity_internal_remarks,
                    :activity_status,
                    :id_rep,
                    :id_user,
                    :uname,
                    :log_status
			)"
        );
        
        $qryBookingActivityDeleteLog->execute(array(
            ":id_booking_activity" => $id_booking_activity,
            ":id_booking" => $id_booking,
            ":activity_service_paid_by" => $activity_service_paid_by,
            ":id_tour_operator" => $id_tour_operator,
            ":id_client" => $id_client,
            ":activity_date" => $activity_date,
            ":activity_time" => $activity_time,
            ":activity_booking_date" => $activity_booking_date,
            ":id_product" => $id_product,
            ":id_product_service" => $id_product_service,
            ":activity_name" => $activity_name,
            ":activity_duration" => $activity_duration,
            ":id_hotel" => $id_hotel,
            ":hotelname" => $hotelname,
            ":activity_pickup_time" => $activity_pickup_time,
            ":activity_clients" => $activity_clients,
            ":activity_adult_amt" => $activity_adult_amt,
            ":activity_teen_amt" => $activity_teen_amt,
            ":activity_child_amt" => $activity_child_amt,
            ":activity_infant_amt" => $activity_infant_amt,
            ":activity_total_pax" => $activity_total_pax,
            ":id_product_service_claim" => $id_product_service_claim,
            ":id_product_service_claim_cur" => $id_product_service_claim_cur,
            ":id_dept" => $id_dept,
            ":activity_claim_dept" => $activity_claim_dept,
            ":activity_charge" => $activity_charge,
            ":activity_adult_claim" => $activity_adult_claim,
            ":activity_teen_claim" => $activity_teen_claim,
            ":activity_child_claim" => $activity_child_claim,
            ":activity_infant_claim" => $activity_infant_claim,
            ":activity_total_claim" => $activity_total_claim,
            ":id_product_service_cost" => $id_product_service_cost,
            ":id_product_service_cost_cur" => $id_product_service_cost_cur,
            ":activity_adult_cost" => $activity_adult_cost,
            ":activity_teen_cost" => $activity_teen_cost,
            ":activity_child_cost" => $activity_child_cost,
            ":activity_infant_cost" => $activity_infant_cost,
            ":activity_total_cost" => $activity_total_cost,
            ":activity_rebate_type" => $activity_rebate_type,
            ":activity_rebate_approve_by" => $activity_rebate_approve_by,
            ":activity_discount_percentage" => $activity_discount_percentage,
            ":activity_adult_claim_after_disc" => $activity_adult_claim_after_disc,
            ":activity_teen_claim_after_disc" => $activity_teen_claim_after_disc,
            ":activity_child_claim_after_disc" => $activity_child_claim_after_disc,
            ":activity_infant_claim_after_disc" => $activity_infant_claim_after_disc,
            ":activity_total_claim_after_disc" => $activity_total_claim_after_disc,
            ":activity_client_room_no" => $activity_client_room_no,
            ":id_language" => $id_language,
            ":activity_voucher_no" => $activity_voucher_no,
            ":activity_remarks" => $activity_remarks,
            ":activity_internal_remarks" => $activity_internal_remarks,
            ":activity_status" => $activity_status,
            ":id_rep" => $id_rep,
            ":id_user" => $id_user,
            ":uname" => $uname,
            ":log_status" => $log_status
        ));	
        $bookingActivity_result= array("OUTCOME" => "OK", "id_booking"=>$id_booking, "id_booking_activity"=>$id_booking_activity);
        echo json_encode($bookingActivity_result);     
        
        }
    else
        {
            $bookingActivity_result= array("OUTCOME" => "FAIL", "id_booking"=>$id_booking, "id_booking_activity"=>$id_booking_activity);
            echo json_encode($bookingActivity_result);  
        }
    
}
catch (Exception $ex) 
{
    die(json_encode(array("OUTCOME" => "ERROR: " . $ex->getMessage())));
}

?>
