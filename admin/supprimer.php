<?php
session_start();

// sécurité — non connecté = retour login
if (!isset($_SESSION['admin_connecte']) || $_SESSION['admin_connecte'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

// on récupère l'id dans l'URL
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

// pas d'id valide → retour liste
if ($id === 0) {
    header('Location: produits.php');
    exit;
}

// on récupère l'oeuvre pour afficher son titre dans la confirmation
$stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
$stmt->execute([$id]);
$oeuvre = $stmt->fetch();

// oeuvre introuvable → retour liste
if (!$oeuvre) {
    header('Location: produits.php');
    exit;
}

// si l'admin confirme la suppression via le formulaire POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer'])) {

    // supprime l'image du serveur si elle existe
    if (!empty($oeuvre['image'])) {
        $chemin_image = '../images/oeuvres/' . $oeuvre['image'];
        if (file_exists($chemin_image)) {
            unlink($chemin_image); // unlink = supprimer un fichier en PHP
        }
    }

    // supprime l'oeuvre de la BDD
    $stmt = $pdo->prepare("DELETE FROM produits WHERE id = ?");
    $stmt->execute([$id]);

    // retour à la liste avec message de confirmation
    header('Location: produits.php?supprime=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer une œuvre — Admin</title>
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

    <!-- page de confirmation centrée -->
    <main class="admin-main">

        <div class="confirm-carte">

            <div class="confirm-icone">⚠️</div>

            <!-- aperçu de l'oeuvre à supprimer -->
            <?php if (!empty($oeuvre['image']) && file_exists('../images/oeuvres/' . $oeuvre['image'])): ?>
                <img src="../images/oeuvres/<?php echo htmlspecialchars($oeuvre['image']); ?>"
                     class="confirm-image"
                     alt="<?php echo htmlspecialchars($oeuvre['titre']); ?>">
            <?php else: ?>
                <div class="confirm-image-placeholder"></div>
            <?php endif; ?>

            <h1 class="confirm-titre">Supprimer cette œuvre ?</h1>

            <p class="confirm-oeuvre">
                « <?php echo htmlspecialchars($oeuvre['titre']); ?> »
            </p>

            <p class="confirm-avertissement">
                Cette action est irréversible.<br>
                L'œuvre sera supprimée de la galerie et son image sera effacée du serveur.
            </p>

            <!-- formulaire POST pour confirmer — GET serait trop facile à déclencher accidentellement -->
            <div class="confirm-boutons">

                <a href="produits.php" class="btn-annuler">
                    ← Annuler
                </a>

                <form method="POST" action="supprimer.php?id=<?php echo $id; ?>">
                    <button type="submit" name="confirmer" value="1"
                            class="btn-confirmer-suppression">
                        Supprimer définitivement
                    </button>
                </form>

            </div>

        </div>

    </main>

</div>

</body>
</html>