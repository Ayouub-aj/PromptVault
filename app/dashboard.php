<?php
require_once dirname(__DIR__) . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
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
} catch (PDOException $e) {
    $error = "Error loading your prompts: " . $e->getMessage();
}

$allUsers = [];
if ($isAdmin) {
    try {
        $userStmt = $pdo->query("SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC");
        $allUsers = $userStmt->fetchAll();
    } catch (PDOException $e) {
        $error = "Error loading user list: " . $e->getMessage();
    }
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="hero-section text-center mb-5">
    <div class="container">
        <h1 class="fw-900 mb-2">Welcome back, <?php echo htmlspecialchars($username); ?>!</h1>
        <p class="lead fw-500 opacity-75"><?php echo $promptCount; ?> prompts published in your private library.</p>
        <div class="mt-4 d-flex justify-content-center gap-3">
            <a href="create-prompt.php" class="btn btn-dark btn-lg px-5 py-3 rounded-pill fw-bold shadow-sm transition-hover">
                + New Prompt
            </a>
            <?php if ($isAdmin): ?>
                <button type="button" class="btn btn-success btn-lg px-5 py-3 rounded-pill fw-bold shadow-sm transition-hover" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    + New Category
                </button>
            <?php endif; ?>
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

<div class="container pb-5">
    <div class="row">
        <div class="col-12">
            <div class="card shadow rounded-4 border-0 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="fw-bold m-0">Your Prompts</h3>
                    <span class="badge bg-success rounded-pill px-3"><?php echo $promptCount; ?> total</span>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger border-0"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($promptCount > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3 border-0 small text-uppercase fw-bold text-muted">Title</th>
                                    <th class="border-0 small text-uppercase fw-bold text-muted">Category</th>
                                    <th class="border-0 small text-uppercase fw-bold text-muted">Date</th>
                                    <th class="border-0 text-end pe-3 small text-uppercase fw-bold text-muted">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($prompts as $prompt): ?>
                                    <tr>
                                        <td class="ps-3 fw-bold">
                                            <a href="view-prompt.php?id=<?php echo $prompt['id']; ?>" class="text-dark text-decoration-none">
                                                <?php echo htmlspecialchars($prompt['title']); ?>
                                            </a>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border rounded-pill px-3">
                                                <?php echo htmlspecialchars($prompt['category_name']); ?>
                                            </span>
                                        </td>
                                        <td class="text-muted small">
                                            <?php echo date('M d, Y', strtotime($prompt['created_at'])); ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="edit-prompt.php?id=<?php echo $prompt['id']; ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-bold me-1">Edit</a>
                                            <a href="delete-prompt.php?id=<?php echo $prompt['id']; ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold">Delete</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="bi bi-journal-plus fs-1 text-muted"></i>
                        </div>
                        <h4 class="fw-bold">No prompts yet</h4>
                        <p class="text-muted">Start building your prompt repository today.</p>
                        <a href="create-prompt.php" class="btn btn-success rounded-pill px-4 fw-bold mt-2">Create Your First Prompt</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($isAdmin): ?>
        <div class="row mt-5">
            <div class="col-12">
                <div class="card shadow rounded-4 border-0 p-4 bg-light">
                    <h3 class="fw-bold mb-4 text-dark"><span class="text-success">Admin:</span> User Management</h3>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr class="small text-uppercase fw-bold text-muted">
                                    <th class="ps-3 border-0">ID</th>
                                    <th class="border-0">Username</th>
                                    <th class="border-0">Email</th>
                                    <th class="border-0">Role</th>
                                    <th class="border-0">Joined Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white">
                                <?php foreach ($allUsers as $u): ?>
                                    <tr>
                                        <td class="ps-3 text-muted"><?php echo $u['id']; ?></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($u['username']); ?></td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td>
                                            <span class="badge <?php echo ($u['role'] === 'admin') ? 'bg-dark' : 'bg-secondary'; ?> rounded-pill">
                                                <?php echo strtoupper($u['role']); ?>
                                            </span>
                                        </td>
                                        <td class="text-muted small">
                                            <?php echo date('M d, Y', strtotime($u['created_at'])); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php 
require_once dirname(__DIR__) . '/includes/footer.php'; 
?>
