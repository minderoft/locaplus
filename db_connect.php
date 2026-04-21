<?php
// c:\xampp\htdocs\locaplus\db_connect.php

// Désactive l'affichage des erreurs en production pour ne pas exposer de détails sensibles.
ini_set('display_errors', getenv('RAILWAY_ENVIRONMENT') === 'production' ? '0' : '1');

// Variable pour suivre l'état de la connexion
$db_connected = false;
$db_error_message = ''; // Nouvelle variable pour stocker le message d'erreur détaillé

// Configuration pour RAILWAY (utilise les variables d'environnement standard)
$host = getenv('MYSQLHOST') ?: '127.0.0.1'; // Fallback sur localhost
$port = getenv('MYSQLPORT') ?: '3306';      // Fallback sur le port par défaut
$user = getenv('MYSQLUSER') ?: 'root';      // Fallback pour XAMPP/WAMP
$pass = getenv('MYSQLPASSWORD') ?: '';      // Fallback pour XAMPP/WAMP
$dbname = getenv('MYSQLDATABASE') ?: 'locaplus_db'; // Nom de DB local suggéré

try {
    // Vérification ajoutée ici pour un message d'erreur plus précis
    if (!extension_loaded('pdo_mysql')) {
        $db_connected = false;
        throw new PDOException("L'extension pdo_mysql n'est pas chargée dans l'environnement PHP.");
    }

    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db_connected = true; // La connexion a réussi
} catch (PDOException $e) {
    $db_error_message = $e->getMessage(); // Stocke le message d'erreur
    // On log l'erreur pour le débogage.
    error_log("Erreur de connexion à la base de données : " . $db_error_message);
    
    // Vous pouvez choisir de laisser le script continuer (avec $db_connected = false)
    // ou d'arrêter complètement avec un message générique.
    // Pour ce projet, nous laissons le script continuer pour afficher un message d'erreur dans l'interface.
    $pdo = null;
}
?>