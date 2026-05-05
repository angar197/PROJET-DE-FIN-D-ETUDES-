<?php 
// require_once '../includes/auth_check.php'; // redirection vers login
require_once __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure the 'id' is set and is a valid integer
    if (isset($_POST['id']) && is_numeric($_POST['id'])) {
        $id = $_POST['id'];
        $reponse_text = $_POST['reponse'] ?? '';
        $question_text = $_POST['question'] ?? '';

        // 🔍 Check if the ID exists
        $checkStmt = $pdo->prepare("SELECT r.id FROM reponses r WHERE r.id = ?");
        $checkStmt->execute([$id]);

        if ($checkStmt->rowCount() === 0) {
            // ID does not exist
            header("Location: admin_dashboard.php?tab=edit&error=id_not_found");
            exit;
        }

        try {
            $pdo->beginTransaction();

            // Update the 'reponses' table
            $stmt1 = $pdo->prepare("UPDATE reponses r JOIN questions q ON r.question_id = q.id SET r.reponse = ? WHERE r.id = ?");
            $stmt1->execute([$reponse_text, $id]);

            // Update the 'questions' table
            $stmt2 = $pdo->prepare("UPDATE questions q JOIN reponses r ON r.question_id = q.id SET q.question = ? WHERE q.id = ?");
            $stmt2->execute([$question_text, $id]);

            $pdo->commit();

            header("Location: admin_dashboard.php?tab=edit&success=update");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            echo "Error: " . $e->getMessage();
        }

    } else {
        echo "Invalid ID provided.";
    }
}
