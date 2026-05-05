<?php
require '../includes/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe)
                           VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['nom'], $_POST['prenom'], $_POST['email'], password_hash($_POST['password'], PASSWORD_BCRYPT),
                   ]);
    header('Location: login.php'); exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8">
<title>Inscription</title>
<link rel="stylesheet" href="../assets/auth.css">
</head>
<body>
<h2>Register</h2>
<form  method="POST">
    <input name="nom" placeholder="Nom" required>
    <input name="prenom" placeholder="Prénom" required>
    <input name="email" type="email" placeholder="Email" required>
    <input name="password" type="password" placeholder="Mot de passe" required>
    <button type="submit">S'inscrire</button>
</form>
</body></html>