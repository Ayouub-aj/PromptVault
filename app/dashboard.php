<?php
require_once dirname(__DIR__) . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth/login.php");
    exit;
}

$u_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$error = "";

try {
    $uStmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $uStmt->execute([$u_id]);
    $user = $uStmt->fetch();
    $isAdmin = ($user['role'] === 'admin');
} catch (PDOException $e) {
    die("Error fetching user data: " . $e->getMessage());
}

try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name 
        FROM prompts p 
        INNER JOIN categories c ON p.category_id = c.id 
        WHERE p.user_id = ? 
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$u_id]);
    $prompts = $stmt->fetchAll();
    $promptCount = count($prompts);

    if ($isAdmin) {
        $allStmt = $pdo->query("
            SELECT p.*, u.username as author_name, c.name as category_name 
            FROM prompts p 
            INNER JOIN users u ON p.user_id = u.id 
            INNER JOIN categories c ON p.category_id = c.id 
            ORDER BY p.created_at DESC
        ");
        $allPrompts = $allStmt->fetchAll();

        $topStmt = $pdo->query("
            SELECT u.username, u.email, COUNT(p.id) as prompt_count 
            FROM users u 
            LEFT JOIN prompts p ON u.id = p.user_id 
            GROUP BY u.id 
            ORDER BY prompt_count DESC 
            LIMIT 5
        ");
        $contributors = $topStmt->fetchAll();

        $userCountStmt = $pdo->query("SELECT COUNT(*) FROM users");
        $totalUsers = $userCountStmt->fetchColumn();
        
        $catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
        $allCategories = $catStmt->fetchAll();
    }
} catch (PDOException $e) {
    $error = "Data sync error: " . $e->getMessage();
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="hero-section text-center mb-5">
    <div class="container">
        <h1 class="fw-900 mb-2">Welcome back, <?php echo htmlspecialchars($username); ?>!</h1>
        <p class="lead fw-500 opacity-75"><?php echo $promptCount; ?> prompts published in your library.</p>
        <div class="mt-4 d-flex justify-content-center gap-3">
            <a href="prompt/create-prompt.php" class="btn btn-dark btn-lg px-5 py-3 rounded-pill fw-bold shadow-sm transition-hover">
                + New Prompt
            </a>
            <?php if ($isAdmin): ?>
                <button type="button" class="btn btn-success btn-lg px-5 py-3 rounded-pill fw-bold shadow-sm transition-hover" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    + New Category
                </button>
                <a href="admin.php" class="btn btn-outline-dark btn-lg px-5 py-3 rounded-pill fw-bold shadow-sm transition-hover">
                    Admin Full Panel
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($isAdmin): ?>
<div class="container mb-5">
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow rounded-4 border-0 p-4 text-center bg-secondary text-white">
                <h4 class="fw-bold mb-0 text-success"><?php echo count($allPrompts); ?></h4>
                <span class="small text-uppercase opacity-100">Global Assets</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow rounded-4 border-0 p-4 text-center bg-secondary text-white">
                <h4 class="fw-bold mb-0 text-success"><?php echo $totalUsers; ?></h4>
                <span class="small text-uppercase opacity-100">System Users</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow rounded-4 border-0 p-4 text-center bg-secondary text-white">
                <h4 class="fw-bold mb-0 text-success"><?php echo count($allCategories); ?></h4>
                <span class="small text-uppercase opacity-100">Categories</span>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container pb-5">
    <div class="row g-4 h-100">
        <div class="col-lg-<?php echo $isAdmin ? '8' : '12'; ?>">
            <div class="card shadow rounded-4 border-0 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold m-0"><?php echo $isAdmin ? 'Global Repository' : 'Your Prompts'; ?></h3>
                    <span class="badge bg-success rounded-pill px-3"><?php echo $isAdmin ? count($allPrompts) : $promptCount; ?> total</span>
                </div>

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
                            <?php 
                            $displayList = $isAdmin ? $allPrompts : $prompts;
                            foreach ($displayList as $p): 
                            ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-dark">
                                            <a href="prompt/view-prompt.php?id=<?php echo $p['id']; ?>" class="text-dark text-decoration-none"><?php echo htmlspecialchars($p['title']); ?></a>
                                        </div>
                                        <?php if ($isAdmin): ?>
                                            <div class="text-muted small">@<?php echo htmlspecialchars($p['author_name']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border rounded-pill px-3"><?php echo htmlspecialchars($p['category_name']); ?></span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="prompt/edit-prompt.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-bold me-1">Edit</a>
                                        <a href="prompt/delete-prompt.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if ($isAdmin): ?>
        <div class="col-lg-4">
            <div class="card shadow rounded-4 border-0 p-4 bg-light h-100">
                <h4 class="fw-bold mb-4">🏆 Top Contributors</h4>
                <div class="list-group list-group-flush">
                    <?php foreach ($contributors as $c): ?>
                        <div class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-3">
                            <div>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($c['username']); ?></div>
                                <div class="text-muted small"><?php echo htmlspecialchars($c['email']); ?></div>
                            </div>
                            <span class="badge bg-dark rounded-pill"><?php echo $c['prompt_count']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
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
                    <input type="hidden" name="add_category" value="1">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg fw-bold rounded-pill">Create Category</button>
                        <button type="button" class="btn btn-light rounded-pill fw-bold" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
