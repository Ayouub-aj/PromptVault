<?php
require_once dirname(__DIR__) . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$u_id = $_SESSION['user_id'];

try {
    $uStmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $uStmt->execute([$u_id]);
    $user = $uStmt->fetch();

    if (!$user || $user['role'] !== 'admin') {
        header("Location: dashboard.php");
        exit;
    }
} catch (PDOException $e) {
    die("Authorization error: " . $e->getMessage());
}

$error = "";
$success = "";

// Handle Adding a New Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $catName = trim($_POST['cat_name']);
    if (!empty($catName)) {
        try {
            $addCat = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $addCat->execute([$catName]);
            $success = "Category '$catName' added successfully!";
        } catch (PDOException $e) {
            $error = "Error adding category: " . $e->getMessage();
        }
    } else {
        $error = "Category name cannot be empty.";
    }
}

try {
    // 1. All Prompts
    $stmt = $pdo->query("
        SELECT p.*, u.username as author_name, c.name as category_name 
        FROM prompts p 
        INNER JOIN users u ON p.user_id = u.id 
        INNER JOIN categories c ON p.category_id = c.id 
        ORDER BY p.created_at DESC
    ");
    $allPrompts = $stmt->fetchAll();

    // 2. All Categories
    $catListStmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
    $categories = $catListStmt->fetchAll();

    // 3. Top Contributors
    $topStmt = $pdo->query("
        SELECT u.username, u.email, COUNT(p.id) as prompt_count 
        FROM users u 
        LEFT JOIN prompts p ON u.id = p.user_id 
        GROUP BY u.id 
        ORDER BY prompt_count DESC 
        LIMIT 5
    ");
    $contributors = $topStmt->fetchAll();

    // 4. Global Stats
    $totalPrompts = count($allPrompts);
    $userCountStmt = $pdo->query("SELECT COUNT(*) FROM users");
    $totalUsers = $userCountStmt->fetchColumn();
} catch (PDOException $e) {
    $error = "Error fetching administration data: " . $e->getMessage();
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="hero-section text-center mb-5 bg-dark text-white py-5">
    <div class="container">
        <h1 class="fw-900 mb-1"><span class="text-success">ADMIN</span> COMMAND CENTER</h1>
        <p class="lead fw-500 opacity-75">Full control over the PromptVault repository.</p>
        
        <div class="mt-4">
            <a href="create-prompt.php" class="btn btn-success btn-lg px-5 py-3 rounded-pill fw-bold shadow-sm transition-hover">
                + Create New Prompt
            </a>
        </div>

        <div class="row mt-5 g-3 justify-content-center">
            <div class="col-6 col-md-3">
                <div class="bg-white bg-opacity-10 p-3 rounded-4 border border-light border-opacity-25">
                    <h4 class="fw-bold mb-0 text-success"><?php echo $totalPrompts; ?></h4>
                    <span class="small text-uppercase opacity-50">Total Assets</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-white bg-opacity-10 p-3 rounded-4 border border-light border-opacity-25">
                    <h4 class="fw-bold mb-0 text-success"><?php echo $totalUsers; ?></h4>
                    <span class="small text-uppercase opacity-50">System Users</span>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-white bg-opacity-10 p-3 rounded-4 border border-light border-opacity-25">
                    <h4 class="fw-bold mb-0 text-success"><?php echo count($categories); ?></h4>
                    <span class="small text-uppercase opacity-50">Categories</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container pb-5">
    <?php if ($error): ?>
        <div class="alert alert-danger shadow-sm border-0 mb-4"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success shadow-sm border-0 mb-4"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="row g-4 mb-5">
        <div class="col-lg-8">
            <div class="card shadow rounded-4 border-0 p-4 h-100">
                <h4 class="fw-bold mb-4">Global Prompt Repository</h4>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr class="small text-uppercase fw-bold text-muted">
                                <th class="ps-3 border-0">Asset</th>
                                <th class="border-0">Category</th>
                                <th class="border-0 text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allPrompts as $p): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($p['title']); ?></div>
                                        <div class="text-muted small">@<?php echo htmlspecialchars($p['author_name']); ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3"><?php echo htmlspecialchars($p['category_name']); ?></span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="edit-prompt.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-dark rounded-pill px-3 fw-bold me-1">Edit</a>
                                        <a href="delete-prompt.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold">Del</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow rounded-4 border-0 p-4 h-100">
                <h4 class="fw-bold mb-4">🏆 Top Contributors</h4>
                <div class="list-group list-group-flush">
                    <?php if (empty($contributors)): ?>
                        <p class="text-muted small">No data detected.</p>
                    <?php else: ?>
                        <?php foreach ($contributors as $c): ?>
                            <div class="list-group-item bg-transparent border-light d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($c['username']); ?></div>
                                    <div class="text-muted small"><?php echo htmlspecialchars($c['email']); ?></div>
                                </div>
                                <span class="badge bg-dark rounded-pill"><?php echo $c['prompt_count']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-12">
            <div class="card shadow rounded-4 border-0 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold m-0">🏷️ Manage Categories</h4>
                    <button class="btn btn-success btn-sm rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addCategoryModal">+ Add New</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr class="small text-uppercase fw-bold text-muted">
                                <th class="ps-3 border-0">ID</th>
                                <th class="border-0">Category Name</th>
                                <th class="border-0">Status</th>
                                <th class="border-0 text-end pe-3">Control</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td class="ps-3 text-muted">#<?php echo $cat['id']; ?></td>
                                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($cat['name']); ?></td>
                                    <td><span class="badge bg-light text-dark border rounded-pill px-3">Active</span></td>
                                    <td class="text-end pe-3">
                                        <a href="edit-category.php?id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-bold me-1">Edit</a>
                                        <a href="delete-category.php?id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body p-5">
                <h3 class="fw-bold mb-4">New Category</h3>
                <form action="admin.php" method="POST">
                    <div class="mb-4">
                        <label for="cat_name" class="form-label fw-bold text-uppercase small letter-spacing-1">Category Label</label>
                        <input type="text" name="cat_name" id="cat_name" class="form-control" required placeholder="e.g. Generative AI">
                    </div>
                    <div class="d-grid gap-2">
                        <input type="hidden" name="add_category" value="1">
                        <button type="submit" class="btn btn-success btn-lg fw-bold rounded-pill">Create Category</button>
                        <button type="button" class="btn btn-light rounded-pill fw-bold" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php 
require_once dirname(__DIR__) . '/includes/footer.php'; 
?>
