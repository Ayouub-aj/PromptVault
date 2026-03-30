<?php
require_once dirname(__DIR__, 2) . '/config/db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$p_id = $_GET['id'];

try {
    $stmt = $pdo->prepare("
        SELECT 
            p.title, 
            p.content, 
            p.created_at, 
            u.username AS author, 
            c.name AS category 
        FROM prompts p
        INNER JOIN users u ON p.user_id = u.id
        INNER JOIN categories c ON p.category_id = c.id
        WHERE p.id = ?
    ");
    $stmt->execute([$p_id]);
    $prompt = $stmt->fetch();

    if (!$prompt) {
        throw new Exception("Prompt asset not found.");
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

require_once dirname(__DIR__, 2) . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row g-5">
        <div class="col-lg-12">
            <div class="card p-5 shadow rounded-4 border-0 mb-4 bg-light">
                <div class="card-body">
                    <div class="mb-4 d-flex justify-content-between align-items-center">
                        <span class="badge bg-success py-2 px-3 fw-bold text-uppercase letter-spacing-1">
                            <?php echo htmlspecialchars($prompt['category']); ?>
                        </span>
                        <div class="text-secondary small">
                            Published on <?php echo date('M d, Y', strtotime($prompt['created_at'])); ?> 
                            by <span class="fw-bold text-dark"><?php echo htmlspecialchars($prompt['author']); ?></span>
                        </div>
                    </div>

                    <h1 class="display-4 fw-bold mb-4 text-dark"><?php echo htmlspecialchars($prompt['title']); ?></h1>
                    
                    <hr class="mb-5 opacity-25">

                    <h5 class="fw-bold small text-uppercase text-secondary mb-3 letter-spacing-1">Prompt Sequence:</h5>
                    <div class="p-4 bg-white border rounded-4 position-relative shadow-sm overflow-auto" id="promptArea" style="max-height: 500px;">
                        <pre id="promptText" style="white-space: pre-wrap; margin: 0; font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; color: #212529;"><?php echo htmlspecialchars($prompt['content']); ?></pre>
                        
                        <div class="position-absolute top-0 end-0 p-3">
                             <button class="btn btn-dark btn-sm rounded-pill py-2 px-3 fw-bold border-0 shadow-sm" onclick="copyPrompt()">
                                <i class="bi bi-clipboard me-1"></i> Copy to Clipboard
                             </button>
                        </div>
                    </div>

                    <div class="mt-5 border-top pt-4 d-flex justify-content-center gap-3">
                        <a href="../index.php" class="btn btn-light px-4 py-2 fw-bold rounded-pill text-muted">
                            &larr; Back to Repository
                        </a>
                        <a href="download.php?id=<?php echo $p_id; ?>" class="btn btn-success px-5 py-2 fw-bold rounded-pill shadow-sm">
                            <i class="bi bi-download me-1"></i> Download As .txt
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyPrompt() {
    const text = document.getElementById('promptText').innerText;
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard successfully!');
    }).catch(err => {
        console.error('Failed to copy text: ', err);
    });
}
</script>

<?php 
require_once dirname(__DIR__, 2) . '/includes/footer.php'; 
?>
