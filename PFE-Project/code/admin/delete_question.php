<?php 
// require_once '../includes/auth_check.php'; // redirection vers login
require_once __DIR__ . '/../includes/db.php';

if (isset($_GET['id'])) {
    // Get the question and response first
    $stmt = $pdo->prepare("SELECT q.question, r.reponse FROM reponses r JOIN questions q ON r.question_id = q.id WHERE r.id = ?");
    $stmt->execute([$_GET['id']]);
    $data = $stmt->fetch();

    if ($data) {
        // Save data in URL before deletion
        $question = urlencode($data['question']);
        $reponse = urlencode($data['reponse']);

        // Delete the question and response
        $deleteStmt = $pdo->prepare("
            DELETE r, q
            FROM reponses r
            JOIN questions q ON r.question_id = q.id
            WHERE r.id = ?
        ");
        $deleteStmt->execute([$_GET['id']]);

        // Redirect to the delete tab with data
        header("Location: admin_dashboard.php?tab=delete&success=delete&question=$question&reponse=$reponse");
        exit;
    }
}
header("Location: admin_dashboard.php?tab=delete");
exit;     