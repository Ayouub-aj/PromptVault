<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
