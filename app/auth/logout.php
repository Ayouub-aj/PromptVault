<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

$_SESSION = array();

session_destroy();

header("Location: ../index.php");
exit;
?>
