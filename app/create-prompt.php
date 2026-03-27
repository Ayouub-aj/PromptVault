<?php
require_once dirname(__DIR__) . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$u_id = $_SESSION['user_id'];
$error = "";
$title = "";
$category_id = "";
$content = "";

try {
    $catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $catStmt->fetchAll();
} catch (PDOException $e) {
    $error = "Failed to load categories.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title']);
    $category_id = $_POST['category_id'];
    $content     = trim($_POST['content']);

    if (empty($title) || empty($category_id) || empty($content)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            $insert = $pdo->prepare("INSERT INTO prompts (title, content, user_id, category_id) VALUES (?, ?, ?, ?)");
            $insert->execute([$title, $content, $u_id, $category_id]);
            
            $new_id = $pdo->lastInsertId();
            header("Location: view-prompt.php?id=$new_id");
            exit;
        } catch (PDOException $e) {
            $error = "Creation failed: " . $e->getMessage();
        }
    }
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="dashboard.php" class="text-success text-decoration-none">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">New Prompt</li>
                </ol>
            </nav>

            <div class="card shadow rounded-4 border-0 p-4">
                <div class="card-body">
                    <h2 class="fw-bold mb-1">Create New Prompt</h2>
                    <p class="text-muted mb-4">Add a new high-performance prompt to your library.</p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger shadow-sm border-0"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form action="create-prompt.php" method="POST">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="title" class="form-label fw-bold small text-uppercase letter-spacing-1">Prompt Title</label>
                                <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($title); ?>" placeholder="e.g. Advanced SEO Analyst Strategy" required>
                            </div>
                            <div class="col-md-4">
                                <label for="category_id" class="form-label fw-bold small text-uppercase letter-spacing-1">Category</label>
                                <select name="category_id" id="category_id" class="form-select" required>
                                    <option value="" disabled selected>Select category...</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $category_id) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 mt-4">
                                <label for="content" class="form-label fw-bold small text-uppercase letter-spacing-1">Prompt Content</label>
                                <textarea name="content" id="content" rows="10" class="form-control" placeholder="Paste your prompt structure here..." required><?php echo htmlspecialchars($content); ?></textarea>
                            </div>
                        </div>

                        <div class="mt-5 border-top pt-4 text-end">
                            <a href="dashboard.php" class="btn btn-light px-4 me-2 rounded-pill fw-bold">Discard</a>
                            <button type="submit" class="btn btn-dark px-5 py-2 fw-bold rounded-pill shadow-sm">Publish Prompt &rarr;</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require_once dirname(__DIR__) . '/includes/footer.php'; 
?>
