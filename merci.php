<?php
// ============================================================
// MERCI.PHP — page affichée après paiement Stripe réussi
// Stripe redirige ici avec ?session_id=xxx&oeuvre_id=xxx
// ============================================================

require_once 'config/database.php';
require_once 'config/stripe-config.php';

$oeuvre     = null;
$session    = null;
$client_nom = '';

// vérifie que la session Stripe est valide
if (isset($_GET['session_id']) && isset($_GET['oeuvre_id'])) {

    $oeuvre_id = (int)$_GET['oeuvre_id'];

    try {
        // récupère les infos de la session Stripe
        $session = \Stripe\Checkout\Session::retrieve($_GET['session_id']);

        // récupère le nom du client depuis Stripe
        if ($session->customer_details) {
            $client_nom = $session->customer_details->name ?? '';
        }

        // récupère l'oeuvre
        $stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ?");
        $stmt->execute([$oeuvre_id]);
        $oeuvre = $stmt->fetch();

    } catch (\Stripe\Exception\ApiErrorException $e) {
        // session invalide → on continue quand même avec la page de remerciement
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Merci — Kaz Ahmed Koné</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Cormorant+Garamond:wght@300;400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <style>
        /* page centrée simple */
        .merci-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .merci-carte {
            text-align: center;
            max-width: 600px;
            width: 100%;
        }

        .merci-icone {
            font-size: 4rem;
            margin-bottom: 2rem;
        }

        .merci-titre {
            font-family: var(--titre);
            font-size: clamp(2rem, 5vw, 3.5rem);
            font-weight: 400;
            color: var(--texte);
            margin-bottom: 1.5rem;
        }

        .merci-oeuvre {
            font-family: var(--titre);
            font-size: 1.2rem;
            color: var(--accent);
            font-style: italic;
            margin-bottom: 1rem;
        }

        .merci-texte {
            color: var(--texte-discret);
            line-height: 1.8;
            font-size: 1rem;
            margin-bottom: 3rem;
        }

        .merci-ligne {
            width: 40px;
            height: 1px;
            background: var(--accent);
            margin: 2rem auto;
        }
    </style>
</head>
<body>

<!-- navigation -->
<header class="header scrolle" id="header">
    <nav class="nav">
        <div class="nav-logo">
            <a href="index.php">KAZ <span>AHMED KONÉ</span></a>
        </div>
        <ul class="nav-liens">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="galerie.php">Galerie</a></li>
            <li><a href="a-propos.php">À propos</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>
    </nav>
</header>

<main class="merci-section">
    <div class="merci-carte">

        <div class="merci-icone">✓</div>

        <h1 class="merci-titre">
            <?php echo !empty($client_nom) ? "Merci, " . htmlspecialchars($client_nom) . " !" : "Merci pour votre acquisition !"; ?>
        </h1>

        <?php if ($oeuvre): ?>
            <p class="merci-oeuvre">
                « <?php echo htmlspecialchars($oeuvre['titre']); ?> »
            </p>
        <?php endif; ?>

        <div class="merci-ligne"></div>

        <p class="merci-texte">
            Votre paiement a bien été reçu.<br>
            Kaz Ahmed Koné vous contactera prochainement
            pour organiser la livraison ou la remise de l'oeuvre.<br><br>
            Un email de confirmation vous a été envoyé par Stripe.
        </p>

        <a href="galerie.php" class="bouton-secondaire">
            ← Retour à la galerie
        </a>

    </div>
</main>

<script src="js/main.js"></script>
</body>
</html>