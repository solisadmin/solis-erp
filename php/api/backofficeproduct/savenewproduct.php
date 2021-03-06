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
    
    $id_product = $_POST["id_product"];
    $id_product_type = trim($_POST["id_product_type"]);
    $id_service_type = trim($_POST["id_service_type"]);
    $product_name = strtoupper(trim($_POST["product_name"]));
    $active = trim($_POST["active"]);

    $id_user = $_SESSION["solis_userid"];
    $uname = $_SESSION["solis_username"];
    $log_status = "CREATE";

    $con = pdo_con();

    // //check duplicates for services
    // $sql = "SELECT * FROM product WHERE id_product = :id_product ";
    // $stmt = $con->prepare($sql);
    // $stmt->execute(array(":id_product" => $id_product));
    // if ($rw = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //     throw new Exception("DUPLICATE SERVICES!");
    // }

    //check duplicates for area name
    $sql_name = "SELECT * FROM product 
            WHERE product_name = :product_name 
            AND active = 1";
    $stmt_name = $con->prepare($sql_name);
    $stmt_name->bindParam(':product_name', $product_name);
    $stmt_name->execute(); 

    if ($rw = $stmt_name->fetch(PDO::FETCH_ASSOC)) {
        // throw new Exception("DUPLICATE PRODUCT NAME!");
        die(json_encode(array("OUTCOME" => "ERROR_NAME")));
    }

    if ($id_product == "-1") {
        $sql = "INSERT INTO product (id_product_type, id_service_type, product_name, active) 
                VALUES (:id_product_type, :id_service_type, :product_name, :active)";

        $stmt = $con->prepare($sql);
        $stmt->execute(array(
            ":id_product_type" => $id_product_type, 
            ":id_service_type" => $id_service_type,
            ":product_name" => $product_name,
            ":active" => $active));
        
        $id_product = $con->lastInsertId();
        
        // Start Product Log
        $sqlLog = "INSERT INTO product_log ( 
            id_product,
            id_product_type, 
            id_service_type, 
            product_name,
            id_user,
            uname,
            log_status
            ) 
                VALUES (
                    :id_product,
                    :id_product_type, 
                    :id_service_type, 
                    :product_name,
                    :id_user,
                    :uname,
                    :log_status
                    )";
    
        $stmt = $con->prepare($sqlLog);
                    $stmt->execute(array(
                    ":id_product" => $id_product,
                    ":id_product_type" => $id_product_type, 
                    ":id_service_type" => $id_service_type,
                    ":product_name" => $product_name,
                    ":id_user" => $id_user,
                    ":uname" => $uname,
                    ":log_status" => $log_status
                ));
    
        // End Of Log
    } else {
        $sql = "UPDATE product SET 
                id_product_type=:id_product_type, 
                id_service_type=:id_service_type, 
                product_name=:product_name,
                active=:active,
                WHERE id_product=:id_product";

        $stmt = $con->prepare($sql);
        $stmt->execute(array(
            ":id_product" => $id_product,
            ":id_product_type" => $id_product_type, 
            ":id_service_type" => $id_service_type,
            ":product_name" => $product_name, 
            ":active" => $active));
    }
    echo json_encode(array("OUTCOME" => "OK", "id_product"=>$id_product));
} catch (Exception $ex) {
    die(json_encode(array("OUTCOME" => "ERROR: " . $ex->getMessage())));
}
?>
