<?php
ob_start();

$db_name = 'searchengine_database';
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';

try {
   $con= new PDO('mysql:dbname='.$db_name.';host='.$db_host.';charset=utf8mb4', $db_user, $db_pass);
   $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

} catch (PDOException $e) {

   echo "Connection failed: ".$e->getMessage();

}