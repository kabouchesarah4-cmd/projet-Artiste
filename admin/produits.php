<?php
session_start();

// sécurité — non connecté = retour login
if (!isset($_SESSION['admin_connecte']) || $_SESSION['admin_connecte'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

// récupère toutes les oeuvres avec le nom de leur catégorie
$stmt = $pdo->query("
    SELECT p.*, c.nom AS categorie_nom
    FROM produits p
    LEFT JOIN categories c ON p.id_categorie = c.id
    ORDER BY p.id DESC
");
$oeuvres = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des œuvres — Admin</title>
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
        <a href="produits.php" class="admin-nav-lien actif">Œuvres</a>
        <a href="ajouter.php" class="admin-nav-lien">+ Ajouter une œuvre</a>
        <a href="messages.php" class="admin-nav-lien">Messages</a>
        <a href="profil.php" class="admin-nav-lien">Mon Profil</a>
        <a href="../index.html" class="admin-nav-lien" style="margin-top: 1rem;">← Site public</a>
        <a href="logout.php" class="admin-nav-lien danger" style="margin-top: auto;">Déconnexion</a>
    </aside>

    <!-- contenu -->
    <main class="admin-main">

        <div class="admin-header">
            <h1 class="admin-titre">Gestion des œuvres</h1>
            <a href="ajouter.php" class="bouton-principal">+ Ajouter une œuvre</a>
        </div>

        <?php if (empty($oeuvres)): ?>
            <p style="color: var(--texte-discret);">Aucune œuvre pour le moment.</p>
        <?php else: ?>
        <div class="admin-table-wrapper">
            <table class="admin-table">
                <thead>
                <tr>
                    <th>Image</th>
                    <th>Titre</th>
                    <th>Catégorie</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($oeuvres as $oeuvre): ?>
                    <tr>
                        <td>
                            <?php if (!empty($oeuvre['image']) && file_exists('../images/oeuvres/' . $oeuvre['image'])): ?>
                                <img src="../images/oeuvres/<?php echo htmlspecialchars($oeuvre['image']); ?>"
                                     class="thumb" alt="<?php echo htmlspecialchars($oeuvre['titre']); ?>">
                            <?php else: ?>
                                <div class="thumb-placeholder"></div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($oeuvre['titre']); ?></td>
                        <td style="color: var(--texte-discret);">
                            <?php echo htmlspecialchars($oeuvre['categorie_nom'] ?? '—'); ?>
                        </td>
                        <td>
                            <?php echo !empty($oeuvre['prix']) ? number_format($oeuvre['prix'], 0, ',', ' ') . ' €' : '—'; ?>
                        </td>
                        <td>
                            <?php if ($oeuvre['stock'] > 0): ?>
                                <span class="badge-dispo">● Disponible</span>
                            <?php else: ?>
                                <span class="badge-vendu-admin">● Vendu</span>                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="modifier.php?id=<?php echo $oeuvre['id']; ?>" class="btn-modifier">Modifier</a>
                            <a href="supprimer.php?id=<?php echo $oeuvre['id']; ?>"
                               class="btn-supprimer"
                               onclick="return confirm('Supprimer définitivement cette œuvre ?')">
                                Supprimer
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </main>

</div>

</body>
</html>