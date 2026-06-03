<?php
// ============================================================
// STRIPE CONFIG — clés API
// CE FICHIER DOIT ÊTRE DANS .gitignore — jamais sur GitHub
// ============================================================

// il faut que j'installe la lib Stripe via terminal :
// composer require stripe/stripe-php
// ou alors je peux télécharger stripe-php sur https://github.com/stripe/stripe-php

require_once __DIR__ . '/../vendor/autoload.php';

// clés récupérées sur https://dashboard.stripe.com/apikeys
// en local on utilise les clés "test" (commencent par sk_test_)
// en production on utilise les clés "live" (commencent par sk_live_)

if ($_SERVER['SERVER_NAME'] === 'localhost') {
    // MODE TEST — aucun vrai paiement
    define('STRIPE_SECRET_KEY',      'sk_test_REMPLACE_PAR_TA_CLE');
    define('STRIPE_PUBLISHABLE_KEY', 'pk_test_REMPLACE_PAR_TA_CLE');
    define('STRIPE_WEBHOOK_SECRET',  'whsec_REMPLACE_PAR_TON_SECRET');
    define('SITE_URL', 'http://localhost/kaz-artiste');
} else {
    // MODE PRODUCTION — vrais paiements
    define('STRIPE_SECRET_KEY',      'sk_live_REMPLACE_PAR_TA_CLE');
    define('STRIPE_PUBLISHABLE_KEY', 'pk_live_REMPLACE_PAR_TA_CLE');
    define('STRIPE_WEBHOOK_SECRET',  'whsec_REMPLACE_PAR_TON_SECRET');
    define('SITE_URL', 'https://kazahmedkone.fr');
}

// pour initialiser Stripe avec la clé secrète
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);