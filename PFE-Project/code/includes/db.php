<?php
$host = 'localhost';
$dbname = 'chatbot_bachelier'; // mets ici le nom réel de ta base
$username = 'root';
$password = ''; // mot de passe vide

try {
    $pdo = new PDO("mysql:host=$host;port=3306;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

