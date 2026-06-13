<?php
// ÉTAPE 1 : CONNEXION À LA BASE DE DONNÉES
// On inclut le fichier de configuration PDO dès la première ligne du fichier.
// Comme on est à la racine du site, le chemin est 'config/database.php' (sans le '../')
require_once 'config/database.php';

// ÉTAPE 2 : REQUÊTE SQL POUR LES 3 DERNIÈRES ŒUVRES
// On va chercher les produits dans la BDD, triés du plus récent au plus ancien (DESC).
// On utilise LIMIT 3 pour ne pas surcharger la page d'accueil.
// Le LEFT JOIN permet de récupérer le nom de la catégorie pour l'afficher en sous-titre.
$stmt = $pdo->query("
    SELECT p.*, c.nom AS categorie_nom 
    FROM produits p 
    LEFT JOIN categories c ON p.id_categorie = c.id 
    ORDER BY p.id DESC 
    LIMIT 3
");
$oeuvres_recentes = $stmt->fetchAll();

//NOUVEAU : Récupération des données du profil de l'artiste ( pour dynamiser les infos )
$stmt_profil = $pdo->query("SELECT * FROM profil_artiste WHERE id = 1");
$profil = $stmt_profil->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kaz Ahmed Koné — Artiste Plasticien · Nancy</title>
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
            <li><a href="index.php" class="actif">Accueil</a></li>
            <li><a href="galerie.php">Galerie</a></li>
            <li><a href="a-propos.php">À propos</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
    </nav>
</header>

<section class="hero">
    <div class="hero-overlay"></div>
    <div class="hero-contenu">
        <p class="hero-tag">Artiste plasticien · Nancy</p>
        <h1 class="hero-titre">
            <span class="hero-prenom">Kaz Ahmed</span>
            <span class="hero-nom">Koné</span>
        </h1>
        <div class="hero-ligne"></div>
        <p class="hero-description">
            « Je crois en un art qui ne se contente pas d'être regardé, mais ressenti »
        </p>
        <a href="galerie.php" class="bouton-principal">
            Découvrir les œuvres <span class="bouton-fleche">→</span>
        </a>
    </div>
    <div class="hero-scroll">
        <span>Défiler</span>
        <div class="hero-scroll-ligne"></div>
    </div>
</section>

<section class="apercu-oeuvres">
    <div class="section-header">
        <span class="section-tag">Sélection</span>
        <h2 class="section-titre">Œuvres récentes</h2>
    </div>

    <div class="apercu-grille">

        <?php if (empty($oeuvres_recentes)): ?>
            <p style="color: var(--texte-discret); text-align: center; width: 100%;">Nouvelles œuvres à venir...</p>
        <?php else: ?>

            <?php foreach ($oeuvres_recentes as $index => $oeuvre): ?>
                <?php
                // Logique de design : dans ton code HTML d'origine, la 2ème carte (index 1)
                // avait la classe "apercu-carte--grande". On la rajoute dynamiquement ici.
                $classe_grande = ($index === 1) ? 'apercu-carte--grande' : '';

                // Logique d'affichage de l'image : on remplace tes couleurs dégradées par la vraie image.
                // Si l'image existe physiquement dans le dossier, on la met en background-image.
                // Sinon, on remet un fond sombre par défaut.
                if (!empty($oeuvre['image']) && file_exists('images/oeuvres/' . $oeuvre['image'])) {
                    $bg_image = "background-image: url('images/oeuvres/" . htmlspecialchars($oeuvre['image']) . "'); background-size: cover; background-position: center;";
                } else {
                    $bg_image = "background: linear-gradient(135deg, #1a1a1a, #0d0d0d);";
                }
                ?>

                <a href="galerie.php" class="apercu-carte <?php echo $classe_grande; ?>">
                    <div class="apercu-image" style="<?php echo $bg_image; ?>"></div>
                    <div class="apercu-info">
                        <p class="apercu-titre"><?php echo htmlspecialchars($oeuvre['titre']); ?></p>
                        <p class="apercu-details">
                            <?php echo htmlspecialchars($oeuvre['categorie_nom'] ?? 'Œuvre originale'); ?>
                            <?php if (!empty($oeuvre['prix'])) echo ' · ' . number_format($oeuvre['prix'], 0, ',', ' ') . ' €'; ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>

        <?php endif; ?>

    </div>

    <div class="apercu-footer">
        <a href="galerie.php" class="bouton-secondaire">Voir toute la galerie</a>
    </div>
</section>

<section class="citation">
    <blockquote>
        <?= nl2br(htmlspecialchars($profil['citation'])) ?>
    </blockquote>
    <cite>— Kaz Ahmed Koné</cite>
</section>

<section class="presentation">
    <div class="presentation-image">
        <img src="images/artiste/<?= htmlspecialchars($profil['image_accueil']) ?>" alt="Kaz Ahmed Koné">
    </div>
    <div class="presentation-texte">
        <span class="section-tag">L'artiste</span>
        <h2>Kaz Ahmed Koné</h2>
        <p>
            <?= nl2br(htmlspecialchars($profil['presentation_accueil'])) ?>
        </p>
        <a href="a-propos.php" class="bouton-secondaire">Lire la biographie</a>
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