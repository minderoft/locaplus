<?php
// c:\xampp\htdocs\locaplus\config_paystack.php

// IMPORTANT : Remplacez ces valeurs par vos clés Paystack (test ou live).
// IMPORTANT : Assurez-vous que ce fichier n'est jamais accessible publiquement
// et qu'il est ajouté à votre .gitignore pour ne pas être versionné.

// Clé secrète (pour le backend PHP)
// Utilise la variable d'environnement PAYSTACK_SECRET_KEY si elle existe, sinon une clé de test locale.
define('PAYSTACK_SECRET_KEY', getenv('PAYSTACK_SECRET_KEY') ?: 'sk_test_VOTRE_CLE_SECRETE_LOCALE_ICI');

// Clé publique (pour le frontend JavaScript)
// Utilise la variable d'environnement PAYSTACK_PUBLIC_KEY si elle existe, sinon une clé de test locale.
define('PAYSTACK_PUBLIC_KEY', getenv('PAYSTACK_PUBLIC_KEY') ?: 'pk_test_VOTRE_CLE_PUBLIQUE_LOCALE_ICI');