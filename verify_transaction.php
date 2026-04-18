<?php
// c:\xampp\htdocs\locaplus\verify_transaction.php

// Vérification critique de l'extension cURL
if (!extension_loaded('curl')) {
    die("<div style='font-family: sans-serif; text-align: center; padding: 2rem; background: #fff0f0; border: 1px solid #d9534f; margin: 2rem;'>
            <h2 style='color: #d9534f;'>Erreur Critique : Extension manquante</h2>
            <p>L'extension PHP <strong>cURL</strong> est requise pour vérifier les paiements mais n'est pas activée.</p>
         </div>");
}
require_once 'config_paystack.php'; // Inclusion de la clé secrète
require_once 'db_connect.php';

session_start(); // Pour récupérer les données de l'annonce

$reference = $_GET['reference'] ?? null;

if (!$reference) {
    die("Aucune référence de transaction fournie.");
}

// 1. Vérifier la transaction auprès de Paystack
$url = 'https://api.paystack.co/transaction/verify/' . rawurlencode($reference);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . PAYSTACK_SECRET_KEY,
]);

$response = curl_exec($ch);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    // Gérer l'erreur réseau
    header('Location: locaplus.php?payment_status=error&message=network_error');
    exit();
}

$transaction = json_decode($response, true);

if ($transaction && $transaction['status'] && $transaction['data']['status'] === 'success') {
    // 2. Le paiement est réussi. Mettre à jour la base de données.
    $listingData = $_SESSION['pending_listing'] ?? null;

    if ($db_connected && $listingData) { // Assurez-vous que la DB est connectée et que les données existent
        try {
            // Validation côté serveur
            if (empty($listingData['title']) || empty($listingData['price']) || empty($listingData['category'])) {
                throw new Exception("Données de l'annonce invalides ou manquantes.");
            }

            // Préparation des données pour l'insertion
            $params = [
                'title' => $listingData['title'],
                'description' => $listingData['description'],
                'price' => (int)$listingData['price'],
                'location' => $listingData['location'],
                'type' => $listingData['category'],
                'subcat' => $listingData['subcat'],
                'badge' => $listingData['badge'],
                'contact' => $listingData['contact'],
                'photos' => json_encode($listingData['photos']),
                'user_id' => $listingData['user_id'],
                'plan' => $listingData['plan'],
                'payment_ref' => $reference,
                'status' => 'active', // L'annonce est directement active après paiement
                'details' => json_encode($listingData['details']) // Stocke les champs dynamiques
            ];

            $sql = "INSERT INTO listings (title, description, price, location, type, subcat, badge, contact, photos, user_id, plan, payment_ref, status, details, createdAt) 
                    VALUES (:title, :description, :price, :location, :type, :subcat, :badge, :contact, :photos, :user_id, :plan, :payment_ref, :status, :details, NOW())";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            unset($_SESSION['pending_listing']); // Nettoyer la session
            
            // Rediriger vers la page d'accueil avec un message de succès
            header('Location: locaplus.php?payment_status=success');
            exit();

        } catch (PDOException $e) {
            error_log("Erreur DB après paiement: " . $e->getMessage());
            header('Location: locaplus.php?payment_status=error&message=db_error');
            exit();
        } catch (Exception $e) {
            error_log("Erreur de validation après paiement: " . $e->getMessage());
            header('Location: locaplus.php?payment_status=error&message=validation_failed');
            exit();
        }
    } else {
        // Si les données de session sont manquantes, c'est un problème.
        error_log("Données de session 'pending_listing' manquantes pour la référence: " . $reference);
        header('Location: locaplus.php?payment_status=error&message=session_expired');
        exit();
    }
}

// 3. Le paiement a échoué ou n'a pas été vérifié.
header('Location: locaplus.php?payment_status=failed');
exit();