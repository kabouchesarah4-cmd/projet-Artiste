<?php
// ============================================================
// PAIEMENT.PHP — crée une session Stripe et redirige
// Appelé quand le visiteur clique "Acheter" sur une fiche
//
// URL : paiement.php?id=3
// ============================================================

session_start();
require_once 'config/database.php';
require_once 'config/stripe-config.php';

// récupère l'id de l'oeuvre
$id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    header('Location: galerie.php');
    exit;
}

// vérifie que l'oeuvre existe et est disponible
$stmt = $pdo->prepare("SELECT * FROM produits WHERE id = ? AND stock > 0");
$stmt->execute([$id]);
$oeuvre = $stmt->fetch();

if (!$oeuvre) {
    // oeuvre introuvable ou déjà vendue
    header('Location: galerie.php?erreur=indisponible');
    exit;
}

// calcule le prix en centimes (Stripe travaille en centimes)
$prix_centimes = (int)($oeuvre['prix'] * 100);

if ($prix_centimes <= 0) {
    // pas de prix défini → on redirige vers le formulaire de contact
    header('Location: contact.php?oeuvre=' . urlencode($oeuvre['titre']));
    exit;
}

try {
    // crée la session de paiement Stripe
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency'     => 'eur',
                'unit_amount'  => $prix_centimes,
                'product_data' => [
                    'name'        => $oeuvre['titre'],
                    'description' => $oeuvre['description'] ?? 'Oeuvre originale de Kaz Ahmed Koné',
                    // si image disponible on l'envoie à Stripe
                    'images'      => !empty($oeuvre['image'])
                        ? [SITE_URL . '/images/oeuvres/' . $oeuvre['image']]
                        : [],
                ],
            ],
            'quantity' => 1,
        ]],
        'mode'        => 'payment',
        // page affichée après paiement réussi
        'success_url' => SITE_URL . '/merci.php?session_id={CHECKOUT_SESSION_ID}&oeuvre_id=' . $id,
        // page si le visiteur annule
        'cancel_url'  => SITE_URL . '/fiche-produit.php?id=' . $id,
        // métadonnées pour retrouver l'oeuvre dans le webhook
        'metadata' => [
            'oeuvre_id' => $id,
        ],
    ]);

    // redirige vers la page de paiement Stripe
    header('Location: ' . $session->url);
    exit;

} catch (\Stripe\Exception\ApiErrorException $e) {
    // erreur Stripe
    if ($_SERVER['SERVER_NAME'] === 'localhost') {
        die("Erreur Stripe (local) : " . $e->getMessage());
    } else {
        header('Location: fiche-produit.php?id=' . $id . '&erreur=paiement');
        exit;
    }
}