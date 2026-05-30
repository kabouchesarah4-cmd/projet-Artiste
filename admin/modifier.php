<?php
session_start();

// sécurité — non connecté = retour login
if (!isset($_SESSION['admin_connecte']) || $_SESSION['admin_connecte'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

// on récupère l'id dans l'URL : modifier.php?id=3
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

// si pas d'id valide → retour liste
if ($id === 0) {
    header('Location: produits.php');
    exit;
}

// on récupère l'oeuvre existante pour pré-remplir le formulaire
$stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
$stmt->execute([$id]);
$oeuvre = $stmt->fetch();

// si l'oeuvre n'existe pas en BDD → retour liste
if (!$oeuvre) {
    header('Location: produits.php');
    exit;
}

// toutes les catégories pour le select
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();

$message = '';
$erreur  = '';

// traitement du formulaire quand on soumet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre        = trim($_POST['titre'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $prix         = trim($_POST['prix'] ?? '');
    $stock        = (int)($_POST['stock'] ?? 1);
    $id_categorie = (int)($_POST['id_categorie'] ?? 0);

    // nom de l'image : on garde l'ancienne par défaut
    $nom_image = $oeuvre['image'];

    // si une nouvelle image est uploadée, on la remplace
    if (!empty($_FILES['image']['name'])) {
        $types_autorises = ['image/jpeg', 'image/png', 'image/webp'];
        $type_fichier    = mime_content_type($_FILES['image']['tmp_name']);

        if (!in_array($type_fichier, $types_autorises)) {
            $erreur = "Format non supporté. Utilisez JPG, PNG ou WebP.";
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $erreur = "Image trop lourde (max 5 Mo).";
        } else {
            $nouveau_nom = time() . '_' . basename($_FILES['image']['name']);
            $chemin_dest = '../images/oeuvres/' . $nouveau_nom;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $chemin_dest)) {
                // supprime l'ancienne image si elle existe
                if (!empty($oeuvre['image']) && file_exists('../images/oeuvres/' . $oeuvre['image'])) {
                    unlink('../images/oeuvres/' . $oeuvre['image']);
                }
                $nom_image = $nouveau_nom;
            } else {
                $erreur = "Erreur lors de l'upload. Vérifiez les permissions du dossier.";
            }
        }
    }

    // si pas d'erreur image → on met à jour en BDD
    if (empty($erreur)) {
        $stmt = $pdo->prepare("
            UPDATE produits
            SET titre = ?, description = ?, prix = ?, stock = ?, image = ?, id_categorie = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $titre,
            $description,
            $prix ?: null,
            $stock,
            $nom_image,
            $id_categorie ?: null,
            $id
        ]);

        $message = "Œuvre mise à jour avec succès !";

        // on recharge l'oeuvre pour que le formulaire affiche les nouvelles valeurs
        $stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
        $stmt->execute([$id]);
        $oeuvre = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier — <?php echo htmlspecialchars($oeuvre['titre']); ?></title>
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
        <a href="../index.html" class="admin-nav-lien" style="margin-top: 1rem;">← Site public</a>
        <a href="logout.php" class="admin-nav-lien danger" style="margin-top: auto;">Déconnexion</a>
    </aside>

    <!-- contenu -->
    <main class="admin-main">

        <h1 class="admin-titre">
            Modifier — <?php echo htmlspecialchars($oeuvre['titre']); ?>
        </h1>

        <div class="form-container">

            <?php if (!empty($message)): ?>
                <div class="succes"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if (!empty($erreur)): ?>
                <div class="erreur-admin"><?php echo htmlspecialchars($erreur); ?></div>
            <?php endif; ?>

            <form method="POST"
                  enctype="multipart/form-data"
                  action="modifier.php?id=<?php echo $id; ?>"
                  class="formulaire">

                <div class="champ-groupe">
                    <label for="titre">Titre de l'œuvre *</label>
                    <input type="text" name="titre" id="titre" required
                           value="<?php echo htmlspecialchars($oeuvre['titre']); ?>">
                </div>

                <div class="champ-groupe">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" rows="4"><?php
                        echo htmlspecialchars($oeuvre['description'] ?? '');
                        ?></textarea>
                </div>

                <div class="champ-groupe">
                    <label for="id_categorie">Catégorie</label>
                    <select name="id_categorie" id="id_categorie">
                        <option value="">— Choisir une catégorie —</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>"
                                <?php echo $oeuvre['id_categorie'] == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="champ-groupe">
                    <label for="prix">Prix (€)</label>
                    <input type="number" name="prix" id="prix"
                           min="0" step="0.01"
                           value="<?php echo htmlspecialchars($oeuvre['prix'] ?? ''); ?>">
                </div>

                <div class="champ-groupe">
                    <label for="stock">Disponibilité</label>
                    <select name="stock" id="stock">
                        <option value="1" <?php echo $oeuvre['stock'] > 0 ? 'selected' : ''; ?>>
                            Disponible
                        </option>
                        <option value="0" <?php echo $oeuvre['stock'] == 0 ? 'selected' : ''; ?>>
                            Vendu / Non disponible
                        </option>
                    </select>
                </div>

                <div class="champ-groupe">
                    <label>Image actuelle</label>
                    <div class="image-actuelle">
                        <?php if (!empty($oeuvre['image']) && file_exists('../images/oeuvres/' . $oeuvre['image'])): ?>
                            <img src="../images/oeuvres/<?php echo htmlspecialchars($oeuvre['image']); ?>"
                                 alt="Image actuelle">
                            <p><?php echo htmlspecialchars($oeuvre['image']); ?></p>
                        <?php else: ?>
                            <p style="color: var(--texte-discret);">Aucune image pour le moment</p>
                        <?php endif; ?>
                    </div>

                    <label for="image">Nouvelle image (laisser vide pour garder l'actuelle)</label>
                    <input type="file" name="image" id="image"
                           accept="image/jpeg,image/png,image/webp">
                </div>

                <button type="submit" class="bouton-principal"
                        style="width: 100%; margin-top: 1.5rem; justify-content: center;">
                    Enregistrer les modifications →
                </button>

            </form>

            <p style="margin-top: 2rem;">
                <a href="produits.php" style="color: var(--texte-discret); font-size: 0.8rem;">
                    ← Retour à la liste
                </a>
            </p>

        </div>

    </main>

</div>

</body>
</html>