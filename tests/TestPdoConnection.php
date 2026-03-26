<?php
/**
 * PromptVault — Core System Test (CLI/Simple Style)
 * 
 * This diagnostic script performs a "Deep Scan" of your database environment.
 * It checks for connectivity, schema integrity, and relational logic (JOINs).
 * 
 * USE CASE: Run this after migrations or when setting up a new environment.
 * SECURITY: Delete this file before going live to prevent leaking schema details.
 */

// Load the PDO connection from the config folder.
// dirname(__DIR__) ensures we always point to the project root.
require_once dirname(__DIR__) . '/config/db.php';

/**
 * Report helper for simple output
 * 
 * @param string $name Name of the test
 * @param string $status PASS, WARN, or FAIL
 * @param string $message Detailed result message
 */
function report($name, $status, $message = "") {
    $icon = $status === 'PASS' ? "✅" : ($status === 'WARN' ? "⚠️" : "❌");
    // Using simple HTML for clean browser output without heavy CSS
    echo "{$icon} [{$status}] <strong>{$name}</strong>: {$message}<br>" . PHP_EOL;
}

echo "<h2>PromptVault — Deep System Diagnostics</h2>" . PHP_EOL;

// ----------------------------------------------------------------------------
//  TEST 1: PHP Environment
//  ----------------------------------------------------------------------------
//  We check if the server has the required tools to talk to MySQL.
// ----------------------------------------------------------------------------
try {
    report("PHP Version", "PASS", "Running on PHP " . phpversion());
    report("PDO Driver", extension_loaded('pdo_mysql') ? "PASS" : "FAIL", "pdo_mysql " . (extension_loaded('pdo_mysql') ? "is active" : "is MISSING"));
} catch (Exception $e) {
    report("Environment", "FAIL", $e->getMessage());
}

// ----------------------------------------------------------------------------
//  TEST 2: Basic Connection & Configuration
//  ----------------------------------------------------------------------------
//  Verify that the $pdo object from config/db.php is healthy and correctly configured.
// ----------------------------------------------------------------------------
try {
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        throw new Exception("The \$pdo variable was not found. Check config/db.php.");
    }
    
    // Check if Error Mode is set to EXCEPTION. 
    // This is CRITICAL because it allows us to use try-catch for database errors.
    $errMode = $pdo->getAttribute(PDO::ATTR_ERRMODE);
    $modeMsg = ($errMode === PDO::ERRMODE_EXCEPTION) ? "EXCEPTION (Correct)" : "SILENT (Change to EXCEPTION in db.php for safety)";
    report("Error Handling", ($errMode === PDO::ERRMODE_EXCEPTION ? "PASS" : "WARN"), $modeMsg);

    // Verify which database we are actually talking to.
    $dbName = $pdo->query("SELECT DATABASE()")->fetchColumn();
    report("Active Schema", "PASS", "Connected to: <code>{$dbName}</code>");

} catch (Exception $e) {
    report("Linkage", "FAIL", "Critical Connection Error: " . $e->getMessage());
    exit; // Stop here if we can't connect.
}

// ----------------------------------------------------------------------------
//  TEST 3: Schema Structural Integrity
//  ----------------------------------------------------------------------------
//  Check if the core tables exist and report their currently stored data volume.
// ----------------------------------------------------------------------------
try {
    $required = ['users', 'categories', 'prompts'];
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($required as $table) {
        if (in_array($table, $tables)) {
            // If table exists, count rows to provide a "Density" report.
            $count = $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
            report("Table Existence: {$table}", "PASS", "Found with {$count} records.");
        } else {
            report("Table Existence: {$table}", "FAIL", "TABLE MISSING! Run your SQL migrations.");
        }
    }
} catch (PDOException $e) {
    report("Schema Scan", "FAIL", $e->getMessage());
}

// ----------------------------------------------------------------------------
//  TEST 4: Relational Logic (JOIN Test)
//  ----------------------------------------------------------------------------
//  This is the MOST IMPORTANT test. It verifies if your Foreign Keys are correctly
//  linking Prompts to their Authors (Users) and Categories.
// ----------------------------------------------------------------------------
try {
    $sql = "
        SELECT 
            p.title AS prompt_title, 
            u.username AS author_name, 
            c.name AS category_name
        FROM prompts p
        INNER JOIN users u ON p.user_id = u.id
        INNER JOIN categories c ON p.category_id = c.id
        LIMIT 1
    ";
    
    $stmt = $pdo->query($sql);
    $sample = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sample) {
        $detail = "Linked <strong>'{$sample['prompt_title']}'</strong> to author <strong>'{$sample['author_name']}'</strong> in category <strong>'{$sample['category_name']}'</strong>.";
        report("Relational JOIN", "PASS", "Successfully connected prompts-users-categories. Details: {$detail}");
    } else {
        // If query works but no rows exist, it's a warning (infrastructure is okay, but data is empty).
        report("Relational JOIN", "WARN", "SQL syntax is CORRECT, but no sample data exists to verify actual linkage. Add one prompt first!");
    }
} catch (PDOException $e) {
    // If this fails, usually user_id or category_id columns are named incorrectly or FKs are broken.
    report("Relational JOIN", "FAIL", "Relationship Link BROKEN: " . $e->getMessage());
}

// ----------------------------------------------------------------------------
//  TEST 5: Data Integrity (Unique Emails)
//  ----------------------------------------------------------------------------
//  Verifies if the 'email' column is truly UNIQUE in the users table.
//  If this test says FAIL, it means your code allows multiple accounts with the same email.
// ----------------------------------------------------------------------------
try {
    $pdo->beginTransaction(); // Use transaction to keep DB clean.
    
    $testEmail = "diag_" . time() . "@test.com"; // Unique test email
    $sql = "INSERT INTO users (username, email, password_hash) VALUES ('diag_user', '{$testEmail}', 'secret_hash')";
    $pdo->exec($sql);
    
    try {
        // Attempt to insert the same email again.
        $pdo->exec($sql); 
        report("Unique Constraint", "FAIL", "Database allowed duplicate emails! Security risk high.");
    } catch (PDOException $e) {
        // If we hit this block, it's a PASS because the database correctly blocked the duplicate.
        report("Unique Constraint", "PASS", "Correctly blocked duplicate email (Error successfully triggered).");
    }
    
    $pdo->rollBack(); // Always cleanup.
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    report("Unique Test Error", "FAIL", $e->getMessage());
}

// ----------------------------------------------------------------------------
//  TEST 6: Orphan Prevention (Foreign Keys)
//  ----------------------------------------------------------------------------
//  Testing if you can create a prompt for a category that doesn't exist.
// ----------------------------------------------------------------------------
try {
    $pdo->beginTransaction();
    try {
        // ID 999999 is unlikely to exist.
        $stmt = $pdo->prepare("INSERT INTO prompts (title, content, user_id, category_id) VALUES ('Orphan', 'Content', 1, 999999)");
        $stmt->execute();
        report("Referential Guard", "FAIL", "Allowed creation of a prompt with a non-existent category (Check Foreign Keys!).");
    } catch (PDOException $e) {
        report("Referential Guard", "PASS", "Successfully blocked orphaned data.");
    }
    $pdo->rollBack();
} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
}

echo "<br><strong>System Diagnostics Completed.</strong>";

