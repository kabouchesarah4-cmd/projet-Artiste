<?php
session_start();

if (!isset($_SESSION['admin_connecte']) || $_SESSION['admin_connecte'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

// marquer un message comme lu si on clique dessus
if (isset($_GET['lire']) && is_numeric($_GET['lire'])) {
    $stmt = $pdo->prepare("UPDATE messages_contact SET lu = 1 WHERE id = ?");
    $stmt->execute([(int)$_GET['lire']]);
    header('Location: messages.php');
    exit;
}

// supprimer un message
if (isset($_GET['supprimer']) && is_numeric($_GET['supprimer'])) {
    $stmt = $pdo->prepare("DELETE FROM messages_contact WHERE id = ?");
    $stmt->execute([(int)$_GET['supprimer']]);
    header('Location: messages.php');
    exit;
}

// tous les messages du plus récent au plus ancien
$messages = $pdo->query("SELECT * FROM messages_contact ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages — Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Cormorant+Garamond:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">

</head>
<body>

<div class="admin-layout">

    <!-- sidebar -->
    <aside class="admin-sidebar">
        <div class="admin-logo">KAZ <span>ADMIN</span></div>
        <a href="dashboard.php" class="admin-nav-lien">Tableau de bord</a>
        <a href="produits.php" class="admin-nav-lien">Œuvres</a>
        <a href="ajouter.php" class="admin-nav-lien">+ Ajouter une œuvre</a>
        <a href="messages.php" class="admin-nav-lien actif">Messages</a>
        <a href="../index.html" class="admin-nav-lien" style="margin-top: 1rem;">← Site public</a>
        <a href="logout.php" class="admin-nav-lien danger" style="margin-top: auto;">Déconnexion</a>
    </aside>

    <!-- contenu -->
    <main class="admin-main">

        <h1 class="admin-titre">
            Messages reçus
            <?php
            $non_lus = array_filter($messages, fn($m) => !$m['lu']);
            if (count($non_lus) > 0):
                ?>
                <span class="badge-non-lu"><?php echo count($non_lus); ?> non lus</span>
            <?php endif; ?>
        </h1>

        <?php if (empty($messages)): ?>
            <p style="color: var(--texte-discret);">Aucun message pour le moment.</p>
        <?php else: ?>
            <?php foreach ($messages as $msg): ?>
                <div class="message-carte <?php echo !$msg['lu'] ? 'non-lu' : ''; ?>">

                    <div class="message-header">
                        <div>
                            <p class="message-expediteur"><?php echo htmlspecialchars($msg['nom']); ?></p>
                            <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>"
                               class="message-email">
                                <?php echo htmlspecialchars($msg['email']); ?>
                            </a>
                        </div>
                        <span class="message-date">
                            <?php echo date('d/m/Y à H:i', strtotime($msg['date_envoi'])); ?>
                        </span>
                    </div>

                    <?php if (!empty($msg['sujet'])): ?>
                        <p class="message-sujet"><?php echo htmlspecialchars($msg['sujet']); ?></p>
                    <?php endif; ?>

                    <p class="message-texte">
                        <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                    </p>

                    <div class="message-actions">
                        <?php if (!$msg['lu']): ?>
                            <a href="?lire=<?php echo $msg['id']; ?>" class="btn-lire">
                                ✓ Marquer comme lu
                            </a>
                        <?php else: ?>
                            <span style="font-size: 0.7rem; color: var(--texte-discret);">✓ Lu</span>
                        <?php endif; ?>
                        <a href="?supprimer=<?php echo $msg['id']; ?>"
                           class="btn-supprimer-msg"
                           onclick="return confirm('Supprimer ce message ?')">
                            Supprimer
                        </a>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </main>

</div>

</body>
</html>