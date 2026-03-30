<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PromptVault — Your archive for High Performance Prompts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>style/main.css">
</head>

<body>
    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    $is_logged_in = isset($_SESSION['user_id']);
    ?>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>app/index.php">
                PROMPT<span class="text-success">VAULT</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item">
                        <a class="nav-link px-3 <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>app/index.php">Home</a>
                    </li>

                    <?php if ($is_logged_in): ?>
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                            <li class="nav-item">
                                <a class="nav-link px-3 text-success fw-bold <?php echo ($current_page == 'admin.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>app/admin.php">Admin Panel</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link px-3 <?php echo ($current_page == 'dashboard.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>app/dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item ms-lg-2">
                            <a class="btn btn-outline-danger btn-sm rounded-pill px-4 fw-bold" href="<?php echo BASE_URL; ?>app/auth/logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link px-3 <?php echo ($current_page == 'login.php') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>app/auth/login.php">Login</a>
                        </li>
                        <li class="nav-item ms-lg-2">
                            <a class="btn btn-success btn-sm rounded-pill px-4 fw-bold" href="<?php echo BASE_URL; ?>app/auth/register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <main>
