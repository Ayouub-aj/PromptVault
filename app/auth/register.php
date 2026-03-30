<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        try {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $password_hash]);

            $success = "Account created! You can now <a href='login.php' class='fw-bold text-success'>Sign In</a>.";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "This username or email is already registered.";
            } else {
                $error = "Registration error: " . $e->getMessage();
            }
        }
    }
}

require_once dirname(__DIR__) . '/../includes/header.php';
?>

<div class="container py-5 mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-4 p-lg-5">
                    <h2 class="fw-bold mb-4 text-center text-dark">Join PROMPT<span class="text-success">VAULT</span></h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger py-2 small fw-bold mb-3"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success py-2 small fw-bold mb-3 shadow-sm border-0"><?php echo $success; ?></div>
                    <?php endif; ?>

                    <form action="register.php" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label fw-bold small text-uppercase letter-spacing-1">Choose Username</label>
                            <input type="text" name="username" id="username" class="form-control" required placeholder="e.g. prompt_expert">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold small text-uppercase letter-spacing-1">Email Address</label>
                            <input type="email" name="email" id="email" class="form-control" required placeholder="you@example.com">
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold small text-uppercase letter-spacing-1">Create Password</label>
                            <input type="password" name="password" id="password" class="form-control" required placeholder="Minimum 8 characters">
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-dark btn-lg fw-bold rounded-pill shadow-sm transition-hover">Create Account &rarr;</button>
                        </div>
                    </form>

                    <div class="text-center mt-4 pt-4 border-top border-light">
                        <p class="text-muted mb-0 small">Already a member? <a href="login.php" class="text-success fw-bold text-decoration-none transition-hover">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
require_once dirname(__DIR__) . '/../includes/footer.php'; 
?>
