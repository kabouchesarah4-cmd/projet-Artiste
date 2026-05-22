<?php

//  — PHASE 2 : DYNAMISATION DU CATALOGUE VIA LA BDD
// On inclut le script de connexion PDO créé à l'étape 2.3.
// Grâce au require_once, la variable de connexion $pdo devient disponible ici.
require_once 'config/database.php';

try {
    // REQUÊTE AVEC JOINTURE (Génie Logiciel L3) :
    // Au lieu de lire uniquement la table 'produits', on effectue un LEFT JOIN
    // avec la table 'categories' pour récupérer directement le nom textuel.
    // Le LEFT JOIN garantit qu'une œuvre s'affiche même si sa catégorie est NULL.
    // 'c.nom AS categorie_nom' évite les conflits si deux colonnes s'appellent 'nom'.
    $requete = $pdo->query("
        SELECT p.*, c.nom AS categorie_nom
        FROM produits p
        LEFT JOIN categories c ON p.id_categorie = c.id
        ORDER BY p.id DESC
    ");

    // On extrait toutes les lignes d'un coup sous forme de tableau associatif
    $oeuvres = $requete->fetchAll();

} catch (PDOException $e) {
    // SÉCURITÉ EN PRODUCTION : On applique ton filtre d'environnement.
    // On affiche l'erreur SQL complète uniquement en local pour déboguer.
    if ($_SERVER['SERVER_NAME'] === 'localhost') {
        die("Erreur de requête (local) : " . $e->getMessage());
    } else {
        die("Une erreur technique est survenue. Veuillez réessayer plus tard.");
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galerie — Kaz Ahmed Koné</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Cormorant+Garamond:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/galerie.css">
    <link rel="stylesheet" href="css/responsive.css">
</head>
<body>

<header class="header" id="header">
    <nav class="nav">
        <div class="nav-logo">
            <a href="index.html">KAZ <span>AHMED KONÉ</span></a>
        </div>
        <button class="nav-hamburger" id="hamburger" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
        <ul class="nav-liens" id="nav-liens">
            <li><a href="index.html">Accueil</a></li>
            <li><a href="galerie.php" class="actif">Galerie</a></li>
            <li><a href="a-propos.html">À propos</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
    </nav>
</header>

<div class="page-header">
    <span class="section-tag">Portfolio 2015–2025</span>
    <h1 class="page-titre">Galerie</h1>
    <p class="page-sous-titre">Peinture · Création numérique · Sculpture · Upcycling</p>
</div>

<div class="filtres">
    <button class="filtre-btn actif" data-filtre="tout">Tout</button>
    <button class="filtre-btn" data-filtre="numerique">Art numérique</button>
    <button class="filtre-btn" data-filtre="acrylique">Acrylique</button>
    <button class="filtre-btn" data-filtre="sculpture">Sculpture</button>
</div>

<main class="galerie-grille" id="galerie-grille">

    <?php
    // ÉVALUATION DU CODE : On parcourt le tableau d'œuvres extrait de MySQL.
    // Chaque entrée devient un tableau '$oeuvre' contenant les colonnes de la BDD.
    foreach ($oeuvres as $oeuvre) :
        ?>

        <?php
        // 1. HARMONISATION POUR LE FILTRAGE JS :
        // On récupère le nom de la catégorie issu du JOIN. Si vide, on met 'tout'.
        // strtolower() passe le nom en minuscules pour matcher avec le 'data-filtre' du JS.
        $cat = !empty($oeuvre['categorie_nom']) ? strtolower($oeuvre['categorie_nom']) : 'tout';

        // 2. CONVERSIONS SÉCURISÉES ET TRAITEMENT DU TEXTE (Anti-faille XSS) :
        // L'opérateur ?? '' (coalescence nulle) évite un crash si la description est NULL en BDD.
        $details = htmlspecialchars($oeuvre['description'] ?? '');

        // 3. LOGIQUE MÉTIER DYNAMIQUE (Gestion du stock / Disponibilité) :
        // Si le stock est supérieur à 0, l'œuvre est disponible : on formate et affiche son prix.
        // Si le stock est à 0, l'œuvre est marquée vendue pour valoriser le portfolio de l'artiste.
        if ($oeuvre['stock'] > 0) {
            if (!empty($oeuvre['prix']) && $oeuvre['prix'] > 0) {
                // number_format() ajoute un espace pour séparer les milliers (ex: 1 500 € au lieu de 1500)
                $details .= ' · ' . number_format($oeuvre['prix'], 0, ',', ' ') . ' €';
            }
        } else {
            $details .= ' · Vendu (Collection privée)';
        }
        ?>

        <a href="fiche-produit.php?id=<?php echo $oeuvre['id']; ?>"
           class="galerie-carte"
           style="text-decoration: none; color: inherit; display: block;"
           data-categorie="<?php echo htmlspecialchars($cat); ?>"
           data-titre="<?php echo htmlspecialchars($oeuvre['titre']); ?>"
           data-details="<?php echo $details; ?>">

            <?php
            // VÉRIFICATION DE SÉCURITÉ DU FICHIER IMAGE :
            // file_exists() vérifie si le fichier existe physiquement dans le dossier 'images/oeuvres/'
            // Si oui, on l'affiche. Si non (ou si le champ est vide), on génère un bloc dégradé CSS neutre.
            if (!empty($oeuvre['image']) && file_exists("images/oeuvres/" . $oeuvre['image'])) :
                ?>
                <img src="images/oeuvres/<?php echo htmlspecialchars($oeuvre['image']); ?>"
                     class="galerie-image"
                     alt="<?php echo htmlspecialchars($oeuvre['titre']); ?>">
            <?php else : ?>
                <div class="galerie-image" style="background: linear-gradient(135deg, #111, #444);"></div>
            <?php endif; ?>

            <?php if ($oeuvre['stock'] <= 0) : ?>
                <div class="badge-vendu" style="position: absolute; top: 15px; left: 15px; background: #8b0000; color: #fff; padding: 5px 10px; font-size: 12px; font-family: 'Playfair Display'; z-index: 2; letter-spacing: 1px;">VENDU</div>
            <?php endif; ?>

            <div class="galerie-overlay">
                <p class="galerie-carte-titre"><?php echo htmlspecialchars($oeuvre['titre']); ?></p>
                <p class="galerie-carte-details"><?php echo $details; ?></p>
                <span class="galerie-loupe">+</span>
            </div>

        </a>

    <?php endforeach; ?>

</main>

<footer class="footer">
    <div class="footer-logo">KAZ <span>AHMED KONÉ</span></div>
    <div class="footer-nav">
        <a href="galerie.php">Galerie</a>
        <a href="a-propos.html">À propos</a>
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