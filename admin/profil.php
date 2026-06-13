<?php
session_start();

// Sécurité : non connecté = retour login
if (!isset($_SESSION['admin_connecte']) || $_SESSION['admin_connecte'] !== true) {
    header('Location: login.php');
    exit;
}

require_once '../config/database.php';

$message = '';
$erreur  = '';


// TRAITEMENT 1 : MISE À JOUR DES INFOS ET IMAGES DU SITE

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_infos'])) {
    $citation = trim($_POST['citation']);
    $presentation = trim($_POST['presentation_accueil']);
    $biographie = trim($_POST['biographie_complete']);

    $img_accueil = $_POST['image_accueil_actuelle'];
    $img_apropos = $_POST['image_apropos_actuelle'];

    function gererUpload($inputName, $ancienneImage) {
        if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
            $nomFichier = time() . '_' . basename($_FILES[$inputName]['name']);
            $cheminDestination = '../images/artiste/' . $nomFichier;

            if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $cheminDestination)) {
                if (!empty($ancienneImage) && file_exists('../images/artiste/' . $ancienneImage)) {
                    unlink('../images/artiste/' . $ancienneImage);
                }
                return $nomFichier;
            }
        }
        return $ancienneImage;
    }

    $img_accueil = gererUpload('image_accueil', $img_accueil);
    $img_apropos = gererUpload('image_apropos', $img_apropos);

    $stmt = $pdo->prepare("UPDATE profil_artiste SET citation = ?, presentation_accueil = ?, biographie_complete = ?, image_accueil = ?, image_apropos = ? WHERE id = 1");
    $stmt->execute([$citation, $presentation, $biographie, $img_accueil, $img_apropos]);

    $message = "Le contenu du site a été mis à jour avec succès !";
}


// TRAITEMENT 2 : CHANGEMENT DE MOT DE PASSE

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_password'])) {
    $ancien_mdp  = $_POST['ancien_mdp'] ?? '';
    $nouveau_mdp = $_POST['nouveau_mdp'] ?? '';
    $confirm_mdp = $_POST['confirm_mdp'] ?? '';

    $admin_id = $_SESSION['admin_id'] ?? 1;

    if (empty($ancien_mdp) || empty($nouveau_mdp) || empty($confirm_mdp)) {
        $erreur = "Veuillez remplir tous les champs de mot de passe.";
    } elseif (strlen($nouveau_mdp) < 8) {
        $erreur = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
    } elseif ($nouveau_mdp !== $confirm_mdp) {
        $erreur = "Les nouveaux mots de passe ne correspondent pas.";
    } else {
        $stmt = $pdo->prepare("SELECT mot_de_passe FROM admin WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($ancien_mdp, $admin['mot_de_passe'])) {
            $nouveau_hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE admin SET mot_de_passe = ? WHERE id = ?");
            $update->execute([$nouveau_hash, $admin_id]);
            $message = "Mot de passe modifié avec succès !";
        } else {
            $erreur = "L'ancien mot de passe est incorrect.";
        }
    }
}


// RÉCUPÉRATION DES DONNÉES DU PROFIL (Pour le formulaire 1)

$stmt_profil = $pdo->query("SELECT * FROM profil_artiste WHERE id = 1");
$profil = $stmt_profil->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil — Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Cormorant+Garamond:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

<div class="admin-layout">

    <aside class="admin-sidebar">
        <div class="admin-logo">KAZ <span>ADMIN</span></div>
        <a href="dashboard.php" class="admin-nav-lien">Tableau de bord</a>
        <a href="produits.php" class="admin-nav-lien">Œuvres</a>
        <a href="ajouter.php" class="admin-nav-lien">+ Ajouter une œuvre</a>
        <a href="messages.php" class="admin-nav-lien">Messages</a>
        <a href="profil.php" class="admin-nav-lien actif">Mon Profil</a>
        <a href="../index.php" class="admin-nav-lien" style="margin-top: 1rem;">← Site public</a>
        <a href="logout.php" class="admin-nav-lien danger" style="margin-top: auto;">Déconnexion</a>
    </aside>

    <main class="admin-main">
        <h1 class="admin-titre">Mon Profil</h1>
        <p class="admin-sous-titre">Gérer les informations publiques et la sécurité du compte</p>

        <?php if (!empty($message)): ?>
            <div class="succes"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if (!empty($erreur)): ?>
            <div class="erreur-admin"><?php echo htmlspecialchars($erreur); ?></div>
        <?php endif; ?>

        <div class="admin-container" style="margin-bottom: 3rem;">
            <h2 class="admin-section-titre" style="margin-bottom: 2rem;">Contenu public (Accueil & À propos)</h2>

            <form action="profil.php" method="POST" enctype="multipart/form-data">

                <input type="hidden" name="image_accueil_actuelle" value="<?= htmlspecialchars($profil['image_accueil']) ?>">
                <input type="hidden" name="image_apropos_actuelle" value="<?= htmlspecialchars($profil['image_apropos']) ?>">

                <div class="champ-groupe">
                    <label>Citation courte (Accueil)</label>
                    <textarea name="citation" rows="3"><?= htmlspecialchars($profil['citation']) ?></textarea>
                </div>

                <div class="champ-groupe">
                    <label>Présentation courte (Accueil)</label>
                    <textarea name="presentation_accueil" rows="5"><?= htmlspecialchars($profil['presentation_accueil']) ?></textarea>
                </div>

                <div class="champ-groupe">
                    <label>Biographie complète (Page À propos)</label>
                    <textarea name="biographie_complete" rows="10"><?= htmlspecialchars($profil['biographie_complete']) ?></textarea>
                </div>

                <p style="font-size: 0.75rem; color: #ff9090; margin-bottom: 0.5rem;">
                    Pour ne pas ralentir le chargement de votre site public, veuillez utiliser des images compressées (moins de 2 Mo si possible).
                </p>

                <div class="champ-groupe">
                    <label>Image Portrait (Accueil)</label>
                    <p style="font-size: 0.8rem; color: #666; margin-bottom: 0.5rem;">Actuelle : <?= htmlspecialchars($profil['image_accueil']) ?></p>
                    <input type="file" name="image_accueil" accept="image/jpeg, image/png, image/webp">
                </div>

                <div class="champ-groupe">
                    <label>Image Biographie (Page À propos)</label>
                    <p style="font-size: 0.8rem; color: #666; margin-bottom: 0.5rem;">Actuelle : <?= htmlspecialchars($profil['image_apropos']) ?></p>
                    <input type="file" name="image_apropos" accept="image/jpeg, image/png, image/webp">
                </div>

                <button type="submit" name="submit_infos" class="btn-submit">Enregistrer le contenu</button>
            </form>
        </div>

        <div class="admin-container">
            <h2 class="admin-section-titre" style="margin-bottom: 2rem;">Sécurité du compte</h2>

            <form method="POST" action="profil.php" class="formulaire">
                <div class="champ-groupe">
                    <label for="ancien_mdp">Ancien mot de passe</label>
                    <input type="password" name="ancien_mdp" id="ancien_mdp" autocomplete="off" required style="padding: 0.8rem; border: 1px solid rgba(255, 255, 255, 0.1); background: var(--fond-carte); color: var(--texte);">
                </div>

                <div class="champ-groupe">
                    <label for="nouveau_mdp">Nouveau mot de passe</label>
                    <input type="password" name="nouveau_mdp" id="nouveau_mdp" autocomplete="off" required style="padding: 0.8rem; border: 1px solid rgba(255, 255, 255, 0.1); background: var(--fond-carte); color: var(--texte);">
                </div>

                <div class="champ-groupe">
                    <label for="confirm_mdp">Confirmer le nouveau mot de passe</label>
                    <input type="password" name="confirm_mdp" id="confirm_mdp" autocomplete="off" required style="padding: 0.8rem; border: 1px solid rgba(255, 255, 255, 0.1); background: var(--fond-carte); color: var(--texte);">
                </div>

                <button type="submit" name="submit_password" class="btn-submit">Modifier le mot de passe</button>
            </form>
        </div>

    </main>

</div>

</body>
</html>