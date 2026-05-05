<!-- chat/dashboard.php -->
<?php
 require_once '../includes/auth_check.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php"); // Rediriger vers page de connexion
    exit;
}
// Fetch user data (assuming you're storing user_id in session and have DB connection)
require_once '../includes/db.php'; // Your DB connection
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT prenom , nom ,email FROM utilisateurs WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();


?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Chatbot</title>
  <link rel="stylesheet" href="../assets/dashboardStyle.css">
</head>
<body>
  <!-- Navbar -->
<div class="navbar">
  <div class="logo">ChatBot</div>
  <div class="user-icon" id="userIcon">
    <img src="../images/user.png" alt="User Icon" />
        <div class="user-panel" id="userPanel">
      <div class="user-info">
        <img src="../images/user.png" alt="User Avatar" class="panel-avatar" />
        <p class="user-name"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></p>
        <p class="user-name"><?= htmlspecialchars($user['email']) ?></p>
      </div>
      <hr>
      <a href="../auth/logout.php" class="logout-btn">Se déconnecter</a>
    </div>
  </div>
</div>

<div class="container">
  <div class="sidebar">
    <h2>Conversations</h2>
    <button id="new-convo-btn">+ Nouvelle conversation</button>
    <ul id="history-list"></ul>
  </div>

  <div class="chat-area">
    <div id="chatBox" class="chat-box"></div>
    <form id="chatForm" class="chat-form">
    <input type="text" id="messageInput" placeholder="Pose ta question..." />
    <input type="hidden" id="activeConversationId" value="">

    <button type="submit">Envoyer</button>
    </form>
  </div>
</div>
<script src="../assets/dashboard.js"></script>
</body>
</html>
