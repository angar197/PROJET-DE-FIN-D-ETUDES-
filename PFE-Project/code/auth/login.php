 <!-- auth/login.php  -->
<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification de l'existence des clés avant d'y accéder
    $identifier = isset($_POST['identifier']) ? trim($_POST['identifier']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Si un champ est vide, on renvoie une erreur
    if (empty($identifier) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        
       // 1. Admin (base de données)
       $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = :username");
       $stmt->execute(['username' => $identifier]);
       $admin = $stmt->fetch();

       if ($admin && password_verify($password, $admin['password'])) {
           $_SESSION['admin_logged_in'] = true;
           $_SESSION['admin_id'] = $admin['id'];
           $_SESSION['username'] = $admin['username'];
           header("Location: ../admin/admin_dashboard.php");
           exit;
       }


       // 3. Utilisateur normal
       $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = :email");
       $stmt->execute(['email' => $identifier]);
       $user = $stmt->fetch();

       if ($user && password_verify($password, $user['mot_de_passe'])) {
           $_SESSION['user_logged_in'] = true;
           $_SESSION['user_id'] = $user['id'];
           $_SESSION['prenom'] = $user['prenom'];
           header("Location: ../chat/dashboard.php");
           exit;
        } else {
            $error = "Identifiants incorrects.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="../assets/auth.css">
</head>
<body>
    <h2>Connexion</h2>
    <form method="POST">
        <input name="identifier" type="text" placeholder="Nom d'utilisateur ou email" required>
        <input name="password" type="password" placeholder="Mot de passe" required>
        <button type="submit">Se connecter</button>
        <p>Nouvelle utilisateur ? <a href="register.php" class="register">Crée Compte</a></p>
    </form>
    <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
</body>
</html>
