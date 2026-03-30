<?php
require_once dirname(__DIR__) . '/config/db.php';

$search   = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$sort     = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page     = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$limit    = 6;
$offset   = ($page - 1) * $limit;

try {
    $baseQuery = "
        FROM prompts p
        INNER JOIN users u ON p.user_id = u.id
        INNER JOIN categories c ON p.category_id = c.id
        WHERE 1=1
    ";
    
    $params = [];

    if (!empty($search)) {
        $baseQuery .= " AND (p.title LIKE ? OR p.content LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if (!empty($category) && is_numeric($category)) {
        $baseQuery .= " AND p.category_id = ?";
        $params[] = (int)$category;
    }

    $countStmt = $pdo->prepare("SELECT COUNT(*) $baseQuery");
    $countStmt->execute($params);
    $totalCount = $countStmt->fetchColumn();
    $totalPages = ceil($totalCount / $limit);

    $orderBy = ($sort === 'oldest') ? 'p.created_at ASC' : 'p.created_at DESC';
    $mainStmt = $pdo->prepare("SELECT p.*, u.username as author, c.name as category $baseQuery ORDER BY $orderBy LIMIT $limit OFFSET $offset");
    $mainStmt->execute($params);
    $prompts = $mainStmt->fetchAll();

    $catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name ASC");
    $categories = $catStmt->fetchAll();

} catch (PDOException $e) {
    $error = "System Synchronization Error: " . $e->getMessage();
}

require_once dirname(__DIR__) . '/includes/header.php';
?>

<section class="hero-section text-center shadow-lg mb-5 pb-5">
    <div class="container py-lg-5">
        <h1 class="display-3 fw-900 mb-3 text-dark">
            PROMPT<span class="text-white bg-dark px-2 rounded ms-1">VAULT</span>
        </h1>
        <p class="lead mb-5 mx-auto w-75 fw-500 opacity-75">
            The world's most sophisticated archive for high-performance AI prompts.
        </p>

        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <form action="index.php" method="GET" class="bg-white p-2 rounded-pill shadow-lg d-flex gap-2">
                    <input type="text" name="search" class="form-control border-0 px-4 rounded-pill" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search prompts..." style="box-shadow: none;">
                    
                    <select name="category" class="form-select border-0 w-auto d-none d-md-block" style="box-shadow: none;">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $category) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <select name="sort" class="form-select border-0 w-auto d-none d-lg-block" style="box-shadow: none;">
                        <option value="newest" <?php echo ($sort == 'newest') ? 'selected' : ''; ?>>Newest</option>
                        <option value="oldest" <?php echo ($sort == 'oldest') ? 'selected' : ''; ?>>Oldest</option>
                    </select>

                    <button type="submit" class="btn btn-dark px-4 rounded-pill fw-bold">Scan Vault</button>
                </form>
            </div>
        </div>
    </div>
</section>

<div class="container pb-5" id="prompts">
    <div class="row align-items-center mb-5 mt-4">
        <div class="col-md-8">
            <h2 class="fw-900 display-6 mb-0">Repository Logs</h2>
            <p class="text-muted">
                <?php if (!empty($search) || !empty($category)): ?>
                    Filtered Results: <span class="fw-bold text-dark">'<?php echo htmlspecialchars($search); ?>'</span> 
                    <a href="index.php" class="text-danger small ms-2 text-decoration-none">Clear Filters &times;</a>
                <?php else: ?>
                    The latest contributions from our global engineering network.
                <?php endif; ?>
            </p>
        </div>
        <div class="col-md-4 text-md-end">
            <span class="badge bg-dark rounded-pill py-2 px-4 shadow-sm fs-6">
                <?php echo $totalCount; ?> ASSETS DETECTED
            </span>
        </div>
    </div>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger py-4 shadow-sm rounded-4" role="alert">
            <h5 class="alert-heading fw-bold">Critical Error</h5>
            <p class="mb-0"><code><?php echo $error; ?></code></p>
        </div>
    <?php elseif (empty($prompts)): ?>
        <div class="text-center py-5 bg-light rounded-5 border-dashed">
            <h3 class="text-muted fw-bold italic h1 opacity-25">Ø RESULTS</h3>
            <p class="mb-0">The search query returned 0 matches in our database.</p>
        </div>
    <?php else: ?>
        <div class="row g-4 justify-content-center">
            <?php foreach ($prompts as $prompt): ?>
                <div class="col-sm-12 col-md-6 col-lg-4">
                    <article class="card h-100 shadow-sm rounded-4 border-light transition-hover overflow-hidden">
                        <div class="card-body p-4 d-flex flex-column">
                            <div class="mb-3">
                                <span class="badge bg-success bg-opacity-10 text-success text-uppercase fw-bold py-2 px-3 small">
                                    <?php echo htmlspecialchars($prompt['category']); ?>
                                </span>
                            </div>
                            <h4 class="card-title fw-bold mb-3"><?php echo htmlspecialchars($prompt['title']); ?></h4>
                            <p class="card-text text-secondary mb-4 flex-grow-1">
                                <?php 
                                    $snippet = htmlspecialchars($prompt['content']);
                                    echo (strlen($snippet) > 130) ? substr($snippet, 0, 130) . "..." : $snippet;
                                ?>
                            </p>
                            <div class="mt-auto d-flex justify-content-between align-items-center border-top border-light pt-3">
                                <div class="text-muted small">
                                    By <span class="fw-bold text-dark"><?php echo htmlspecialchars($prompt['author']); ?></span>
                                </div>
                                <a href="prompt/view-prompt.php?id=<?php echo $prompt['id']; ?>" class="btn btn-sm btn-dark px-3 rounded-pill fw-bold shadow-sm">
                                    Open Asset &rarr;
                                </a>
                            </div>
                        </div>
                    </article>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="mt-5">
                <ul class="pagination justify-content-center gap-2">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                            <a class="page-link rounded-circle border-0 fw-bold shadow-sm <?php echo ($i === $page) ? 'bg-dark text-white' : 'text-dark'; ?>" 
                               href="index.php?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category; ?>&sort=<?php echo $sort; ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/includes/footer.php'; ?>
