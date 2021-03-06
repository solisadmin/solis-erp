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
    
    if (!isset($_GET["t"])) {
        throw new Exception("INVALID TOKEN");
    }
    if ($_GET["t"] != $_SESSION["token"]) {
        throw new Exception("INVALID TOKEN");
    }

    require_once("../../connector/pdo_connect_main.php");
	
    $con = pdo_con();
   
	$id_booking_activity_claim = $_POST["id_booking_activity_claim"];
    $id_booking = $_POST["id_booking"];
    $activity_service_paid_by = trim($_POST["activity_service_paid_by"]);
    $id_tour_operator = trim($_POST["id_tour_operator"]);
    $id_client = $_POST["id_client"];
    $activity_date = trim($_POST["activity_date"]);
    $activity_time = trim($_POST["activity_time"]);    
    $activity_booking_date = trim($_POST["activity_booking_date"]);
    $id_product = trim($_POST["id_product"]);
    $id_product_service = trim($_POST["id_product_service"]);
	$id_product_service_claim = trim($_POST["id_product_service_claim"]);
    $booking_client = $_POST["booking_client"];
    
    $qry_bookingDetails = $con->prepare("
		SELECT * FROM booking WHERE id_booking = :id_booking AND active =1");

	$qry_bookingDetails->execute(array(":id_booking"=>$id_booking));

	$row_count_bookingDetails = $qry_bookingDetails->rowCount();
    
	if ($row_count_bookingDetails > 0) 
	{
		while ($rowDept = $qry_bookingDetails->fetch(PDO::FETCH_ASSOC))
		{
			$id_dept = $rowDept["id_dept"];
		}
	}
    
    $qry_TAXDetails = $con->prepare("
        SELECT * 
        FROM tax_rate
        WHERE tax_dateFrom < :activity_date
        AND id_tax_code = 3
        AND active = 1
        ORDER BY tax_dateFrom DESC
        LIMIT 1");
    $qry_TAXDetails->execute(array(":activity_date"=>$activity_date));

    $row_count_TAXDetails = $qry_TAXDetails->rowCount();
    
    if ($row_count_TAXDetails > 0) 
    {
        while ($rowTAX = $qry_TAXDetails->fetch(PDO::FETCH_ASSOC))
        {
            $tax_value = $rowTAX["tax_value"];
        }
    }
    
     if($activity_service_paid_by == "TO")
    {
        $qry_tourOperator = $con->prepare("SELECT * FROM tbltouroperator WHERE id = :id_tour_operator AND active = 1");
		$qry_tourOperator->execute(array(":id_tour_operator"=>$id_tour_operator));

		$row_count_tourOperator = $qry_tourOperator->rowCount();

		if ($row_count_tourOperator > 0) 
		{
			while ($row = $qry_tourOperator->fetch(PDO::FETCH_ASSOC))
			{
				$id_tax_TO = $row["id_vat"];
			}
		}

    }
    else
    {
        $id_tax_TO = 3;
    }
    
    $qry_activityDetails = $con->prepare("
		SELECT 
			PS.service_name,
			PS.duration,
			PS_CLAIM.id_currency AS id_product_service_claim_cur,
			PS_CLAIM.id_dept,
			PS_CLAIM.charge,
			PS.id_tax AS id_service_tax,
			C.vat_flag AS creditor_vat_flag,
			PS_CLAIM.ps_adult_claim,
			PS_CLAIM.ps_teen_claim,
			PS_CLAIM.ps_child_claim,
			PS_CLAIM.ps_infant_claim,
			PS.is_pakage,
			PS_COST.id_product_service_cost,
			PS_COST.id_currency AS id_product_service_cost_cur,
			PS_COST.ps_adult_cost,
			PS_COST.ps_teen_cost,
			PS_COST.ps_child_cost,
			PS_COST.ps_infant_cost
		FROM
			product_service_claim PS_CLAIM,
			product_service_cost PS_COST,
			product_service PS,
			creditor C
		WHERE PS_CLAIM.id_product_service_cost = PS_COST.id_product_service_cost
		AND PS_CLAIM.id_product_service = PS.id_product_service
		AND PS.id_creditor = C.id_creditor
		AND PS_CLAIM.id_product_service_claim = :id_product_service_claim 
		AND PS_CLAIM.active = 1
		AND PS_COST.active = 1
		AND PS.active =1");
	
    $qry_activityDetails->execute(array(":id_product_service_claim"=>$id_product_service_claim));

		$row_count_activityDetails = $qry_activityDetails->rowCount();

		if ($row_count_activityDetails > 0) 
		{
            while ($rowActivity = $qry_activityDetails->fetch(PDO::FETCH_ASSOC))
			{
				$is_pakage = $rowActivity["is_pakage"];
				$activity_name = $rowActivity["service_name"];
				$activity_duration = $rowActivity["duration"];
                if($activity_duration == ' ' || $activity_duration == '')
                {
                    $activity_duration = null;
                }
				$activity_adult_amt = trim($_POST["activity_adult_amt"]);
				$activity_teen_amt = trim($_POST["activity_teen_amt"]);
				$activity_child_amt = trim($_POST["activity_child_amt"]);
				$activity_infant_amt = trim($_POST["activity_infant_amt"]);
				$activity_total_pax = trim($_POST["activity_total_pax"]);
				$id_product_service_claim_cur = $rowActivity["id_product_service_claim_cur"];
                $id_service_tax = $rowActivity["id_service_tax"];
                $creditor_vat_flag = $rowActivity["creditor_vat_flag"];
				$activity_charge = $rowActivity["charge"];
                
                if ($id_tax_TO ==1)
                {
                    if($creditor_vat_flag == 'Y' && $id_service_tax == '3')
                    {
                          // remove VAT on claim
                           if ($activity_charge == "PAX")
                           {
                                if($rowActivity["ps_adult_cost"] == null)
                                {
                                    $activity_adult_cost = 0;
                                }
                                else
                                {
                                    $activity_adult_cost = $rowActivity["ps_adult_cost"];
                                }

                                if($rowActivity["ps_teen_cost"] == null)
                                {
                                    $activity_teen_cost = 0;
                                }
                                else
                                {
                                    $activity_teen_cost = $rowActivity["ps_teen_cost"];
                                }

                                if($rowActivity["ps_child_cost"] == null)
                                {
                                    $activity_child_cost = 0;
                                }
                                else
                                {
                                    $activity_child_cost = $rowActivity["ps_child_cost"];
                                }

                                if($rowActivity["ps_infant_cost"] == null)
                                {
                                    $activity_infant_cost = 0;
                                }
                                else
                                {
                                    $activity_infant_cost = $rowActivity["ps_infant_cost"];
                                }

                                $activity_total_cost = (($activity_adult_amt * $activity_adult_cost) +($activity_teen_amt * $activity_teen_cost) + ($activity_child_amt * $activity_child_cost) +($activity_infant_amt * $activity_infant_cost));
                                $activity_total_cost_exTAX = $activity_total_cost * ((100 - $tax_value)/100);
                               
                                if($rowActivity["ps_adult_claim"] == null)
                                {
                                    $activity_adult_claim_exTAX = 0;
                                    $activity_adult_claim = 0;
                                }
                                else
                                {
                                    $activity_adult_claim_exTAX = $rowActivity["ps_adult_claim"] * ((100 - $tax_value)/100);
                                    $activity_adult_claim = $activity_adult_claim_exTAX;
                                }

                                if($rowActivity["ps_teen_claim"] == null)
                                {
                                    $activity_teen_claim_exTAX = 0;
                                    $activity_teen_claim = 0;
                                }
                                else
                                {
                                    $activity_teen_claim_exTAX = $rowActivity["ps_teen_claim"] * ((100 - $tax_value)/100);
                                    $activity_teen_claim = $activity_teen_claim_exTAX;
                                }

                                if($rowActivity["ps_child_claim"] == null)
                                {
                                    $activity_child_claim_exTAX = 0;
                                    $activity_child_claim = 0;
                                }
                                else
                                {
                                    $activity_child_claim_exTAX = $rowActivity["ps_child_claim"] * ((100 - $tax_value)/100);
                                    $activity_child_claim = $activity_child_claim_exTAX;
                                }

                                if($rowActivity["ps_infant_claim"] == null)
                                {
                                    $activity_infant_claim_exTAX = 0;
                                    $activity_infant_claim = 0;
                                }
                                else
                                {
                                    $activity_infant_claim_exTAX = $rowActivity["ps_infant_claim"] * ((100 - $tax_value)/100);
                                    $activity_infant_claim = $activity_infant_claim_exTAX;
                                }

                                $activity_total_claim_exTAX = (($activity_adult_amt * $activity_adult_claim_exTAX) +($activity_teen_amt * $activity_teen_claim_exTAX) + ($activity_child_amt * $activity_child_claim_exTAX) +($activity_infant_amt * $activity_infant_claim_exTAX));
                                $activity_total_claim = $activity_total_claim_exTAX;
                               
                           }
                          else
                          {
                                $activity_adult_cost = $rowActivity["ps_adult_cost"];
                                $activity_teen_cost = 0;
                                $activity_child_cost = 0;
                                $activity_infant_cost = 0;
                                $activity_total_cost_exTAX = $rowActivity["ps_adult_cost"] * ((100 - $tax_value)/100);
                                $activity_total_cost = $rowActivity["ps_adult_cost"];
                                $activity_adult_claim_exTAX = $rowActivity["ps_adult_claim"] * ((100 - $tax_value)/100);
                                $activity_adult_claim = $activity_adult_claim_exTAX;
                                $activity_teen_claim_exTAX= 0;
                                $activity_teen_claim = 0;
                                $activity_child_claim_exTAX = 0;
                                $activity_child_claim = 0;
                                $activity_infant_claim_exTAX = 0;
                                $activity_infant_claim = 0;
                                $activity_total_claim_exTAX = $rowActivity["ps_adult_claim"] * ((100 - $tax_value)/100);
                                $activity_total_claim = $activity_total_claim_exTAX;
                          }
                            $id_product_service_cost = $rowActivity["id_product_service_cost"];
                            $id_product_service_cost_cur = $rowActivity["id_product_service_cost_cur"]; 
                        
                            $activity_rebate_claim_type = trim($_POST["activity_rebate_claim_type"]);
                            $activity_rebate_claim_approve_by = trim($_POST["activity_rebate_claim_approve_by"]);
                            $activity_rebate_claim_percentage = trim($_POST["activity_rebate_claim_percentage"]);
                            $activity_adult_claim_rebate = trim($_POST["activity_adult_claim_after_rebate"]);
                            if($activity_adult_claim_rebate !=0)
                            {
                                $activity_adult_claim_after_rebate_exTAX = $activity_adult_claim_rebate * ((100 - $tax_value)/100);
                                $activity_adult_claim_after_rebate = $activity_adult_claim_after_rebate_exTAX;
                            }
                            else
                            {
                                $activity_adult_claim_after_rebate_exTAX = 0;
                                $activity_adult_claim_after_rebate = 0;
                            }
                            $activity_teen_claim_rebate = trim($_POST["activity_teen_claim_after_rebate"]);
                            if($activity_teen_claim_rebate !=0)
                            {
                                $activity_teen_claim_after_rebate_exTAX  = $activity_teen_claim_rebate * ((100 - $tax_value)/100);
                                $activity_teen_claim_after_rebate = $activity_teen_claim_after_rebate_exTAX;
                            }
                            else
                            {
                                $activity_teen_claim_after_rebate_exTAX = 0;
                                $activity_teen_claim_after_rebate = 0;
                            }
                            $activity_child_claim_rebate = trim($_POST["activity_child_claim_after_rebate"]);
                            if($activity_child_claim_rebate !=0)
                            {
                                $activity_child_claim_after_rebate_exTAX  = $activity_child_claim_rebate * ((100 - $tax_value)/100);
                                $activity_child_claim_after_rebate = $activity_child_claim_after_rebate_exTAX;
                            }
                            else
                            {
                                $activity_child_claim_after_rebate_exTAX = 0;
                                $activity_child_claim_after_rebate = 0;
                            }
                            $activity_infant_claim_rebate = trim($_POST["activity_infant_claim_after_rebate"]);
                            if($activity_infant_claim_rebate !=0)
                            {
                                $activity_infant_claim_after_rebate_exTAX  = $activity_infant_claim_rebate * ((100 - $tax_value)/100);
                                $activity_infant_claim_after_rebate = $activity_infant_claim_after_rebate_exTAX;
                            }
                            else
                            {
                                $activity_infant_claim_after_rebate_exTAX = 0;
                                $activity_infant_claim_after_rebate = 0;
                            }
                            
                            if ($activity_rebate_claim_type == 'Percentage')
                            {
                                $activity_total_claim_after_rebate_exTAX = $activity_total_claim_exTAX * ((100-$activity_rebate_claim_percentage)/100);
                                $activity_total_claim_after_rebate = $activity_total_claim_after_rebate_exTAX;
                            }
                            else if ($activity_rebate_claim_type == 'Fixed Tariff')
                            {
                                if ($activity_charge == "PAX")
                                {
                                    $activity_total_claim_after_rebate_exTAX = (($activity_adult_amt * $activity_adult_claim_after_rebate_exTAX) + ($activity_teen_amt * $activity_teen_claim_after_rebate_exTAX) + ($activity_child_amt * $activity_child_claim_after_rebate_exTAX) + ($activity_infant_amt * $activity_infant_claim_after_rebate_exTAX));
                                    $activity_total_claim_after_rebate = $activity_total_claim_after_rebate_exTAX;
                                }
                                else
                                {
                                    $activity_total_claim_after_rebate_exTAX = $activity_adult_claim_after_rebate_exTAX;
                                    $activity_total_claim_after_rebate = $activity_total_claim_after_rebate_exTAX;
                                    
                                }
                            }
                            else if ($activity_rebate_claim_type == 'FOC')
                            {
                                $activity_total_claim_after_rebate = 0;
                                $activity_total_claim_after_rebate_exTAX = 0;
                            }
                            else 
                            {
                                $activity_total_claim_after_rebate_exTAX = $activity_total_claim_exTAX;
                                $activity_total_claim_after_rebate = $activity_total_claim_after_rebate_exTAX;
                            }
                            
                            $activity_rebate_cost_type = trim($_POST["activity_rebate_cost_type"]);
                            $activity_rebate_cost_approve_by = trim($_POST["activity_rebate_cost_approve_by"]);
                            $activity_rebate_cost_percentage = trim($_POST["activity_rebate_cost_percentage"]);
                            $activity_adult_cost_rebate = trim($_POST["activity_adult_cost_after_rebate"]);
                            if($activity_adult_cost_rebate !=0)
                            {
                                $activity_adult_cost_after_rebate_exTAX = $activity_adult_cost_rebate * ((100 - $tax_value)/100);
                                $activity_adult_cost_after_rebate = $activity_adult_cost_rebate;
                            }
                            else
                            {
                                $activity_adult_cost_after_rebate_exTAX = 0;
                                $activity_adult_cost_after_rebate = 0;
                            }
                            $activity_teen_cost_rebate = trim($_POST["activity_teen_cost_after_rebate"]);
                            if($activity_teen_cost_rebate !=0)
                            {
                                $activity_teen_cost_after_rebate_exTAX = $activity_teen_cost_rebate * ((100 - $tax_value)/100);
                                $activity_teen_cost_after_rebate = $activity_teen_cost_rebate;
                            }
                            else
                            {
                                $activity_teen_cost_after_rebate_exTAX = 0;
                                $activity_teen_cost_after_rebate = 0;
                            }
                            $activity_child_cost_rebate = trim($_POST["activity_child_cost_after_rebate"]);
                            if($activity_child_cost_rebate !=0)
                            {
                                $activity_child_cost_after_rebate_exTAX = $activity_child_cost_rebate * ((100 - $tax_value)/100);
                                $activity_child_cost_after_rebate = $activity_child_cost_rebate;
                            }
                            else
                            {
                                $activity_child_cost_after_rebate_exTAX = 0;
                                $activity_child_cost_after_rebate = 0;
                            }
                            $activity_infant_cost_rebate = trim($_POST["activity_infant_cost_after_rebate"]);
                            if($activity_infant_cost_rebate !=0)
                            {
                                $activity_infant_cost_after_rebate_exTAX = $activity_infant_cost_rebate * ((100 - $tax_value)/100);
                                $activity_infant_cost_after_rebate = $activity_infant_cost_rebate;
                            }
                            else
                            {
                                $activity_infant_cost_after_rebate_exTAX = 0;
                                $activity_infant_cost_after_rebate = 0;
                            }
                            if ($activity_rebate_cost_type == 'Percentage')
                            {
                                $activity_total_cost_after_rebate_exTAX = $activity_total_cost_exTAX * ((100-$activity_rebate_cost_percentage)/100);
                                $activity_total_cost_after_rebate = $activity_total_cost * ((100-$activity_rebate_cost_percentage)/100);
                            }
                            else if ($activity_rebate_cost_type == 'Fixed Tariff')
                            {
                                if ($activity_charge == "PAX")
                                {
                                    $activity_total_cost_after_rebate_exTAX = (($activity_adult_amt * $activity_adult_cost_after_rebate_exTAX) + ($activity_teen_amt * $activity_teen_cost_after_rebate_exTAX) + ($activity_child_amt * $activity_child_cost_after_rebate_exTAX) + ($activity_infant_amt * $activity_infant_cost_after_rebate_exTAX));
                                    $activity_total_cost_after_rebate = (($activity_adult_amt * $activity_adult_cost_after_rebate) +($activity_teen_amt * $activity_teen_cost_after_rebate) + ($activity_child_amt * $activity_child_cost_after_rebate) +($activity_infant_amt * $activity_infant_cost_after_rebate));
                                }
                                else
                                {
                                    $activity_total_cost_after_rebate_exTAX = $activity_adult_cost_after_rebate_exTAX;
                                    $activity_total_cost_after_rebate = $activity_adult_cost_after_rebate;
                                }
                            }
                            else if ($activity_rebate_cost_type == 'FOC')
                            {
                                $activity_total_cost_after_rebate_exTAX = 0;
                                $activity_total_cost_after_rebate = 0;
                            }
                            else 
                            {
                                $activity_total_cost_after_rebate_exTAX = $activity_total_cost_exTAX;
                                $activity_total_cost_after_rebate = $activity_total_cost;
                            }
                    }
                    else
                    {
                        // remove VAT on markup
                        if ($activity_charge == "PAX")
                        {
                            if($rowActivity["ps_adult_cost"] == null)
                            {
                                $activity_adult_cost = 0;
                            }
                            else
                            {
                                $activity_adult_cost = $rowActivity["ps_adult_cost"];
                            }

                            if($rowActivity["ps_teen_cost"] == null)
                            {
                                $activity_teen_cost = 0;
                            }
                            else
                            {
                                $activity_teen_cost = $rowActivity["ps_teen_cost"];
                            }

                            if($rowActivity["ps_child_cost"] == null)
                            {
                                $activity_child_cost = 0;
                            }
                            else
                            {
                                $activity_child_cost = $rowActivity["ps_child_cost"];
                            }

                            if($rowActivity["ps_infant_cost"] == null)
                            {
                                $activity_infant_cost = 0;
                            }
                            else
                            {
                                $activity_infant_cost = $rowActivity["ps_infant_cost"];
                            }

                            $activity_total_cost_exTAX = (($activity_adult_amt * $activity_adult_cost) +($activity_teen_amt * $activity_teen_cost) + ($activity_child_amt * $activity_child_cost) +($activity_infant_amt * $activity_infant_cost));
                            $activity_total_cost = (($activity_adult_amt * $activity_adult_cost) +($activity_teen_amt * $activity_teen_cost) + ($activity_child_amt * $activity_child_cost) +($activity_infant_amt * $activity_infant_cost));

                            if($rowActivity["ps_adult_claim"] == null)
                            {
                                $activity_adult_claim_exTAX = 0;
                                $activity_adult_claim = 0;
                            }
                            else
                            {
                                $adult_claim = $rowActivity["ps_adult_claim"];
                                $adult_markup_exTAX = ($adult_claim - $activity_adult_cost) * ((100 - $tax_value)/100);
                                $activity_adult_claim_exTAX =   $adult_markup_exTAX + $activity_adult_cost;
                                $activity_adult_claim = $activity_adult_claim_exTAX;
                            }

                            if($rowActivity["ps_teen_claim"] == null)
                            {
                                $activity_teen_claim_exTAX = 0;
                                $activity_teen_claim = 0;
                            }
                            else
                            {
                                $teen_claim = $rowActivity["ps_teen_claim"];
                                $teen_markup_exTAX = ($teen_claim - $activity_teen_cost) * ((100 - $tax_value)/100);
                                $activity_teen_claim_exTAX =   $teen_markup_exTAX + $activity_teen_cost;
                                $activity_teen_claim = $activity_teen_claim_exTAX;
                            }

                            if($rowActivity["ps_child_claim"] == null)
                            {
                                $activity_child_claim_exTAX = 0;
                                $activity_child_claim = 0;
                            }
                            else
                            {
                                $child_claim = $rowActivity["ps_child_claim"];
                                $child_markup_exTAX = ($child_claim - $activity_child_cost) * ((100 - $tax_value)/100);
                                $activity_child_claim_exTAX =   $child_markup_exTAX + $activity_child_cost;
                                $activity_child_claim = $activity_child_claim_exTAX;
                            }

                            if($rowActivity["ps_infant_claim"] == null)
                            {
                                $activity_infant_claim_exTAX = 0;
                                $activity_infant_claim = 0;
                            }
                            else
                            {
                                $infant_claim = $rowActivity["ps_infant_claim"];
                                $infant_markup_exTAX = ($infant_claim - $activity_infant_cost) * ((100 - $tax_value)/100);
                                $activity_infant_claim_exTAX =   $infant_markup_exTAX + $activity_infant_cost;
                                $activity_infant_claim = $activity_infant_claim_exTAX;
                            }

                            $activity_total_claim_exTAX = (($activity_adult_amt * $activity_adult_claim_exTAX) +($activity_teen_amt * $activity_teen_claim_exTAX) + ($activity_child_amt * $activity_child_claim_exTAX) +($activity_infant_amt * $activity_infant_claim_exTAX));
                            $activity_total_claim = $activity_total_claim_exTAX;
                        }
                        else
                        {
                            $activity_adult_cost = $rowActivity["ps_adult_cost"];
                            $activity_teen_cost = 0;
                            $activity_child_cost = 0;
                            $activity_infant_cost = 0;
                            $activity_total_cost_exTAX = $rowActivity["ps_adult_cost"];
                            $activity_total_cost = $rowActivity["ps_adult_cost"];
                            $adult_claim = $rowActivity["ps_adult_claim"];
                            $adult_markup_exTAX = ($adult_claim - $activity_adult_cost) * ((100 - $tax_value)/100);
                            $activity_adult_claim_exTAX =   $adult_markup_exTAX + $activity_adult_cost;
                            $activity_adult_claim = $activity_adult_claim_exTAX;
                            $activity_teen_claim_exTAX= 0;
                            $activity_teen_claim = 0;
                            $activity_child_claim_exTAX = 0;
                            $activity_child_claim = 0;
                            $activity_infant_claim_exTAX = 0;
                            $activity_infant_claim = 0;
                            $activity_total_claim = $activity_adult_claim;
                            $activity_total_claim_exTAX = $activity_adult_claim_exTAX;
                        }
                        $id_product_service_cost = $rowActivity["id_product_service_cost"];
                        $id_product_service_cost_cur = $rowActivity["id_product_service_cost_cur"]; 
                        
                        $activity_rebate_claim_type = trim($_POST["activity_rebate_claim_type"]);
                        $activity_rebate_claim_approve_by = trim($_POST["activity_rebate_claim_approve_by"]);
                        $activity_rebate_claim_percentage = trim($_POST["activity_rebate_claim_percentage"]);
                        $activity_adult_claim_rebate = trim($_POST["activity_adult_claim_after_rebate"]);
                        if($activity_adult_claim_rebate !=0)
                        {
                            $adult_rebate_markup_exTAX = ($activity_adult_claim_rebate - $activity_adult_cost) * ((100 - $tax_value)/100);
                            $activity_adult_claim_after_rebate_exTAX = $activity_adult_claim_rebate * ((100 - $tax_value)/100);
                            $activity_adult_claim_after_rebate = $activity_adult_claim_after_rebate_exTAX;
                        }
                        else
                        {
                            $activity_adult_claim_after_rebate_exTAX = 0;
                            $activity_adult_claim_after_rebate = 0;
                        }
                        $activity_teen_claim_rebate = trim($_POST["activity_teen_claim_after_rebate"]);
                        if($activity_teen_claim_rebate !=0)
                        {
                            $teen_rebate_markup_exTAX = ($activity_teen_claim_rebate - $activity_teent_cost) * ((100 - $tax_value)/100);
                            $activity_teen_claim_after_rebate_exTAX = $activity_teen_claim_rebate * ((100 - $tax_value)/100);
                            $activity_teen_claim_after_rebate = $activity_teen_claim_after_rebate_exTAX;
                        }
                        else
                        {
                            $activity_teen_claim_after_rebate_exTAX = 0;
                            $activity_teen_claim_after_rebate = 0;
                        }
                        $activity_child_claim_rebate = trim($_POST["activity_child_claim_after_rebate"]);
                        if($activity_child_claim_rebate !=0)
                        {
                            $child_rebate_markup_exTAX = ($activity_child_claim_rebate - $activity_childt_cost) * ((100 - $tax_value)/100);
                            $activity_child_claim_after_rebate_exTAX = $activity_child_claim_rebate * ((100 - $tax_value)/100);
                            $activity_child_claim_after_rebate = $activity_child_claim_after_rebate_exTAX;
                        }
                        else
                        {
                            $activity_child_claim_after_rebate_exTAX = 0;
                            $activity_child_claim_after_rebate = 0;
                        }
                        $activity_infant_claim_rebate = trim($_POST["activity_infant_claim_after_rebate"]);
                        if($activity_infant_claim_rebate !=0)
                        {
                            $infant_rebate_markup_exTAX = ($activity_infant_claim_rebate - $activity_infantt_cost) * ((100 - $tax_value)/100);
                            $activity_infant_claim_after_rebate_exTAX = $activity_infant_claim_rebate * ((100 - $tax_value)/100);
                            $activity_infant_claim_after_rebate = $activity_infant_claim_after_rebate_exTAX;
                        }
                        else
                        {
                            $activity_infant_claim_after_rebate_exTAX = 0;
                            $activity_infant_claim_after_rebate = 0;
                        }
                        
                        if ($activity_rebate_claim_type == 'Percentage')
                        {
                            $activity_total_claim_after_rebate = $activity_total_claim * ((100-$activity_rebate_claim_percentage)/100);
                            $activity_total_claim_after_rebate_exTAX = $activity_total_claim_exTAX * ((100-$activity_rebate_claim_percentage)/100);
                        }
                        else if ($activity_rebate_claim_type == 'Fixed Tariff')
                        {
                            if ($activity_charge == "PAX")
                            {
                                $activity_total_claim_after_rebate = (($activity_adult_amt * $activity_adult_claim_after_rebate) +($activity_teen_amt * $activity_teen_claim_after_rebate) + ($activity_child_amt * $activity_child_claim_after_rebate) +($activity_infant_amt * $activity_infant_claim_after_rebate));
                                $activity_total_claim_after_rebate_exTAX = (($activity_adult_amt * $activity_adult_claim_after_rebate_exTAX) + ($activity_teen_amt * $activity_teen_claim_after_rebate_exTAX) + ($activity_child_amt * $activity_child_claim_after_rebate_exTAX) + ($activity_infant_amt * $activity_infant_claim_after_rebate_exTAX));
                            }
                            else
                            {
                                $activity_total_claim_after_rebate = $activity_adult_claim_after_rebate;
                                $activity_total_claim_after_rebate_exTAX = $activity_adult_claim_after_rebate_exTAX;
                            }
                        }
                        else if ($activity_rebate_claim_type == 'FOC')
                        {
                            $activity_total_claim_after_rebate = 0;
                            $activity_total_claim_after_rebate_exTAX = 0;
                        }
                        else 
                        {
                            $activity_total_claim_after_rebate = $activity_total_claim;
                            $activity_total_claim_after_rebate_exTAX = $activity_total_claim_exTAX;
                        }
                        
                        $activity_rebate_cost_type = trim($_POST["activity_rebate_cost_type"]);
                        $activity_rebate_cost_approve_by = trim($_POST["activity_rebate_cost_approve_by"]);
                        $activity_rebate_cost_percentage = trim($_POST["activity_rebate_cost_percentage"]);
                        $activity_adult_cost_rebate = trim($_POST["activity_adult_cost_after_rebate"]);
                        if($activity_adult_cost_rebate !=0)
                        {
                            $activity_adult_cost_after_rebate_exTAX = $activity_adult_cost_rebate;
                            $activity_adult_cost_after_rebate = $activity_adult_cost_rebate;
                        }
                        else
                        {
                            $activity_adult_cost_after_rebate_exTAX = 0;
                            $activity_adult_cost_after_rebate = 0;
                        }
                        $activity_teen_cost_rebate = trim($_POST["activity_teen_cost_after_rebate"]);
                        if($activity_teen_cost_rebate !=0)
                        {
                            $activity_teen_cost_after_rebate_exTAX = $activity_teen_cost_rebate;
                            $activity_teen_cost_after_rebate = $activity_teen_cost_rebate;
                        }
                        else
                        {
                            $activity_teen_cost_after_rebate_exTAX = 0;
                            $activity_teen_cost_after_rebate = 0;
                        }
                        $activity_child_cost_rebate = trim($_POST["activity_child_cost_after_rebate"]);
                        if($activity_child_cost_rebate !=0)
                        {
                            $activity_child_cost_after_rebate_exTAX = $activity_child_cost_rebate;
                            $activity_child_cost_after_rebate = $activity_child_cost_rebate;
                        }
                        else
                        {
                            $activity_child_cost_after_rebate_exTAX = 0;
                            $activity_child_cost_after_rebate = 0;
                        }
                        $activity_infant_cost_rebate = trim($_POST["activity_infant_cost_after_rebate"]);
                        if($activity_infant_cost_rebate !=0)
                        {
                            $activity_infant_cost_after_rebate_exTAX = $activity_infant_cost_rebate;
                            $activity_infant_cost_after_rebate = $activity_infant_cost_rebate;
                        }
                        else
                        {
                            $activity_infant_cost_after_rebate_exTAX = 0;
                            $activity_infant_cost_after_rebate = 0;
                        }
                        
                        if ($activity_rebate_cost_type == 'Percentage')
                        {
                            $activity_total_cost_after_rebate_exTAX = $activity_total_cost_exTAX * ((100-$activity_rebate_cost_percentage)/100);
                            $activity_total_cost_after_rebate = $activity_total_cost * ((100-$activity_rebate_cost_percentage)/100);
                        }
                        else if ($activity_rebate_cost_type == 'Fixed Tariff')
                        {
                            $activity_total_cost_after_rebate_exTAX = (($activity_adult_amt * $activity_adult_cost_after_rebate_exTAX) + ($activity_teen_amt * $activity_teen_cost_after_rebate_exTAX) + ($activity_child_amt * $activity_child_cost_after_rebate_exTAX) + ($activity_infant_amt * $activity_infant_cost_after_rebate_exTAX));
                            $activity_total_cost_after_rebate = (($activity_adult_amt * $activity_adult_cost_after_rebate) +($activity_teen_amt * $activity_teen_cost_after_rebate) + ($activity_child_amt * $activity_child_cost_after_rebate) +($activity_infant_amt * $activity_infant_cost_after_rebate));
                        }
                        else if ($activity_rebate_cost_type == 'FOC')
                        {
                            $activity_total_cost_after_rebate_exTAX = 0;
                            $activity_total_cost_after_rebate = 0;
                        }
                        else 
                        {
                            $activity_total_cost_after_rebate_exTAX = $activity_total_cost_exTAX;
                            $activity_total_cost_after_rebate = $activity_total_cost;
                        }
                    }
                }
                else
                {
                    if ($creditor_vat_flag == "Y" && $id_service_tax == 3)
                    {
                        // VAT inlucded 
                        if ($activity_charge == "PAX")
                        {
                            if($rowActivity["ps_adult_cost"] == null)
                            {
                                $activity_adult_cost = 0;
                            }
                            else
                            {
                                $activity_adult_cost = $rowActivity["ps_adult_cost"];
                            }

                            if($rowActivity["ps_teen_cost"] == null)
                            {
                                $activity_teen_cost = 0;
                            }
                            else
                            {
                                $activity_teen_cost = $rowActivity["ps_teen_cost"];
                            }

                            if($rowActivity["ps_child_cost"] == null)
                            {
                                $activity_child_cost = 0;
                            }
                            else
                            {
                                $activity_child_cost = $rowActivity["ps_child_cost"];
                            }

                            if($rowActivity["ps_infant_cost"] == null)
                            {
                                $activity_infant_cost = 0;
                            }
                            else
                            {
                                $activity_infant_cost = $rowActivity["ps_infant_cost"];
                            }

                            $activity_total_cost = (($activity_adult_amt * $activity_adult_cost) +($activity_teen_amt * $activity_teen_cost) + ($activity_child_amt * $activity_child_cost) +($activity_infant_amt * $activity_infant_cost));
                            $activity_total_cost_exTAX = $activity_total_cost * ((100 - $tax_value)/100); 

                            if($rowActivity["ps_adult_claim"] == null)
                            {
                                $activity_adult_claim_exTAX = 0;
                                $activity_adult_claim = 0;
                            }
                            else
                            {
                                $activity_adult_claim_exTAX = $rowActivity["ps_adult_claim"] * ((100 - $tax_value)/100);
                                $activity_adult_claim = $rowActivity["ps_adult_claim"];
                            }

                            if($rowActivity["ps_teen_claim"] == null)
                            {
                                $activity_teen_claim_exTAX = 0;
                                $activity_teen_claim = 0;
                            }
                            else
                            {
                                $activity_teen_claim_exTAX = $rowActivity["ps_teen_claim"] * ((100 - $tax_value)/100);
                                $activity_teen_claim = $rowActivity["ps_teen_claim"];
                            }

                            if($rowActivity["ps_child_claim"] == null)
                            {
                                $activity_child_claim_exTAX = 0;
                                $activity_child_claim = 0;
                            }
                            else
                            {
                                $activity_child_claim_exTAX = $rowActivity["ps_child_claim"] * ((100 - $tax_value)/100);
                                $activity_child_claim = $rowActivity["ps_child_claim"];
                            }

                            if($rowActivity["ps_infant_claim"] == null)
                            {
                                $activity_infant_claim_exTAX = 0;
                                $activity_infant_claim = 0;
                            }
                            else
                            {
                                $activity_infant_claim_exTAX = $rowActivity["ps_infant_claim"] * ((100 - $tax_value)/100);
                                $activity_infant_claim = $rowActivity["ps_infant_claim"];
                            }

                            $activity_total_claim_exTAX = (($activity_adult_amt * $activity_adult_claim_exTAX) +($activity_teen_amt * $activity_teen_claim_exTAX) + ($activity_child_amt * $activity_child_claim_exTAX) +($activity_infant_amt * $activity_infant_claim_exTAX));
                            $activity_total_claim = (($activity_adult_amt * $activity_adult_claim) +($activity_teen_amt * $activity_teen_claim) + ($activity_child_amt * $activity_child_claim) +($activity_infant_amt * $activity_infant_claim));
                        }
                        else
                        {
                            $activity_adult_cost = $rowActivity["ps_adult_cost"];
                            $activity_teen_cost = 0;
                            $activity_child_cost = 0;
                            $activity_infant_cost = 0;
                            $activity_total_cost_exTAX = $rowActivity["ps_adult_cost"]* ((100 - $tax_value)/100);
                            $activity_total_cost = $rowActivity["ps_adult_cost"];
                            $activity_adult_claim_exTAX = $rowActivity["ps_adult_claim"] * ((100 - $tax_value)/100);
                            $activity_adult_claim = $rowActivity["ps_adult_claim"];
                            $activity_teen_claim_exTAX= 0;
                            $activity_teen_claim = 0;
                            $activity_child_claim_exTAX = 0;
                            $activity_child_claim = 0;
                            $activity_infant_claim_exTAX = 0;
                            $activity_infant_claim = 0;
                            $activity_total_claim_exTAX = $rowActivity["ps_adult_claim"] * ((100 - $tax_value)/100);
                            $activity_total_claim = $rowActivity["ps_adult_claim"];
                        }
                        $id_product_service_cost = $rowActivity["id_product_service_cost"];
                        $id_product_service_cost_cur = $rowActivity["id_product_service_cost_cur"]; 
                       
                        $activity_rebate_claim_type = trim($_POST["activity_rebate_claim_type"]);
                        $activity_rebate_claim_approve_by = trim($_POST["activity_rebate_claim_approve_by"]);
                        $activity_rebate_claim_percentage = trim($_POST["activity_rebate_claim_percentage"]);
                        $activity_adult_claim_rebate = trim($_POST["activity_adult_claim_after_rebate"]);
                        if($activity_adult_claim_rebate !=0)
                        {
                            $activity_adult_claim_after_rebate_exTAX = $activity_adult_claim_rebate * ((100 - $tax_value)/100);
                            $activity_adult_claim_after_rebate = $activity_adult_claim_rebate;
                        }
                        else
                        {
                            $activity_adult_claim_after_rebate_exTAX = 0;
                            $activity_adult_claim_after_rebate = 0;
                        }
                        $activity_teen_claim_rebate = trim($_POST["activity_teen_claim_after_rebate"]);
                        if($activity_teen_claim_rebate !=0)
                        {
                            $activity_teen_claim_after_rebate_exTAX = $activity_teen_claim_rebate * ((100 - $tax_value)/100);
                            $activity_teen_claim_after_rebate = $activity_teen_claim_rebate;
                        }
                        else
                        {
                            $activity_teen_claim_after_rebate_exTAX = 0;
                            $activity_teen_claim_after_rebate = 0;
                        }
                        $activity_child_claim_rebate = trim($_POST["activity_child_claim_after_rebate"]);
                        if($activity_child_claim_rebate !=0)
                        {
                            $activity_child_claim_after_rebate_exTAX = $activity_child_claim_rebate * ((100 - $tax_value)/100);
                            $activity_child_claim_after_rebate = $activity_child_claim_rebate;
                        }
                        else
                        {
                            $activity_child_claim_after_rebate_exTAX = 0;
                            $activity_child_claim_after_rebate = 0;
                        }
                        $activity_infant_claim_rebate = trim($_POST["activity_infant_claim_after_rebate"]);
                        if($activity_infant_claim_rebate !=0)
                        {
                            $activity_infant_claim_after_rebate_exTAX = $activity_infant_claim_rebate * ((100 - $tax_value)/100);
                            $activity_infant_claim_after_rebate = $activity_infant_claim_rebate;
                        }
                        else
                        {
                            $activity_infant_claim_after_rebate_exTAX = 0;
                            $activity_infant_claim_after_rebate = 0;
                        }
                        
                        if ($activity_rebate_claim_type == 'Percentage')
                        {
                            $activity_total_claim_after_rebate_exTAX = $activity_total_claim_exTAX * ((100-$activity_rebate_claim_percentage)/100);
                            $activity_total_claim_after_rebate = $activity_total_claim * ((100-$activity_rebate_claim_percentage)/100);
                        }
                        else if ($activity_rebate_claim_type == 'Fixed Tariff')
                        {
                            if ($activity_charge == "PAX")
                            {
                                $activity_total_claim_after_rebate_exTAX = (($activity_adult_amt * $activity_adult_claim_after_rebate_exTAX) + ($activity_teen_amt * $activity_teen_claim_after_rebate_exTAX) + ($activity_child_amt * $activity_child_claim_after_rebate_exTAX) + ($activity_infant_amt * $activity_infant_claim_after_rebate_exTAX));
                                $activity_total_claim_after_rebate = (($activity_adult_amt * $activity_adult_claim_after_rebate) +($activity_teen_amt * $activity_teen_claim_after_rebate) + ($activity_child_amt * $activity_child_claim_after_rebate) +($activity_infant_amt * $activity_infant_claim_after_rebate));
                            }
                            else
                            {
                                $activity_total_claim_after_rebate_exTAX = $activity_adult_claim_after_rebate_exTAX;
                                $activity_total_claim_after_rebate = $activity_adult_claim_after_rebate;
                                
                            }
                        }
                        else if ($activity_rebate_claim_type == 'FOC')
                        {
                            $activity_total_claim_after_rebate_exTAX = 0;
                            $activity_total_claim_after_rebate = 0;
                        }
                        else 
                        {
                            $activity_total_claim_after_rebate_exTAX = $activity_total_claim_exTAX;
                            $activity_total_claim_after_rebate = $activity_total_claim;
                        }
                        
                        $activity_rebate_cost_type = trim($_POST["activity_rebate_cost_type"]);
                        $activity_rebate_cost_approve_by = trim($_POST["activity_rebate_cost_approve_by"]);
                        $activity_rebate_cost_percentage = trim($_POST["activity_rebate_cost_percentage"]);
                        $activity_adult_cost_rebate = trim($_POST["activity_adult_cost_after_rebate"]);
                        if($activity_adult_cost_rebate !=0)
                        {
                            $activity_adult_cost_after_rebate_exTAX = $activity_adult_cost_rebate * ((100 - $tax_value)/100);
                            $activity_adult_cost_after_rebate = $activity_adult_cost_rebate;
                        }
                        else
                        {
                            $activity_adult_cost_after_rebate_exTAX = 0;
                            $activity_adult_cost_after_rebate = 0;
                        }
                        $activity_teen_cost_rebate = trim($_POST["activity_teen_cost_after_rebate"]);
                        if($activity_teen_cost_rebate !=0)
                        {
                            $activity_teen_cost_after_rebate_exTAX = $activity_teen_cost_rebate * ((100 - $tax_value)/100);
                            $activity_teen_cost_after_rebate = $activity_teen_cost_rebate;
                        }
                        else
                        {
                            $activity_teen_cost_after_rebate_exTAX = 0;
                            $activity_teen_cost_after_rebate = 0;
                        }
                        $activity_child_cost_rebate = trim($_POST["activity_child_cost_after_rebate"]);
                        if($activity_child_cost_rebate !=0)
                        {
                            $activity_child_cost_after_rebate_exTAX = $activity_child_cost_rebate * ((100 - $tax_value)/100);
                            $activity_child_cost_after_rebate = $activity_child_cost_rebate;
                        }
                        else
                        {
                            $activity_child_cost_after_rebate_exTAX = 0;
                            $activity_child_cost_after_rebate = 0;
                        }
                        $activity_infant_cost_rebate = trim($_POST["activity_infant_cost_after_rebate"]);
                        if($activity_infant_cost_rebate !=0)
                        {
                            $activity_infant_cost_after_rebate_exTAX = $activity_infant_cost_rebate * ((100 - $tax_value)/100);
                            $activity_infant_cost_after_rebate = $activity_infant_cost_rebate;
                        }
                        else
                        {
                            $activity_infant_cost_after_rebate_exTAX = 0;
                            $activity_infant_cost_after_rebate = 0;
                        }
                        if ($activity_rebate_cost_type == 'Percentage')
                        {
                            $activity_total_cost_after_rebate_exTAX = $activity_total_cost_exTAX * ((100-$activity_rebate_cost_percentage)/100);
                            $activity_total_cost_after_rebate = $activity_total_cost * ((100-$activity_rebate_cost_percentage)/100);
                        }
                        else if ($activity_rebate_cost_type == 'Fixed Tariff')
                        {
                            if ($activity_charge == "PAX")
                            {
                                $activity_total_cost_after_rebate_exTAX = (($activity_adult_amt * $activity_adult_cost_after_rebate_exTAX) + ($activity_teen_amt * $activity_teen_cost_after_rebate_exTAX) + ($activity_child_amt * $activity_child_cost_after_rebate_exTAX) + ($activity_infant_amt * $activity_infant_cost_after_rebate_exTAX));
                                $activity_total_cost_after_rebate = (($activity_adult_amt * $activity_adult_cost_after_rebate) +($activity_teen_amt * $activity_teen_cost_after_rebate) + ($activity_child_amt * $activity_child_cost_after_rebate) +($activity_infant_amt * $activity_infant_cost_after_rebate));
                            }
                            else
                            {
                                $activity_total_cost_after_rebate_exTAX = $activity_adult_cost_after_rebate_exTAX;
                                $activity_total_cost_after_rebate = $activity_adult_cost_after_rebate;
                            }
                        }
                        else if ($activity_rebate_cost_type == 'FOC')
                        {
                            $activity_total_cost_after_rebate_exTAX = 0;
                            $activity_total_cost_after_rebate = 0;
                        }
                        else 
                        {
                            $activity_total_cost_after_rebate_exTAX = $activity_total_cost_exTAX;
                            $activity_total_cost_after_rebate = $activity_total_cost;
                        }
                    }
                    else
                    {
                        // VAT on markup
                        if ($activity_charge == "PAX")
                        {
                            if($rowActivity["ps_adult_cost"] == null)
                            {
                                $activity_adult_cost = 0;
                            }
                            else
                            {
                                $activity_adult_cost = $rowActivity["ps_adult_cost"];
                            }

                            if($rowActivity["ps_teen_cost"] == null)
                            {
                                $activity_teen_cost = 0;
                            }
                            else
                            {
                                $activity_teen_cost = $rowActivity["ps_teen_cost"];
                            }

                            if($rowActivity["ps_child_cost"] == null)
                            {
                                $activity_child_cost = 0;
                            }
                            else
                            {
                                $activity_child_cost = $rowActivity["ps_child_cost"];
                            }

                            if($rowActivity["ps_infant_cost"] == null)
                            {
                                $activity_infant_cost = 0;
                            }
                            else
                            {
                                $activity_infant_cost = $rowActivity["ps_infant_cost"];
                            }

                            $activity_total_cost_exTAX = (($activity_adult_amt * $activity_adult_cost) +($activity_teen_amt * $activity_teen_cost) + ($activity_child_amt * $activity_child_cost) +($activity_infant_amt * $activity_infant_cost));
                            $activity_total_cost = (($activity_adult_amt * $activity_adult_cost) +($activity_teen_amt * $activity_teen_cost) + ($activity_child_amt * $activity_child_cost) +($activity_infant_amt * $activity_infant_cost));
                            
                            if($rowActivity["ps_adult_claim"] == null)
                            {
                                $activity_adult_claim_exTAX = 0;
                                $activity_adult_claim = 0;
                            }
                            else
                            {
                                $activity_adult_claim = $rowActivity["ps_adult_claim"];
                                $adult_markup_exTAX = ($activity_adult_claim - $activity_adult_cost)* ((100 - $tax_value)/100);
                                $activity_adult_claim_exTAX = $adult_markup_exTAX + $activity_adult_cost;
                            }

                            if($rowActivity["ps_teen_claim"] == null)
                            {
                                $activity_teen_claim_exTAX = 0;
                                $activity_teen_claim = 0;
                            }
                            else
                            {
                                $activity_teen_claim = $rowActivity["ps_teen_claim"];
                                $teen_markup_exTAX = ($activity_teen_claim - $activity_teen_cost)* ((100 - $tax_value)/100);
                                $activity_teen_claim_exTAX = $teen_markup_exTAX + $activity_teen_cost;
                            }

                            if($rowActivity["ps_child_claim"] == null)
                            {
                                $activity_child_claim_exTAX = 0;
                                $activity_child_claim = 0;
                            }
                            else
                            {
                                $activity_child_claim = $rowActivity["ps_child_claim"];
                                $child_markup_exTAX = ($activity_child_claim - $activity_child_cost)* ((100 - $tax_value)/100);
                                $activity_child_claim_exTAX = $child_markup_exTAX + $activity_child_cost;
                            }

                            if($rowActivity["ps_infant_claim"] == null)
                            {
                                $activity_infant_claim_exTAX = 0;
                                $activity_infant_claim = 0;
                            }
                            else
                            {
                                $activity_infant_claim = $rowActivity["ps_infant_claim"];
                                $infant_markup_exTAX = ($activity_infant_claim - $activity_infant_cost)* ((100 - $tax_value)/100);
                                $activity_infant_claim_exTAX = $infant_markup_exTAX + $activity_infant_cost;
                            }

                            $activity_total_claim_exTAX = (($activity_adult_amt * $activity_adult_claim_exTAX) +($activity_teen_amt * $activity_teen_claim_exTAX) + ($activity_child_amt * $activity_child_claim_exTAX) +($activity_infant_amt * $activity_infant_claim_exTAX));
                            $activity_total_claim = $activity_total_claim_exTAX;
                            
                        }
                        else
                        {
                            $activity_adult_cost = $rowActivity["ps_adult_cost"];
                            $activity_teen_cost = 0;
                            $activity_child_cost = 0;
                            $activity_infant_cost = 0;
                            $activity_total_cost_exTAX = $rowActivity["ps_adult_cost"];
                            $activity_total_cost = $rowActivity["ps_adult_cost"];
                            $activity_adult_claim = $rowActivity["ps_adult_claim"];
                            $adult_markup_exTAX = ($activity_adult_claim - $activity_adult_cost)* ((100 - $tax_value)/100);
                            $activity_adult_claim_exTAX = $adult_markup_exTAX + $activity_adult_cost;
                            $activity_teen_claim = 0;
                            $activity_teen_claim_exTAX= 0;
                            $activity_child_claim = 0;
                            $activity_infant_claim_exTAX = 0;
                            $activity_total_claim_exTAX = $activity_adult_claim_exTAX;
                            $activity_total_claim = $activity_total_claim_exTAX;
                        }
                        $id_product_service_cost = $rowActivity["id_product_service_cost"];
                        $id_product_service_cost_cur = $rowActivity["id_product_service_cost_cur"]; 
                        $activity_rebate_claim_type = trim($_POST["activity_rebate_claim_type"]);
                        $activity_rebate_claim_approve_by = trim($_POST["activity_rebate_claim_approve_by"]);
                        $activity_rebate_claim_percentage = trim($_POST["activity_rebate_claim_percentage"]);
                        $activity_adult_claim_rebate= trim($_POST["activity_adult_claim_after_rebate"]);
                        if($activity_adult_claim_rebate !=0)
                        {
                            $adult_rebate_markup_exTAX = ($activity_adult_claim_rebate - $activity_adult_cost)* ((100 - $tax_value)/100);
                            $activity_adult_claim_after_rebate_exTAX = $adult_rebate_markup_exTAX + $activity_adult_cost;
                            $activity_adult_claim_after_rebate = $activity_adult_claim_after_rebate_exTAX;
                        }
                        else
                        {
                            $activity_adult_claim_after_rebate_exTAX = 0;
                            $activity_adult_claim_after_rebate = 0;
                        }
                        $activity_teen_claim_rebate= trim($_POST["activity_teen_claim_after_rebate"]);
                        if($activity_teen_claim_rebate !=0)
                        {
                            $teen_rebate_markup_exTAX = ($activity_teen_claim_rebate - $activity_teen_cost)* ((100 - $tax_value)/100);
                            $activity_teen_claim_after_rebate_exTAX = $teen_rebate_markup_exTAX + $activity_teen_cost;
                            $activity_teen_claim_after_rebate = $activity_teen_claim_after_rebate_exTAX;
                        }
                        else
                        {
                            $activity_teen_claim_after_rebate_exTAX = 0;
                            $activity_teen_claim_after_rebate = 0;
                        }
                        $activity_child_claim_rebate= trim($_POST["activity_child_claim_after_rebate"]);
                        if($activity_child_claim_rebate !=0)
                        {
                            $child_rebate_markup_exTAX = ($activity_child_claim_rebate - $activity_child_cost)* ((100 - $tax_value)/100);
                            $activity_child_claim_after_rebate_exTAX = $child_rebate_markup_exTAX + $activity_child_cost;
                            $activity_child_claim_after_rebate = $activity_child_claim_after_rebate_exTAX;
                        }
                        else
                        {
                            $activity_child_claim_after_rebate_exTAX = 0;
                            $activity_child_claim_after_rebate = 0;
                        }
                        $activity_infant_claim_rebate= trim($_POST["activity_infant_claim_after_rebate"]);
                        if($activity_child_claim_rebate !=0)
                        {
                            $infant_rebate_markup_exTAX = ($activity_infant_claim_rebate - $activity_infant_cost)* ((100 - $tax_value)/100);
                            $activity_infant_claim_after_rebate_exTAX = $infant_rebate_markup_exTAX + $activity_infant_cost;
                            $activity_infant_claim_after_rebate = $activity_infant_claim_after_rebate_exTAX;
                        }
                        else
                        {
                            $activity_infant_claim_after_rebate_exTAX = 0;
                            $activity_infant_claim_after_rebate = 0;
                        }
                         if ($activity_rebate_claim_type == 'Percentage')
                        {
                            $activity_total_claim_after_rebate = $activity_total_claim * ((100-$activity_rebate_claim_percentage)/100);
                            $activity_total_claim_after_rebate_exTAX = $activity_total_claim_exTAX * ((100-$activity_rebate_claim_percentage)/100);
                        }
                        else if ($activity_rebate_claim_type == 'Fixed Tariff')
                        {
                            if ($activity_charge == "PAX")
                            {
                                $activity_total_claim_after_rebate_exTAX = (($activity_adult_amt * $activity_adult_claim_after_rebate_exTAX) + ($activity_teen_amt * $activity_teen_claim_after_rebate_exTAX) + ($activity_child_amt * $activity_child_claim_after_rebate_exTAX) + ($activity_infant_amt * $activity_infant_claim_after_rebate_exTAX));
                                $activity_total_claim_after_rebate = (($activity_adult_amt * $activity_adult_claim_after_rebate) +($activity_teen_amt * $activity_teen_claim_after_rebate) + ($activity_child_amt * $activity_child_claim_after_rebate) +($activity_infant_amt * $activity_infant_claim_after_rebate));
                            }
                            else
                            {
                                $activity_total_claim_after_rebate_exTAX = $activity_adult_claim_after_rebate_exTAX;
                                $activity_total_claim_after_rebate = $activity_adult_claim_after_rebate;
                                
                            }
                        }
                        else if ($activity_rebate_claim_type == 'FOC')
                        {
                            $activity_total_claim_after_rebate_exTAX = 0;
                            $activity_total_claim_after_rebate = 0;
                        }
                        else 
                        {
                            $activity_total_claim_after_rebate_exTAX = $activity_total_claim_exTAX;
                            $activity_total_claim_after_rebate = $activity_total_claim;
                        }
                         $activity_rebate_cost_type = trim($_POST["activity_rebate_cost_type"]);
                        $activity_rebate_cost_approve_by = trim($_POST["activity_rebate_cost_approve_by"]);
                        $activity_rebate_cost_percentage = trim($_POST["activity_rebate_cost_percentage"]);
                        $activity_adult_cost_rebate = trim($_POST["activity_adult_cost_after_rebate"]);
                        if($activity_adult_cost_rebate !=0)
                        {
                            $activity_adult_cost_after_rebate_exTAX = $activity_adult_cost_rebate;
                            $activity_adult_cost_after_rebate = $activity_adult_cost_rebate;
                        }
                        else
                        {
                            $activity_adult_cost_after_rebate_exTAX = 0;
                            $activity_adult_cost_after_rebate = 0;
                        }
                        $activity_teen_cost_rebate = trim($_POST["activity_teen_cost_after_rebate"]);
                        if($activity_teen_cost_rebate !=0)
                        {
                            $activity_teen_cost_after_rebate_exTAX = $activity_teen_cost_rebate;
                            $activity_teen_cost_after_rebate = $activity_teen_cost_rebate;
                        }
                        else
                        {
                            $activity_teen_cost_after_rebate_exTAX = 0;
                            $activity_teen_cost_after_rebate = 0;
                        }
                        $activity_child_cost_rebate = trim($_POST["activity_child_cost_after_rebate"]);
                        if($activity_child_cost_rebate !=0)
                        {
                            $activity_child_cost_after_rebate_exTAX = $activity_child_cost_rebate;
                            $activity_child_cost_after_rebate = $activity_child_cost_rebate;
                        }
                        else
                        {
                            $activity_child_cost_after_rebate_exTAX = 0;
                            $activity_child_cost_after_rebate = 0;
                        }
                        $activity_infant_cost_rebate = trim($_POST["activity_infant_cost_after_rebate"]);
                        if($activity_infant_cost_rebate !=0)
                        {
                            $activity_infant_cost_after_rebate_exTAX = $activity_infant_cost_rebate;
                            $activity_infant_cost_after_rebate = $activity_infant_cost_rebate;
                        }
                        else
                        {
                            $activity_infant_cost_after_rebate_exTAX = 0;
                            $activity_infant_cost_after_rebate = 0;
                        }
                        
                        if ($activity_rebate_cost_type == 'Percentage')
                        {
                            $activity_total_cost_after_rebate_exTAX = $activity_total_cost_exTAX * ((100-$activity_rebate_cost_percentage)/100);
                            $activity_total_cost_after_rebate = $activity_total_cost * ((100-$activity_rebate_cost_percentage)/100);
                        }
                        else if ($activity_rebate_cost_type == 'Fixed Tariff')
                        {
                            if ($activity_charge == "PAX")
                            {
                                $activity_total_cost_after_rebate_exTAX = (($activity_adult_amt * $activity_adult_cost_after_rebate_exTAX) + ($activity_teen_amt * $activity_teen_cost_after_rebate_exTAX) + ($activity_child_amt * $activity_child_cost_after_rebate_exTAX) + ($activity_infant_amt * $activity_infant_cost_after_rebate_exTAX));
                                $activity_total_cost_after_rebate = (($activity_adult_amt * $activity_adult_cost_after_rebate) +($activity_teen_amt * $activity_teen_cost_after_rebate) + ($activity_child_amt * $activity_child_cost_after_rebate) +($activity_infant_amt * $activity_infant_cost_after_rebate));
                            }
                            else
                            {
                                $activity_total_cost_after_rebate_exTAX = $activity_adult_cost_after_rebate_exTAX;
                                $activity_total_cost_after_rebate = $activity_adult_cost_after_rebate;
                                
                            }
                        }
                        else if ($activity_rebate_cost_type == 'FOC')
                        {
                            $activity_total_cost_after_rebate_exTAX = 0;
                            $activity_total_cost_after_rebate = 0;
                        }
                        else 
                        {
                            $activity_total_cost_after_rebate_exTAX = $activity_total_cost_exTAX;
                            $activity_total_cost_after_rebate = $activity_total_cost;
                        }
                    }
                }
                
        }
        }
        $activity_remarks = trim($_POST["activity_remarks"]);
        $activity_internal_remarks = trim($_POST["activity_internal_remarks"]);
        $activity_status = trim($_POST["activity_status"]);
        $created_by = $_SESSION["solis_userid"];
        $created_name = $_SESSION["solis_username"];
        $id_user = $_SESSION["solis_userid"];
        $uname = $_SESSION["solis_username"];
        $log_status = "CREATE";
        if ($activity_booking_date == "") 
        {
            $activity_booking_date = date("Y-m-d");
        }

        if ($activity_time == '')
        {
            $activity_time = null;
        }	
      
        //BOOKING ACTIVITY CLAIM
        $sqlActivityClaim = "SELECT * FROM booking_activity_claim WHERE id_booking_activity_claim = :id_booking_activity_claim";
        $stmt = $con->prepare($sqlActivityClaim);
        $stmt->execute(array(":id_booking_activity_claim" => $id_booking_activity_claim));
        if ($rw = $stmt->fetch(PDO::FETCH_ASSOC)) {
            throw new Exception("DUPLICATE SERVICES!");
        }
        $sqlSaveActivityClaim= "
            INSERT INTO booking_activity_claim
                (
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
                    activity_adult_amt,
                    activity_teen_amt,
                    activity_child_amt,
                    activity_infant_amt,
                    activity_total_pax,
                    id_product_service_claim,
                    id_product_service_claim_cur,
                    id_dept,
                    activity_charge,
                    id_service_tax,
                    tax_value,
                    activity_adult_claim_exTAX,
                    activity_adult_claim,
                    activity_teen_claim_exTAX,
                    activity_teen_claim,
                    activity_child_claim_exTAX,
                    activity_child_claim,
                    activity_infant_claim_exTAX,
                    activity_infant_claim,
                    activity_total_claim_exTAX,
                    activity_total_claim,
                    activity_rebate_claim_type,
                    activity_rebate_claim_approve_by,
                    activity_rebate_claim_percentage,
                    activity_adult_claim_rebate,
                    activity_adult_claim_after_rebate_exTAX,
                    activity_adult_claim_after_rebate,
                    activity_teen_claim_rebate,
                    activity_teen_claim_after_rebate,
                    activity_teen_claim_after_rebate_exTAX,
                    activity_child_claim_rebate,
                    activity_child_claim_after_rebate,
                    activity_child_claim_after_rebate_exTAX,
                    activity_infant_claim_rebate,
                    activity_infant_claim_after_rebate,
                    activity_infant_claim_after_rebate_exTAX,
                    activity_total_claim_after_rebate_exTAX,
                    activity_total_claim_after_rebate,
                    activity_remarks,
                    activity_internal_remarks,
                    activity_status,
                    created_by,
                    created_name)
                VALUES
                (
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
                    :activity_adult_amt,
                    :activity_teen_amt,
                    :activity_child_amt,
                    :activity_infant_amt,
                    :activity_total_pax,
                    :id_product_service_claim,
                    :id_product_service_claim_cur,
                    :id_dept,
                    :activity_charge,
                    :id_service_tax,
                    :tax_value,
                    :activity_adult_claim_exTAX,
                    :activity_adult_claim,
                    :activity_teen_claim_exTAX,
                    :activity_teen_claim,
                    :activity_child_claim_exTAX,
                    :activity_child_claim,
                    :activity_infant_claim_exTAX,
                    :activity_infant_claim,
                    :activity_total_claim_exTAX,
                    :activity_total_claim,
                    :activity_rebate_claim_type,
                    :activity_rebate_claim_approve_by,
                    :activity_rebate_claim_percentage,
                    :activity_adult_claim_rebate,
                    :activity_adult_claim_after_rebate_exTAX,
                    :activity_adult_claim_after_rebate,
                    :activity_teen_claim_rebate,
                    :activity_teen_claim_after_rebate,
                    :activity_teen_claim_after_rebate_exTAX,
                    :activity_child_claim_rebate,
                    :activity_child_claim_after_rebate,
                    :activity_child_claim_after_rebate_exTAX,
                    :activity_infant_claim_rebate,
                    :activity_infant_claim_after_rebate,
                    :activity_infant_claim_after_rebate_exTAX,
                    :activity_total_claim_after_rebate_exTAX,
                    :activity_total_claim_after_rebate,
				    :activity_remarks,
				    :activity_internal_remarks,
				    :activity_status,
                    :created_by,
                    :created_name 
                )";

        $stmt = $con->prepare($sqlSaveActivityClaim);
        $stmt->execute(array(
                ":id_booking"                                   =>$id_booking,
				":activity_service_paid_by"              =>$activity_service_paid_by,
                ":id_tour_operator"                          =>$id_tour_operator,
                ":id_client"                                        =>$id_client,
                 ":activity_date"                                =>$activity_date,
                 ":activity_time"                                =>$activity_time,
                 ":activity_booking_date"                  =>$activity_booking_date,
                 ":id_product"                                   =>$id_product,
                 ":id_product_service"                       =>$id_product_service,
                 ":activity_name"                               =>$activity_name,
                 ":activity_duration"                           =>$activity_duration,
                 ":activity_adult_amt"                          =>$activity_adult_amt,
                 ":activity_teen_amt"                            =>$activity_teen_amt,
                 ":activity_child_amt"                            =>$activity_child_amt,
                 ":activity_infant_amt"                          =>$activity_infant_amt,
                 ":activity_total_pax"                            =>$activity_total_pax,
                 ":id_product_service_claim"                =>$id_product_service_claim,
                 ":id_product_service_claim_cur"          =>$id_product_service_claim_cur,
                 ":id_dept"                                            =>$id_dept,
                 ":activity_charge"                                 =>$activity_charge,
                 ":id_service_tax"                                   =>$id_service_tax,
                 ":tax_value"                                          =>$tax_value,
                 ":activity_adult_claim_exTAX"               =>$activity_adult_claim_exTAX,
                 ":activity_adult_claim"                           =>$activity_adult_claim,
                 ":activity_teen_claim_exTAX"                =>$activity_teen_claim_exTAX,
                 ":activity_teen_claim"                            =>$activity_teen_claim,
                 ":activity_child_claim_exTAX"                =>$activity_child_claim_exTAX,
                 ":activity_child_claim"                            =>$activity_child_claim,
                 ":activity_infant_claim_exTAX"                =>$activity_infant_claim_exTAX,
                 ":activity_infant_claim"                           =>$activity_infant_claim,
                 ":activity_total_claim_exTAX"                  =>$activity_total_claim_exTAX,
                 ":activity_total_claim"                              =>$activity_total_claim,
                 ":activity_rebate_claim_type"                   =>$activity_rebate_claim_type,
                 ":activity_rebate_claim_approve_by"                 =>$activity_rebate_claim_approve_by,
                 ":activity_rebate_claim_percentage"               =>$activity_rebate_claim_percentage,
                 ":activity_adult_claim_rebate"                                =>$activity_adult_claim_rebate,
                 ":activity_adult_claim_after_rebate_exTAX"  =>$activity_adult_claim_after_rebate_exTAX,
                 ":activity_adult_claim_after_rebate"              =>$activity_adult_claim_after_rebate, 
                 ":activity_teen_claim_rebate"                                  =>$activity_teen_claim_rebate,
                 ":activity_teen_claim_after_rebate"               =>$activity_teen_claim_after_rebate,
                 ":activity_teen_claim_after_rebate_exTAX"    =>$activity_teen_claim_after_rebate_exTAX,
                 ":activity_child_claim_rebate"                                  =>$activity_child_claim_rebate,
                 ":activity_child_claim_after_rebate"               =>$activity_child_claim_after_rebate,
                 ":activity_child_claim_after_rebate_exTAX"   =>$activity_child_claim_after_rebate_exTAX,
                 ":activity_infant_claim_rebate"                                =>$activity_infant_claim_rebate,
                 ":activity_infant_claim_after_rebate"             =>$activity_infant_claim_after_rebate,
                 ":activity_infant_claim_after_rebate_exTAX" =>$activity_infant_claim_after_rebate_exTAX,
                 ":activity_total_claim_after_rebate_exTAX"   =>$activity_total_claim_after_rebate_exTAX,
                 ":activity_total_claim_after_rebate"              =>$activity_total_claim_after_rebate,
                 ":activity_remarks"                                    =>$activity_remarks,
                 ":activity_internal_remarks"                      =>$activity_internal_remarks,
                 ":activity_status"                                       =>$activity_status,
                 ":created_by"                                            =>$created_by,
                 ":created_name"                                       =>$created_name
			));
        $id_booking_activity_claim = $con->lastInsertId();    
           
         // CLIENT ACTIVITY
        $sqlClientActivity = "INSERT INTO booking_activity_client (id_client, id_booking_activity_claim,id_booking) 
        VALUES (:booking_client, :id_booking_activity_claim,:id_booking)";

        $stmt = $con->prepare($sqlClientActivity);
        $data = $booking_client;

        foreach($data as $d) {
            $stmt->execute(array(':id_booking_activity_claim' => $id_booking_activity_claim,':id_booking' => $id_booking, ':booking_client' => $d));
        }
            
        // BOOKING ACTIVITY CLAIM LOG
        $sqlSaveActivityClaimLog= "
        INSERT INTO booking_activity_claim_log
            (
                id_booking_activity_claim,
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
                activity_clients,
                activity_adult_amt,
                activity_teen_amt,
                activity_child_amt,
                activity_infant_amt,
                activity_total_pax,
                id_product_service_claim,
                id_product_service_claim_cur,
                id_dept,
                activity_charge,
                id_service_tax,
                tax_value,
                activity_adult_claim_exTAX,
                activity_adult_claim,
                activity_teen_claim_exTAX,
                activity_teen_claim,
                activity_child_claim_exTAX,
                activity_child_claim,
                activity_infant_claim_exTAX,
                activity_infant_claim,
                activity_total_claim_exTAX,
                activity_total_claim,
                activity_rebate_claim_type,
                activity_rebate_claim_approve_by,
                activity_rebate_claim_percentage,
                activity_adult_claim_rebate,
                activity_adult_claim_after_rebate_exTAX,
                activity_adult_claim_after_rebate,
                activity_teen_claim_rebate,
                activity_teen_claim_after_rebate,
                activity_teen_claim_after_rebate_exTAX,
                activity_child_claim_rebate,
                activity_child_claim_after_rebate,
                activity_child_claim_after_rebate_exTAX,
                activity_infant_claim_rebate,
                activity_infant_claim_after_rebate,
                activity_infant_claim_after_rebate_exTAX,
                activity_total_claim_after_rebate_exTAX,
                activity_total_claim_after_rebate,
                activity_remarks,
                activity_internal_remarks,
                activity_status,
                id_user,
                uname,
                log_status
            )
            VALUES
            (
                :id_booking_activity_claim,
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
                :activity_clients,
                :activity_adult_amt,
                :activity_teen_amt,
                :activity_child_amt,
                :activity_infant_amt,
                :activity_total_pax,
                :id_product_service_claim,
                :id_product_service_claim_cur,
                :id_dept,
                :activity_charge,
                :id_service_tax,
                :tax_value,
                :activity_adult_claim_exTAX,
                :activity_adult_claim,
                :activity_teen_claim_exTAX,
                :activity_teen_claim,
                :activity_child_claim_exTAX,
                :activity_child_claim,
                :activity_infant_claim_exTAX,
                :activity_infant_claim,
                :activity_total_claim_exTAX,
                :activity_total_claim,
                :activity_rebate_claim_type,
                :activity_rebate_claim_approve_by,
                :activity_rebate_claim_percentage,
                :activity_adult_claim_rebate,
                :activity_adult_claim_after_rebate_exTAX,
                :activity_adult_claim_after_rebate,
                :activity_teen_claim_rebate,
                :activity_teen_claim_after_rebate,
                :activity_teen_claim_after_rebate_exTAX,
                :activity_child_claim_rebate,
                :activity_child_claim_after_rebate,
                :activity_child_claim_after_rebate_exTAX,
                :activity_infant_claim_rebate,
                :activity_infant_claim_after_rebate,
                :activity_infant_claim_after_rebate_exTAX,
                :activity_total_claim_after_rebate_exTAX,
                :activity_total_claim_after_rebate,
                :activity_remarks,
                :activity_internal_remarks,
                :activity_status,
                :id_user,
                :uname,
                :log_status
            )";

        $stmt = $con->prepare($sqlSaveActivityClaimLog);
                $stmt->execute(array(
                ":id_booking_activity_claim"                      =>$id_booking_activity_claim,
                ":id_booking"                                             =>$id_booking,
                ":activity_service_paid_by"                        =>$activity_service_paid_by,
                ":id_tour_operator"                                    =>$id_tour_operator,
                ":id_client"                                                 =>$id_client,
                 ":activity_date"                                         =>$activity_date,
                 ":activity_time"                                         =>$activity_time,
                 ":activity_booking_date"                           =>$activity_booking_date,
                 ":id_product"                                            =>$id_product,
                 ":id_product_service"                               =>$id_product_service,
                 ":activity_name"                                       =>$activity_name,
                 ":activity_duration"                                  =>$activity_duration,
                 ":activity_clients"                                     =>implode( ", ", $booking_client ),
                 ":activity_adult_amt"                                =>$activity_adult_amt,
                 ":activity_teen_amt"                                 =>$activity_teen_amt,
                 ":activity_child_amt"                                 =>$activity_child_amt,
                 ":activity_infant_amt"                                =>$activity_infant_amt,
                 ":activity_total_pax"                                   =>$activity_total_pax,
                 ":id_product_service_claim"                       =>$id_product_service_claim,
                 ":id_product_service_claim_cur"                =>$id_product_service_claim_cur,
                 ":id_dept"                                                   =>$id_dept,
                 ":activity_charge"                                        =>$activity_charge,
                 ":id_service_tax"                                          =>$id_service_tax,
                 ":tax_value"                                                 =>$tax_value,
                 ":activity_adult_claim_exTAX"                     =>$activity_adult_claim_exTAX,
                 ":activity_adult_claim"                                 =>$activity_adult_claim,
                 ":activity_teen_claim_exTAX"                      =>$activity_teen_claim_exTAX,
                 ":activity_teen_claim"                                  =>$activity_teen_claim,
                 ":activity_child_claim_exTAX"                      =>$activity_child_claim_exTAX,
                 ":activity_child_claim"                                  =>$activity_child_claim,
                 ":activity_infant_claim_exTAX"                     =>$activity_infant_claim_exTAX,
                 ":activity_infant_claim"                                 =>$activity_infant_claim,
                 ":activity_total_claim_exTAX"                       =>$activity_total_claim_exTAX,
                 ":activity_total_claim"                                   =>$activity_total_claim,
                 ":activity_rebate_claim_type"                       =>$activity_rebate_claim_type,
                 ":activity_rebate_claim_approve_by"            =>$activity_rebate_claim_approve_by,
                 ":activity_rebate_claim_percentage"             =>$activity_rebate_claim_percentage,
                 ":activity_adult_claim_rebate"                       =>$activity_adult_claim_rebate,
                 ":activity_adult_claim_after_rebate_exTAX"  =>$activity_adult_claim_after_rebate_exTAX,
                 ":activity_adult_claim_after_rebate"              =>$activity_adult_claim_after_rebate, 
                 ":activity_teen_claim_rebate"                        =>$activity_teen_claim_rebate,
                 ":activity_teen_claim_after_rebate"               =>$activity_teen_claim_after_rebate,
                 ":activity_teen_claim_after_rebate_exTAX"    =>$activity_teen_claim_after_rebate_exTAX,
                 ":activity_child_claim_rebate"                        =>$activity_child_claim_rebate,
                 ":activity_child_claim_after_rebate"               =>$activity_child_claim_after_rebate,
                 ":activity_child_claim_after_rebate_exTAX"   =>$activity_child_claim_after_rebate_exTAX,
                 ":activity_infant_claim_rebate"                      =>$activity_infant_claim_rebate,
                 ":activity_infant_claim_after_rebate"             =>$activity_infant_claim_after_rebate,
                 ":activity_infant_claim_after_rebate_exTAX" =>$activity_infant_claim_after_rebate_exTAX,
                 ":activity_total_claim_after_rebate_exTAX"   =>$activity_total_claim_after_rebate_exTAX,
                 ":activity_total_claim_after_rebate"              =>$activity_total_claim_after_rebate,
                 ":activity_remarks"                                        =>$activity_remarks,
                 ":activity_internal_remarks"                           =>$activity_internal_remarks,
                 ":activity_status"                                            =>$activity_status,
				 ":id_user"                                                        => $id_user,
				 ":uname"                                                         => $uname,
				 ":log_status"                                                    => $log_status
            ));
    
        if($is_pakage == "N")
        {
             // BOOKING ACTIVITY COST
            $sqlSaveActivityCost ="
                INSERT INTO booking_activity_cost
                    (
                        id_booking_activity_claim,
                        id_booking,
                        id_product,
                        id_product_service,
                        id_product_service_claim,
                        id_product_service_cost,
                        id_product_service_cost_cur,
                        activity_adult_amt,
                        activity_teen_amt,
                        activity_child_amt,
                        activity_infant_amt,
                        activity_total_pax,
                        activity_adult_cost,
                        activity_teen_cost,
                        activity_child_cost,
                        activity_infant_cost,
                        activity_total_cost_exTAX,
                        activity_total_cost,
                        activity_rebate_cost_type,
                        activity_rebate_cost_approve_by,
                        activity_rebate_cost_percentage,
                        activity_adult_cost_rebate,
                        activity_adult_cost_after_rebate_exTAX,
                        activity_adult_cost_after_rebate,
                        activity_teen_cost_rebate,
                        activity_teen_cost_after_rebate,
                        activity_teen_cost_after_rebate_exTAX,
                        activity_child_cost_rebate,
                        activity_child_cost_after_rebate,
                        activity_child_cost_after_rebate_exTAX,
                        activity_infant_cost_rebate,
                        activity_infant_cost_after_rebate,
                        activity_infant_cost_after_rebate_exTAX,
                        activity_total_cost_after_rebate_exTAX,
                        activity_total_cost_after_rebate,
                        created_by,
                        created_name
                    )
                 VALUES
                    (
                        :id_booking_activity_claim,
                        :id_booking,
                        :id_product,
                        :id_product_service,
                        :id_product_service_claim,
                        :id_product_service_cost,
                        :id_product_service_cost_cur,
                        :activity_adult_amt,
                        :activity_teen_amt,
                        :activity_child_amt,
                        :activity_infant_amt,
                        :activity_total_pax,
                        :activity_adult_cost,
                        :activity_teen_cost,
                        :activity_child_cost,
                        :activity_infant_cost,
                        :activity_total_cost_exTAX,
                        :activity_total_cost,
                        :activity_rebate_cost_type,
                        :activity_rebate_cost_approve_by,
                        :activity_rebate_cost_percentage,
                        :activity_adult_cost_rebate,
                        :activity_adult_cost_after_rebate_exTAX,
                        :activity_adult_cost_after_rebate,
                        :activity_teen_cost_rebate,
                        :activity_teen_cost_after_rebate,
                        :activity_teen_cost_after_rebate_exTAX,
                        :activity_child_cost_rebate,
                        :activity_child_cost_after_rebate,
                        :activity_child_cost_after_rebate_exTAX,
                        :activity_infant_cost_rebate,
                        :activity_infant_cost_after_rebate,
                        :activity_infant_cost_after_rebate_exTAX,
                        :activity_total_cost_after_rebate_exTAX,
                        :activity_total_cost_after_rebate,
                        :created_by,
                        :created_name
                    )";

                $stmt = $con->prepare($sqlSaveActivityCost);
                $stmt->execute(array(
                ":id_booking_activity_claim"            =>$id_booking_activity_claim,
                ":id_booking"                                   =>$id_booking,
                ":id_product"                                   =>$id_product,
                ":id_product_service"                       =>$id_product_service,
                ":id_product_service_claim"             =>$id_product_service_claim,
                ":id_product_service_cost"              =>$id_product_service_cost,
                ":id_product_service_cost_cur"       =>$id_product_service_cost_cur,
                ":activity_adult_amt"                        =>$activity_adult_amt,
                ":activity_teen_amt"                         =>$activity_teen_amt,
                ":activity_child_amt"                        =>$activity_child_amt,
                ":activity_infant_amt"                      =>$activity_infant_amt,
                ":activity_total_pax"                         =>$activity_total_pax,
                ":activity_adult_cost"                       => $activity_adult_cost,
                ":activity_teen_cost"                        => $activity_teen_cost,
                ":activity_child_cost"                        => $activity_child_cost,
                ":activity_infant_cost"                      => $activity_infant_cost,
                ":activity_total_cost_exTAX"            =>$activity_total_cost_exTAX,
                ":activity_total_cost"                        => $activity_total_cost,
                ":activity_rebate_cost_type"            => $activity_rebate_cost_type,
                ":activity_rebate_cost_approve_by" => $activity_rebate_cost_approve_by,
                ":activity_rebate_cost_percentage"  => $activity_rebate_cost_percentage,
                ":activity_adult_cost_rebate"            => $activity_adult_cost_rebate,
                ":activity_adult_cost_after_rebate_exTAX" => $activity_adult_cost_after_rebate_exTAX,
                ":activity_adult_cost_after_rebate"   => $activity_adult_cost_after_rebate,
                ":activity_teen_cost_rebate"             => $activity_teen_cost_rebate,
                ":activity_teen_cost_after_rebate"    => $activity_teen_cost_after_rebate,
                ":activity_teen_cost_after_rebate_exTAX" => $activity_teen_cost_after_rebate_exTAX,
                ":activity_child_cost_rebate"             => $activity_child_cost_rebate,
                ":activity_child_cost_after_rebate"    => $activity_child_cost_after_rebate,
                ":activity_child_cost_after_rebate_exTAX"   =>$activity_child_cost_after_rebate_exTAX,
                ":activity_infant_cost_rebate"                      =>$activity_infant_cost_rebate,
                ":activity_infant_cost_after_rebate"             =>$activity_infant_cost_after_rebate,
                ":activity_infant_cost_after_rebate_exTAX"  =>$activity_infant_cost_after_rebate_exTAX,
                ":activity_total_cost_after_rebate_exTAX"    => $activity_total_cost_after_rebate_exTAX,
                ":activity_total_cost_after_rebate"                =>$activity_total_cost_after_rebate,   
                ":created_by"                                   =>$created_by,
                ":created_name"                              =>$created_name
                ));

            $id_booking_activity_cost = $con->lastInsertId();    

            // BOOKING ACTIVITY COST LOG
            $sqlSaveActivityCostLog ="
                INSERT INTO booking_activity_cost_log
                    (
                        id_booking_activity_cost,
                        id_booking_activity_claim,
                        id_booking,
                        id_product,
                        id_product_service,
                        id_product_service_claim,
                        id_product_service_cost,
                        id_product_service_cost_cur,
                        activity_adult_amt,
                        activity_teen_amt,
                        activity_child_amt,
                        activity_infant_amt,
                        activity_total_pax,
                        activity_adult_cost,
                        activity_teen_cost,
                        activity_child_cost,
                        activity_infant_cost,
                        activity_total_cost_exTAX,
                        activity_total_cost,
                        activity_rebate_cost_type,
                        activity_rebate_cost_approve_by,
                        activity_rebate_cost_percentage,
                        activity_adult_cost_rebate,
                        activity_adult_cost_after_rebate_exTAX,
                        activity_adult_cost_after_rebate,
                        activity_teen_cost_rebate,
                        activity_teen_cost_after_rebate,
                        activity_teen_cost_after_rebate_exTAX,
                        activity_child_cost_rebate,
                        activity_child_cost_after_rebate,
                        activity_child_cost_after_rebate_exTAX,
                        activity_infant_cost_rebate,
                        activity_infant_cost_after_rebate,
                        activity_infant_cost_after_rebate_exTAX,
                        activity_total_cost_after_rebate_exTAX,
                        activity_total_cost_after_rebate,
                        id_user,
                        uname,
                        log_status
                    )
                 VALUES
                    (
                        :id_booking_activity_cost,
                        :id_booking_activity_claim,
                        :id_booking,
                        :id_product,
                        :id_product_service,
                        :id_product_service_claim,
                        :id_product_service_cost,
                        :id_product_service_cost_cur,
                        :activity_adult_amt,
                        :activity_teen_amt,
                        :activity_child_amt,
                        :activity_infant_amt,
                        :activity_total_pax,
                        :activity_adult_cost,
                        :activity_teen_cost,
                        :activity_child_cost,
                        :activity_infant_cost,
                        :activity_total_cost_exTAX,
                        :activity_total_cost,
                        :activity_rebate_cost_type,
                        :activity_rebate_cost_approve_by,
                        :activity_rebate_cost_percentage,
                        :activity_adult_cost_rebate,
                        :activity_adult_cost_after_rebate_exTAX,
                        :activity_adult_cost_after_rebate,
                        :activity_teen_cost_rebate,
                        :activity_teen_cost_after_rebate,
                        :activity_teen_cost_after_rebate_exTAX,
                        :activity_child_cost_rebate,
                        :activity_child_cost_after_rebate,
                        :activity_child_cost_after_rebate_exTAX,
                        :activity_infant_cost_rebate,
                        :activity_infant_cost_after_rebate,
                        :activity_infant_cost_after_rebate_exTAX,
                        :activity_total_cost_after_rebate_exTAX,
                        :activity_total_cost_after_rebate,
                        :id_user,
                        :uname,
                        :log_status
                    )";

                $stmt = $con->prepare($sqlSaveActivityCostLog);
                $stmt->execute(array(
                ":id_booking_activity_cost"             =>$id_booking_activity_cost,
                ":id_booking_activity_claim"            =>$id_booking_activity_claim,
                ":id_booking"                                   =>$id_booking,
                ":id_product"                                   =>$id_product,
                ":id_product_service"                       =>$id_product_service,
                ":id_product_service_claim"             =>$id_product_service_claim,
                ":id_product_service_cost"              =>$id_product_service_cost,
                ":id_product_service_cost_cur"       =>$id_product_service_cost_cur,
                ":activity_adult_amt"                        =>$activity_adult_amt,
                ":activity_teen_amt"                         =>$activity_teen_amt,
                ":activity_child_amt"                        =>$activity_child_amt,
                ":activity_infant_amt"                      =>$activity_infant_amt,
                ":activity_total_pax"                         =>$activity_total_pax,
                ":activity_adult_cost"                       => $activity_adult_cost,
                ":activity_teen_cost"                        => $activity_teen_cost,
                ":activity_child_cost"                        => $activity_child_cost,
                ":activity_infant_cost"                      => $activity_infant_cost,
                ":activity_total_cost_exTAX"            =>$activity_total_cost_exTAX,
                ":activity_total_cost"                        => $activity_total_cost,
                ":activity_rebate_cost_type"            => $activity_rebate_cost_type,
                ":activity_rebate_cost_approve_by" => $activity_rebate_cost_approve_by,
                ":activity_rebate_cost_percentage"  => $activity_rebate_cost_percentage,
                ":activity_adult_cost_rebate"            => $activity_adult_cost_rebate,
                ":activity_adult_cost_after_rebate_exTAX" => $activity_adult_cost_after_rebate_exTAX,
                ":activity_adult_cost_after_rebate"   => $activity_adult_cost_after_rebate,
                ":activity_teen_cost_rebate"             => $activity_teen_cost_rebate,
                ":activity_teen_cost_after_rebate"    => $activity_teen_cost_after_rebate,
                ":activity_teen_cost_after_rebate_exTAX" => $activity_teen_cost_after_rebate_exTAX,
                ":activity_child_cost_rebate"             => $activity_child_cost_rebate,
                ":activity_child_cost_after_rebate"    => $activity_child_cost_after_rebate,
                ":activity_child_cost_after_rebate_exTAX"   =>$activity_child_cost_after_rebate_exTAX,
                ":activity_infant_cost_rebate"                      =>$activity_infant_cost_rebate,
                ":activity_infant_cost_after_rebate"             =>$activity_infant_cost_after_rebate,
                ":activity_infant_cost_after_rebate_exTAX"  =>$activity_infant_cost_after_rebate_exTAX,
                ":activity_total_cost_after_rebate_exTAX"    => $activity_total_cost_after_rebate_exTAX,
                ":activity_total_cost_after_rebate"                =>$activity_total_cost_after_rebate,   
                 ":id_user"                                        => $id_user,
                 ":uname"                                         => $uname,
                 ":log_status"                                    => $log_status
                ));
        }
        else
        {
            $qry_activityPackageCost = $con->prepare("
                SELECT 
                    PS_COST.*,
                    PS.service_name, 
                    PS.id_tax
                FROM 
                    product_service_package PSP,
                    product_service PS,
                    product_service_cost PS_COST
                WHERE PSP.id_product_service_included = PS.id_product_service
                AND PS.id_product_service = PS_COST.id_product_service
                AND PSP.id_product_service = :id_product_service
                AND :activity_date BETWEEN PS_COST.valid_from AND PS_COST.valid_to
                AND PSP.active = 1");
            $qry_activityPackageCost->execute(array(":id_product_service"=>$id_product_service,":activity_date"=>$activity_date));

            $row_count_activityPackageCost = $qry_activityPackageCost->rowCount();
            if ($row_count_activityPackageCost > 0) 
            {
               while ($row = $qry_activityPackageCost->fetch(PDO::FETCH_ASSOC)) {
                    $activity_charge = $row["charge"];
                    $activity_adult_cost = $row["ps_adult_cost"];
                    $activity_teen_cost = $row["ps_teen_cost"];
                    $activity_child_cost = $row["ps_child_cost"];
                    $activity_infant_cost = $row["ps_infant_cost"];
                    $id_product_service = $row["id_product_service"];
                    $id_product_service_cost = $row["id_product_service_cost"];
                    $id_product_service_cost_cur = $row["id_currency"];
                    $service_cost_name = $row["service_name"];

                    $activity_cost_adult_amt = 0;
                    $activity_cost_teen_amt = 0;
                    $activity_cost_child_amt = 0; 
                    $activity_cost_infant_amt = 0; 
                    $activity_clients = $booking_client;
                    foreach($activity_clients as $activity_bookingClient)
                    {
                        // CLIENT ACTIVITY
                        $qry_ClientActivityDef = $con->prepare("
                            SELECT 
                                C.type,
                                BC.*,
                                PS.for_infant,
                                PS.age_inf_from,
                                PS.age_inf_to,
                                PS.for_child,
                                PS.age_child_from,
                                PS.age_child_to,
                                PS.for_teen,
                                PS.age_teen_from,
                                PS.age_teen_to,
                                PS.for_adult,
                                PS.min_age,
                                PS.max_age
                            FROM 
                                booking_client BC,
                                CLIENT C,
                                product_service PS
                            WHERE BC.id_client = C.id_client
                            AND BC.id_booking_client = :booking_client
                            AND PS.id_product_service = :id_product_service");
                        $qry_ClientActivityDef->execute(array(":id_product_service"=>$id_product_service,":booking_client"=>$activity_bookingClient));
                        $row_count_clientActivityDef = $qry_ClientActivityDef->rowCount();
                        if ($row_count_clientActivityDef > 0) 
                        {
                            while ($rowClientDefinition = $qry_ClientActivityDef->fetch(PDO::FETCH_ASSOC)) {
                                if($rowClientDefinition["yearMonth"] == "MONTH")
                                {
                                    $paxAge =  $rowClientDefinition["age"] /12;
                                   
                                    if($rowClientDefinition["for_infant"] == 1 && $paxAge != null && $paxAge <= $rowClientDefinition["age_inf_to"] )
                                    {
                                        $activity_cost_infant_amt ++;
                                    }
                                    else if($rowClientDefinition["for_child"] == 1 && $paxAge != null && $paxAge <= $rowClientDefinition["age_child_to"] )
                                    {
                                        $activity_cost_child_amt ++;
                                    }
                                    else if($rowClientDefinition["for_teen"] == 1 && $paxAge != null && $paxAge <= $rowClientDefinition["age_teen_to"] )
                                    {
                                        $activity_cost_teen_amt ++;
                                    }
                                    else if($rowClientDefinition["for_adult"] == 1 && $rowClientDefinition["type"] == "ADULT" )
                                    {
                                        $activity_cost_adult_amt ++;
                                    }
                                    else if($rowClientDefinition["for_adult"] == 1 && $rowClientDefinition["age"] != null )
                                    {
                                        $activity_cost_adult_amt ++;
                                    }
                                }
                                else
                                {
                                    if($rowClientDefinition["for_infant"] == 1 && $rowClientDefinition["age"] != null && $rowClientDefinition["age"] <= $rowClientDefinition["age_inf_to"] )
                                    {
                                        $activity_cost_infant_amt ++;
                                    }
                                    else if($rowClientDefinition["for_child"] == 1 && $rowClientDefinition["age"] != null && $rowClientDefinition["age"] <= $rowClientDefinition["age_child_to"] )
                                    {
                                        $activity_cost_child_amt ++;
                                    }
                                    else if($rowClientDefinition["for_teen"] == 1 && $rowClientDefinition["age"] != null && $rowClientDefinition["age"] <= $rowClientDefinition["age_teen_to"] )
                                    {
                                        $activity_cost_teen_amt ++;
                                    }
                                    else if($rowClientDefinition["for_adult"] == 1 && $rowClientDefinition["type"] == "ADULT" )
                                    {
                                        $activity_cost_adult_amt ++;
                                    }
                                    else if($rowClientDefinition["for_adult"] == 1 && $rowClientDefinition["age"] != null )
                                    {
                                        $activity_cost_adult_amt ++;
                                    }
                                }
                            }
                        }
                    }
           
                    $activity_total_pax = trim($_POST["activity_total_pax"]);

                    if ($activity_charge=="PAX")
                    {
                        $activity_total_cost_exTAX = (($activity_cost_adult_amt * $activity_adult_cost) + ($activity_cost_teen_amt * $activity_teen_cost) + ($activity_cost_child_amt * $activity_child_cost) + ($activity_cost_infant_amt * $activity_infant_cost))* ((100 - $tax_value)/100); 
                        $activity_total_cost =(($activity_cost_adult_amt * $activity_adult_cost) + ($activity_cost_teen_amt * $activity_teen_cost) + ($activity_cost_child_amt * $activity_child_cost) + ($activity_cost_infant_amt * $activity_infant_cost));
                    }
                    else
                    {
                        $activity_total_cost_exTAX = $activity_adult_cost * ((100 - $tax_value)/100); 
                        $activity_total_cost = $activity_adult_cost;
                    }

                     // BOOKING ACTIVITY COST
                    $sqlSaveActivityCost ="
                        INSERT INTO booking_activity_cost
                            (
                                id_booking_activity_claim,
                                id_booking,
                                id_product,
                                id_product_service,
                                id_product_service_claim,
                                id_product_service_cost,
                                id_product_service_cost_cur,
                                activity_adult_amt,
                                activity_teen_amt,
                                activity_child_amt,
                                activity_infant_amt,
                                activity_total_pax,
                                activity_charge,
                                activity_adult_cost,
                                activity_teen_cost,
                                activity_child_cost,
                                activity_infant_cost,
                                activity_total_cost_exTAX,
                                activity_total_cost,
                                activity_rebate_cost_type,
                                activity_rebate_cost_approve_by,
                                activity_rebate_cost_percentage,
                                activity_adult_cost_rebate,
                                activity_adult_cost_after_rebate_exTAX,
                                activity_adult_cost_after_rebate,
                                activity_teen_cost_rebate,
                                activity_teen_cost_after_rebate,
                                activity_teen_cost_after_rebate_exTAX,
                                activity_child_cost_rebate,
                                activity_child_cost_after_rebate,
                                activity_child_cost_after_rebate_exTAX,
                                activity_infant_cost_rebate,
                                activity_infant_cost_after_rebate,
                                activity_infant_cost_after_rebate_exTAX,
                                activity_total_cost_after_rebate_exTAX,
                                activity_total_cost_after_rebate,
                                created_by,
                                created_name
                            )
                         VALUES
                            (
                                :id_booking_activity_claim,
                                :id_booking,
                                :id_product,
                                :id_product_service,
                                :id_product_service_claim,
                                :id_product_service_cost,
                                :id_product_service_cost_cur,
                                :activity_adult_amt,
                                :activity_teen_amt,
                                :activity_child_amt,
                                :activity_infant_amt,
                                :activity_total_pax,
                                :activity_charge,
                                :activity_adult_cost,
                                :activity_teen_cost,
                                :activity_child_cost,
                                :activity_infant_cost,
                                :activity_total_cost_exTAX,
                                :activity_total_cost,
                                :activity_rebate_cost_type,
                                :activity_rebate_cost_approve_by,
                                :activity_rebate_cost_percentage,
                                :activity_adult_cost_rebate,
                                :activity_adult_cost_after_rebate_exTAX,
                                :activity_adult_cost_after_rebate,
                                :activity_teen_cost_rebate,
                                :activity_teen_cost_after_rebate,
                                :activity_teen_cost_after_rebate_exTAX,
                                :activity_child_cost_rebate,
                                :activity_child_cost_after_rebate,
                                :activity_child_cost_after_rebate_exTAX,
                                :activity_infant_cost_rebate,
                                :activity_infant_cost_after_rebate,
                                :activity_infant_cost_after_rebate_exTAX,
                                :activity_total_cost_after_rebate_exTAX,
                                :activity_total_cost_after_rebate,
                                :created_by,
                                :created_name
                            )";

                    $stmt = $con->prepare($sqlSaveActivityCost);
                    $stmt->execute(array(
                    ":id_booking_activity_claim"                    =>$id_booking_activity_claim,
                    ":id_booking"                                           =>$id_booking,
                    ":id_product"                                           =>$id_product,
                    ":id_product_service"                               =>$id_product_service,
                    ":id_product_service_claim"                     =>$id_product_service_claim,
                    ":id_product_service_cost"                      =>$id_product_service_cost,
                    ":id_product_service_cost_cur"                =>$id_product_service_cost_cur,
                    ":activity_adult_amt"                                =>$activity_cost_adult_amt,
                    ":activity_teen_amt"                                 =>$activity_cost_teen_amt,
                    ":activity_child_amt"                                 =>$activity_cost_child_amt,
                    ":activity_infant_amt"                               =>$activity_cost_infant_amt,
                    ":activity_total_pax"                                  =>$activity_total_pax,
                    ":activity_charge"                                      => $activity_charge,
                    ":activity_adult_cost"                                => $activity_adult_cost,
                    ":activity_teen_cost"                                 => $activity_teen_cost,
                    ":activity_child_cost"                                 => $activity_child_cost,
                    ":activity_infant_cost"                                => $activity_infant_cost,
                    ":activity_total_cost_exTAX"                      => $activity_total_cost_exTAX,
                    ":activity_total_cost"                                  => $activity_total_cost,
                    ":activity_rebate_cost_type"                      => 'NONE',
                    ":activity_rebate_cost_approve_by"           => 0,
                    ":activity_rebate_cost_percentage"            => 0,
                    ":activity_adult_cost_rebate"                      => 0,
                    ":activity_adult_cost_after_rebate_exTAX" => 0,
                    ":activity_adult_cost_after_rebate"             => 0,
                    ":activity_teen_cost_rebate"                       => 0,
                    ":activity_teen_cost_after_rebate"              => 0,
                    ":activity_teen_cost_after_rebate_exTAX"   => 0,
                    ":activity_child_cost_rebate"                       => 0,
                    ":activity_child_cost_after_rebate"              => 0,
                    ":activity_child_cost_after_rebate_exTAX"   => 0,
                    ":activity_infant_cost_rebate"                      => 0,
                    ":activity_infant_cost_after_rebate"             => 0,
                    ":activity_infant_cost_after_rebate_exTAX"  => 0,
                    ":activity_total_cost_after_rebate_exTAX"    => $activity_total_cost_exTAX,
                    ":activity_total_cost_after_rebate"                => $activity_total_cost, 
                    ":created_by"                                   =>$created_by,
                    ":created_name"                              =>$created_name
                    ));

                    $id_booking_activity_cost = $con->lastInsertId();    

                    // BOOKING ACTIVITY COST LOG
                    $sqlSaveActivityCostLog ="
                        INSERT INTO booking_activity_cost_log
                            (
                                id_booking_activity_cost,
                                id_booking_activity_claim,
                                id_booking,
                                id_product,
                                id_product_service,
                                id_product_service_claim,
                                id_product_service_cost,
                                id_product_service_cost_cur,
                                activity_adult_amt,
                                activity_teen_amt,
                                activity_child_amt,
                                activity_infant_amt,
                                activity_total_pax,
                                activity_charge,
                                activity_adult_cost,
                                activity_teen_cost,
                                activity_child_cost,
                                activity_infant_cost,
                                activity_total_cost_exTAX,
                                activity_total_cost,
                                activity_rebate_cost_type,
                                activity_rebate_cost_approve_by,
                                activity_rebate_cost_percentage,
                                activity_adult_cost_rebate,
                                activity_adult_cost_after_rebate_exTAX,
                                activity_adult_cost_after_rebate,
                                activity_teen_cost_rebate,
                                activity_teen_cost_after_rebate,
                                activity_teen_cost_after_rebate_exTAX,
                                activity_child_cost_rebate,
                                activity_child_cost_after_rebate,
                                activity_child_cost_after_rebate_exTAX,
                                activity_infant_cost_rebate,
                                activity_infant_cost_after_rebate,
                                activity_infant_cost_after_rebate_exTAX,
                                activity_total_cost_after_rebate_exTAX,
                                activity_total_cost_after_rebate,
                                id_user,
                                uname,
                                log_status
                            )
                         VALUES
                            (
                                :id_booking_activity_cost,
                                :id_booking_activity_claim,
                                :id_booking,
                                :id_product,
                                :id_product_service,
                                :id_product_service_claim,
                                :id_product_service_cost,
                                :id_product_service_cost_cur,
                                :activity_adult_amt,
                                :activity_teen_amt,
                                :activity_child_amt,
                                :activity_infant_amt,
                                :activity_total_pax,
                                :activity_charge,
                                :activity_adult_cost,
                                :activity_teen_cost,
                                :activity_child_cost,
                                :activity_infant_cost,
                                :activity_total_cost_exTAX,
                                :activity_total_cost,
                                :activity_rebate_cost_type,
                                :activity_rebate_cost_approve_by,
                                :activity_rebate_cost_percentage,
                                :activity_adult_cost_rebate,
                                :activity_adult_cost_after_rebate_exTAX,
                                :activity_adult_cost_after_rebate,
                                :activity_teen_cost_rebate,
                                :activity_teen_cost_after_rebate,
                                :activity_teen_cost_after_rebate_exTAX,
                                :activity_child_cost_rebate,
                                :activity_child_cost_after_rebate,
                                :activity_child_cost_after_rebate_exTAX,
                                :activity_infant_cost_rebate,
                                :activity_infant_cost_after_rebate,
                                :activity_infant_cost_after_rebate_exTAX,
                                :activity_total_cost_after_rebate_exTAX,
                                :activity_total_cost_after_rebate,
                                :id_user,
                                :uname,
                                :log_status
                            )";

                    $stmt = $con->prepare($sqlSaveActivityCostLog);
                    $stmt->execute(array(
                    ":id_booking_activity_cost"                     =>$id_booking_activity_cost,
                    ":id_booking_activity_claim"                    =>$id_booking_activity_claim,
                    ":id_booking"                                           =>$id_booking,
                    ":id_product"                                           =>$id_product,
                    ":id_product_service"                               =>$id_product_service,
                    ":id_product_service_claim"                     =>$id_product_service_claim,
                    ":id_product_service_cost"                      =>$id_product_service_cost,
                    ":id_product_service_cost_cur"                =>$id_product_service_cost_cur,
                    ":activity_adult_amt"                                =>$activity_cost_adult_amt,
                    ":activity_teen_amt"                                 =>$activity_cost_teen_amt,
                    ":activity_child_amt"                                 =>$activity_cost_child_amt,
                    ":activity_infant_amt"                               =>$activity_cost_infant_amt,
                    ":activity_total_pax"                                  =>$activity_total_pax,
                    ":activity_charge"                                      => $activity_charge,
                    ":activity_adult_cost"                                => $activity_adult_cost,
                    ":activity_teen_cost"                                 => $activity_teen_cost,
                    ":activity_child_cost"                                 => $activity_child_cost,
                    ":activity_infant_cost"                                => $activity_infant_cost,
                    ":activity_total_cost_exTAX"                      => $activity_total_cost_exTAX,
                    ":activity_total_cost"                                  => $activity_total_cost,
                    ":activity_rebate_cost_type"                      => 'NONE',
                    ":activity_rebate_cost_approve_by"           => 0,
                    ":activity_rebate_cost_percentage"            => 0,
                    ":activity_adult_cost_rebate"                      => 0,
                    ":activity_adult_cost_after_rebate_exTAX" => 0,
                    ":activity_adult_cost_after_rebate"             => 0,
                    ":activity_teen_cost_rebate"                       => 0,
                    ":activity_teen_cost_after_rebate"              => 0,
                    ":activity_teen_cost_after_rebate_exTAX"   => 0,
                    ":activity_child_cost_rebate"                       => 0,
                    ":activity_child_cost_after_rebate"              => 0,
                    ":activity_child_cost_after_rebate_exTAX"   => 0,
                    ":activity_infant_cost_rebate"                      => 0,
                    ":activity_infant_cost_after_rebate"             => 0,
                    ":activity_infant_cost_after_rebate_exTAX"  => 0,
                    ":activity_total_cost_after_rebate_exTAX"    => $activity_total_cost_exTAX,
                    ":activity_total_cost_after_rebate"                => $activity_total_cost, 
                     ":id_user"                                                     => $id_user,
                     ":uname"                                                      => $uname,
                     ":log_status"                                                 => $log_status
                    ));
                    
                }
            }
        }
                               
    $bookingActivity_result= array("OUTCOME" => "OK", "id_booking"=>$id_booking, "id_booking_activity_claim"=>$id_booking_activity_claim, "created_by" =>$created_name);
    echo json_encode($bookingActivity_result); 
} catch (Exception $ex) {
    die(json_encode(array("OUTCOME" => "ERROR: " . $ex->getMessage())));
}
?>
    
    