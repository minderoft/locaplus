<?php
// c:\xampp\htdocs\locaplus\initialize_payment.php

header('Content-Type: application/json');

require_once 'security_init.php'; // Initialise la session et les en-têtes de sécurité

// Vérification critique de l'extension cURL
if (!extension_loaded('curl')) {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => 'Erreur Serveur: L\'extension PHP cURL est requise mais n\'est pas activée.']);
    exit;
}

require_once 'config_paystack.php';

/**
 * Initialise une transaction Paystack en utilisant cURL.
 *
 * @param string $email L'email du client.
 * @param int $amount Le montant en sous-unité (kobo/centimes).
 * @param string $callback_url L'URL de redirection après le paiement.
 * @return array Le tableau de réponse de Paystack ou un tableau d'erreur.
 */
function initializePaystackTransaction($email, $amount, $callback_url) {
    $url = "https://api.paystack.co/transaction/initialize";

    $fields = [
        'email'        => $email,
        'amount'       => $amount,
        'callback_url' => $callback_url
        // Vous pouvez ajouter des métadonnées ici si nécessaire
        // 'metadata' => ['custom_fields' => [['display_name' => "Annonce", 'variable_name' => "listing_title", 'value' => $titleFromForm]]]
    ];

    $fields_string = http_build_query($fields);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
        "Cache-Control: no-cache",
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        return ['status' => false, 'message' => "Erreur cURL: " . $err];
    }

    return json_decode($result, true);
}

$postData = json_decode(file_get_contents('php://input'), true);

// --- CORRECTIONS DE SÉCURITÉ ET DE LOGIQUE APPLIQUÉES ---

// 1. Validation des données entrantes (email et catégorie) depuis le corps JSON de la requête
$email = filter_var($postData['email'] ?? null, FILTER_VALIDATE_EMAIL);
$category = isset($postData['category']) ? trim($postData['category']) : null;

if (!$email || !$category) {
    http_response_code(400);
    echo json_encode(['status' => false, 'message' => 'Données de paiement invalides.']);
    exit;
}

// 2. Logique de tarification stricte et sécurisée côté serveur
$amount = 0;
switch ($category) {
    // Les clés correspondent à ce qui est envoyé par le script.js
    case 'immo':
        $amount = 5000;
        break;
    case 'btp':
    case 'veh':
        $amount = 4000;
        break;
    case 'tech':
        $amount = 3000;
        break;
    default:
        // Si une catégorie inconnue est envoyée, on bloque la transaction pour la sécurité.
        http_response_code(400); // Bad Request
        echo json_encode(['status' => false, 'message' => 'Catégorie de produit invalide.']);
        exit;
}

// 3. Initialisation de la transaction avec le montant converti (x100) et l'URL de production
$response = initializePaystackTransaction($email, $amount * 100, 'https://locaplus-production.up.railway.app/verify_transaction.php');

echo json_encode($response);