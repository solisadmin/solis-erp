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

if (!isset($_GET["idrates_fk"])) {
    throw new Exception("INVALID ID". $_GET["idrates_fk"]);
}

require_once("../../connector/pdo_connect_main.php");
require_once("../../connector/db_pdo.php");
require_once("../../connector/data_connector.php");

$idrates_fk = $_GET["idrates_fk"];
$con = pdo_con();

$query_c = $con->prepare("SELECT * FROM tblexcursion_services_rates_insertclosedate WHERE idrates_fk = :idrates_fk");
$query_c->execute(array(":idrates_fk"=>$idrates_fk));

$row_count_c = $query_c->rowCount();

if ($row_count_c > 0) {
    while ($row = $query_c->fetch(PDO::FETCH_ASSOC)) {
        $rateCloseDateDetails[] = array(
            'id'                => $row['id'],
            'idservicesfk'      => $row['idservicesfk'],
            'idrates_fk'      => $row['idrates_fk'],
            'serviceclosedstartdate' => $row['serviceclosedstartdate'],
            'serviceclosedenddate'  => $row['serviceclosedenddate']
        );
    }
    $myData = $rateCloseDateDetails;
    echo json_encode($myData);
} else {
    //echo "NO DATA";
    $rateCloseDateDetails[] = array(
        'id' => '-',
        'idservicesfk' => '-',
        'idrates_fk' => '-',
        'serviceclosedstartdate' => '-',
        'serviceclosedenddate' => '-'
    );
    $myData = $rateCloseDateDetails;
    echo json_encode($myData);
}
