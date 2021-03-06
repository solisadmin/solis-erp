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

require_once("../../connector/pdo_connect_main.php");

$con = pdo_con();

$query_c = $con->prepare("SELECT id_service_extra, extra_name FROM service_extra WHERE for_transfer=1 ORDER BY extra_name ASC");
$query_c->execute();
$row_count_c = $query_c->rowCount();

if ($row_count_c > 0) {
    while ($row = $query_c->fetch(PDO::FETCH_ASSOC)) {
        $ug[] = array(
            'id_service_extra' => $row['id_service_extra'],
            'extra_name' => $row['extra_name']
        );
    }
    $myData = $ug;
    echo json_encode($myData);
} else {
    echo "NO DATA";
}
?>