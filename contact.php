<?php

// PHASE 3 : ROUTAGE ET SÉCURISATION DU FORMULAIRE DE CONTACT (Génie Logiciel)

// 1. CONNEXION À LA BASE DE DONNÉES
// On inclut le script de connexion PDO. La variable $pdo devient disponible.
require_once 'config/database.php';

// 2. INITIALISATION DES VARIABLES D'ÉTAT
// Permet d'éviter les erreurs "Variable undefined" lors du premier chargement de la page.
$message_succes = '';
$message_erreur = '';
$sujet_predefini = '';
$nom = '';
$email = '';
$sujet = '';
$message = '';

// 3. LOGIQUE MÉTIER : INTERCEPTION DES PARAMÈTRES URL (UX / Pré-remplissage)
// Si le visiteur arrive depuis le bouton "Demander l'acquisition" d'une fiche produit,
// l'URL contient "?oeuvre=Nom_De_L_oeuvre". On l'intercepte via $_GET pour pré-remplir le champ Sujet.
if (isset($_GET['oeuvre']) && !empty($_GET['oeuvre'])) {
    $sujet_predefini = "Acquisition : " . htmlspecialchars($_GET['oeuvre']);
}

// 4. TRAITEMENT DU FORMULAIRE (Soumission en méthode POST)
// On intercepte la requête uniquement si le serveur confirme que la méthode utilisée est POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // SÉCURITÉ ANTI-FAILLE XSS (Cross-Site Scripting) :
    // trim() supprime les espaces inutiles au début et à la fin.
    // htmlspecialchars() neutralise les balises HTML/Script injectées par un utilisateur malveillant.
    $nom = trim(htmlspecialchars($_POST['nom'] ?? ''));
    $sujet = trim(htmlspecialchars($_POST['sujet'] ?? ''));
    $message = trim(htmlspecialchars($_POST['message'] ?? ''));

    // SÉCURITÉ ET NETTOYAGE DE L'EMAIL :
    // FILTER_SANITIZE_EMAIL supprime les caractères illégaux d'une adresse email.
    $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));

    // VALIDATION DES DONNÉES (Logique de contrôle côté serveur)
    if (empty($nom) || empty($email) || empty($message)) {
        $message_erreur = "Veuillez remplir tous les champs obligatoires (*).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Validation stricte de la syntaxe de l'adresse email
        $message_erreur = "L'adresse email fournie n'est pas valide.";
    } else {

        // SÉCURITÉ ANTI-INJECTION SQL (Requête préparée) :
        // On ne concatène JAMAIS les variables dans la chaîne SQL. On utilise des marqueurs (?).
        try {
            $stmt = $pdo->prepare("
                INSERT INTO messages_contact (nom, email, sujet, message) 
                VALUES (?, ?, ?, ?)
            ");

            // L'exécution lie les données nettoyées aux marqueurs de manière totalement sécurisée.
            $stmt->execute([$nom, $email, $sujet, $message]);

            // Notification de succès pour l'utilisateur
            $message_succes = "Merci $nom. Votre message a bien été transmis à l'artiste.";

            // RÉINITIALISATION DES CHAMPS : On vide le formulaire pour éviter un double envoi au rafraîchissement
            $nom = $email = $sujet = $message = $sujet_predefini = '';

        } catch (PDOException $e) {
            // SÉCURITÉ EN PRODUCTION : Filtrage de l'affichage des erreurs SQL selon l'environnement
            if ($_SERVER['SERVER_NAME'] === 'localhost') {
                $message_erreur = "Erreur BDD (local) : " . $e->getMessage();
            } else {
                $message_erreur = "Une erreur technique est survenue. Veuillez réessayer plus tard.";
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
    <title>Contact — Kaz Ahmed Koné</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Cormorant+Garamond:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
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
            <li><a href="galerie.php">Galerie</a></li>
            <li><a href="a-propos.html">À propos</a></li>
            <li><a href="contact.php" class="actif">Contact</a></li>
        </ul>
    </nav>
</header>

<div class="page-header">
    <span class="section-tag">Échanger</span>
    <h1 class="page-titre">Contact</h1>
    <p class="page-sous-titre">Demandes d'acquisition, collaborations ou expositions.</p>
</div>

<main class="contact-section">
    <div class="contact-infos">
        <div class="contact-info-item">
            <span class="contact-info-label">Atelier (Sur rendez-vous)</span>
            <span class="contact-info-valeur">Nancy, France</span>
        </div>
        <div class="contact-info-item">
            <span class="contact-info-label">Email professionnel</span>
            <a href="mailto:contact@kazahmedkone.fr" class="contact-info-valeur">contact@kazahmedkone.fr</a>
        </div>

        <div class="contact-reseaux" style="margin-top: 1rem;">
            <a href="https://instagram.com/kazahmedkone" target="_blank">Instagram Artiste</a>
            <a href="https://instagram.com/artpapakaz" target="_blank">Instagram Projets</a>
        </div>

        <p class="contact-citation">
            "L'art est un pont entre celui qui crée et celui qui regarde. Écrivez-moi pour traverser ce pont."
        </p>
    </div>

    <div>
        <?php if (!empty($message_erreur)): ?>
            <div style="background: rgba(139, 0, 0, 0.1); border: 1px solid #8b0000; color: #ff6b6b; padding: 1rem; margin-bottom: 2rem; font-size: 0.9rem;">
                <?php echo $message_erreur; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($message_succes)): ?>
            <div style="background: rgba(76, 175, 80, 0.1); border: 1px solid #4caf50; color: #81c784; padding: 1rem; margin-bottom: 2rem; font-size: 0.9rem;">
                <?php echo $message_succes; ?>
            </div>
        <?php endif; ?>

        <form action="contact.php" method="POST" class="formulaire">

            <div class="champ-groupe">
                <label for="nom">Nom complet *</label>
                <input type="text" id="nom" name="nom" required value="<?php echo htmlspecialchars($nom); ?>" placeholder="Votre nom">
            </div>

            <div class="champ-groupe">
                <label for="email">Adresse Email *</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>" placeholder="votre@email.com">
            </div>

            <div class="champ-groupe">
                <label for="sujet">Sujet de votre message</label>
                <input type="text" id="sujet" name="sujet" value="<?php echo htmlspecialchars(!empty($sujet_predefini) ? $sujet_predefini : $sujet); ?>" placeholder="Ex: Acquisition d'une oeuvre, Exposition...">
            </div>

            <div class="champ-groupe">
                <label for="message">Votre message *</label>
                <textarea id="message" name="message" required placeholder="Comment puis-je vous aider ?"><?php echo htmlspecialchars($message); ?></textarea>
            </div>

            <button type="submit" class="bouton-principal" style="width: fit-content; margin-top: 1rem;">
                Envoyer le message <span class="bouton-fleche">→</span>
            </button>
        </form>
    </div>
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
            <a href="https://www.linkedin.com/in/sarah-kabouche-2004263a2/" target="_blank" style="color: var(--accent); transition: var(--transition); white-space: nowrap;">Sarah Kabouche</a>
            <span style="color: var(--texte-discret); margin: 0 5px;">|</span>
            <a href="https://github.com/kabouchesarah4-cmd" target="_blank" style="color: var(--texte-discret); transition: var(--transition);">GitHub</a>
        </p>
    </div>
</footer>

<script src="js/main.js"></script>

</body>
</html>