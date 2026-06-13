<?php
// on récupère la connexion BDD
require_once 'config/database.php';

// on récupère l'id passé dans l'URL : fiche-produit.php?id=3
// si pas d'id ou id non numérique → on redirige vers la galerie
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    header('Location: galerie.php');
    exit;
}

try {
    // requête préparée → sécurisée contre les injections SQL
    $requete = $pdo->prepare("
        SELECT p.*, c.nom AS categorie_nom
        FROM produits p
        LEFT JOIN categories c ON p.id_categorie = c.id
        WHERE p.id = ?
    ");
    $requete->execute([$id]);
    $oeuvre = $requete->fetch();

    // si l'id n'existe pas en BDD → retour galerie
    if (!$oeuvre) {
        header('Location: galerie.php');
        exit;
    }

} catch (PDOException $e) {
    if ($_SERVER['SERVER_NAME'] === 'localhost') {
        die("Erreur BDD (local) : " . $e->getMessage());
    } else {
        die("Une erreur technique est survenue.");
    }
}

// formatage du prix
$prix_affiche = '';
if ($oeuvre['stock'] > 0 && !empty($oeuvre['prix']) && $oeuvre['prix'] > 0) {
    $prix_affiche = number_format($oeuvre['prix'], 0, ',', ' ') . ' €';
}

$disponible = $oeuvre['stock'] > 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($oeuvre['titre']); ?> — Kaz Ahmed Koné</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Cormorant+Garamond:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>

<header class="header" id="header">
    <nav class="nav">
        <div class="nav-logo">
            <a href="index.php">KAZ <span>AHMED KONÉ</span></a>
        </div>
        <button class="nav-hamburger" id="hamburger" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
        <ul class="nav-liens" id="nav-liens">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="galerie.php" class="actif">Galerie</a></li>
            <li><a href="a-propos.php">À propos</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
    </nav>
</header>

<section class="fiche-section">

    <div class="fiche-image-wrapper">

        <?php if (!$disponible) : ?>
            <div class="fiche-badge-vendu">VENDU</div>
        <?php endif; ?>

        <?php if (!empty($oeuvre['image']) && file_exists("images/oeuvres/" . $oeuvre['image'])) : ?>
            <img src="images/oeuvres/<?php echo htmlspecialchars($oeuvre['image']); ?>"
                 class="fiche-image"
                 alt="<?php echo htmlspecialchars($oeuvre['titre']); ?>">
        <?php else : ?>
            <div class="fiche-image-placeholder"></div>
        <?php endif; ?>

    </div>

    <div class="fiche-infos">

        <a href="galerie.php" class="fiche-retour">← Retour à la galerie</a>

        <?php if (!empty($oeuvre['categorie_nom'])) : ?>
            <span class="fiche-categorie">
                <?php echo htmlspecialchars($oeuvre['categorie_nom']); ?>
            </span>
        <?php endif; ?>

        <h1 class="fiche-titre">
            <?php echo htmlspecialchars($oeuvre['titre']); ?>
        </h1>

        <?php if (!empty($oeuvre['description'])) : ?>
            <p class="fiche-description">
                <?php echo nl2br(htmlspecialchars($oeuvre['description'])); ?>
            </p>
        <?php endif; ?>

        <div class="fiche-meta">
            <?php if (!empty($oeuvre['categorie_nom'])) : ?>
                <div class="fiche-meta-ligne">
                    <span class="fiche-meta-label">Technique</span>
                    <span class="fiche-meta-valeur">
                        <?php echo htmlspecialchars($oeuvre['categorie_nom']); ?>
                    </span>
                </div>
            <?php endif; ?>

            <div class="fiche-meta-ligne">
                <span class="fiche-meta-label">Disponibilité</span>
                <span class="fiche-meta-valeur">
                    <?php if ($disponible) : ?>
                        <span class="fiche-stock-dispo">● Disponible</span>
                    <?php else : ?>
                        <span class="fiche-stock-vendu">● Vendu — Collection privée</span>
                    <?php endif; ?>
                </span>
            </div>

            <?php if (!empty($oeuvre['date_ajout'])) : ?>
                <div class="fiche-meta-ligne">
                    <span class="fiche-meta-label">Ajouté le</span>
                    <span class="fiche-meta-valeur">
                        <?php echo date('d/m/Y', strtotime($oeuvre['date_ajout'])); ?>
                    </span>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($disponible && !empty($prix_affiche)) : ?>
            <div class="fiche-prix"><?php echo $prix_affiche; ?></div>
        <?php endif; ?>

        <?php if (!$disponible) : ?>
            <a href="contact.php" class="bouton-secondaire">
                Me contacter pour d'autres œuvres →
            </a>
        <?php elseif (!empty($oeuvre['lien_stripe'])) : ?>
            <a href="<?php echo htmlspecialchars($oeuvre['lien_stripe']); ?>" target="_blank" class="bouton-principal">
                Acquérir (Paiement sécurisé) →
            </a>
        <?php else : ?>
            <a href="contact.php?oeuvre=<?php echo urlencode($oeuvre['titre']); ?>" class="bouton-principal">
                Demander l'acquisition →
            </a>
        <?php endif; ?>

    </div>

</section>

<footer class="footer">
    <div class="footer-logo">KAZ <span>AHMED KONÉ</span></div>
    <div class="footer-nav">
        <a href="galerie.php">Galerie</a>
        <a href="a-propos.php">À propos</a>
        <a href="contact.php">Contact</a>
    </div>
    <div class="footer-reseaux">
        <a href="https://instagram.com/kazahmedkone" target="_blank">@kazahmedkone</a>
        <a href="https://instagram.com/artpapakaz" target="_blank">@artpapakaz</a>
    </div>
    <div class="footer-copy" style="min-width: 250px;">
        <p>© 2026 Kaz Ahmed Koné · Tous droits réservés</p>
        <p style="margin-top: 0.8rem; font-size: 0.7rem; line-height: 1.6;">
            Design & Développement par <br>
            <a href="https://www.linkedin.com/in/sarah-kabouche-2004263a2/" target="_blank" style="color: var(--accent); transition: var(--transition); white-space: nowrap;">Sara Kabouche</a>
            <span style="color: var(--texte-discret); margin: 0 5px;">|</span>
            <a href="https://github.com/kabouchesarah4-cmd" target="_blank" style="color: var(--texte-discret); transition: var(--transition);">GitHub</a>
        </p>
    </div>
</footer>

<script src="js/main.js"></script>

</body>
</html>