<?php
 require_once '../includes/auth_check.php'; // redirection vers login

require_once __DIR__ . '/../includes/db.php';
$activeTab = $_GET['tab'] ?? 'list'; // default to 'list'

$questions = $pdo->query("SELECT  r.id, q.question, r.reponse  FROM reponses r JOIN 
        questions q ON r.question_id = q.id  ORDER BY 
        r.id DESC")->fetchAll();
$admins = $pdo->query("SELECT id, username, email FROM admins ORDER BY id DESC")->fetchAll();

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Admin Chatbot Dashboard</title>
    <!-- CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <link rel="stylesheet" href="../assets/adminStyle.css"> 
   
    <script>
        function showTab(tabId) {
            document.querySelectorAll(".tab-content").forEach(el => el.classList.remove("active"));
            document.getElementById(tabId).classList.add("active");
        }
    </script>
</head>
<body>
<div class="container">
    <div class="sidebar">
        <h3>  Admin Chatbot</h3>
        <a href="?tab=list">📋 Liste des Question</a>
        <a href="?tab=add">➕ Ajouter Question</a>
        <a href="?tab=edit">✏️ Modifier</a>
        <a href="?tab=delete"> 🗑 Supprimer</a>
        <a href="../auth/logout.php?from=admin">🚪 Déconnecter</a>
    </div>
    <div class="content">
        <!-- Liste des questions -->
        <div id="list" class="tab-content <?= $activeTab === 'list' ? 'active' : '' ?>">
            <h2>Liste des questions</h2>
            <table>
                <tr><th>ID</th><th>Question</th><th>Réponse</th><th>Actions</th></tr>
                <?php foreach ($questions as $q): ?>
                    <tr>
                        <td><?= $q['id'] ?></td>
                        <td><?= htmlspecialchars($q['question']) ?></td>
                        <td><?= htmlspecialchars($q['reponse']) ?></td>
                        <td class="actions">
                        <a href="#" onclick="editPrefill(<?= $q['id'] ?>, '<?= htmlspecialchars(addslashes($q['question'])) ?>', '<?= htmlspecialchars(addslashes($q['reponse'])) ?>')">✏️</a>
                        <a href="delete_question.php?id=<?= $q['id'] ?>" class="delete" onclick="return confirm('Supprimer ?')">🗑</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>

  <!-- Message-->
  <?php
        function showAlert($id, $message, $color) {
            echo "<div id=\"$id\" style=\"background: $color; color: white; padding: 10px; margin-bottom: 10px;\">$message</div>";
        }

        if (isset($_GET['error']) && $_GET['error'] === 'exists') {
            showAlert('error-msg', '❗ Cette question existe déjà dans la base de données.', '#e74c3c');
        }
        if (isset($_GET['error']) && $_GET['error'] === 'id_not_found') {
    showAlert('error-msg', '❗ L\'ID fourni n\'existe pas dans la base de données.', '#e74c3c');
}
        if (isset($_GET['success'])) {
            switch ($_GET['success']) {
                case '1':
                    showAlert('success-msg', '✅ Question ajoutée avec succès.', '#2ecc71');
                    break;
                case 'update':
                    showAlert('success-msg', '✅ Question modifiée avec succès.', '#3498db');
                    break;
                case 'delete':
                    showAlert('success-msg', '✅ Question supprimée avec succès.', '#f39c12');
                    break;
            }
        }
        ?>
        <script>
            setTimeout(function () {
                const ids = ['error-msg', 'success-msg'];
                ids.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.style.display = 'none';
                });
            }, 2000);
        </script>


        <!-- Ajouter Question -->
        <div id="add" class="tab-content <?= $activeTab === 'add' ? 'active' : '' ?>">
            <h2>Ajouter une question</h2>
            <form method="POST" action="add_question.php">
                <label>Question:</label>
                <input type="text" name="question" required>

                <label>Réponse:</label>
                <textarea name="reponse" required></textarea>

                <button type="submit">Ajouter</button>
            </form>

        </div>

        <!-- Modifier -->
        <div id="edit" class="tab-content <?= $activeTab === 'edit' ? 'active' : '' ?>">
            <h2>Modifier une question</h2>
            <form method="POST" action="edit_question.php">
                <label>ID:</label>
                <input type="text" name="id" id="edit_id" required>
                <label>Question:</label>
                <input type="text" name="question" id="edit_question" required>
                <label>Réponse:</label>
                <textarea name="reponse" id="edit_reponse" required></textarea>
                <button type="submit">Modifier</button>
            </form>
        </div>

        <!-- Supprimer -->
        <div id="delete" class="tab-content <?= $activeTab === 'delete' ? 'active' : '' ?>">
            <h2>Supprimer une question</h2>
            <p>Utilisez la poubelle rouge dans l'onglet "Liste des Question" pour supprimer une question.</p>
        </div>


    </div>
</div>

<script>
function editPrefill(id, question, reponse, type_bac) {
    showTab('edit');
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_question').value = question;
    document.getElementById('edit_reponse').value = reponse;
    document.getElementById('edit_type_bac').value = type_bac;
}
</script>


<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

</body>
</html>
