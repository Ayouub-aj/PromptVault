<?php
require_once dirname(__DIR__) . '/config/db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin.php");
    exit;
}

$c_id = $_GET['id'];
$error = "";

try {
    $stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $stmt->execute([$c_id]);
    $category = $stmt->fetch();

    if (!$category) {
        header("Location: admin.php");
        exit;
    }
} catch (PDOException $e) {
    die("Database failure: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $delete = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $delete->execute([$c_id]);
        header("Location: admin.php");
        exit;
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            $error = "This category cannot be deleted because it is still linked to prompts. Reassign those prompts first.";
        } else {
            $error = "Deletion failed: " . $e->getMessage();
        }
    }
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5 text-center">
            <div class="card shadow-lg rounded-4 border-0 p-5">
                <div class="card-body">
                    <div class="mb-4 text-danger">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="bi bi-exclamation-triangle-fill" viewBox="0 0 16 16">
                            <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
                        </svg>
                    </div>
                    
                    <h2 class="fw-bold mb-3">Delete Category?</h2>
                    <p class="text-muted mb-4">
                        You are about to delete <strong class="text-dark">"<?php echo htmlspecialchars($category['name']); ?>"</strong>.
                    </p>

                    <?php if ($error): ?>
                        <div class="alert alert-danger mb-4 small fw-bold" role="alert"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form action="delete-category.php?id=<?php echo $c_id; ?>" method="POST" class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger btn-lg fw-bold rounded-pill shadow-sm transition-hover">
                            Confirm Deletion
                        </button>
                    </form>
                    
                    <div class="mt-3">
                        <a href="admin.php" class="btn btn-link text-muted fw-bold text-decoration-none">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require_once dirname(__DIR__) . '/includes/footer.php'; 
?>
