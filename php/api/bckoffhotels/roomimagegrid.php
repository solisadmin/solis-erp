<?php

session_start();



if (!isset($_SESSION["solis_userid"])) {
    die("NO LOG IN!");
}

if (!isset($_GET["t"])) {
    die("INVALID TOKEN");
}
if ($_GET["t"] != $_SESSION["token"]) {
    die("INVALID TOKEN");
}

if (!isset($_GET["roomid"])) {
    die("INVALID ROOM ID");
}

$roomid = $_GET["roomid"];

require_once("../../connector/pdo_connect_main.php");
require_once("../../connector/db_pdo.php");
require_once("../../connector/data_connector.php");
require_once("../../utils/utilities.php");

$con = pdo_con();

$relative_path = utils_getsysparams($con, "HOTEL_ROOM", "PHOTO", "RELATIVE_PATH");


        
$data = new JSONDataConnector($con, "PDO");

$sql = "select 
        id,
        roomfk,
        CONCAT('<img src=\"" . $relative_path  . "',image_name, '\" height=\"137px\" width=\"206px\" style=\"margin:10px 0px\">') AS image_name_url,
        image_name,
        image_description,
        isdefault 
        from tblhotel_room_images where roomfk = $roomid order by isdefault desc;";

$data->render_sql($sql, "id", "roomfk,image_name_url,image_name,image_description,isdefault");
?>

