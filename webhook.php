<?php
// ============================================================
// WEBHOOK.PHP — reçoit les notifications de Stripe
//
// Quand un paiement réussit, Stripe envoie une requête POST
// à cette URL. On met alors le stock à 0 automatiquement.
//
// URL à configurer sur dashboard.stripe.com/webhooks :
// https://kazahmedkone.com/webhook.php
// Événement à écouter : checkout.session.completed
// ============================================================

require_once 'config/database.php';
require_once 'config/stripe-config.php';

// on lit le contenu brut de la requête Stripe
$payload   = file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    // vérifie que la requête vient vraiment de Stripe
    // (et pas d'un hacker qui ferait semblant)
    $event = \Stripe\Webhook::constructEvent(
        $payload,
        $sig_header,
        STRIPE_WEBHOOK_SECRET
    );

} catch (\UnexpectedValueException $e) {
    // payload invalide
    http_response_code(400);
    exit;
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    // signature incorrecte → pas de Stripe
    http_response_code(400);
    exit;
}

// on traite uniquement les paiements réussis
if ($event->type === 'checkout.session.completed') {

    $session   = $event->data->object;
    $oeuvre_id = $session->metadata->oeuvre_id ?? null;

    if ($oeuvre_id && is_numeric($oeuvre_id)) {
        // met le stock à 0 → l'oeuvre passe en "Vendu" sur le site
        $stmt = $pdo->prepare("UPDATE produits SET stock = 0 WHERE id = ?");
        $stmt->execute([(int)$oeuvre_id]);
    }
}

// on répond 200 à Stripe pour confirmer la réception
http_response_code(200);
echo json_encode(['status' => 'ok']);