<?php
// require_once '../includes/auth_check.php'; // redirection vers login
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question = $_POST['question'];
$reponse = $_POST['reponse'];

// Vérifier si la question existe déjà
$stmt = $pdo->prepare("SELECT id FROM questions WHERE question = ?");
$stmt->execute([$question]);
$existingQuestion = $stmt->fetch();

if ($existingQuestion) {
    $question_id = $existingQuestion['id'];
} else {
    // Insérer nouvelle question
    $stmt = $pdo->prepare("INSERT INTO questions (question) VALUES (?)");
    $stmt->execute([$question]);
    $question_id = $pdo->lastInsertId();
}

// Vérifier si une réponse pour ce bac existe déjà pour cette question
$check = $pdo->prepare("SELECT * FROM reponses WHERE question_id = ? ");
$check->execute([$question_id]);
if ($check->rowCount() > 0) {
    // Question already exists
    header("Location: admin_dashboard.php?tab=add&error=exists");
    exit;
}

// Insérer la réponse
$stmt = $pdo->prepare("INSERT INTO reponses (question_id, reponse) VALUES (?, ?)");
$stmt->execute([$question_id, $reponse]);

header("Location: admin_dashboard.php?tab=add&success=1");
exit;

}
?>
