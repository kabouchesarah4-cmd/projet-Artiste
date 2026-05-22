<?php
// DÉMARRAGE DE LA SESSION
session_start();

// SÉCURITÉ : Si l'utilisateur n'est pas connecté, on le dégage vers la page de login.
if (!isset($_SESSION['admin_connecte']) || $_SESSION['admin_connecte'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'config/database.php';

// RÉCUPÉRATION DES MESSAGES
// On les affiche du plus récent au plus ancien (ORDER BY date_envoi DESC)
$stmt = $pdo->query("SELECT * FROM messages_contact ORDER BY id DESC");
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration — Kaz Ahmed Koné</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { background: #f4f4f4; padding: 2rem; color: #333; }
        .dashboard-container { max-width: 1000px; margin: 0 auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 2rem; }
        th, td { padding: 1rem; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #eee; }
        .btn-logout { background: #8b0000; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>

<div class="dashboard-container">
    <header style="display: flex; justify-content: space-between; align-items: center;">
        <h1>Tableau de bord</h1>
        <a href="logout.php" class="btn-logout">Déconnexion</a>
    </header>

    <table>
        <thead>
        <tr>
            <th>Nom</th>
            <th>Email</th>
            <th>Sujet</th>
            <th>Message</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($messages)): ?>
            <tr><td colspan="4">Aucun message pour le moment.</td></tr>
        <?php else: ?>
            <?php foreach ($messages as $msg): ?>
                <tr>
                    <td><?php echo htmlspecialchars($msg['nom']); ?></td>
                    <td><?php echo htmlspecialchars($msg['email']); ?></td>
                    <td><?php echo htmlspecialchars($msg['sujet']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($msg['message'])); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>