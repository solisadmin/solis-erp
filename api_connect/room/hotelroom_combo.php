<?php

session_start();

// TO BE UPDATED
$_SESSION["solis_userid"] = 1;
$_SESSION["id_tour_operator"] = 1;
$_SESSION["id_country"] = 979;
// TO BE UPDATED

if (!isset($_SESSION["solis_userid"])) {
    die("NO LOG IN!");
}

if (!isset($_GET["t"])) {
    die("INVALID TOKEN");
}
if ($_GET["t"] != $_SESSION["token"]) {
    die("INVALID TOKEN");
}

require_once("../../php/connector/pdo_connect_main.php");

$con = pdo_con();

$hoid = $_GET["hoid"];

$query_c = $con->prepare("SELECT id, roomname FROM tblhotel_rooms WHERE hotelfk=:hotelfk ORDER BY roomname ASC");
$query_c->execute(array(":hotelfk"=>$hoid));
$row_count_c = $query_c->rowCount();

if ($row_count_c > 0) {
    while ($row = $query_c->fetch(PDO::FETCH_ASSOC)) {
        $ug[] = array(
            'value' => $row['id'],
            'text' => $row['roomname']
        );
    }
    $myData = $ug;
    echo json_encode($myData);
} else {
    echo "NO DATA";
}
?>