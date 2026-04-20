<?php
// Vérification critique des prérequis serveur
if (!extension_loaded('pdo_mysql')) {
    die("<div style='font-family: sans-serif; text-align: center; padding: 2rem; background: #fff0f0; border: 1px solid #d9534f; margin: 2rem;'>
            <h2 style='color: #d9534f;'>Erreur Critique : Extension manquante</h2>
            <p>L'extension PHP <strong>pdo_mysql</strong> n'est pas chargée. Le site ne peut pas se connecter à la base de données. Veuillez l'activer dans votre fichier <strong>php.ini</strong>.</p>
         </div>");
}
require_once 'security_init.php';
require_once 'db_connect.php';

// Génération et stockage du token CSRF s'il n'existe pas
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Get current listing type from URL, default to 'immo'
$currentListingType = $_GET['type'] ?? 'immo';
$searchQuery = $_GET['q'] ?? '';
$filterType = $_GET['filter_type'] ?? '';
$filterVille = $_GET['filter_ville'] ?? '';
$filterBudget = $_GET['filter_budget'] ?? '';
$filterOffre = $_GET['filter_offre'] ?? '';
$offset = (int) ($_GET['offset'] ?? 0);
$limit = 6; // Number of listings to display per page

$allListingsFromDB = []; // To store all listings for JS App.allListings
$displayListings = [];   // To store filtered/paginated listings for PHP rendering

$activeListingsCount = '0'; // Valeur par défaut
try {
    if ($db_connected) { 
        // Fetch all listings for client-side JS functions like openDetail
        $stmtAll = $pdo->query("SELECT * FROM listings WHERE status = 'active' ORDER BY createdAt DESC");
        $allListingsFromDB = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

        // Build WHERE clause for display listings
        $whereClauses = ["status = 'active'"];
        $params = [];

        if (!empty($currentListingType)) {
            $whereClauses[] = "type = :type";
            $params[':type'] = $currentListingType;
        }

        if (!empty($searchQuery)) {
            $whereClauses[] = "(title LIKE :searchQuery OR location LIKE :searchQuery OR description LIKE :searchQuery)";
            $params[':searchQuery'] = '%' . $searchQuery . '%';
        }
        if (!empty($filterType)) {
            $whereClauses[] = "subcat = :filterType";
            $params[':filterType'] = $filterType;
        }
        if (!empty($filterVille)) {
            $whereClauses[] = "location LIKE :filterVille";
            $params[':filterVille'] = '%' . $filterVille . '%';
        }
        if (!empty($filterOffre)) {
            $whereClauses[] = "badge = :filterOffre";
            $params[':filterOffre'] = $filterOffre;
        }
        // CORRECTION: Logique de filtrage par budget améliorée
        if (!empty($filterBudget)) {
            if (strpos($filterBudget, '+') !== false) {
                $min = (int)str_replace('+', '', $filterBudget);
                $whereClauses[] = "price >= :minBudget";
                $params[':minBudget'] = $min * 1000;
            } else {
                list($min, $max) = explode('-', $filterBudget);
                $whereClauses[] = "price BETWEEN :minBudget AND :maxBudget";
                $params[':minBudget'] = (int)$min * 1000;
                $params[':maxBudget'] = (int)$max * 1000;
            }
        }

        $sql = "SELECT * FROM listings";
        if (!empty($whereClauses)) {
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }
        $sql .= " ORDER BY createdAt DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $pdo->prepare($sql);
        foreach ($params as $key => &$val) {
            $stmt->bindParam($key, $val);
        }
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $displayListings = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Récupère le nombre d'annonces actives pour la section HERO
        $stmtCount = $pdo->query("SELECT COUNT(*) FROM listings WHERE status = 'active'");
        $count = $stmtCount->fetchColumn();
        $activeListingsCount = ($count > 1000) ? number_format($count, 0, ',', ' ') . '+' : $count;
    }
    // Si la connexion échoue, $db_connected est false et le bloc ci-dessus est ignoré.
    // $displayListings et $allListingsFromDB restent des tableaux vides.
    // Le message d'erreur sera affiché dans le HTML.

} catch (PDOException $e) {
    error_log("Error fetching listings: " . $e->getMessage());
    $displayListings = [];
    $allListingsFromDB = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LocaPlus — Plateforme Multiservices</title> 

<!-- Balises pour PWA et expérience mobile -->
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="LocaPlus">
<meta name="application-name" content="LocaPlus">
<meta name="theme-color" content="#007AFF">
<!-- <link rel="manifest" href="manifest.json"> -->

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://js.paystack.co/v1/inline.js"></script>
<link rel="stylesheet" href="style.css">
</head>
<body>

<!-- LOADER -->
<div id="app-loader">
  <div class="loader-logo">Loca<span>Plus</span></div>
  <div class="loader-bar"><div class="loader-progress"></div></div>
</div>

<!-- TOAST -->
<div id="toast-container"></div>

<!-- ═══════════════ NAVBAR ═══════════════ -->
<nav id="navbar">
  <div class="nav-logo" onclick="showPage('home')">Loca<span>Plus</span></div>
  <ul class="nav-links">
    <li><a onclick="showPage('home')" id="nav-home">Accueil</a></li>
    <li><a onclick="scrollToSection('listings-section')">Annonces</a></li>
    <li><a onclick="scrollToSection('publish-section')">Publier</a></li>
    <li><a onclick="openContactGeneral()">Contact</a></li>
  </ul>
  <div class="nav-right">
    <div class="nav-user" id="nav-user">
      <div class="nav-notif btn btn-icon btn-ghost" onclick="openDashboard('messages')">
        🔔<span class="notif-dot" id="notif-dot" style="display:none"></span>
      </div>
      <div class="user-avatar" id="user-avatar" onclick="openDashboard('annonces')">?</div>
      <button class="btn btn-ghost" onclick="logout()">Déconnexion</button>
    </div>
    <div id="nav-auth" style="display:flex;gap:0.6rem">
      <button class="btn btn-ghost" onclick="openModal('auth-modal');showAuthTab('login')">Connexion</button>
      <button class="btn btn-primary" onclick="openModal('auth-modal');showAuthTab('register')">S'inscrire</button>
    </div>
    <div class="hamburger" onclick="toggleMenu()"><span></span><span></span><span></span></div>
  </div>
</nav>

<!-- Mobile Menu -->
<div id="mobile-menu" style="display:none;position:fixed;top:64px;left:0;right:0;background:var(--dark);border-bottom:1px solid var(--dark3);z-index:490;padding:1rem 1.5rem;flex-direction:column;gap:0.5rem">
  <a onclick="showPage('home');toggleMenu()" style="padding:0.65rem 0;color:var(--muted);font-size:0.9rem;cursor:pointer;display:block">🏠 Accueil</a>
  <a onclick="scrollToSection('listings-section');toggleMenu()" style="padding:0.65rem 0;color:var(--muted);font-size:0.9rem;cursor:pointer;display:block">📋 Annonces</a>
  <a onclick="scrollToSection('publish-section');toggleMenu()" style="padding:0.65rem 0;color:var(--muted);font-size:0.9rem;cursor:pointer;display:block">📝 Publier</a>
  <a onclick="openContactGeneral();toggleMenu()" style="padding:0.65rem 0;color:var(--muted);font-size:0.9rem;cursor:pointer;display:block">📞 Contact</a>
</div>

<!-- ═══════════════ MAIN PAGES ═══════════════ -->
<main id="main-pages">

<!-- ─── HOME PAGE ─── -->
<div id="home-page">

  <!-- HERO -->
  <section id="hero" style="background:var(--dark)">
    <div class="hero-mesh"></div>
    <div class="hero-badge">✨ La référence multiservices en Côte d'Ivoire</div>
    <h1>Votre recherche s'arrête ici.<br><em>Commencez votre projet.</em></h1>
    <p>Explorez des milliers d'annonces vérifiées pour l'immobilier, les véhicules, le BTP et les services de techniciens. La solution simple et sécurisée pour tous vos besoins.</p>
    <div class="hero-cta">
      <button class="btn btn-primary btn-lg" onclick="scrollToSection('search-section')">🔍 Rechercher une annonce</button>
    </div>
    <div class="hero-stats">
      <div class="stat-item"><div class="stat-num" id="stat-annonces"><?php echo htmlspecialchars($activeListingsCount); ?></div><div class="stat-label">Annonces actives</div></div>
      <div class="stat-item"><div class="stat-num">8 200</div><div class="stat-label">Clients satisfaits</div></div>
      <div class="stat-item"><div class="stat-num">4</div><div class="stat-label">Secteurs couverts</div></div>
      <div class="stat-item"><div class="stat-num">24/7</div><div class="stat-label">Support sécurisé</div></div>
    </div>
  </section>

  <!-- SEARCH -->
  <section id="search-section">
    <div class="search-inner">
      <div class="search-tabs" id="search-tabs">
        <button class="s-tab <?php echo $currentListingType == 'immo' ? 'active' : ''; ?>" id="stab-immo" onclick="switchSearchTab('immo')">🏠 Immobilier</button>
        <button class="s-tab <?php echo $currentListingType == 'veh' ? 'active' : ''; ?>" id="stab-veh" onclick="switchSearchTab('veh')">🚗 Véhicules</button>
        <button class="s-tab <?php echo $currentListingType == 'btp' ? 'active' : ''; ?>" id="stab-btp" onclick="switchSearchTab('btp')">🏗️ BTP & Matériel</button>
        <button class="s-tab <?php echo $currentListingType == 'tech' ? 'active' : ''; ?>" id="stab-tech" onclick="switchSearchTab('tech')">🛠️ Techniciens</button>
      </div>
      <div class="search-box">
        <span style="color:var(--muted);font-size:1.1rem;flex-shrink:0">🔍</span>
        <input type="text" id="search-input" placeholder="Appartement 3 pièces à Cocody..." oninput="filterListings()" autocomplete="off" maxlength="200" value="<?php echo htmlspecialchars($searchQuery); ?>">
        <button class="btn btn-primary" onclick="filterListings()"><span class="btn-text">Rechercher</span><span class="btn-spinner"></span></button>
      </div>
      <div class="search-filters" id="search-filters">
        <select class="filter-select" id="filter-type" onchange="filterListings()"><option value="">Type</option><option <?php echo $filterType == 'Appartement' ? 'selected' : ''; ?>>Appartement</option><option <?php echo $filterType == 'Maison' ? 'selected' : ''; ?>>Maison</option><option <?php echo $filterType == 'Bureau' ? 'selected' : ''; ?>>Bureau</option><option <?php echo $filterType == 'Terrain' ? 'selected' : ''; ?>>Terrain</option><option <?php echo $filterType == 'Villa' ? 'selected' : ''; ?>>Villa</option></select>
        <select class="filter-select" id="filter-ville" onchange="filterListings()"><option value="">Ville</option><option <?php echo $filterVille == 'Abidjan' ? 'selected' : ''; ?>>Abidjan</option><option <?php echo $filterVille == 'Bouaké' ? 'selected' : ''; ?>>Bouaké</option><option <?php echo $filterVille == 'Yamoussoukro' ? 'selected' : ''; ?>>Yamoussoukro</option><option <?php echo $filterVille == 'San-Pédro' ? 'selected' : ''; ?>>San-Pédro</option><option <?php echo $filterVille == 'Daloa' ? 'selected' : ''; ?>>Daloa</option></select>
        <select class="filter-select" id="filter-budget" onchange="filterListings()"><option value="">Budget</option><option value="0-100" <?php echo $filterBudget == '0-100' ? 'selected' : ''; ?>>Moins de 100k</option><option value="100-500" <?php echo $filterBudget == '100-500' ? 'selected' : ''; ?>>100k – 500k</option><option value="500-2000" <?php echo $filterBudget == '500-2000' ? 'selected' : ''; ?>>500k – 2M</option><option value="2000+" <?php echo $filterBudget == '2000+' ? 'selected' : ''; ?>>Plus de 2M</option></select>
        <select class="filter-select" id="filter-offre" onchange="filterListings()"><option value="">Type d'offre</option><option <?php echo $filterOffre == 'Location' ? 'selected' : ''; ?>>Location</option><option <?php echo $filterOffre == 'Vente' ? 'selected' : ''; ?>>Vente</option></select>
      </div>
    </div>
  </section>

  <!-- SECTORS -->
  <section class="section">
    <div class="section-inner">
      <div class="section-tag">Nos secteurs</div>
      <h2 class="section-title" style="text-align:center">Un accès unique à tout ce dont vous avez besoin</h2>
      <p class="section-sub" style="text-align:center">Fini les recherches interminables. LocaPlus centralise les meilleures offres de quatre secteurs clés pour vous simplifier la vie.</p>
      <div class="sector-grid">
        <div class="sector-card immo" onclick="switchListingTab('immo');scrollToSection('listings-section')">
          <div class="sector-icon-wrap">🏠</div>
          <h3>Immobilier</h3>
          <p>Appartements meublés, villas, maisons, bureaux et terrains à travers tout le pays. Achat, vente ou location.</p>
          <div class="sector-bottom">
            <div><div class="sector-count-big" id="count-immo">6 800+</div><div class="sector-count-sub">Biens disponibles</div></div>
            <div class="sector-arr">→</div>
          </div>
        </div>
        <div class="sector-card veh" onclick="switchListingTab('veh');scrollToSection('listings-section')">
          <div class="sector-icon-wrap">🚗</div>
          <h3>Véhicules</h3>
          <p>Location courte durée, achat de véhicules d'occasion ou neufs. Toutes les marques, toutes les gammes.</p>
          <div class="sector-bottom">
            <div><div class="sector-count-big" id="count-veh">3 200+</div><div class="sector-count-sub">Véhicules listés</div></div>
            <div class="sector-arr">→</div>
          </div>
        </div>
        <div class="sector-card btp" onclick="switchListingTab('btp');scrollToSection('listings-section')">
          <div class="sector-icon-wrap">🏗️</div>
          <h3>BTP & Matériel</h3>
          <p>Engins de chantier, grues, bétonnières et équipements professionnels. Répondez à vos besoins rapidement.</p>
          <div class="sector-bottom">
            <div><div class="sector-count-big" id="count-btp">2 400+</div><div class="sector-count-sub">Équipements dispo</div></div>
            <div class="sector-arr">→</div>
          </div>
        </div>
        <div class="sector-card tech" onclick="switchListingTab('tech');scrollToSection('listings-section')">
          <div class="sector-icon-wrap">🛠️</div>
          <h3>Techniciens</h3>
          <p>Plombiers, électriciens, peintres et autres artisans qualifiés pour tous vos travaux et réparations.</p>
          <div class="sector-bottom">
            <div><div class="sector-count-big" id="count-tech">1 500+</div><div class="sector-count-sub">Artisans disponibles</div></div>
            <div class="sector-arr">→</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- LISTINGS -->
  <section id="listings-section" class="section">
    <div class="section-inner">
      <div class="listing-header">
        <div>
          <div class="section-tag">Annonces récentes</div>
          <h2 class="section-title">Sélection du moment</h2>
        </div>
        <div class="listing-tabs" id="listing-tabs">
            <button class="l-tab <?php echo $currentListingType == 'immo' ? 'active' : ''; ?>" id="ltab-immo" onclick="switchListingTab('immo')">🏠 Immobilier</button>
            <button class="l-tab <?php echo $currentListingType == 'veh' ? 'active' : ''; ?>" id="ltab-veh" onclick="switchListingTab('veh')">🚗 Véhicules</button>
            <button class="l-tab <?php echo $currentListingType == 'btp' ? 'active' : ''; ?>" id="ltab-btp" onclick="switchListingTab('btp')">🏗️ BTP</button>
            <button class="l-tab <?php echo $currentListingType == 'tech' ? 'active' : ''; ?>" id="ltab-tech" onclick="switchListingTab('tech')">🛠️ Techniciens</button>
        </div>
      </div>
      <div class="card-grid" id="listings-grid">
        <?php if (!$db_connected): // Check if DB connection failed ?>
          <div style="grid-column: 1 / -1; text-align:center; padding:3rem; color:var(--danger); background: rgba(226,75,74,0.05); border: 1px solid rgba(226,75,74,0.1); border-radius: var(--radius-lg);">
            <div style="font-size:3rem;margin-bottom:1rem">🔌</div>
            <p style="font-weight: bold; color: var(--danger);">Erreur de connexion à la base de données.</p>
            <p>Impossible de charger les annonces. Veuillez vérifier la configuration du serveur.</p>
          </div>
        <?php elseif (empty($displayListings)): // If connected but no listings ?>
          <div style="grid-column: 1 / -1; text-align:center; padding:3rem; color:var(--muted);">
            <div style="font-size:3rem;margin-bottom:1rem">🔍</div>
            <p>Aucune annonce trouvée pour cette recherche.</p>
            <button class="btn btn-ghost" style="margin-top:1rem" onclick="resetFilters()">Réinitialiser les filtres</button>
          </div>
        <?php else: ?>
          <?php foreach ($displayListings as $l):
            $photos = json_decode($l['photos'], true) ?: [];
            $chips = json_decode($l['chips'], true) ?: [];
            $isFav = false; // This would need server-side check for logged-in user
            $imgHtml = !empty($photos) ? '<img src="' . htmlspecialchars($photos[0]) . '" alt="' . htmlspecialchars($l['title']) . '" loading="lazy">' : '';
          ?>
            <div class="listing-card" onclick="openDetail('<?php echo htmlspecialchars($l['id']); ?>')">
              <div class="card-thumb <?php echo htmlspecialchars($l['type']); ?>">
                <?php echo $imgHtml; ?>
                <?php if (empty($photos)): ?><span><?php echo htmlspecialchars($l['emoji'] ?? '🏢'); ?></span><?php endif; ?>
                <span class="card-badge <?php echo htmlspecialchars($l['badgeClass']); ?>"><?php echo htmlspecialchars($l['badge']); ?></span>
                <div class="card-fav <?php echo $isFav ? 'active' : ''; ?>" onclick="event.stopPropagation();toggleFav('<?php echo htmlspecialchars($l['id']); ?>',this)">
                  <?php echo $isFav ? '❤️' : '🤍'; ?>
                </div>
                <?php if ($l['verified']): ?><div class="card-verified">✅ Vérifié</div><?php endif; ?>
              </div>
              <div class="card-body">
                <div class="card-title"><?php echo htmlspecialchars($l['title']); ?></div>
                <div class="card-location">📍 <?php echo htmlspecialchars($l['location']); ?></div>
                <div class="card-footer">
                  <div><div class="card-price"><?php echo htmlspecialchars($l['price']); ?> <span class="card-price-unit">FCFA <?php echo htmlspecialchars($l['priceUnit'] ?? ''); ?></span></div></div>
                  <div class="card-chips"><?php echo implode('', array_map(fn($c) => '<span class="chip">' . htmlspecialchars($c) . '</span>', $chips)); ?></div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <div style="text-align:center;margin-top:2.5rem">
        <?php if (count($displayListings) == $limit): // If we fetched max, there might be more ?>
          <button class="btn btn-secondary" id="load-more-btn" onclick="loadMore()">Voir plus d'annonces →</button>
        <?php else: ?>
          <button class="btn btn-secondary" id="load-more-btn" style="display:none">Voir plus d'annonces →</button>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- PUBLISH SECTION -->
  <section id="publish-section" class="section">
    <div class="section-inner">
      <div class="publish-grid">
        <div class="publish-left">
          <div class="section-tag">Vendez. Louez. Proposez.</div>
          <h3>Donnez une visibilité maximale à votre offre</h3>
          <p>Publier sur LocaPlus, c'est simple, rapide et efficace. Rejoignez notre communauté de vendeurs et prestataires de confiance et touchez des milliers de clients potentiels chaque jour.</p>
          <div class="publish-features">
            <div class="pub-feat">
              <div class="pub-feat-icon">✅</div>
              <div class="pub-feat-text">
                <h4>Validation rapide</h4>
                <p>Votre annonce est examinée et mise en ligne en moins de 24h pour une visibilité immédiate.</p>
              </div>
            </div>
            <div class="pub-feat">
              <div class="pub-feat-icon">📸</div>
              <div class="pub-feat-text">
                <h4>Présentation soignée</h4>
                <p>Mettez en valeur votre offre avec jusqu'à 10 photos HD pour attirer l'œil et convaincre.</p>
              </div>
            </div>
            <div class="pub-feat">
              <div class="pub-feat-icon">🔒</div>
              <div class="pub-feat-text">
                <h4>Transactions sécurisées</h4>
                <p>Nous utilisons Paystack, leader du paiement en ligne, pour garantir la sécurité de chaque transaction.</p>
              </div>
            </div>
            <div class="pub-feat">
              <div class="pub-feat-icon">📊</div>
              <div class="pub-feat-text">
                <h4>Tableau de bord complet</h4>
                <p>Suivez les vues, contacts et performances de vos annonces en temps réel.</p>
              </div>
            </div>
          </div>
        </div>
        <div>
          <p class="section-tag">Choisir un forfait</p>
          <div style="margin-bottom:1rem">
            <div class="plans-grid" id="plans-grid">
              <div class="plan-card selected" data-plan="starter" data-price="5000" onclick="selectPlan(this)">
                <div class="plan-name">Starter</div>
                <div class="plan-price">5 000 <span>FCFA</span></div>
                <div class="plan-features">
                  <div class="plan-feat">1 annonce active</div>
                  <div class="plan-feat">5 photos max</div>
                  <div class="plan-feat">30 jours de visibilité</div>
                  <div class="plan-feat">Messagerie intégrée</div>
                </div>
              </div>
              <div class="plan-card" data-plan="pro" data-price="15000" onclick="selectPlan(this)">
                <div class="plan-popular">Populaire</div>
                <div class="plan-name">Pro</div>
                <div class="plan-price">15 000 <span>FCFA</span></div>
                <div class="plan-features">
                  <div class="plan-feat">3 annonces actives</div>
                  <div class="plan-feat">10 photos max</div>
                  <div class="plan-feat">60 jours de visibilité</div>
                  <div class="plan-feat">Badge vérifié</div>
                  <div class="plan-feat">Mise en avant</div>
                </div>
              </div>
              <div class="plan-card" data-plan="business" data-price="35000" onclick="selectPlan(this)">
                <div class="plan-name">Business</div>
                <div class="plan-price">35 000 <span>FCFA</span></div>
                <div class="plan-features">
                  <div class="plan-feat">10 annonces actives</div>
                  <div class="plan-feat">Photos illimitées</div>
                  <div class="plan-feat">90 jours de visibilité</div>
                  <div class="plan-feat">Badge Professionnel</div>
                  <div class="plan-feat">Support prioritaire</div>
                </div>
              </div>
            </div>
          </div>
          <button class="btn btn-primary btn-full btn-lg" onclick="requireAuth(()=>openPublishModal())">
            📝 <span class="btn-text">Publier mon annonce</span><span class="btn-spinner"></span>
          </button>
          <p style="font-size:0.78rem;color:var(--muted);text-align:center;margin-top:0.75rem">Paiement sécurisé via <strong style="color:var(--text)">Paystack</strong> · SSL/TLS · PCI DSS</p>
        </div>
      </div>
    </div>
  </section>

  <!-- FEATURES -->
  <section class="section">
    <div class="section-inner">
      <div class="section-tag">Pourquoi nous choisir</div>
      <h2 class="section-title" style="text-align:center">Votre tranquillité d'esprit, notre priorité</h2>
      <div class="feat-grid">
        <div class="feat-item">
          <div class="feat-icon">🔒</div>
          <h4>Sécurité maximale</h4>
          <p>Nous appliquons les standards de sécurité les plus stricts pour protéger vos données à chaque instant.</p>
        </div>
        <div class="feat-item">
          <div class="feat-icon">✅</div>
          <h4>Annonces vérifiées</h4>
          <p>Notre équipe de modération valide chaque annonce pour une expérience fiable et sans surprise.</p>
        </div>
        <div class="feat-item">
          <div class="feat-icon">💬</div>
          <h4>Messagerie sécurisée</h4>
          <p>Échangez en toute confiance grâce à notre messagerie interne qui protège vos informations personnelles.</p>
        </div>
        <div class="feat-item">
          <div class="feat-icon">💳</div>
          <h4>Paiements certifiés</h4>
          <p>Transactions 100% sécurisées via Paystack, certifié PCI DSS niveau 1 – le plus haut standard.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <div class="cta-wrap">
    <div class="cta-inner">
      <h2>Lancez-vous sur LocaPlus dès aujourd'hui</h2>
      <p>Créez un compte gratuit et commencez à explorer, publier et échanger sur la plateforme multiservices la plus complète de Côte d'Ivoire.</p>
      <div class="cta-btns">
        <button class="btn btn-primary btn-lg" onclick="requireAuth(()=>openPublishModal())">Créer mon annonce</button>
        <button class="btn btn-secondary btn-lg" onclick="openModal('auth-modal');showAuthTab('register')">Créer un compte</button>
      </div>
    </div>
  </div>

  <!-- FOOTER -->
  <footer>
    <div class="footer-grid">
      <div class="footer-brand">
        <span class="nav-logo">Loca<span>Plus</span></span>
        <p>La plateforme de confiance pour tous vos besoins en immobilier, véhicules, BTP et services techniques en Côte d'Ivoire.</p>
        <div class="footer-socials">
          <div class="social-link">f</div>
          <div class="social-link">in</div>
          <div class="social-link">tw</div>
          <div class="social-link">yt</div>
        </div>
      </div>
      <div class="footer-col">
        <h4>Secteurs</h4>
        <ul class="footer-links-list">
          <li><a onclick="switchListingTab('immo');scrollToSection('listings-section')">Immobilier</a></li>
          <li><a onclick="switchListingTab('veh');scrollToSection('listings-section')">Véhicules</a></li>
          <li><a onclick="switchListingTab('btp');scrollToSection('listings-section')">BTP & Matériel</a></li>
          <li><a onclick="switchListingTab('tech');scrollToSection('listings-section')">Techniciens</a></li>
          <li><a onclick="requireAuth(()=>openPublishModal())">Publier une annonce</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Entreprise</h4>
        <ul class="footer-links-list">
          <li><a>À propos</a></li>
          <li><a>Comment ça marche</a></li>
          <li><a>Blog</a></li>
          <li><a onclick="openContactGeneral()">Contact</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Légal</h4>
        <ul class="footer-links-list">
          <li><a>Conditions d'utilisation</a></li>
          <li><a>Politique de confidentialité</a></li>
          <li><a>Cookies</a></li>
          <li><a>Signaler une annonce</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      <p>© 2025 LocaPlus – Tous droits réservés</p>
      <div class="footer-badges">
        <span class="footer-badge">🔒 SSL Sécurisé</span>
        <span class="footer-badge">💳 Paystack Certifié</span>
        <span class="footer-badge">✅ Annonces Vérifiées</span>
      </div>
    </div>
  </footer>
</div>
<!-- END HOME PAGE -->

<!-- ─── DETAIL PAGE ─── -->
<div id="detail-page">
  <div class="detail-back" onclick="showPage('home')">← Retour aux annonces</div>
  <div class="detail-main">
    <div>
      <div class="detail-gallery" id="detail-main-img"><span id="detail-emoji">🏢</span></div>
      <div class="gallery-thumbs" id="gallery-thumbs"></div>
      <div style="margin-top:2rem">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;margin-bottom:0.5rem">
          <h1 class="detail-title" id="detail-title">-</h1>
          <button class="btn btn-icon btn-ghost" id="detail-fav-btn" onclick="toggleDetailFav()" title="Ajouter aux favoris" style="flex-shrink:0;margin-top:4px">🤍</button>
        </div>
        <div class="detail-loc" id="detail-loc">📍 -</div>
        <div class="detail-tags" id="detail-tags"></div>
        <div class="detail-desc">
          <h3 style="margin-top:2rem">Description</h3>
          <p id="detail-desc">-</p>
        </div>
      </div>
    </div>
    <div class="detail-sidebar">
      <div class="detail-price-card">
        <div class="detail-price" id="detail-price">-</div>
        <div style="font-size:0.82rem;color:var(--muted)" id="detail-price-unit">-</div>
        <div class="detail-seller">
          <div class="seller-avatar" id="seller-avatar">?</div>
          <div>
            <div class="seller-name" id="seller-name">-</div>
            <div class="seller-since" id="seller-since">-</div>
            <div class="verified-badge">✅ Propriétaire vérifié</div>
          </div>
        </div>
        <div class="detail-contact-btns">
          <button class="btn btn-primary btn-full" onclick="openMessageModal()">💬 Envoyer un message</button>
          <button class="btn btn-secondary btn-full" onclick="openCallModal()">📞 Appeler le propriétaire</button>
          <button class="btn btn-ghost btn-full" onclick="reportListing()">🚩 Signaler cette annonce</button>
        </div>
      </div>
      <div class="security-badge">
        🔒 <div><strong>Transactions sécurisées</strong><br>Ne payez jamais en dehors de la plateforme</div>
      </div>
    </div>
  </div>
</div>
<!-- END DETAIL PAGE -->

<!-- ─── DASHBOARD ─── -->
<div id="dashboard">
  <div class="dash-header">
    <div style="display:flex;align-items:center;justify-content:space-between">
      <div>
        <div class="section-tag">Espace personnel</div>
        <h2 class="dash-title" id="dash-welcome">Mon tableau de bord</h2>
      </div>
      <button class="btn btn-primary" onclick="requireAuth(()=>openPublishModal())">+ Nouvelle annonce</button>
    </div>
  </div>
  <div class="dash-tabs">
    <button class="dash-tab active" id="dtab-annonces" onclick="switchDashTab('annonces')">📋 Mes annonces</button>
    <button class="dash-tab" id="dtab-favoris" onclick="switchDashTab('favoris')">❤️ Favoris</button>
    <button class="dash-tab" id="dtab-messages" onclick="switchDashTab('messages')">💬 Messages</button>
    <button class="dash-tab" id="dtab-profil" onclick="switchDashTab('profil')">👤 Mon profil</button>
  </div>
  <div class="dash-content" id="dash-content"></div>
</div>
<!-- END DASHBOARD -->

</main>

<!-- ═══════════════ MODALS ═══════════════ -->

<!-- AUTH MODAL -->
<div class="modal-overlay" id="auth-modal" onclick="handleOverlayClick(event,'auth-modal')">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="auth-modal-title">
    <div class="modal-header">
      <div class="modal-title" id="auth-modal-title">Bienvenue sur LocaPlus</div>
      <button class="modal-close" onclick="closeModal('auth-modal')" aria-label="Fermer">✕</button>
    </div>
    <div class="modal-body">
      <div class="auth-tabs">
        <button class="auth-tab active" id="auth-tab-login" onclick="showAuthTab('login')">Connexion</button>
        <button class="auth-tab" id="auth-tab-register" onclick="showAuthTab('register')">Inscription</button>
      </div>
      <!-- LOGIN -->
      <div id="auth-login">
        <div class="social-auth">
          <div class="social-btn" onclick="socialAuth('Google')">🔵 Google</div>
          <div class="social-btn" onclick="socialAuth('Facebook')">🔷 Facebook</div>
        </div>
        <div class="auth-divider"><span>ou par email</span></div>
        <div class="form-group" id="fg-login-email">
          <label class="form-label">Email <span class="required">*</span></label>
          <div class="input-icon-wrap">
            <span class="input-icon">📧</span>
            <input type="email" class="form-input" id="login-email" placeholder="votre@email.com" autocomplete="email" maxlength="200">
          </div>
          <div class="form-error" id="err-login-email">Email invalide</div>
        </div>
        <div class="form-group" id="fg-login-pwd">
          <label class="form-label">Mot de passe <span class="required">*</span></label>
          <div class="input-icon-wrap">
            <span class="input-icon">🔒</span>
            <input type="password" class="form-input" id="login-pwd" placeholder="Votre mot de passe" autocomplete="current-password" maxlength="200" onkeydown="if(event.key==='Enter')submitLogin()">
            <button class="password-toggle" onclick="togglePwd('login-pwd',this)" type="button">👁</button>
          </div>
          <a class="forgot-link" onclick="openForgotModal()">Mot de passe oublié ?</a>
          <div class="form-error" id="err-login-pwd">Mot de passe incorrect</div>
        </div>
        <button class="btn btn-primary btn-full btn-lg" onclick="submitLogin()" id="btn-login">
          <span class="btn-text">Se connecter</span><span class="btn-spinner"></span>
        </button>
      </div>
      <!-- REGISTER -->
      <div id="auth-register" style="display:none">
        <div class="social-auth">
          <div class="social-btn" onclick="socialAuth('Google')">🔵 Google</div>
          <div class="social-btn" onclick="socialAuth('Facebook')">🔷 Facebook</div>
        </div>
        <div class="auth-divider"><span>ou par email</span></div>
        <div class="form-row">
          <div class="form-group" id="fg-reg-prenom">
            <label class="form-label">Prénom <span class="required">*</span></label>
            <input type="text" class="form-input" id="reg-prenom" placeholder="Jean" autocomplete="given-name" maxlength="100">
            <div class="form-error" id="err-reg-prenom">Prénom requis (2+ caractères)</div>
          </div>
          <div class="form-group" id="fg-reg-nom">
            <label class="form-label">Nom <span class="required">*</span></label>
            <input type="text" class="form-input" id="reg-nom" placeholder="Kouassi" autocomplete="family-name" maxlength="100">
            <div class="form-error" id="err-reg-nom">Nom requis (2+ caractères)</div>
          </div>
        </div>
        <div class="form-group" id="fg-reg-email">
          <label class="form-label">Email <span class="required">*</span></label>
          <div class="input-icon-wrap">
            <span class="input-icon">📧</span>
            <input type="email" class="form-input" id="reg-email" placeholder="votre@email.com" autocomplete="email" maxlength="200">
          </div>
          <div class="form-error" id="err-reg-email">Email invalide</div>
        </div>
        <div class="form-group" id="fg-reg-tel">
          <label class="form-label">Téléphone <span class="required">*</span></label>
          <div class="input-icon-wrap">
            <span class="input-icon">📱</span>
            <input type="tel" class="form-input" id="reg-tel" placeholder="+225 07 00 00 00 00" autocomplete="tel" maxlength="20">
          </div>
          <div class="form-error" id="err-reg-tel">Numéro invalide</div>
        </div>
        <div class="form-group" id="fg-reg-pwd">
          <label class="form-label">Mot de passe <span class="required">*</span></label>
          <div class="input-icon-wrap">
            <span class="input-icon">🔒</span>
            <input type="password" class="form-input" id="reg-pwd" placeholder="8 caractères minimum" autocomplete="new-password" maxlength="200" oninput="checkPasswordStrength(this.value)">
            <button class="password-toggle" onclick="togglePwd('reg-pwd',this)" type="button">👁</button>
          </div>
          <div class="strength-bar"><div class="strength-fill" id="strength-fill"></div></div>
          <div class="strength-label" id="strength-label">Saisissez un mot de passe</div>
          <div class="form-error" id="err-reg-pwd">8 caractères min, majuscule, chiffre, symbole</div>
        </div>
        <div class="form-group" id="fg-reg-pwd2">
          <label class="form-label">Confirmer le mot de passe <span class="required">*</span></label>
          <div class="input-icon-wrap">
            <span class="input-icon">🔒</span>
            <input type="password" class="form-input" id="reg-pwd2" placeholder="Confirmez votre mot de passe" autocomplete="new-password" maxlength="200" onkeydown="if(event.key==='Enter')submitRegister()">
          </div>
          <div class="form-error" id="err-reg-pwd2">Les mots de passe ne correspondent pas</div>
        </div>
        <div class="form-group">
          <label class="terms-check">
            <input type="checkbox" id="terms-check">
            J'accepte les <a onclick="closeModal('auth-modal')">conditions d'utilisation</a> et la <a>politique de confidentialité</a> de LocaPlus
          </label>
        </div>
        <button class="btn btn-primary btn-full btn-lg" onclick="submitRegister()" id="btn-register">
          <span class="btn-text">Créer mon compte</span><span class="btn-spinner"></span>
        </button>
      </div>
    </div>
  </div>
</div>

<!-- PUBLISH MODAL -->
<div class="modal-overlay publish-modal" id="publish-modal" onclick="handleOverlayClick(event,'publish-modal')">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="publish-modal-title" style="max-width:740px">
    <div class="modal-header">
      <div class="modal-title" id="publish-modal-title">📝 Publier une annonce</div>
      <button class="modal-close" onclick="closeModal('publish-modal')" aria-label="Fermer">✕</button>
    </div>
    <div class="modal-body">
      <!-- Steps -->
      <div class="steps-bar">
        <div class="step active" id="pub-step-1"><div class="step-num">1</div><div class="step-label">Catégorie</div></div>
        <div class="step-line" id="line-1-2"></div>
        <div class="step" id="pub-step-2"><div class="step-num">2</div><div class="step-label" id="step-label-2">Détails</div></div>
        <div class="step-line" id="line-2-3"></div>
        <div class="step" id="pub-step-3"><div class="step-num">3</div><div class="step-label">Photos</div></div>
        <div class="step-line" id="line-3-4"></div>
        <div class="step" id="pub-step-4"><div class="step-num">4</div><div class="step-label">Paiement</div></div>
      </div>

      <!-- STEP 1 -->
      <div id="pub-s1"> 
        <p class="form-section-title">Choisissez la catégorie</p> 
        <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:1rem;margin-bottom:1.5rem">
          <div class="sector-card immo" id="cat-immo" style="padding:1.25rem;cursor:pointer;text-align:center" onclick="selectCategory('immo')">
            <div style="font-size:1.5rem;margin-bottom:0.5rem">🏠</div>
            <div style="font-family:Inter,sans-serif;font-size:0.88rem;font-weight:700">Immobilier</div>
            <div style="font-size:0.75rem;color:var(--muted);margin-top:0.25rem">Bien & terrain</div>
          </div>
          <div class="sector-card veh" id="cat-veh" style="padding:1.25rem;cursor:pointer;text-align:center" onclick="selectCategory('veh')">
            <div style="font-size:1.5rem;margin-bottom:0.5rem">🚗</div>
            <div style="font-family:Inter,sans-serif;font-size:0.88rem;font-weight:700">Véhicules</div>
            <div style="font-size:0.75rem;color:var(--muted);margin-top:0.25rem">Auto & moto</div>
          </div>
          <div class="sector-card btp" id="cat-btp" style="padding:1.25rem;cursor:pointer;text-align:center" onclick="selectCategory('btp')">
            <div style="font-size:1.5rem;margin-bottom:0.5rem">🏗️</div>
            <div style="font-family:Inter,sans-serif;font-size:0.88rem;font-weight:700">BTP</div>
            <div style="font-size:0.75rem;color:var(--muted);margin-top:0.25rem">Engins & matériel</div>
          </div>
          <div class="sector-card tech" id="cat-tech" style="padding:1.25rem;cursor:pointer;text-align:center" onclick="selectCategory('tech')">
            <div style="font-size:1.5rem;margin-bottom:0.5rem">🛠️</div>
            <div style="font-family:Inter,sans-serif;font-size:0.88rem;font-weight:700">Technicien</div>
            <div style="font-size:0.75rem;color:var(--muted);margin-top:0.25rem">Artisan & service</div>
          </div>
        </div>
        <div class="form-group" id="fg-cat-type">
          <label class="form-label">Type d'annonce <span class="required">*</span></label>
          <select class="form-select" id="pub-offre-type">
            <option value="">Sélectionnez</option>
            <option value="location">Location</option>
            <option value="vente">Vente</option>
          </select>
          <div class="form-error" id="err-pub-type">Sélectionnez un type</div>
        </div>
        <div class="form-group" id="fg-cat-sub">
          <label class="form-label">Sous-catégorie <span class="required">*</span></label>
          <select class="form-select" id="pub-subcat">
            <option value="">Sélectionnez d'abord une catégorie</option>
          </select>
          <div class="form-error" id="err-pub-subcat">Sélectionnez une sous-catégorie</div>
        </div>
        <!-- Plan selection inside publish -->
        <div class="form-group">
          <label class="form-label">Forfait de publication <span class="required">*</span></label>
          <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:0.75rem" id="pub-plans">
            <div class="plan-card selected" data-plan="starter" data-price="5000" onclick="selectPubPlan(this)" style="padding:1rem">
              <div class="plan-name" style="font-size:0.85rem">Starter</div>
              <div class="plan-price" style="font-size:1.2rem">5 000 <span>F</span></div>
              <div style="font-size:0.72rem;color:var(--muted);margin-top:0.4rem">30 jours</div>
            </div>
            <div class="plan-card" data-plan="pro" data-price="15000" onclick="selectPubPlan(this)" style="padding:1rem;position:relative">
              <div class="plan-popular" style="font-size:0.6rem">Populaire</div>
              <div class="plan-name" style="font-size:0.85rem">Pro</div>
              <div class="plan-price" style="font-size:1.2rem">15 000 <span>F</span></div>
              <div style="font-size:0.72rem;color:var(--muted);margin-top:0.4rem">60 jours</div>
            </div>
            <div class="plan-card" data-plan="business" data-price="35000" onclick="selectPubPlan(this)" style="padding:1rem">
              <div class="plan-name" style="font-size:0.85rem">Business</div>
              <div class="plan-price" style="font-size:1.2rem">35 000 <span>F</span></div>
              <div style="font-size:0.72rem;color:var(--muted);margin-top:0.4rem">90 jours</div>
            </div>
          </div>
        </div>
      </div>

      <!-- STEP 2 -->
      <div id="pub-s2" style="display:none">
        <p class="form-section-title" id="pub-s2-title">Informations sur le bien</p>
        <div class="form-group" id="fg-pub-titre">
          <label class="form-label">Titre de l'annonce <span class="required">*</span></label>
          <input type="text" class="form-input" id="pub-titre" placeholder="Ex: Appartement 3 pièces meublé – Cocody" maxlength="150">
          <div class="form-error" id="err-pub-titre">Titre requis (10–150 caractères)</div>
        </div>
        <div class="form-row">
          <div class="form-group" id="fg-pub-ville">
            <label class="form-label">Ville <span class="required">*</span></label>
            <select class="form-select" id="pub-ville">
              <option value="">Sélectionnez</option>
              <option>Abidjan</option><option>Bouaké</option><option>Yamoussoukro</option>
              <option>San-Pédro</option><option>Daloa</option><option>Korhogo</option>
            </select>
            <div class="form-error" id="err-pub-ville">Ville requise</div>
          </div>
          <div class="form-group" id="fg-pub-commune">
            <label class="form-label">Commune / Quartier</label>
            <input type="text" class="form-input" id="pub-commune" placeholder="Ex: Cocody Angré" maxlength="100">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group" id="fg-pub-prix">
            <label class="form-label">Prix (FCFA) <span class="required">*</span></label>
            <input type="number" class="form-input" id="pub-prix" placeholder="Ex: 250000" min="0" max="9999999999">
            <div class="form-error" id="err-pub-prix">Prix invalide</div>
          </div>
          <div class="form-group" id="fg-pub-surface">
            <label class="form-label" id="lbl-surface">Surface (m²)</label>
            <input type="number" class="form-input" id="pub-surface" placeholder="Ex: 80" min="0" max="99999">
          </div>
        </div>
        <div class="form-group" id="pub-immo-fields">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Nombre de pièces</label>
              <select class="form-select" id="pub-pieces">
                <option value="">-</option><option>Studio</option><option>1 pièce</option>
                <option>2 pièces</option><option>3 pièces</option><option>4 pièces</option><option>5+ pièces</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Meublé ?</label>
              <select class="form-select" id="pub-meuble">
                <option value="">-</option><option>Meublé</option><option>Non meublé</option>
              </select>
            </div>
          </div>
        </div>
        <div class="form-group" id="fg-pub-desc">
          <label class="form-label">Description <span class="required">*</span></label>
          <textarea class="form-textarea" id="pub-desc" placeholder="Décrivez votre bien en détail : état, équipements, avantages, conditions..." style="min-height:130px" maxlength="2000"></textarea>
          <div style="display:flex;justify-content:space-between;margin-top:0.3rem">
            <div class="form-error" id="err-pub-desc">Description requise (50+ caractères)</div>
            <div style="font-size:0.72rem;color:var(--muted)" id="desc-counter">0/2000</div>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Contact WhatsApp / Téléphone <span class="required">*</span></label>
          <input type="tel" class="form-input" id="pub-contact" placeholder="+225 07 00 00 00 00" maxlength="20">
          <div class="form-error" id="err-pub-contact">Numéro requis</div>
        </div>
      </div>

      <!-- STEP 3 -->
      <div id="pub-s3" style="display:none">
        <p class="form-section-title">Ajouter des photos</p>
        <div class="upload-zone" id="upload-zone">
          <input type="file" id="photo-input" accept="image/jpeg,image/png,image/webp" multiple>
          <div class="upload-icon">📸</div>
          <div class="upload-text">
            <strong>Cliquez pour uploader</strong> ou glissez vos photos ici<br>
            <span style="font-size:0.8rem">JPEG, PNG, WEBP · Max 5 MB par photo · 10 photos max</span>
          </div>
        </div>
        <div class="photo-preview-grid" id="photo-preview-grid"></div>
        <div style="margin-top:0.75rem;font-size:0.8rem;color:var(--muted)">
          💡 La première photo sera la photo principale de votre annonce
        </div>
        <div style="margin-top:1.25rem;background:var(--dark3);border-radius:var(--radius);padding:1rem">
          <div style="font-size:0.82rem;font-weight:600;margin-bottom:0.5rem">Conseils pour de meilleures photos :</div>
          <div style="font-size:0.78rem;color:var(--muted);display:flex;flex-direction:column;gap:0.3rem">
            <div>✅ Prenez des photos en lumière naturelle</div>
            <div>✅ Montrez toutes les pièces / angles importants</div>
            <div>✅ Évitez les photos floues ou en contre-jour</div>
          </div>
        </div>
      </div>

      <!-- STEP 4 (Summary + Payment) -->
      <div id="pub-s4" style="display:none">
        <p class="form-section-title">Récapitulatif & Paiement</p>
        <div id="pub-summary" style="background:var(--dark3);border-radius:var(--radius-lg);padding:1.25rem;margin-bottom:1.5rem"></div>
        <div class="paystack-logo">
          <span style="font-size:1.1rem">💳</span>
          Paiement sécurisé via <strong>Paystack</strong> · PCI DSS Level 1
        </div>
        <div class="security-info">
          <div class="sec-item">Vos informations bancaires ne sont jamais stockées sur nos serveurs</div>
          <div class="sec-item">Toutes les transactions sont chiffrées avec SSL/TLS 256-bit</div>
          <div class="sec-item">Paystack est certifié PCI DSS Level 1 – le plus haut niveau de sécurité</div>
        </div>
        <div id="pub-payment-summary" style="background:var(--dark2);border:0.5px solid rgba(255,255,255,0.08);border-radius:var(--radius-lg);padding:1.25rem;margin-bottom:1.5rem"></div>
        <button class="btn btn-primary btn-full btn-lg" id="btn-pay-publish" onclick="initiatePaystackPayment()">
          💳 <span class="btn-text">Payer et publier mon annonce</span><span class="btn-spinner"></span>
        </button>
        <p style="font-size:0.75rem;color:var(--muted);text-align:center;margin-top:0.75rem">En cliquant sur "Payer", vous acceptez nos conditions générales</p>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" id="pub-btn-back" onclick="pubPrevStep()" style="display:none">← Retour</button>
      <div style="flex:1"></div>
      <button class="btn btn-primary" id="pub-btn-next" onclick="pubNextStep()">
        <span class="btn-text">Suivant →</span><span class="btn-spinner"></span>
      </button>
    </div>
  </div>
</div>

<!-- PAYMENT MODAL -->
<div class="modal-overlay payment-modal" id="payment-modal" onclick="handleOverlayClick(event,'payment-modal')">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="payment-modal-title">
    <div class="modal-header">
      <div class="modal-title" id="payment-modal-title">💳 Finaliser le paiement</div>
      <button class="modal-close" onclick="closeModal('payment-modal')">✕</button>
    </div>
    <div class="modal-body">
      <div class="payment-summary" id="payment-summary-content"></div>
      <div class="paystack-logo">💳 Paiement sécurisé via <strong>Paystack</strong></div>
      <div class="security-info">
        <div class="sec-item">Données chiffrées SSL 256-bit</div>
        <div class="sec-item">Certifié PCI DSS – aucun numéro de carte stocké</div>
        <div class="sec-item">Remboursement en cas d'erreur sous 48h</div>
      </div>
      <button class="btn btn-primary btn-full btn-lg" id="btn-pay-final" onclick="initiatePaystackPayment()">
        💳 <span class="btn-text">Payer maintenant</span><span class="btn-spinner"></span>
      </button>
      <button class="btn btn-ghost btn-full" style="margin-top:0.75rem" onclick="closeModal('payment-modal')">Annuler</button>
    </div>
  </div>
</div>

<!-- MESSAGE MODAL -->
<div class="modal-overlay" id="message-modal" onclick="handleOverlayClick(event,'message-modal')">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="msg-modal-title" style="max-width:460px">
    <div class="modal-header">
      <div class="modal-title" id="msg-modal-title">💬 Message</div>
      <button class="modal-close" onclick="closeModal('message-modal')">✕</button>
    </div>
    <div class="modal-body">
      <div id="msg-annonce-info" style="padding:0.75rem;background:var(--dark3);border-radius:var(--radius);margin-bottom:1.25rem;font-size:0.85rem;color:var(--muted)"></div>
      <div class="message-thread" id="message-thread"></div>
      <div style="display:flex;gap:0.75rem">
        <input type="text" class="form-input" id="msg-input" placeholder="Votre message..." maxlength="500" onkeydown="if(event.key==='Enter')sendMessage()">
        <button class="btn btn-primary" onclick="sendMessage()">➤</button>
      </div>
    </div>
  </div>
</div>

<!-- CALL MODAL -->
<div class="modal-overlay" id="call-modal" onclick="handleOverlayClick(event,'call-modal')">
  <div class="modal" role="dialog" aria-modal="true" style="max-width:400px">
    <div class="modal-header">
      <div class="modal-title">📞 Contacter le propriétaire</div>
      <button class="modal-close" onclick="closeModal('call-modal')">✕</button>
    </div>
    <div class="modal-body" style="text-align:center;padding:2rem">
      <div style="font-size:3.5rem;margin-bottom:1rem">📞</div>
      <div style="font-family:Inter,sans-serif;font-size:1.4rem;font-weight:700;margin-bottom:0.4rem" id="call-number">+225 07 00 00 00 00</div>
      <div style="color:var(--muted);font-size:0.85rem;margin-bottom:1.5rem" id="call-name">Propriétaire</div>
      <div style="background:rgba(201,168,76,0.06);border:0.5px solid rgba(201,168,76,0.12);border-radius:var(--radius);padding:0.85rem;font-size:0.78rem;color:var(--muted);margin-bottom:1.5rem">
        ⚠️ Attention aux arnaques – Ne versez jamais d'argent avant d'avoir visité le bien. LocaPlus ne vous demandera jamais vos coordonnées bancaires.
      </div>
      <button class="btn btn-primary btn-full" onclick="window.location.href='tel:'+document.getElementById('call-number').dataset.number">📞 Appeler maintenant</button>
    </div>
  </div>
</div>

<!-- CONTACT GENERAL MODAL -->
<div class="modal-overlay contact-modal" id="contact-modal" onclick="handleOverlayClick(event,'contact-modal')">
  <div class="modal" role="dialog" aria-modal="true" style="max-width:440px">
    <div class="modal-header">
      <div class="modal-title">📞 Contacter LocaPlus</div>
      <button class="modal-close" onclick="closeModal('contact-modal')">✕</button>
    </div>
    <div class="modal-body">
      <div class="form-group" id="fg-contact-nom">
        <label class="form-label">Nom complet <span class="required">*</span></label>
        <input type="text" class="form-input" id="contact-nom" placeholder="Jean Kouassi" maxlength="100">
        <div class="form-error" id="err-contact-nom">Nom requis</div>
      </div>
      <div class="form-group" id="fg-contact-email">
        <label class="form-label">Email <span class="required">*</span></label>
        <input type="email" class="form-input" id="contact-email" placeholder="votre@email.com" maxlength="200">
        <div class="form-error" id="err-contact-email">Email invalide</div>
      </div>
      <div class="form-group" id="fg-contact-sujet">
        <label class="form-label">Sujet <span class="required">*</span></label>
        <select class="form-select" id="contact-sujet">
          <option value="">Sélectionnez</option>
          <option>Question générale</option><option>Problème technique</option>
          <option>Signalement d'annonce</option><option>Facturation</option><option>Autre</option>
        </select>
        <div class="form-error" id="err-contact-sujet">Sujet requis</div>
      </div>
      <div class="form-group" id="fg-contact-msg">
        <label class="form-label">Message <span class="required">*</span></label>
        <textarea class="form-textarea" id="contact-msg" placeholder="Décrivez votre demande..." maxlength="1000"></textarea>
        <div class="form-error" id="err-contact-msg">Message requis (20+ caractères)</div>
      </div>
      <button class="btn btn-primary btn-full" onclick="submitContact()"><span class="btn-text">Envoyer →</span><span class="btn-spinner"></span></button>
    </div>
  </div>
</div>

<!-- FORGOT PASSWORD MODAL -->
<div class="modal-overlay" id="forgot-modal" onclick="handleOverlayClick(event,'forgot-modal')">
  <div class="modal" style="max-width:400px">
    <div class="modal-header">
      <div class="modal-title">🔒 Mot de passe oublié</div>
      <button class="modal-close" onclick="closeModal('forgot-modal')">✕</button>
    </div>
    <div class="modal-body">
      <p style="color:var(--muted);font-size:0.88rem;margin-bottom:1.25rem;line-height:1.6">Entrez votre email pour recevoir un lien de réinitialisation.</p>
      <div class="form-group" id="fg-forgot-email">
        <label class="form-label">Email <span class="required">*</span></label>
        <input type="email" class="form-input" id="forgot-email" placeholder="votre@email.com" maxlength="200" onkeydown="if(event.key==='Enter')submitForgot()">
        <div class="form-error" id="err-forgot-email">Email invalide</div>
      </div>
      <button class="btn btn-primary btn-full" onclick="submitForgot()"><span class="btn-text">Envoyer le lien</span><span class="btn-spinner"></span></button>
    </div>
  </div>
</div>

<!-- SUCCESS CONFETTI Container -->
<div class="confetti" id="confetti"></div>

<!--
  PHP to JS Data Bridge
  Ce bloc crucial passe les variables du serveur (PHP) au client (JavaScript).
  Il doit être placé AVANT le chargement du fichier script.js.
-->
<script>
  // Initialise l'état de l'application avec les variables passées par PHP.
  const phpData = <?php echo json_encode([
      'currentListingType' => $currentListingType,
      'allListings' => $allListingsFromDB,
      'paystackPublicKey' => defined('PAYSTACK_PUBLIC_KEY') ? PAYSTACK_PUBLIC_KEY : '',
      'csrfToken' => $csrf_token
  ]); ?>;
</script>

<!-- Main application script -->
<script src="script.js"></script>
</body>
</html>
