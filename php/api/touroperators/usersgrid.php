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

if (!isset($_GET["toid"])) {
    die("INVALID TO ID");
}

$toid = $_GET["toid"];

require_once("../../connector/pdo_connect_main.php");
require_once("../../connector/db_pdo.php");
require_once("../../connector/data_connector.php");

$con=pdo_con();

$data = new JSONDataConnector($con, "PDO");

$sql = "select u.id, u.uname, u.upass, u.email, u.ugrpid, u.status, u.ufullname,
        u.intern_extern, u.tofk, tope.toname,
        DATE_FORMAT(u.date_created,'%d %b %Y %H:%i') AS date_created,
        DATE_FORMAT(u.date_lastlogin_success,'%d %b %Y %H:%i') AS date_lastlogin_success, 
        DATE_FORMAT(u.date_modified,'%d %b %Y %H:%i') AS date_modified, 
        DATE_FORMAT(u.date_activated,'%d %b %Y %H:%i') AS date_activated,        
        g.ugroup,
        g.description
        from tbluser u 
        left join tblugroup g on u.ugrpid = g.id     
        left join tbltouroperator tope on u.tofk = tope.id
        WHERE u.intern_extern = 'EXT' AND u.tofk = $toid
        order by u.uname;
        ";

$data->render_sql($sql,"id","uname,email,ugrpid,description,"
                     . "status,ugroup,ufullname,date_created,date_lastlogin_success,"
                     . "date_modified,date_activated,intern_extern,toname,tofk");

?>
