<?php
$sname   = "localhost:3307";
$uname   = "root";
$db_name = "eduguide_db";

$conn = @mysqli_connect($sname, $uname, "", $db_name);
if (!$conn) $conn = mysqli_connect($sname, $uname, "root", $db_name);
if (!$conn) die("Connection failed: " . mysqli_connect_error());
?>