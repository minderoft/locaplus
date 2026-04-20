<?php
// c:\xampp\htdocs\locaplus\initialize_payment.php

require_once 'security_init.php'; // Initialise la session et les en-têtes de sécurité

// Vérification critique de l'extension cURL
if (!extension_loaded('curl')) {
    http_response_code(500);
    // Afficher une erreur claire si cURL est manquant
    die("Erreur Serveur Critique: L'extension PHP cURL est requise pour les paiements mais n'est pas activée. Veuillez contacter l'administrateur du site.");
    exit;
}

require_once 'config_paystack.php';

/**
 * Initialise une transaction Paystack et retourne l'URL de paiement.
 *
 * @param string $email L'email du client.
 * @param int $amount Le montant en sous-unité (kobo/centimes).
 * @param string $callback_url L'URL de redirection après le paiement.
 * @return string L'URL d'autorisation pour la redirection.
 * @throws Exception Si l'initialisation échoue.
 */
function initializePaystackTransaction($email, $amount, $callback_url) {
    $url = "https://api.paystack.co/transaction/initialize";

    // Les champs à envoyer à l'API Paystack
    $fields = [
        'email'        => $email,
        'amount'       => $amount,
        'callback_url' => $callback_url,
        'currency'     => 'XOF', // Forcer la devise
        'channels'     => ['card', 'mobile_money'] // Activer la carte et le mobile money (Wave, Orange, MTN, Moov)
    ];

    $fields_string = http_build_query($fields);

    // Initialisation de cURL
    $ch = curl_init();
    
    try {
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            // Utilisation de la constante PAYSTACK_SECRET_KEY définie dans config_paystack.php
            "Authorization: Bearer " . PAYSTACK_SECRET_KEY,
            "Cache-Control: no-cache",
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // Ajout d'un timeout pour éviter les requêtes bloquantes
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $result = curl_exec($ch);
        $err = curl_error($ch);

        if ($err) {
            // Erreur réseau ou de configuration cURL
            throw new Exception("Erreur cURL: " . $err);
        }

        $response = json_decode($result, true);

        // Vérifier si Paystack a renvoyé une réponse valide avec une URL d'autorisation
        if (!$response || !isset($response['status']) || $response['status'] !== true || !isset($response['data']['authorization_url'])) {
            // Erreur côté Paystack (ex: clé invalide, mauvais paramètre)
            $paystack_error = $response['message'] ?? 'Réponse invalide de l\'API de paiement.';
            throw new Exception("Erreur Paystack: " . $paystack_error);
        }

        return $response['data']['authorization_url'];

    } finally {
        // S'assurer que la ressource cURL est toujours fermée
        curl_close($ch);
    }
}

// --- LOGIQUE DE PAIEMENT SÉCURISÉE ---

// 1. Récupérer les données envoyées en POST (depuis le formulaire de paiement)
// Prioriser $_POST pour les soumissions de formulaire HTML standard.
if (!empty($_POST)) {
    $requestData = $_POST;
} else {
    // Fallback pour les requêtes avec un corps JSON (ex: fetch avec Content-Type: application/json)
    $requestData = json_decode(file_get_contents('php://input'), true);
}

// 2. Validation stricte des données entrantes
// Utilisation de l'opérateur de coalescence nulle pour éviter les warnings si les clés n'existent pas.
$email = filter_var($requestData['email'] ?? '', FILTER_VALIDATE_EMAIL);
$category = trim($requestData['category'] ?? '');

if (!$email || !$category) {
    http_response_code(400);
    die("Données de paiement invalides ou manquantes. L'email et la catégorie sont requis.");
    exit;
}

// 3. Logique de tarification sécurisée côté serveur (non modifiable par le client)
$amount = 0;
switch ($category) {
    case 'immo': // Immobilier
        $amount = 5000;
        break;
    case 'btp':  // BTP & Matériel
    case 'veh':  // Véhicules
        $amount = 4000;
        break;
    case 'tech': // Techniciens
        $amount = 3000;
        break;
    default:
        // Bloquer la transaction si la catégorie est inconnue
        http_response_code(400);
        die("Catégorie de produit invalide. Transaction annulée pour des raisons de sécurité.");
        exit;
}

// 4. Initialisation de la transaction et redirection
try {
    // Le montant est multiplié par 100 pour être en kobo/centimes
    $amount_in_kobo = $amount * 100;
    $callback_url = 'https://locaplus-production.up.railway.app/verify_transaction.php';

    // Log pour le débogage : vérifier les données juste avant l'appel à Paystack
    error_log("Initialisation Paystack pour: email={$email}, category={$category}, amount={$amount_in_kobo}");

    $authorization_url = initializePaystackTransaction($email, $amount_in_kobo, $callback_url);

    // Redirection de l'utilisateur vers la page de paiement Paystack
    header('Location: ' . $authorization_url);
    exit();

} catch (Exception $e) {
    // Gérer les erreurs de manière propre et afficher un message clair
    http_response_code(500);
    error_log("Erreur d'initialisation de paiement: " . $e->getMessage()); // Log pour le debug
    die("Impossible d'initier le paiement. Erreur: " . htmlspecialchars($e->getMessage()));
}