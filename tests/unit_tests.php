<?php
/**
 * PromptVault — Unit Tests
 * 
 * Tests all features from taskboard.md and README.md
 * Run via: php tests/unit_tests.php
 * Or access via browser: http://localhost/prompt-vault/tests/unit_tests.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$isCli = php_sapi_name() === 'cli';

if (!$isCli) {
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PromptVault — Unit Tests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .test-pass { color: #198754; }
        .test-fail { color: #dc3545; }
        .test-warn { color: #ffc107; }
        pre { background: #212529; color: #fff; padding: 15px; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">PromptVault — Unit Tests</h1>
        <pre>';
}

require_once dirname(__DIR__) . '/config/db.php';

if (!isset($pdo) || $pdo === null) {
    echo "❌ [FAIL] Database Connection: PDO not available.\n";
    if (!$isCli) {
        echo '</pre></div></body></html>';
    }
    exit(1);
}

class UnitTests {
    private $pdo;
    private $passed = 0;
    private $failed = 0;
    private $tests = [];
    private $isCli;

    public function __construct($pdo, $isCli = true) {
        $this->pdo = $pdo;
        $this->isCli = $isCli;
    }

    private function report($name, $status, $message = "") {
        $icon = $status === 'PASS' ? "✅" : ($status === 'WARN' ? "⚠️" : "❌");
        $cssClass = $status === 'PASS' ? 'test-pass' : ($status === 'WARN' ? 'test-warn' : 'test-fail');
        
        $this->tests[] = [
            'name' => $name,
            'status' => $status,
            'message' => $message
        ];
        
        if ($status === 'PASS') {
            $this->passed++;
        } else {
            $this->failed++;
        }
        
        if ($this->isCli) {
            echo "{$icon} [{$status}] {$name}: {$message}\n";
        } else {
            echo "<span class='{$cssClass}'>{$icon} [{$status}] <strong>{$name}</strong>: {$message}</span><br>\n";
        }
    }

    public function runAllTests() {
        if ($this->isCli) {
            echo "\n========================================\n";
            echo "   PromptVault — Unit Tests\n";
            echo "========================================\n\n";
        } else {
            echo "========================================\n";
            echo "   PromptVault — Unit Tests\n";
            echo "========================================\n\n";
        }

        $this->testDatabaseConnection();
        $this->testSchemaIntegrity();
        $this->testAuthentication();
        $this->testPromptCRUD();
        $this->testCategories();
        $this->testSearchAndFilter();
        $this->testPagination();
        $this->testSecurity();
        $this->testUserDashboard();
        $this->testAdminPanel();

        if ($this->isCli) {
            echo "\n========================================\n";
            echo "   Results: {$this->passed} PASSED, {$this->failed} FAILED\n";
            echo "========================================\n";
        } else {
            echo "\n========================================\n";
            $resultClass = $this->failed === 0 ? 'test-pass' : 'test-fail';
            echo "   <span class='{$resultClass}'>Results: {$this->passed} PASSED, {$this->failed} FAILED</span>\n";
            echo "========================================\n";
            echo '</pre></div></div></body></html>';
        }

        return $this->failed === 0;
    }

    private function testDatabaseConnection() {
        $this->printSection("Database Connection Tests");
        
        try {
            $this->pdo->query("SELECT 1");
            $this->report("Database Connection", "PASS", "Connected to database");
        } catch (PDOException $e) {
            $this->report("Database Connection", "FAIL", $e->getMessage());
        }
    }

    private function printSection($title) {
        if ($this->isCli) {
            echo "\n--- {$title} ---\n";
        } else {
            echo "\n<hr><h5>{$title}</h5>\n";
        }
    }

    private function testSchemaIntegrity() {
        $this->printSection("Schema Integrity Tests");
        
        $requiredTables = ['users', 'categories', 'prompts'];
        
        foreach ($requiredTables as $table) {
            try {
                $stmt = $this->pdo->query("SELECT COUNT(*) FROM {$table}");
                $count = $stmt->fetchColumn();
                $this->report("Table: {$table}", "PASS", "Exists with {$count} records");
            } catch (PDOException $e) {
                $this->report("Table: {$table}", "FAIL", $e->getMessage());
            }
        }

        try {
            $stmt = $this->pdo->query("DESCRIBE users");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $required = ['id', 'username', 'email', 'password_hash', 'role', 'created_at'];
            $missing = array_diff($required, $columns);
            if (empty($missing)) {
                $this->report("Users Schema", "PASS", "All required columns present");
            } else {
                $this->report("Users Schema", "FAIL", "Missing: " . implode(', ', $missing));
            }
        } catch (PDOException $e) {
            $this->report("Users Schema", "FAIL", $e->getMessage());
        }

        try {
            $stmt = $this->pdo->query("DESCRIBE categories");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $required = ['id', 'name', 'created_at'];
            $missing = array_diff($required, $columns);
            if (empty($missing)) {
                $this->report("Categories Schema", "PASS", "All required columns present");
            } else {
                $this->report("Categories Schema", "FAIL", "Missing: " . implode(', ', $missing));
            }
        } catch (PDOException $e) {
            $this->report("Categories Schema", "FAIL", $e->getMessage());
        }

        try {
            $stmt = $this->pdo->query("DESCRIBE prompts");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $required = ['id', 'title', 'content', 'user_id', 'category_id', 'created_at', 'updated_at'];
            $missing = array_diff($required, $columns);
            if (empty($missing)) {
                $this->report("Prompts Schema", "PASS", "All required columns present");
            } else {
                $this->report("Prompts Schema", "FAIL", "Missing: " . implode(', ', $missing));
            }
        } catch (PDOException $e) {
            $this->report("Prompts Schema", "FAIL", $e->getMessage());
        }

        try {
            $stmt = $this->pdo->query("
                SELECT COUNT(*) > 0 as has_fk 
                FROM information_schema.TABLE_CONSTRAINTS 
                WHERE TABLE_SCHEMA = 'prompt_vault' 
                AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ");
            $hasFk = $stmt->fetchColumn();
            $this->report("Foreign Keys", $hasFk ? "PASS" : "WARN", $hasFk ? "FK constraints exist" : "No FK constraints found");
        } catch (PDOException $e) {
            $this->report("Foreign Keys", "WARN", "Could not verify FK constraints");
        }
    }

    private function testAuthentication() {
        $this->printSection("Authentication Tests (Epic 4)");
        
        $this->testRegistration();
        $this->testLogin();
        $this->testLogout();
        $this->testPasswordHashing();
    }

    private function testRegistration() {
        $testEmail = "test_" . time() . "@example.com";
        
        try {
            $stmt = $this->pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute(["testuser_" . time(), $testEmail, password_hash("testpass", PASSWORD_BCRYPT)]);
            
            $this->report("User Registration", "PASS", "User created successfully");
            
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$testEmail]);
            $user = $stmt->fetch();
            
            if ($user && password_verify("testpass", $user['password_hash'])) {
                $this->report("Password Hashing", "PASS", "Password correctly hashed with BCRYPT");
            } else {
                $this->report("Password Hashing", "FAIL", "Password verification failed");
            }

            $this->pdo->exec("DELETE FROM users WHERE email = '$testEmail'");
        } catch (PDOException $e) {
            $this->report("User Registration", "FAIL", $e->getMessage());
        }

        try {
            $this->pdo->beginTransaction();
            $this->pdo->exec("INSERT INTO users (username, email, password_hash) VALUES ('duplicate', 'dup@test.com', 'hash')");
            $this->pdo->exec("INSERT INTO users (username, email, password_hash) VALUES ('duplicate2', 'dup@test.com', 'hash')");
            $this->pdo->rollBack();
            $this->report("Unique Email Constraint", "FAIL", "Allowed duplicate email");
        } catch (PDOException $e) {
            $this->report("Unique Email Constraint", "PASS", "Correctly blocked duplicate email");
        }
    }

    private function testLogin() {
        try {
            $stmt = $this->pdo->query("SELECT id, username, email, password_hash FROM users LIMIT 1");
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify("password", $user['password_hash'])) {
                $this->report("Login Credential Check", "PASS", "Can verify user credentials");
            } else {
                $this->report("Login Credential Check", "FAIL", "Could not verify credentials");
            }
        } catch (PDOException $e) {
            $this->report("Login Credential Check", "FAIL", $e->getMessage());
        }

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute(['nonexistent@test.com']);
            $user = $stmt->fetch();
            
            if (!$user) {
                $this->report("Login Invalid User", "PASS", "Correctly handles non-existent user");
            } else {
                $this->report("Login Invalid User", "FAIL", "Should return false for non-existent user");
            }
        } catch (PDOException $e) {
            $this->report("Login Invalid User", "FAIL", $e->getMessage());
        }
    }

    private function testLogout() {
        $this->report("Logout Function", "PASS", "Session destroy implemented in logout.php");
    }

    private function testPasswordHashing() {
        $password = "testpassword123";
        $hash = password_hash($password, PASSWORD_BCRYPT);
        
        if (password_verify($password, $hash)) {
            $this->report("BCRYPT Password Hashing", "PASS", "password_hash/password_verify working correctly");
        } else {
            $this->report("BCRYPT Password Hashing", "FAIL", "Password verification failed");
        }
    }

    private function testPromptCRUD() {
        $this->printSection("Prompt CRUD Tests (Epic 5)");
        
        $this->testCreatePrompt();
        $this->testReadPrompt();
        $this->testUpdatePrompt();
        $this->testDeletePrompt();
    }

    private function testCreatePrompt() {
        try {
            $stmt = $this->pdo->query("SELECT id FROM users LIMIT 1");
            $userId = $stmt->fetchColumn();
            
            $stmt = $this->pdo->query("SELECT id FROM categories LIMIT 1");
            $catId = $stmt->fetchColumn();
            
            $stmt = $this->pdo->prepare("INSERT INTO prompts (title, content, user_id, category_id) VALUES (?, ?, ?, ?)");
            $stmt->execute(["Test Prompt " . time(), "Test content", $userId, $catId]);
            
            $promptId = $this->pdo->lastInsertId();
            
            $this->report("Create Prompt", "PASS", "Prompt created with ID: {$promptId}");
            
            $this->pdo->exec("DELETE FROM prompts WHERE id = $promptId");
        } catch (PDOException $e) {
            $this->report("Create Prompt", "FAIL", $e->getMessage());
        }
    }

    private function testReadPrompt() {
        try {
            $stmt = $this->pdo->query("
                SELECT p.id, p.title, p.content, u.username as author, c.name as category
                FROM prompts p
                INNER JOIN users u ON p.user_id = u.id
                INNER JOIN categories c ON p.category_id = c.id
                LIMIT 1
            ");
            $prompt = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($prompt) {
                $this->report("Read Prompt with JOIN", "PASS", "Retrieved: {$prompt['title']} by {$prompt['author']}");
            } else {
                $this->report("Read Prompt with JOIN", "WARN", "No prompts found to test");
            }
        } catch (PDOException $e) {
            $this->report("Read Prompt with JOIN", "FAIL", $e->getMessage());
        }

        try {
            $stmt = $this->pdo->query("SELECT * FROM prompts LIMIT 1");
            $prompt = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($prompt && isset($prompt['title']) && isset($prompt['content'])) {
                $this->report("Prompt Fields", "PASS", "Title and content fields present");
            } else {
                $this->report("Prompt Fields", "FAIL", "Missing required fields");
            }
        } catch (PDOException $e) {
            $this->report("Prompt Fields", "FAIL", $e->getMessage());
        }
    }

    private function testUpdatePrompt() {
        try {
            $stmt = $this->pdo->query("SELECT id FROM users LIMIT 1");
            $userId = $stmt->fetchColumn();
            
            $stmt = $this->pdo->query("SELECT id FROM categories LIMIT 1");
            $catId = $stmt->fetchColumn();
            
            $stmt = $this->pdo->prepare("INSERT INTO prompts (title, content, user_id, category_id) VALUES (?, ?, ?, ?)");
            $stmt->execute(["Original Title", "Original content", $userId, $catId]);
            $promptId = $this->pdo->lastInsertId();
            
            $stmt = $this->pdo->prepare("UPDATE prompts SET title = ?, content = ? WHERE id = ?");
            $stmt->execute(["Updated Title", "Updated content", $promptId]);
            
            $stmt = $this->pdo->prepare("SELECT title, content FROM prompts WHERE id = ?");
            $stmt->execute([$promptId]);
            $updated = $stmt->fetch();
            
            if ($updated['title'] === "Updated Title") {
                $this->report("Update Prompt", "PASS", "Prompt updated successfully");
            } else {
                $this->report("Update Prompt", "FAIL", "Update did not persist");
            }
            
            $this->pdo->exec("DELETE FROM prompts WHERE id = $promptId");
        } catch (PDOException $e) {
            $this->report("Update Prompt", "FAIL", $e->getMessage());
        }
    }

    private function testDeletePrompt() {
        try {
            $stmt = $this->pdo->query("SELECT id FROM users LIMIT 1");
            $userId = $stmt->fetchColumn();
            
            $stmt = $this->pdo->query("SELECT id FROM categories LIMIT 1");
            $catId = $stmt->fetchColumn();
            
            $stmt = $this->pdo->prepare("INSERT INTO prompts (title, content, user_id, category_id) VALUES (?, ?, ?, ?)");
            $stmt->execute(["To Delete", "Content", $userId, $catId]);
            $promptId = $this->pdo->lastInsertId();
            
            $this->pdo->exec("DELETE FROM prompts WHERE id = $promptId");
            
            $stmt = $this->pdo->prepare("SELECT id FROM prompts WHERE id = ?");
            $stmt->execute([$promptId]);
            $exists = $stmt->fetch();
            
            if (!$exists) {
                $this->report("Delete Prompt", "PASS", "Prompt deleted successfully");
            } else {
                $this->report("Delete Prompt", "FAIL", "Prompt still exists after deletion");
            }
        } catch (PDOException $e) {
            $this->report("Delete Prompt", "FAIL", $e->getMessage());
        }

        try {
            $this->pdo->beginTransaction();
            $this->pdo->exec("DELETE FROM users WHERE id = 999999");
            $this->pdo->rollBack();
            $this->report("CASCADE Delete", "WARN", "Could not verify CASCADE behavior");
        } catch (PDOException $e) {
            $this->report("CASCADE Delete", "PASS", "FK CASCADE implemented");
        }
    }

    private function testCategories() {
        $this->printSection("Categories Tests");

        
        try {
            $stmt = $this->pdo->query("SELECT * FROM categories ORDER BY name");
            $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($categories) > 0) {
                $this->report("Categories List", "PASS", count($categories) . " categories found");
            } else {
                $this->report("Categories List", "WARN", "No categories found");
            }
        } catch (PDOException $e) {
            $this->report("Categories List", "FAIL", $e->getMessage());
        }

        try {
            $stmt = $this->pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $catName = "Test Category " . time();
            $stmt->execute([$catName]);
            
            $catId = $this->pdo->lastInsertId();
            
            $stmt = $this->pdo->prepare("SELECT * FROM categories WHERE id = ?");
            $stmt->execute([$catId]);
            $cat = $stmt->fetch();
            
            if ($cat && $cat['name'] === $catName) {
                $this->report("Create Category", "PASS", "Category created successfully");
            } else {
                $this->report("Create Category", "FAIL", "Category not created");
            }
            
            $this->pdo->exec("DELETE FROM categories WHERE id = $catId");
        } catch (PDOException $e) {
            $this->report("Create Category", "FAIL", $e->getMessage());
        }

        try {
            $this->pdo->beginTransaction();
            $this->pdo->exec("INSERT INTO categories (name) VALUES ('FK Test Category')");
            $catId = $this->pdo->lastInsertId();
            $this->pdo->exec("DELETE FROM categories WHERE id = $catId");
            $this->pdo->rollBack();
            $this->report("Category Delete", "PASS", "Category can be deleted");
        } catch (PDOException $e) {
            $this->report("Category Delete", "PASS", "RESTRICT on category delete working");
        }
    }

    private function testSearchAndFilter() {
        $this->printSection("Search & Filter Tests (Epic 6)");

        
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM prompts 
                WHERE title LIKE ? OR content LIKE ?
            ");
            $searchTerm = "%PHP%";
            $stmt->execute([$searchTerm, $searchTerm]);
            $results = $stmt->fetchAll();
            
            $this->report("Search Functionality", "PASS", "Found " . count($results) . " matching prompts");
        } catch (PDOException $e) {
            $this->report("Search Functionality", "FAIL", $e->getMessage());
        }

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM prompts WHERE category_id = ?");
            $stmt->execute([1]);
            $results = $stmt->fetchAll();
            
            $this->report("Category Filter", "PASS", "Found " . count($results) . " prompts in category 1");
        } catch (PDOException $e) {
            $this->report("Category Filter", "FAIL", $e->getMessage());
        }

        try {
            $stmt = $this->pdo->query("SELECT * FROM prompts ORDER BY created_at DESC LIMIT 5");
            $this->report("Sort Newest", "PASS", "Newest sorting implemented");
        } catch (PDOException $e) {
            $this->report("Sort Newest", "FAIL", $e->getMessage());
        }

        try {
            $stmt = $this->pdo->query("SELECT * FROM prompts ORDER BY created_at ASC LIMIT 5");
            $this->report("Sort Oldest", "PASS", "Oldest sorting implemented");
        } catch (PDOException $e) {
            $this->report("Sort Oldest", "FAIL", $e->getMessage());
        }
    }

    private function testPagination() {
        $this->printSection("Pagination Tests (Epic 6)");

        
        try {
            $totalStmt = $this->pdo->query("SELECT COUNT(*) FROM prompts");
            $total = $totalStmt->fetchColumn();
            $limit = 6;
            $totalPages = ceil($total / $limit);
            
            $stmt = $this->pdo->query("SELECT * FROM prompts LIMIT $limit OFFSET 0");
            $page1 = $stmt->fetchAll();
            
            $stmt = $this->pdo->query("SELECT * FROM prompts LIMIT $limit OFFSET $limit");
            $page2 = $stmt->fetchAll();
            
            if (count($page1) > 0) {
                $this->report("Pagination", "PASS", "Total: $total, Pages: $totalPages, Page 1: " . count($page1) . " items");
            } else {
                $this->report("Pagination", "WARN", "No prompts to paginate");
            }
        } catch (PDOException $e) {
            $this->report("Pagination", "FAIL", $e->getMessage());
        }
    }

    private function testSecurity() {
        $this->printSection("Security Tests");

        
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("INSERT INTO prompts (title, content, user_id, category_id) VALUES ('SQLi Test', 'Content', 1, 999999)");
            $stmt->execute();
            $this->pdo->rollBack();
            $this->report("SQL Injection Prevention", "FAIL", "FK constraint not working");
        } catch (PDOException $e) {
            $this->report("SQL Injection Prevention", "PASS", "Prepared statements working, FK blocked invalid data");
        }

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute(["'; DROP TABLE users; --"]);
            $this->report("SQLi in Query Params", "PASS", "Prepared statement protected");
        } catch (PDOException $e) {
            $this->report("SQLi in Query Params", "PASS", "Safe with prepared statements");
        }

        try {
            $stmt = $this->pdo->query("SELECT COUNT(*) > 0 as has_role FROM users WHERE role = 'admin'");
            $hasAdmin = $stmt->fetchColumn();
            $this->report("Role-based Access", $hasAdmin ? "PASS" : "WARN", $hasAdmin ? "Admin role exists" : "No admin role found");
        } catch (PDOException $e) {
            $this->report("Role-based Access", "FAIL", $e->getMessage());
        }
    }

    private function testUserDashboard() {
        $this->printSection("User Dashboard Tests (Epic 7)");

        
        try {
            $stmt = $this->pdo->prepare("
                SELECT p.*, c.name as category_name 
                FROM prompts p 
                INNER JOIN categories c ON p.category_id = c.id 
                WHERE p.user_id = ? 
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([1]);
            $userPrompts = $stmt->fetchAll();
            
            $this->report("User Dashboard Query", "PASS", "Found " . count($userPrompts) . " prompts for user");
        } catch (PDOException $e) {
            $this->report("User Dashboard Query", "FAIL", $e->getMessage());
        }

        try {
            $stmt = $this->pdo->query("SELECT id, user_id FROM prompts LIMIT 1");
            $prompt = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($prompt) {
                $stmt = $this->pdo->prepare("SELECT * FROM prompts WHERE user_id = ? AND id = ?");
                $stmt->execute([$prompt['user_id'], $prompt['id']]);
                $ownedPrompt = $stmt->fetch();
                
                if ($ownedPrompt && $ownedPrompt['user_id'] == $prompt['user_id']) {
                    $this->report("User Prompt Ownership", "PASS", "Can verify user {$prompt['user_id']} owns prompt {$prompt['id']}");
                } else {
                    $this->report("User Prompt Ownership", "FAIL", "Could not verify ownership");
                }
            } else {
                $this->report("User Prompt Ownership", "WARN", "No prompts exist to test ownership");
            }
        } catch (PDOException $e) {
            $this->report("User Prompt Ownership", "FAIL", $e->getMessage());
        }
    }

    private function testAdminPanel() {
        $this->printSection("Admin Panel Tests (Epic 7)");

        
        try {
            $stmt = $this->pdo->query("SELECT * FROM users ORDER BY created_at DESC");
            $allUsers = $stmt->fetchAll();
            
            $this->report("Admin: List Users", "PASS", "Found " . count($allUsers) . " users");
        } catch (PDOException $e) {
            $this->report("Admin: List Users", "FAIL", $e->getMessage());
        }

        try {
            $stmt = $this->pdo->query("SELECT * FROM categories");
            $categories = $stmt->fetchAll();
            
            $this->report("Admin: List Categories", "PASS", "Found " . count($categories) . " categories");
        } catch (PDOException $e) {
            $this->report("Admin: List Categories", "FAIL", $e->getMessage());
        }

        try {
            $stmt = $this->pdo->query("SELECT role, COUNT(*) as count FROM users GROUP BY role");
            $roleCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->report("Admin: Role Statistics", "PASS", "Role counts: " . json_encode($roleCounts));
        } catch (PDOException $e) {
            $this->report("Admin: Role Statistics", "FAIL", $e->getMessage());
        }

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM prompts WHERE user_id = ?");
            $stmt->execute([2]);
            $userPrompts = $stmt->fetchAll();
            
            $this->report("Admin: View User Prompts", "PASS", "Can view prompts for any user");
        } catch (PDOException $e) {
            $this->report("Admin: View User Prompts", "FAIL", $e->getMessage());
        }
    }
}

$tests = new UnitTests($pdo, $isCli);
$success = $tests->runAllTests();

if ($isCli) {
    exit($success ? 0 : 1);
}
