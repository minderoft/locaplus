<?php
// c:\xampp\htdocs\locaplus\security_init.php

/**
 * Ce script doit être inclus au tout début de chaque page PHP accessible par l'utilisateur.
 * Il configure la sécurité des sessions et les en-têtes HTTP.
 */

// 1. Configuration de la sécurité des sessions
// Utiliser des cookies de session plus sécurisés.
// HttpOnly: Empêche l'accès au cookie de session via JavaScript (protection XSS).
// Secure: Le cookie ne sera envoyé que sur une connexion HTTPS. Mettre à `true` en production.
// SameSite=Strict: Protection forte contre les attaques CSRF.

$is_production = (getenv('RAILWAY_ENVIRONMENT') === 'production');

session_set_cookie_params([
    'lifetime' => 86400, // 24 heures
    'path' => '/',
    'domain' => '', // Mettre votre domaine en production
    'secure' => $is_production, // Mettre à true en production (HTTPS)
    'httponly' => true,
    'samesite' => 'Strict'
]);

// 2. Démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 3. Régénération de l'ID de session pour prévenir la fixation de session
if (!isset($_SESSION['session_regenerated'])) {
    session_regenerate_id(true);
    $_SESSION['session_regenerated'] = true;
}

// 4. Définition des en-têtes de sécurité HTTP

// Politique de sécurité de contenu (CSP)
// Réduit le risque d'attaques XSS en spécifiant les sources de contenu autorisées.
// 'unsafe-inline' a été retiré pour les scripts, ce qui est une bonne pratique.
// Vous devrez peut-être déplacer votre JS inline dans des fichiers .js séparés. 
// CORRECTIONS CSP POUR PAYSTACK :
// - style-src: Ajout de https://paystack.com pour autoriser le chargement de leurs feuilles de style (CSS).
// - frame-src: Ajout de https://checkout.paystack.com pour autoriser l'affichage de l'iframe de paiement.
// - form-action: Déjà présent, il autorise la soumission du formulaire de paiement vers le domaine de Paystack, ce qui est crucial pour la redirection.
header("Content-Security-Policy: default-src 'self'; script-src 'self' https://js.paystack.co; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://paystack.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' data: blob:; connect-src 'self' https://api.paystack.co; frame-src https://js.paystack.co https://checkout.paystack.com; form-action 'self' https://checkout.paystack.com;");

// Empêche le navigateur d'interpréter des fichiers avec un type MIME incorrect.
header("X-Content-Type-Options: nosniff");

// Empêche le clickjacking en interdisant l'affichage de la page dans une frame/iframe.
header("X-Frame-Options: DENY");

// Active la protection XSS intégrée des navigateurs.
header("X-XSS-Protection: 1; mode=block");

// Contrôle les informations de référent envoyées.
header("Referrer-Policy: strict-origin-when-cross-origin");

// Indique que le site ne doit être accédé qu'en HTTPS (à activer en production).
if ($is_production) {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
}
?>