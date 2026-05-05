<!-- create_conversation.php -->
<?php
require_once '../includes/auth_check.php'; // redirection vers login
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Utilisateur non authentifié']);
    exit;
}

require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("INSERT INTO conversations (user_id, title, created_at) VALUES (?, ?, NOW())");
    $stmt->execute([$user_id, 'Nouvelle conversation']);
    
    $conversationId = $pdo->lastInsertId();
    
    echo json_encode(['success' => true, 'conversation_id' => $conversationId]);
} catch (Exception $e) {
    error_log("Erreur création conversation : " . $e->getMessage());
    echo json_encode(['error' => 'Erreur lors de la création de la conversation']);
}
