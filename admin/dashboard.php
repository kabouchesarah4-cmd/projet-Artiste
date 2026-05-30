<?php
session_start();

// sécurité — non connecté = retour login
if (!isset($_SESSION['admin_connecte']) || $_SESSION['admin_connecte'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

// stats rapides pour le tableau de bord
$nb_oeuvres   = $pdo->query("SELECT COUNT(*) FROM produits")->fetchColumn();
$nb_messages  = $pdo->query("SELECT COUNT(*) FROM messages_contact")->fetchColumn();
$nb_non_lus   = $pdo->query("SELECT COUNT(*) FROM messages_contact WHERE lu = 0")->fetchColumn();

// 5 derniers messages
$derniers_msgs = $pdo->query("SELECT * FROM messages_contact ORDER BY id DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord — Admin Kaz Ahmed Koné</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Cormorant+Garamond:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">

</head>
<body>

<div class="admin-layout">

    <!-- sidebar navigation -->
    <aside class="admin-sidebar">
        <div class="admin-logo">KAZ <span>ADMIN</span></div>
        <a href="dashboard.php" class="admin-nav-lien actif">Tableau de bord</a>
        <a href="produits.php" class="admin-nav-lien">Œuvres</a>
        <a href="ajouter.php" class="admin-nav-lien">+ Ajouter une œuvre</a>
        <a href="messages.php" class="admin-nav-lien">
            Messages
            <?php if ($nb_non_lus > 0): ?>
                <span style="background: var(--accent); color: #000; font-size: 0.65rem; padding: 2px 6px; margin-left: 0.5rem;">
                    <?php echo $nb_non_lus; ?>
                </span>
            <?php endif; ?>
        </a>
        <a href="../index.html" class="admin-nav-lien" style="margin-top: 1rem;">← Site public</a>
        <a href="logout.php" class="admin-nav-lien danger" style="margin-top: auto;">Déconnexion</a>
    </aside>

    <!-- contenu -->
    <main class="admin-main">

        <h1 class="admin-titre">Tableau de bord</h1>
        <p class="admin-sous-titre">Bienvenue dans l'espace administration</p>

        <!-- stats -->
        <div class="stats-grille">
            <div class="stat-carte">
                <span class="stat-label">Œuvres</span>
                <span class="stat-valeur"><?php echo $nb_oeuvres; ?></span>
            </div>
            <div class="stat-carte">
                <span class="stat-label">Messages reçus</span>
                <span class="stat-valeur"><?php echo $nb_messages; ?></span>
            </div>
            <div class="stat-carte">
                <span class="stat-label">Non lus</span>
                <span class="stat-valeur" style="color: <?php echo $nb_non_lus > 0 ? '#ff6b6b' : 'var(--accent)'; ?>">
                    <?php echo $nb_non_lus; ?>
                </span>
            </div>
        </div>

        <!-- derniers messages -->
        <p class="admin-section-titre">Derniers messages reçus</p>

        <?php if (empty($derniers_msgs)): ?>
            <p style="color: var(--texte-discret); font-size: 0.9rem;">Aucun message pour le moment.</p>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Sujet</th>
                    <th>Message</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($derniers_msgs as $msg): ?>
                    <tr>
                        <td>
                            <?php if (!$msg['lu']): ?>
                                <span class="badge-non-lu-point"></span>                            <?php endif; ?>
                            <?php echo htmlspecialchars($msg['nom']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($msg['email']); ?></td>
                        <td><?php echo htmlspecialchars($msg['sujet'] ?? '—'); ?></td>
                        <td style="max-width: 300px; color: var(--texte-discret);">
                            <?php echo htmlspecialchars(mb_substr($msg['message'], 0, 80)) . '...'; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <a href="messages.php" class="voir-tout">Voir tous les messages →</a>
        <?php endif; ?>

    </main>

</div>

</body>
</html>