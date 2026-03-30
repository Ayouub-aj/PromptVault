<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = (int)$user['id'];
                $_SESSION['username'] = (string)$user['username'];
                $_SESSION['role'] = (string)$user['role'];

                header("Location: ../index.php");
                exit;
            } else {
                $error = "Incorrect email or password.";
            }
        } catch (PDOException $e) {
            $error = "Login synchronization error: " . $e->getMessage();
        }
    }
}

require_once dirname(__DIR__) . '/../includes/header.php';
?>

<div class="container py-5 mt-4">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card shadow rounded-4 p-4 border-0">
                <div class="card-body">
                    <h2 class="fw-bold mb-4 text-center">Sign In</h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 small fw-bold mb-3" role="alert"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if (isset($_GET['forgot'])): ?>
                        <div class="alert alert-info py-2 small fw-bold mb-3 border-0 shadow-sm" role="alert">
                             A recovery hint has been sent to your email address.
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold text-uppercase small letter-spacing-1">Email</label>
                            <input type="email" name="email" id="email" class="form-control" required placeholder="name@example.com">
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label for="password" class="form-label fw-bold text-uppercase small letter-spacing-1 mb-0">Password</label>
                                <a href="login.php?forgot=1" class="text-success small fw-bold text-decoration-none">Forgot?</a>
                            </div>
                            <input type="password" name="password" id="password" class="form-control" required placeholder="Your password">
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-dark btn-lg fw-bold rounded-pill shadow-sm transition-hover">Sign In &rarr;</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4 pt-3 border-top border-light">
                        <p class="mb-0 text-muted small">New here? <a href="register.php" class="text-success fw-bold text-decoration-none transition-hover">Open an account</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require_once dirname(__DIR__) . '/../includes/footer.php'; 
?>
