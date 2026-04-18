<?php
// On récupère l'URL de connexion fournie par Railway (si elle existe)
$url = getenv('MYSQL_URL'); 

if ($url) {
    // Configuration pour RAILWAY (Automatique)
    $dbvars = parse_url($url);
    $host = $dbvars['host'];
    $port = $dbvars['port'];
    $user = $dbvars['user'];
    $pass = $dbvars['pass'];
    $dbname = ltrim($dbvars['path'], '/');
} else {
    // Configuration pour ton PC (Local)
    $host = 'ton-host-railway'; // À copier depuis l'onglet "Connect" de Railway
    $port = 'ton-port-railway';
    $user = 'ton-user-railway';
    $pass = 'ton-password-railway';
    $dbname = 'railway';
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
?>