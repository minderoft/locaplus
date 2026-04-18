<?php
// c:\xampp\htdocs\locaplus\initialize_payment.php

// Vérification critique de l'extension cURL
if (!extension_loaded('curl')) {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => 'Erreur Serveur: L\'extension PHP cURL est requise mais n\'est pas activée.']);
    exit;
}

require_once 'config_paystack.php';

header('Content-Type: application/json');

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
        'email' => $email,
        'amount' => $amount,
        'callback_url' => $callback_url,
        'metadata' => [
            'custom_fields' => [
                [
                    'display_name' => "Plugin",
                    'variable_name' => "plugin",
                    'value' => "LocaPlus-Custom-Integration"
                ]
            ]
        ]
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
$response = initializePaystackTransaction($postData['email'], $postData['amount'], 'http://localhost/locaplus/verify_transaction.php');

echo json_encode($response);