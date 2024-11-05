<?php
$red = $_POST['r'];
$green = $_POST['g'];
$blue = $_POST['b'];

$server = "localhost";
$user = "msi";
$password = "123456";
$dbname = "sensor";

$conn = mysqli_connect($server, $user, $password, $dbname);

$sql = "INSERT INTO led_rgb(red, green, blue) VALUES ($red, $green, $blue)";
mysqli_query($conn, $sql);

mysqli_close($conn);
?>