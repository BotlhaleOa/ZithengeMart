<?php
// Database configuration


$host     = "your_database_host";
$dbname   = "if0-41982578_zithengemart";
$username = "if0-41982578";
$password = "BotlhaleNth04";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>