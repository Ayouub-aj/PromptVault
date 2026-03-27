<?php
require_once dirname(__DIR__) . '/config/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request targeting the repository logs.");
}

$p_id = $_GET['id'];

try {
    $stmt = $pdo->prepare("SELECT title, content FROM prompts WHERE id = ?");
    $stmt->execute([$p_id]);
    $prompt = $stmt->fetch();

    if (!$prompt) {
        die("Asset not identified in current vault.");
    }

    $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $prompt['title']) . ".txt";

    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');

    echo "PROMPT TITLE: " . strtoupper($prompt['title']) . "\n";
    echo str_repeat("=", strlen($prompt['title']) + 14) . "\n\n";
    echo $prompt['content'];
    exit;

} catch (PDOException $e) {
    die("Data extraction failure: " . $e->getMessage());
}
?>
