<?php
session_start();

// sécurité
if (!isset($_SESSION['admin_connecte']) || $_SESSION['admin_connecte'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

// récupère les catégories pour le select
$categories = $pdo->query("SELECT * FROM categories ORDER BY nom")->fetchAll();

$message = '';
$erreur  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre        = trim($_POST['titre'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $prix         = trim($_POST['prix'] ?? '');
    $stock        = (int)($_POST['stock'] ?? 1);
    $id_categorie = (int)($_POST['id_categorie'] ?? 0);
    $lien_stripe = trim($_POST['lien_stripe'] ?? '');
    // vérification image
    if (empty($_FILES['image']['name'])) {
        $erreur = "Veuillez sélectionner une image.";
    } else {
        // on vérifie que c'est bien une image (sécurité)
        $types_autorises = ['image/jpeg', 'image/png', 'image/webp'];
        $type_fichier    = mime_content_type($_FILES['image']['tmp_name']);

        if (!in_array($type_fichier, $types_autorises)) {
            $erreur = "Format non supporté. Utilisez JPG, PNG ou WebP.";
        } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $erreur = "Image trop lourde (max 5 Mo).";
        } else {
            // renommage unique : timestamp + nom original
            $nom_image   = time() . '_' . basename($_FILES['image']['name']);
            $dossier     = '../images/oeuvres/';
            $chemin_dest = $dossier . $nom_image;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $chemin_dest)) {
                // insertion en BDD dans la table "produits"
                $stmt = $pdo->prepare("
                    INSERT INTO produits (titre, description, prix, stock, image, id_categorie, lien_stripe)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                        $titre,
                        $description,
                        $prix ?: null,
                        $stock,
                        $nom_image,
                        $id_categorie ?: null,
                        $lien_stripe ?: null
                ]);
                $message = "Œuvre ajoutée avec succès !";
            } else {
                $erreur = "Erreur lors de l'upload. Vérifiez les permissions du dossier images/oeuvres/.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une œuvre — Admin</title>
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
        <a href="ajouter.php" class="admin-nav-lien actif">+ Ajouter une œuvre</a>
        <a href="messages.php" class="admin-nav-lien">Messages</a>
        <a href="../index.html" class="admin-nav-lien" style="margin-top: 1rem;">← Site public</a>
        <a href="logout.php" class="admin-nav-lien danger" style="margin-top: auto;">Déconnexion</a>
    </aside>

    <!-- contenu -->
    <main class="admin-main">

        <h1 class="admin-titre">Ajouter une œuvre</h1>

        <div class="form-container">

            <?php if (!empty($message)): ?>
                <div class="succes"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <?php if (!empty($erreur)): ?>
                <div class="erreur-admin"><?php echo htmlspecialchars($erreur); ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="formulaire">

                <div class="champ-groupe">
                    <label for="titre">Titre de l'œuvre *</label>
                    <input type="text" name="titre" id="titre" placeholder="Ex : Zaouli Funk I" required>
                </div>

                <div class="champ-groupe">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" rows="4"
                              placeholder="Technique, inspiration, dimensions..."></textarea>
                </div>

                <div class="champ-groupe">
                    <label for="id_categorie">Catégorie</label>
                    <select name="id_categorie" id="id_categorie">
                        <option value="">— Choisir une catégorie —</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>">
                                <?php echo htmlspecialchars($cat['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="champ-groupe">
                    <label for="prix">Prix (€)</label>
                    <input type="number" name="prix" id="prix"
                           placeholder="Ex : 850" min="0" step="0.01">
                </div>

                <div class="champ-groupe">
                    <label for="stock">Disponibilité</label>
                    <select name="stock" id="stock">
                        <option value="1">Disponible</option>
                        <option value="0">Vendu / Non disponible</option>
                    </select>
                </div>

                <div class="champ-groupe">
                    <label for="image">Image * (JPG, PNG, WebP — max 5 Mo)</label>
                    <input type="file" name="image" id="image"
                           accept="image/jpeg,image/png,image/webp" required>
                </div>

                <div class="champ-groupe">
                    <label for="lien_stripe">Lien Stripe (optionnel)</label>
                    <input type="url" name="lien_stripe" id="lien_stripe"
                           placeholder="https://buy.stripe.com/...">
                    <small style="color: var(--texte-discret); font-size: 0.75rem;">
                        Laisser vide si l'acquisition se fait par contact
                    </small>
                </div>

                <button type="submit" class="bouton-principal"
                        style="width: 100%; margin-top: 1.5rem; justify-content: center;">
                    Enregistrer l'œuvre →
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