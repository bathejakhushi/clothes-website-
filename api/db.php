<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "clothing_store_db";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die(json_encode(["error" => "Connection failed!"]));
}
?>