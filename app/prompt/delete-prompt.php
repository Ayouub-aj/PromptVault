<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../dashboard.php");
    exit;
}

$p_id = $_GET['id'];
$u_id = $_SESSION['user_id'];
$error = "";

try {
    $roleStmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $roleStmt->execute([$u_id]);
    $currentUser = $roleStmt->fetch();
    $isAdmin = ($currentUser && $currentUser['role'] === 'admin');
} catch (PDOException $e) {
    die("Role check failed.");
}

try {
    if ($isAdmin) {
        $stmt = $pdo->prepare("SELECT title FROM prompts WHERE id = ?");
        $stmt->execute([$p_id]);
    } else {
        $stmt = $pdo->prepare("SELECT title FROM prompts WHERE id = ? AND user_id = ?");
        $stmt->execute([$p_id, $u_id]);
    }
    $prompt = $stmt->fetch();

    if (!$prompt) {
        header("Location: ../dashboard.php");
        exit;
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($isAdmin) {
            $delete = $pdo->prepare("DELETE FROM prompts WHERE id = ?");
            $delete->execute([$p_id]);
        } else {
            $delete = $pdo->prepare("DELETE FROM prompts WHERE id = ? AND user_id = ?");
            $delete->execute([$p_id, $u_id]);
        }
        
        $redirect = $isAdmin ? "../admin.php" : "../dashboard.php";
        header("Location: $redirect");
        exit;
    } catch (PDOException $e) {
        $error = "Deletion failed: " . $e->getMessage();
    }
}

require_once dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5 text-center">
            <div class="card shadow-lg rounded-4 border-0 p-5">
                <div class="card-body">
                    <div class="mb-4">
                        <div class="alert alert-danger d-inline-block rounded-circle p-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5Zm-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5ZM4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06Zm6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528ZM8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5Z"/>
                            </svg>
                        </div>
                    </div>
                    
                    <h2 class="fw-bold mb-3">Confirm Deletion</h2>
                    <p class="text-muted mb-4 px-3">
                        Are you sure you want to permanently delete <br>
                        <strong class="text-dark">"<?php echo htmlspecialchars($prompt['title']); ?>"</strong>? 
                        <br>This action cannot be undone.
                    </p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger mb-4"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <form action="delete-prompt.php?id=<?php echo $p_id; ?>" method="POST" class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger btn-lg fw-bold rounded-pill shadow-sm transition-hover">
                            Yes, Delete Permanently
                        </button>
                    </form>
                    
                    <div class="mt-3">
                        <a href="../dashboard.php" class="btn btn-link text-muted fw-bold text-decoration-none">No, Keep It</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require_once dirname(__DIR__, 2) . '/includes/footer.php'; 
?>
