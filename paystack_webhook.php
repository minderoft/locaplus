<?php
// c:\xampp\htdocs\locaplus\paystack_webhook.php

require_once 'config_paystack.php';
require_once 'db_connect.php';

// 1. Vérifier la signature du Webhook pour la sécurité
$input = @file_get_contents("php://input");
if (!isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE']) || $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] !== hash_hmac('sha512', $input, PAYSTACK_SECRET_KEY)) {
    // Signature invalide, ignorer la requête
    http_response_code(401);
    exit();
}

// 2. Décoder l'événement
$event = json_decode($input);

if ($event && isset($event->event) && $event->event === 'charge.success') {
    // 3. Le paiement est confirmé par le serveur de Paystack
    $reference = $event->data->reference;

    // 4. Mettre à jour la base de données
    // On suppose que la référence a été préalablement stockée dans la table `listings`
    // lors de sa création avec un statut 'active' mais sans 'paid_at'.
    if ($db_connected) {
        try {
            // On cherche l'annonce avec la référence de paiement et le statut 'pending'
            $stmt = $pdo->prepare("SELECT id FROM listings WHERE payment_ref = :ref AND status = 'pending'");
            $stmt->execute([':ref' => $reference]);
            $listing = $stmt->fetch();

            if ($listing) {
                // L'annonce existe, on la met à jour
                $updateStmt = $pdo->prepare("UPDATE listings SET status = 'active', paid_at = NOW() WHERE id = :id");
                $updateStmt->execute([':id' => $listing['id']]);
                
                // Log pour le débogage
                error_log("Webhook: Annonce ID " . $listing['id'] . " activée avec succès pour la référence " . $reference);
            } else {
                // L'annonce n'a pas été trouvée, peut-être déjà traitée ou problème
                error_log("Webhook: Référence de paiement " . $reference . " reçue, mais aucune annonce en attente correspondante trouvée.");
            }

        } catch (PDOException $e) {
            // Erreur de base de données, log pour investigation
            error_log("Webhook DB Error: " . $e->getMessage());
            http_response_code(500); // Indique à Paystack qu'il y a eu un problème
            exit();
        }
    }
}

// 5. Répondre à Paystack pour confirmer la réception
http_response_code(200);
exit();

?>