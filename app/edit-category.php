<?php
require_once dirname(__DIR__) . '/config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin.php");
    exit;
}

$c_id = $_GET['id'];
$error = "";
$success = "";

try {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
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
    $new_name = trim($_POST['name']);

    if (empty($new_name)) {
        $error = "Category name cannot be empty.";
    } else {
        try {
            $update = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
            $update->execute([$new_name, $c_id]);
            header("Location: admin.php");
            exit;
        } catch (PDOException $e) {
            $error = "Update failed: " . $e->getMessage();
        }
    }
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow rounded-4 border-0 p-5">
                <div class="card-body">
                    <h2 class="fw-bold mb-4">Edit Category</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger px-4 py-2 small fw-bold mb-4" role="alert"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form action="edit-category.php?id=<?php echo $c_id; ?>" method="POST">
                        <div class="mb-4">
                            <label for="name" class="form-label fw-bold small text-uppercase letter-spacing-1">Category Label</label>
                            <input type="text" name="name" id="name" class="form-control" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-dark btn-lg fw-bold rounded-pill shadow-sm">Save Changes</button>
                            <a href="admin.php" class="btn btn-light rounded-pill fw-bold">Cancel</a>
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
