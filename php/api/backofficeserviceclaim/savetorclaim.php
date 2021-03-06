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
    
    $id = $_POST["id"];
    $idservicefk = trim($_POST["idservicefk"]);
    $idrates_fk = trim($_POST["idrates_fk"]);
    $to_id = trim($_POST["to_id"]);

    $con = pdo_con();

    // check duplicates for service
    $sql = "SELECT * FROM tblexcursion_service_rates_to WHERE id = :id ";
    $stmt = $con->prepare($sql);
    $stmt->execute(array(":id" => $id));
    if ($rw = $stmt->fetch(PDO::FETCH_ASSOC)) {
        throw new Exception("DUPLICATE SERVICES !");
    }

    if ($id == "-1") {
        $sql = "INSERT INTO tblexcursion_service_rates_to (idservicefk, idrates_fk, to_id) 
                VALUES (:idservicefk, :idrates_fk, :to_id)";

        $stmt = $con->prepare($sql);
        $stmt->execute(array(
            ":idservicefk" => $idservicefk, 
            ":idrates_fk" => $idrates_fk,
            ":to_id" => $to_id));
        
        $id = $con->lastInsertId();
        echo $id;
    } else {
        $sql = "UPDATE tblexcursion_service_rates_to SET 
                idservicefk=:idservicefk, 
                idrates_fk=:idrates_fk,
                to_id=:to_id,
                WHERE id=:id";

        $stmt = $con->prepare($sql);
        $stmt->execute(array(
            ":idservicefk" => $idservicefk, 
            ":idrates_fk" => $idrates_fk,
            ":to_id" => $to_id));
    }
    echo json_encode(array("OUTCOME" => "OK", "ID"=>$id));
} catch (Exception $ex) {
    die(json_encode(array("OUTCOME" => "ERROR: " . $ex->getMessage())));
}
?>
