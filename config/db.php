<?php

$host = 'localhost';
$db = 'prompt_vault';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?>