<?php

// PHASE 3 : ROUTAGE ET SÉCURISATION DU FORMULAIRE DE CONTACT (Génie Logiciel)

// 1. CONNEXION À LA BASE DE DONNÉES
// On inclut le script de connexion PDO. La variable $pdo devient disponible.
require_once 'config/database.php';

// 2. INITIALISATION DES VARIABLES D'ÉTAT
// Permet d'éviter les erreurs "Variable undefined" lors du premier chargement de la page.
$message_succes  = '';
$message_erreur  = '';
$sujet_predefini = '';
$nom             = '';
$email           = '';
$sujet           = '';
$message         = '';

// 3. LOGIQUE MÉTIER : INTERCEPTION DES PARAMÈTRES URL (UX / Pré-remplissage)
// Si le visiteur arrive depuis le bouton "Demander l'acquisition" d'une fiche produit,
// l'URL contient "?oeuvre=Nom_De_L_oeuvre". On l'intercepte via $_GET pour pré-remplir le champ Sujet.
if (isset($_GET['oeuvre']) && !empty($_GET['oeuvre'])) {
    $sujet_predefini = "Acquisition : " . htmlspecialchars($_GET['oeuvre']);
}

// 4. TRAITEMENT DU FORMULAIRE (Soumission en méthode POST)
// On intercepte la requête uniquement si le serveur confirme que la méthode utilisée est POST.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // SÉCURITÉ ANTI-SPAM — TECHNIQUE DU HONEYPOT (Pot de miel) :
    // Un champ invisible "honeypot" est présent dans le formulaire HTML (masqué en CSS).
    // Un vrai humain ne le voit pas et ne le remplit pas.
    // Un robot qui scanne et remplit tous les champs aveuglément le remplira.
    // Si le champ honeypot n'est pas vide → c'est un bot → on stoppe immédiatement.
    if (!empty($_POST['honeypot'])) {
        exit; // bot détecté, on coupe sans afficher d'erreur pour ne pas alerter le bot
    }

    // SÉCURITÉ ANTI-FAILLE XSS (Cross-Site Scripting) :
    // trim() supprime les espaces inutiles au début et à la fin.
    // CORRECTION : on utilise trim() seulement ici — PAS htmlspecialchars() sur les données POST.
    // htmlspecialchars() sera appliqué UNIQUEMENT à l'affichage (dans le HTML).
    // L'appliquer ici transformait les apostrophes en &#039; dans la BDD et dans le select.
    $nom     = trim($_POST['nom']     ?? '');
    $sujet   = trim($_POST['sujet']   ?? '');
    $message = trim($_POST['message'] ?? '');

    // SÉCURITÉ ET NETTOYAGE DE L'EMAIL :
    // FILTER_SANITIZE_EMAIL supprime les caractères illégaux d'une adresse email.
    $email   = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));

    // VALIDATION DES DONNÉES (Logique de contrôle côté serveur)
    if (empty($nom) || empty($email) || empty($message)) {
        $message_erreur = "Veuillez remplir tous les champs obligatoires (*).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Validation stricte de la syntaxe de l'adresse email
        $message_erreur = "L'adresse email fournie n'est pas valide.";
    } else {

        // --- NOUVEAU : LE FILTRE ANTI-SPAM (Le Videur) ---
        $is_spam = false;

        // Liste des mots-clés typiques des spambots
        $mots_interdits = [
                'http://',
                'https://',
                'www.',
                'GoogleSearchIndex',
                'SEO',
                'viagra',
                'crypto',
                'marketing'
        ];

        // Vérification dans le message, le sujet et même le nom
        foreach ($mots_interdits as $mot) {
            if (stripos($message, $mot) !== false || stripos($sujet, $mot) !== false || stripos($nom, $mot) !== false) {
                $is_spam = true;
                break; // On arrête la boucle au premier mot interdit trouvé
            }
        }

        // --- DÉCISION DU FILTRE ---
        if ($is_spam) {
            // Leurre : On fait croire au bot que ça a marché, mais on ne sauvegarde rien en BDD.
            $message_succes = "Merci " . htmlspecialchars($nom) . " ! Votre message a bien été transmis à Kaz Ahmed Koné.";

            // RÉINITIALISATION DES CHAMPS : On vide le formulaire pour éviter un double envoi
            $nom = $email = $sujet = $message = $sujet_predefini = '';
        } else {

            // SÉCURITÉ ANTI-INJECTION SQL (Requête préparée) :
            // On ne concatène JAMAIS les variables dans la chaîne SQL. On utilise des marqueurs (?).
            // PDO gère lui-même l'échappement des données — pas besoin de htmlspecialchars() avant.
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO messages_contact (nom, email, sujet, message)
                    VALUES (?, ?, ?, ?)
                ");

                // L'exécution lie les données brutes aux marqueurs de manière totalement sécurisée.
                $stmt->execute([$nom, $email, $sujet, $message]);

                // Notification de succès pour l'utilisateur
                $message_succes = "Merci " . htmlspecialchars($nom) . " ! Votre message a bien été transmis à Kaz Ahmed Koné.";

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

<!-- navigation -->
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
            <li><a href="galerie.php">Galerie</a></li>
            <li><a href="a-propos.php">À propos</a></li>
            <li><a href="contact.php" class="actif">Contact</a></li>
        </ul>
    </nav>
</header>

<!-- titre de la page -->
<div class="page-header">
    <span class="section-tag">Écrire</span>
    <h1 class="page-titre">Contact</h1>
    <p class="page-sous-titre">Pour toute demande d'acquisition, d'exposition ou de collaboration</p>
</div>

<!-- infos + formulaire -->
<section class="contact-section">

    <!-- colonne gauche : infos de Kaz -->
    <div class="contact-infos">

        <div class="contact-info-item">
            <span class="contact-info-label">Email</span>
            <a href="mailto:Ahmed.kone@yahoo.fr" class="contact-info-valeur">
                Ahmed.kone@yahoo.fr
            </a>
        </div>

        <div class="contact-info-item">
            <span class="contact-info-label">Téléphone</span>
            <a href="tel:+33782066412" class="contact-info-valeur">
                +33 (0)7 82 06 64 12
            </a>
        </div>

        <div class="contact-info-item">
            <span class="contact-info-label">Atelier</span>
            <p class="contact-info-valeur">Nancy, Lorraine<br>France</p>
        </div>

        <div class="contact-info-item">
            <span class="contact-info-label">Réseaux</span>
            <div class="contact-reseaux">
                <a href="https://instagram.com/kazahmedkone" target="_blank">@kazahmedkone</a>
                <a href="https://instagram.com/artpapakaz" target="_blank">@artpapakaz</a>
                <a href="https://www.facebook.com/share/1CkPAQPLPU/" target="_blank">Facebook</a>
            </div>
        </div>

        <blockquote class="contact-citation">
            « Je crois en un art qui ne se contente pas
            d'être regardé, mais ressenti. »
        </blockquote>

    </div>

    <!-- colonne droite : formulaire -->
    <div class="contact-formulaire">

        <!-- message erreur -->
        <?php if (!empty($message_erreur)): ?>
            <div class="formulaire-erreur">
                <?php echo htmlspecialchars($message_erreur); ?>
            </div>
        <?php endif; ?>

        <!-- message succès -->
        <?php if (!empty($message_succes)): ?>
            <div class="formulaire-confirmation visible">
                ✓ <?php echo $message_succes; ?>
            </div>
        <?php endif; ?>

        <form action="contact.php" method="POST" class="formulaire">

            <!-- HONEYPOT : champ invisible pour les humains, visible pour les bots
                 display:none le cache visuellement, tabindex="-1" l'exclut de la navigation clavier
                 Un vrai utilisateur ne le remplira jamais — un bot si, et sera rejeté côté PHP -->
            <input type="text"
                   name="honeypot"
                   id="honeypot"
                   value=""
                   style="display:none !important;"
                   tabindex="-1"
                   autocomplete="off">

            <div class="champ-groupe">
                <label for="nom">Nom complet *</label>
                <!-- htmlspecialchars() ici à l'AFFICHAGE : protège contre XSS dans l'attribut value -->
                <input type="text" id="nom" name="nom" required
                       placeholder="Votre nom"
                       value="<?php echo htmlspecialchars($nom); ?>">
            </div>

            <div class="champ-groupe">
                <label for="email">Adresse email *</label>
                <input type="email" id="email" name="email" required
                       placeholder="votre@email.fr"
                       value="<?php echo htmlspecialchars($email); ?>">
            </div>

            <!-- select type de message
                 Les valeurs des options sont des chaînes statiques — pas de htmlspecialchars() nécessaire.
                 htmlspecialchars() est utilisé uniquement sur $sujet (variable PHP) pour la comparaison. -->
            <div class="champ-groupe">
                <label for="sujet">Sujet</label>
                <select id="sujet" name="sujet">
                    <option value="">Choisir un sujet...</option>
                    <option value="Acquisition d'une œuvre"
                            <?php echo $sujet === "Acquisition d'une œuvre" || str_contains($sujet_predefini, 'Acquisition') ? 'selected' : ''; ?>>
                        Acquisition d'une œuvre
                    </option>
                    <option value="Proposition d'exposition"
                            <?php echo $sujet === "Proposition d'exposition" ? 'selected' : ''; ?>>
                        Proposition d'exposition
                    </option>
                    <option value="Collaboration artistique"
                            <?php echo $sujet === "Collaboration artistique" ? 'selected' : ''; ?>>
                        Collaboration artistique
                    </option>
                    <option value="Presse / Médias"
                            <?php echo $sujet === "Presse / Médias" ? 'selected' : ''; ?>>
                        Presse / Médias
                    </option>
                    <option value="Autre demande"
                            <?php echo $sujet === "Autre demande" ? 'selected' : ''; ?>>
                        Autre demande
                    </option>
                </select>
            </div>

            <div class="champ-groupe">
                <label for="message">Message *</label>
                <!-- htmlspecialchars() à l'affichage pour éviter l'injection dans le textarea -->
                <textarea id="message" name="message" rows="6" required
                          placeholder="Votre message..."><?php echo htmlspecialchars($message); ?></textarea>
            </div>

            <button type="submit" class="bouton-principal">
                Envoyer le message <span class="bouton-fleche">→</span>
            </button>

        </form>

    </div>

</section>

<!-- footer -->
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
    <div class="footer-copy">
        <p>© 2026 Kaz Ahmed Koné · Tous droits réservés</p>
        <p style="margin-top: 0.5rem; font-size: 0.7rem;">
            Design & Développement ·
            <a href="https://github.com/kabouchesarah4-cmd" target="_blank"
               style="color: var(--accent);">Sarah Kabouche</a>
        </p>
    </div>
</footer>

<script src="js/main.js"></script>
</body>
</html>