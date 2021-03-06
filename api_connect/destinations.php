<?php

try {
    
    $api_name = "destinationlist";
    
    require_once("../php/utils/utilities.php");
    require_once("../php/connector/pdo_connect_main_login_internet.php");
    
    date_default_timezone_set('Indian/Mauritius');
    
    $con = connect_login_pdo();
    
    //authenticate user
    $authenticate_outcome = utils_authenticate_api_user($con, $_POST, $api_name);
    if(!is_array($authenticate_outcome))
    {
        throw new Exception($authenticate_outcome);
    }
    
    //all is valid
    $tofk = $authenticate_outcome["TOID"]; //get the toid of the user
    
    $sql = "SELECT
                    region.id,
                    region.areaname AS destination,
                    'REGION' AS category
                FROM tblareas region 
                UNION
                SELECT
                    hotel.id,
                    hotel.hotelname AS destination,
                    'HOTEL' AS category
                FROM tblhotels hotel
                WHERE deleted !=1";

    $array_data = utilities_render_query($con, $sql, "id", "destination", "category", array());
    
    echo json_encode(array("OUTCOME"=>"OK","DATA"=>$array_data));
    
} catch (Exception $ex) {
    echo json_encode(array("OUTCOME"=>$ex->getMessage()));
}


?>