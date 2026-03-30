<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../dashboard.php");
    exit;
}

$p_id = $_GET['id'];
$u_id = $_SESSION['user_id'];
$error = "";
$success = "";

try {
    $roleStmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $roleStmt->execute([$u_id]);
    $currentUser = $roleStmt->fetch();
    $isAdmin = ($currentUser && $currentUser['role'] === 'admin');
} catch (PDOException $e) {
    die("Role check failed.");
}

try {
    $catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $catStmt->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to load categories.";
}

try {
    if ($isAdmin) {
        $stmt = $pdo->prepare("SELECT * FROM prompts WHERE id = ?");
        $stmt->execute([$p_id]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM prompts WHERE id = ? AND user_id = ?");
        $stmt->execute([$p_id, $u_id]);
    }
    $prompt = $stmt->fetch();

    if (!$prompt) {
        header("Location: ../dashboard.php");
        exit;
    }
} catch (PDOException $e) {
    die("Database failure: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']);
    $category_id = $_POST['category_id'];
    $content     = trim($_POST['content']);

    if (empty($title) || empty($category_id) || empty($content)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            if ($isAdmin) {
                $update = $pdo->prepare("UPDATE prompts SET title = ?, content = ?, category_id = ? WHERE id = ?");
                $update->execute([$title, $content, $category_id, $p_id]);
            } else {
                $update = $pdo->prepare("UPDATE prompts SET title = ?, content = ?, category_id = ? WHERE id = ? AND user_id = ?");
                $update->execute([$title, $content, $category_id, $p_id, $u_id]);
            }
            
            $success = "Prompt updated successfully! <a href='view-prompt.php?id=$p_id'>View Changes</a>";
            
            $prompt['title'] = $title;
            $prompt['category_id'] = $category_id;
            $prompt['content'] = $content;
        } catch (PDOException $e) {
            $error = "Update failed: " . $e->getMessage();
        }
    }
}

require_once dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow rounded-4 border-0 p-4">
                <div class="card-body">
                    <h2 class="fw-bold mb-1">Modify Existing Prompt</h2>
                    <p class="text-muted mb-4">You are currently editing: <span class="fw-bold text-dark"><?php echo htmlspecialchars($prompt['title']); ?></span></p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form action="edit-prompt.php?id=<?php echo $p_id; ?>" method="POST">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="title" class="form-label fw-bold small text-uppercase letter-spacing-1">Prompt Title</label>
                                <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($prompt['title']); ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="category_id" class="form-label fw-bold small text-uppercase letter-spacing-1">Category</label>
                                <select name="category_id" id="category_id" class="form-select" required>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $prompt['category_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 mt-4">
                                <label for="content" class="form-label fw-bold small text-uppercase letter-spacing-1">Prompt Content</label>
                                <textarea name="content" id="content" rows="10" class="form-control" required><?php echo htmlspecialchars($prompt['content']); ?></textarea>
                            </div>
                        </div>

                        <div class="mt-5 border-top pt-4 text-end">
                            <a href="../dashboard.php" class="btn btn-light px-4 me-2">Cancel</a>
                            <button type="submit" class="btn btn-dark px-5 py-2 fw-bold rounded-pill shadow-sm">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require_once dirname(__DIR__, 2) . '/includes/footer.php'; 
?>
