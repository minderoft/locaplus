// ═══════════════════════════════════════════════════════
//  LOCAPLUS — SECURE APPLICATION CORE
//  Security: XSS prevention, CSRF tokens, input sanitization,
//  rate limiting, session management, secure storage
// ═══════════════════════════════════════════════════════

'use strict';

// ─── SECURITY UTILITIES ───────────────────────────────
const Security = {
  // Escape HTML to prevent XSS
  escapeHtml(str) {
    if (typeof str !== 'string') return '';
    return str
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;')
      .replace(/\//g, '&#x2F;');
  },
  // Sanitize text content
  sanitize(str) {
    if (typeof str !== 'string') return '';
    return str.trim().replace(/[<>]/g, '').substring(0, 2000);
  },
  // Validate email with strict regex
  isValidEmail(email) {
    return /^[a-zA-Z0-9._%+\-]{1,64}@[a-zA-Z0-9.\-]{1,255}\.[a-zA-Z]{2,10}$/.test(email);
  },
  // Validate phone
  isValidPhone(phone) {
    return /^[+]?[\d\s\-()]{8,20}$/.test(phone.replace(/\s/g,''));
  },
  // Check password strength
  passwordStrength(pwd) {
    let score = 0;
    if (pwd.length >= 8) score++;
    if (pwd.length >= 12) score++;
    if (/[A-Z]/.test(pwd)) score++;
    if (/[a-z]/.test(pwd)) score++;
    if (/[0-9]/.test(pwd)) score++;
    if (/[^A-Za-z0-9]/.test(pwd)) score++;
    return score;
  },
  isStrongPassword(pwd) {
    return pwd.length >= 8 && /[A-Z]/.test(pwd) && /[0-9]/.test(pwd);
  },
  // Generate CSRF token
  generateToken() {
    const arr = new Uint8Array(32);
    crypto.getRandomValues(arr);
    return Array.from(arr, b => b.toString(16).padStart(2,'0')).join('');
  },
  // Rate limiter
  rateLimits: {},
  checkRateLimit(action, maxAttempts=5, windowMs=60000) {
    const now = Date.now();
    if (!this.rateLimits[action]) this.rateLimits[action] = { count: 0, resetAt: now + windowMs };
    const rl = this.rateLimits[action];
    if (now > rl.resetAt) { rl.count = 0; rl.resetAt = now + windowMs; }
    if (rl.count >= maxAttempts) return false;
    rl.count++;
    return true;
  },
  // Secure hash simulation (for demo)
  async hashPassword(pwd) {
    const enc = new TextEncoder().encode(pwd + 'loca_salt_v1');
    const hash = await crypto.subtle.digest('SHA-256', enc);
    return Array.from(new Uint8Array(hash)).map(b=>b.toString(16).padStart(2,'0')).join('');
  }
};

// ─── SESSION MANAGEMENT ──────────────────────────────
const Session = {
  _key: 'lp_session_v2',
  _csrfToken: Security.generateToken(),
  get() {
    try {
      const raw = sessionStorage.getItem(this._key);
      if (!raw) return null;
      const data = JSON.parse(raw);
      // Check session expiry (8 hours)
      if (Date.now() > data.expiresAt) { this.destroy(); return null; }
      return data;
    } catch { return null; }
  },
  set(user) {
    const session = {
      ...user,
      csrfToken: this._csrfToken,
      createdAt: Date.now(),
      expiresAt: Date.now() + 8 * 60 * 60 * 1000
    };
    sessionStorage.setItem(this._key, JSON.stringify(session));
    return session;
  },
  destroy() {
    sessionStorage.removeItem(this._key);
    this._csrfToken = Security.generateToken();
  },
  isAuthenticated() { return !!this.get(); }
};

// ─── APP STATE ────────────────────────────────────────
// Cette déclaration unique de l'objet App sert de source de vérité pour l'état de l'application.
const App = {
  currentPage: 'home',
  currentListingTab: 'immo', // Sera écrasé par la valeur PHP
  currentSearchTab: 'immo',  // Sera écrasé par la valeur PHP
  uploadedPhotos: [],
  selectedCategory: null,
  selectedPlan: { plan: 'starter', price: 5000 },
  currentPublishStep: 1,
  currentListing: null,
  pendingAction: null,
  dashTab: 'annonces',
  allListings: [], // Sera rempli par les données PHP
  paystackPublicKey: '', // Sera rempli par la clé publique PHP
  csrfToken: '' // Sera rempli par le token CSRF de PHP
};


// ─── LOCAL DATABASE (simulated) ─────────────────────
const DB = {
  _usersKey: 'lp_users_v2',
  _listingsKey: 'lp_listings_v2',
  _messagesKey: 'lp_messages_v2',
  _favoritesKey: 'lp_favorites_v2',

  getUsers() {
    try { return JSON.parse(localStorage.getItem(this._usersKey) || '[]'); } catch { return []; }
  },
  saveUsers(users) {
    try { localStorage.setItem(this._usersKey, JSON.stringify(users)); } catch {}
  },
  findUser(email) {
    return this.getUsers().find(u => u.email === email.toLowerCase());
  },
  addUser(user) {
    const users = this.getUsers();
    users.push({ ...user, id: 'u_' + Security.generateToken().substr(0,12), createdAt: Date.now() });
    this.saveUsers(users);
    return users[users.length - 1];
  },

  getListings() {
    try { return JSON.parse(localStorage.getItem(this._listingsKey) || '[]'); } catch { return []; }
  },
  saveListings(l) {
    try { localStorage.setItem(this._listingsKey, JSON.stringify(l)); } catch {}
  },
  addListing(listing) {
    const listings = this.getListings();
    const newL = { ...listing, id: 'l_' + Security.generateToken().substr(0,12), createdAt: Date.now(), views: 0, status: 'active' };
    listings.unshift(newL);
    this.saveListings(listings);
    return newL;
  },
  getUserListings(userId) {
    return this.getListings().filter(l => l.userId === userId);
  },

  getMessages() {
    try { return JSON.parse(localStorage.getItem(this._messagesKey) || '[]'); } catch { return []; }
  },
  addMessage(msg) {
    const msgs = this.getMessages();
    msgs.push({ ...msg, id: 'm_' + Date.now(), createdAt: Date.now() });
    try { localStorage.setItem(this._messagesKey, JSON.stringify(msgs)); } catch {}
  },

  getFavorites(userId) {
    try {
      const all = JSON.parse(localStorage.getItem(this._favoritesKey) || '{}');
      return all[userId] || [];
    } catch { return []; }
  },
  toggleFavorite(userId, listingId) {
    try {
      const all = JSON.parse(localStorage.getItem(this._favoritesKey) || '{}');
      if (!all[userId]) all[userId] = [];
      const idx = all[userId].indexOf(listingId);
      if (idx >= 0) all[userId].splice(idx, 1);
      else all[userId].push(listingId);
      localStorage.setItem(this._favoritesKey, JSON.stringify(all));
      return idx < 0;
    } catch { return false; }
  }
};

// ─── SUBCAT DATA (still used for publish modal) ─────────────────────────────────────
const SUBCATS = {
  immo: ['Appartement','Maison','Villa','Studio','Bureau','Terrain','Entrepôt','Local commercial'],
  veh:  ['Berline','SUV / 4x4','Pick-up','Minibus','Camion','Moto','Tricycle','Autre'],
  btp:  ['Pelle hydraulique','Grue','Bulldozer','Chargeur','Bétonnière','Centrale à béton','Compacteur','Échafaudage','Outillage divers']
};
SUBCATS.tech = ['Plombier', 'Électricien', 'Peintre', 'Menuisier', 'Maçon', 'Climaticien', 'Mécanicien', 'Informaticien', 'Autre'];
 
// ─── TOAST SYSTEM ─────────────────────────────────────
function toast(msg, type='info', duration=3500) {
  const icons = { success:'✅', error:'❌', info:'ℹ️', warning:'⚠️' };
  const container = document.getElementById('toast-container');
  const t = document.createElement('div');
  t.className = `toast ${type}`;
  t.innerHTML = `<span class="toast-icon">${icons[type]||'ℹ️'}</span><span class="toast-msg">${Security.escapeHtml(msg)}</span>`;
  container.appendChild(t);
  requestAnimationFrame(() => { requestAnimationFrame(() => { t.classList.add('show'); }); });
  setTimeout(() => {
    t.classList.remove('show');
    setTimeout(() => t.remove(), 300);
  }, duration);
}

// ─── MODAL MANAGEMENT ────────────────────────────────
function openModal(id) {
  const m = document.getElementById(id);
  if (m) { m.classList.add('open'); document.body.style.overflow = 'hidden'; }
}
function closeModal(id) {
  const m = document.getElementById(id);
  if (m) { m.classList.remove('open'); document.body.style.overflow = ''; }
}
function handleOverlayClick(e, id) {
  if (e.target.id === id) closeModal(id);
}
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    document.querySelectorAll('.modal-overlay.open').forEach(m => {
      m.classList.remove('open');
      document.body.style.overflow = '';
    });
  }
});

// ─── PAGE MANAGEMENT ─────────────────────────────────
function showPage(page) {
  document.getElementById('home-page').style.display = 'none';
  document.getElementById('detail-page').style.display = 'none';
  document.getElementById('dashboard').style.display = 'none';
  if (page === 'home') {
    document.getElementById('home-page').style.display = '';
    App.currentPage = 'home';
  } else if (page === 'detail') {
    document.getElementById('detail-page').style.display = '';
    App.currentPage = 'detail';
  } else if (page === 'dashboard') {
    document.getElementById('dashboard').style.display = '';
    App.currentPage = 'dashboard';
    renderDashboard();
  }
  window.scrollTo(0, 0);
}

function scrollToSection(id) {
  if (App.currentPage !== 'home') showPage('home');
  setTimeout(() => {
    const el = document.getElementById(id);
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }, 100);
}

// ─── NAV / HAMBURGER ────────────────────────────────
function toggleMenu() {
  const m = document.getElementById('mobile-menu');
  m.style.display = m.style.display === 'flex' ? 'none' : 'flex';
}

// ─── AUTH: REQUIRE AUTHENTICATION ───────────────────
function requireAuth(callback) {
  if (Session.isAuthenticated()) {
    if (callback) callback();
  } else {
    App.pendingAction = callback;
    openModal('auth-modal');
    showAuthTab('login');
    toast('Connectez-vous pour continuer', 'info');
  }
}

// ─── AUTH TABS ───────────────────────────────────────
function showAuthTab(tab) {
  document.getElementById('auth-login').style.display = tab === 'login' ? '' : 'none';
  document.getElementById('auth-register').style.display = tab === 'register' ? '' : 'none';
  document.getElementById('auth-tab-login').classList.toggle('active', tab === 'login');
  document.getElementById('auth-tab-register').classList.toggle('active', tab === 'register');
  clearAuthErrors();
}

function clearAuthErrors() {
  document.querySelectorAll('#auth-modal .form-group.has-error').forEach(fg => fg.classList.remove('has-error'));
}

function togglePwd(id, btn) {
  const inp = document.getElementById(id);
  if (inp.type === 'password') { inp.type = 'text'; btn.textContent = '🙈'; }
  else { inp.type = 'password'; btn.textContent = '👁'; }
}

function checkPasswordStrength(pwd) {
  const score = Security.passwordStrength(pwd);
  const fill = document.getElementById('strength-fill');
  const label = document.getElementById('strength-label');
  const colors = ['#E24B4A','#E24B4A','#BA7517','#BA7517','#1D9E75','#1D9E75'];
  const labels = ['','Très faible','Faible','Moyen','Fort','Très fort'];
  fill.style.width = (score/6*100) + '%';
  fill.style.background = colors[score] || '#E24B4A';
  label.textContent = labels[score] || '';
}

// ─── LOGIN ───────────────────────────────────────────
async function submitLogin() {
  if (!Security.checkRateLimit('login', 5, 60000)) {
    toast('Trop de tentatives. Attendez 1 minute.', 'error'); return;
  }
  const email = document.getElementById('login-email').value.trim();
  const pwd = document.getElementById('login-pwd').value;
  let valid = true;
  if (!Security.isValidEmail(email)) { setFieldError('fg-login-email','err-login-email'); valid = false; }
  if (pwd.length < 1) { setFieldError('fg-login-pwd','err-login-pwd'); valid = false; }
  if (!valid) return;

  const btn = document.getElementById('btn-login');
  setLoading(btn, true);
  await delay(800);

  const user = DB.findUser(email);
  const pwdHash = await Security.hashPassword(pwd);
  if (!user || user.passwordHash !== pwdHash) {
    setFieldError('fg-login-pwd','err-login-pwd');
    document.getElementById('err-login-pwd').textContent = 'Email ou mot de passe incorrect';
    setLoading(btn, false); return;
  }

  setLoading(btn, false);
  Session.set(user);
  closeModal('auth-modal');
  updateNavAuth();
  toast(`Bienvenue ${Security.escapeHtml(user.prenom)} ! 👋`, 'success');

  if (App.pendingAction) { App.pendingAction(); App.pendingAction = null; }
}

// ─── REGISTER ────────────────────────────────────────
async function submitRegister() {
  if (!Security.checkRateLimit('register', 3, 300000)) {
    toast('Trop de tentatives. Attendez 5 minutes.', 'error'); return;
  }
  const prenom = Security.sanitize(document.getElementById('reg-prenom').value);
  const nom = Security.sanitize(document.getElementById('reg-nom').value);
  const email = document.getElementById('reg-email').value.trim().toLowerCase();
  const tel = document.getElementById('reg-tel').value.trim();
  const pwd = document.getElementById('reg-pwd').value;
  const pwd2 = document.getElementById('reg-pwd2').value;
  const terms = document.getElementById('terms-check').checked;
  let valid = true;

  if (prenom.length < 2) { setFieldError('fg-reg-prenom','err-reg-prenom'); valid = false; }
  if (nom.length < 2) { setFieldError('fg-reg-nom','err-reg-nom'); valid = false; }
  if (!Security.isValidEmail(email)) { setFieldError('fg-reg-email','err-reg-email'); valid = false; }
  if (!Security.isValidPhone(tel)) { setFieldError('fg-reg-tel','err-reg-tel'); valid = false; }
  if (!Security.isStrongPassword(pwd)) { setFieldError('fg-reg-pwd','err-reg-pwd'); valid = false; }
  if (pwd !== pwd2) { setFieldError('fg-reg-pwd2','err-reg-pwd2'); document.getElementById('err-reg-pwd2').textContent = 'Les mots de passe ne correspondent pas'; valid = false; }
  if (!terms) { toast('Vous devez accepter les conditions d\'utilisation', 'warning'); valid = false; }
  if (!valid) return;

  if (DB.findUser(email)) {
    setFieldError('fg-reg-email','err-reg-email');
    document.getElementById('err-reg-email').textContent = 'Cet email est déjà utilisé';
    return;
  }

  const btn = document.getElementById('btn-register');
  setLoading(btn, true);
  await delay(1000);

  const pwdHash = await Security.hashPassword(pwd);
  const user = DB.addUser({ prenom, nom, email, tel, passwordHash: pwdHash });
  Session.set(user);
  setLoading(btn, false);
  closeModal('auth-modal');
  updateNavAuth();
  toast(`Compte créé avec succès ! Bienvenue ${Security.escapeHtml(prenom)} 🎉`, 'success');
  if (App.pendingAction) { App.pendingAction(); App.pendingAction = null; }
}

// ─── LOGOUT ──────────────────────────────────────────
function logout() {
  Session.destroy();
  updateNavAuth();
  showPage('home');
  toast('Vous êtes déconnecté', 'info');
}

// ─── SOCIAL AUTH (Demo) ──────────────────────────────
function socialAuth(provider) {
  toast(`Connexion via ${provider} — Disponible en version production`, 'info');
}

// ─── FORGOT PASSWORD ─────────────────────────────────
function openForgotModal() {
  closeModal('auth-modal');
  openModal('forgot-modal');
}
async function submitForgot() {
  const email = document.getElementById('forgot-email').value.trim();
  if (!Security.isValidEmail(email)) { setFieldError('fg-forgot-email','err-forgot-email'); return; }
  const btn = document.querySelector('#forgot-modal .btn-primary');
  setLoading(btn, true);
  await delay(1000);
  setLoading(btn, false);
  closeModal('forgot-modal');
  toast('Si cet email existe, un lien de réinitialisation a été envoyé.', 'success');
}

// ─── UPDATE NAV ───────────────────────────────────────
function updateNavAuth() {
  const session = Session.get();
  const navAuth = document.getElementById('nav-auth');
  const navUser = document.getElementById('nav-user');
  const userAvatar = document.getElementById('user-avatar');
  if (session) {
    navAuth.style.display = 'none';
    navUser.style.display = 'flex';
    userAvatar.textContent = (session.prenom || 'U')[0].toUpperCase();
    // Check for new messages
    const msgs = DB.getMessages().filter(m => m.toUserId === session.id && !m.read);
    document.getElementById('notif-dot').style.display = msgs.length ? '' : 'none';
  } else {
    navAuth.style.display = 'flex';
    navUser.style.display = 'none';
  }
}

// ─── LISTINGS NAVIGATION / FILTERING (now server-side) ──────────────────────────────
function switchListingTab(tab) {
  const url = new URL(window.location.href);
  url.searchParams.set('type', tab);
  url.searchParams.delete('offset'); // Reset pagination on tab change
  window.location.href = url.toString();
}

function switchSearchTab(tab) {
  const url = new URL(window.location.href);
  url.searchParams.set('type', tab);
  url.searchParams.delete('offset'); // Reset pagination on tab change
  window.location.href = url.toString();
}

function filterListings() {
  const url = new URL(window.location.href);
  url.searchParams.set('q', document.getElementById('search-input').value);
  url.searchParams.set('filter_type', document.getElementById('filter-type')?.value || '');
  url.searchParams.set('filter_ville', document.getElementById('filter-ville')?.value || '');
  url.searchParams.set('filter_budget', document.getElementById('filter-budget')?.value || '');
  url.searchParams.set('filter_offre', document.getElementById('filter-offre')?.value || '');
  url.searchParams.delete('offset'); // Reset pagination on filter change
  window.location.href = url.toString();
}

function resetFilters() {
  // Reset to current tab, clear other filters
  window.location.href = window.location.pathname + '?type=' + App.currentListingTab;
}

function loadMore() {
  const url = new URL(window.location.href);
  const currentOffset = parseInt(url.searchParams.get('offset') || '0');
  const limit = 6; // Assuming 6 listings per page, matches PHP limit
  url.searchParams.set('offset', currentOffset + limit);
  window.location.href = url.toString();
}

// ─── FAVORITES ────────────────────────────────────────
function toggleFav(listingId, el) {
  if (!Session.isAuthenticated()) {
    requireAuth(() => { /* No specific action after auth, just log in */ });
    return;
  }
  const session = Session.get();
  const added = DB.toggleFavorite(session.id, listingId);
  el.innerHTML = added ? '❤️' : '🤍';
  el.classList.toggle('active', added);
  toast(added ? 'Ajouté aux favoris ❤️' : 'Retiré des favoris', added ? 'success' : 'info');
}

// ─── DETAIL PAGE ─────────────────────────────────────
function openDetail(id) {
  const allListings = App.allListings; // Use the listings fetched by PHP
  const listing = allListings.find(l => l.id === id);
  if (!listing) { toast('Annonce introuvable', 'error'); return; }
  App.currentListing = listing;
  
  document.getElementById('detail-emoji').textContent = listing.emoji || '🏢';
  document.getElementById('detail-title').textContent = listing.title;
  document.getElementById('detail-loc').textContent = '📍 ' + listing.location;
  document.getElementById('detail-price').textContent = parseFloat(listing.price).toLocaleString('fr-FR') + ' FCFA';
  document.getElementById('detail-price-unit').textContent = listing.priceUnit;
  document.getElementById('detail-desc').textContent = listing.desc || '';
  document.getElementById('seller-name').textContent = listing.seller;
  document.getElementById('seller-since').textContent = listing.sellerSince;
  const av = document.getElementById('seller-avatar'); 
  av.textContent = (listing.seller || 'V')[0].toUpperCase();
  
  // Photo gallery (assuming photos are stored as JSON string in DB)
  const mainImg = document.getElementById('detail-main-img');
  const thumbs = document.getElementById('gallery-thumbs');
  const photos = JSON.parse(listing.photos) || [];
  mainImg.innerHTML = '';
  thumbs.innerHTML = '';
  if (photos && photos.length) {
    const mainPhoto = document.createElement('img'); 
    mainPhoto.src = photos[0];
    mainPhoto.alt = listing.title;
    // Ensure image styling is applied correctly
    mainPhoto.style.cssText = 'width:100%;height:100%;object-fit:cover;position:absolute;inset:0;';
    mainImg.appendChild(mainPhoto);
    photos.forEach((ph, i) => {
      const t = document.createElement('div');
      t.className = 'gallery-thumb' + (i===0?' active':'');
      t.innerHTML = `<img src="${Security.escapeHtml(ph)}" alt="Photo ${i+1}">`;
      t.onclick = () => {
        mainPhoto.src = ph;
        document.querySelectorAll('.gallery-thumb').forEach(th => th.classList.remove('active'));
        t.classList.add('active');
      };
      thumbs.appendChild(t);
    });
  } else { 
    const emojiEl = document.createElement('span');
    emojiEl.id = 'detail-emoji';
    emojiEl.textContent = listing.emoji || '🏢';
    emojiEl.style.fontSize = '5rem';
    mainImg.appendChild(emojiEl);
  }

  // Tags
  const tagsEl = document.getElementById('detail-tags');
  const chips = JSON.parse(listing.chips) || [];
  tagsEl.innerHTML = [ // Assuming chips are stored as JSON string in DB
    listing.badge, listing.subcat, ...chips
  ].filter(Boolean).map(t => `<span class="detail-tag">${Security.escapeHtml(t)}</span>`).join('');
  
  // Contact info
  document.getElementById('call-number').textContent = listing.contact || '+225 XX XX XX XX';
  document.getElementById('call-number').dataset.number = listing.contact || '';
  document.getElementById('call-name').textContent = listing.seller;

  // Fav button
  const session = Session.get();
  const isFav = session && DB.getFavorites(session.id).includes(id);
  document.getElementById('detail-fav-btn').textContent = isFav ? '❤️' : '🤍';
  
  showPage('detail');
}

function toggleDetailFav() {
  if (!Session.isAuthenticated()) { requireAuth(() => {}); return; }
  if (!App.currentListing) return;
  const session = Session.get();
  const added = DB.toggleFavorite(session.id, App.currentListing.id);
  document.getElementById('detail-fav-btn').textContent = added ? '❤️' : '🤍';
  toast(added ? 'Ajouté aux favoris ❤️' : 'Retiré des favoris', added ? 'success' : 'info');
}

function reportListing() {
  toast('Signalement envoyé à notre équipe. Merci !', 'success');
}

// ─── MESSAGE MODAL ───────────────────────────────────
function openMessageModal() {
  if (!Session.isAuthenticated()) { requireAuth(() => openMessageModal()); return; }
  if (!App.currentListing) return;
  document.getElementById('msg-modal-title').textContent = '💬 Message au propriétaire';
  document.getElementById('msg-annonce-info').textContent = `Annonce : ${Security.escapeHtml(App.currentListing.title)}`;
  const thread = document.getElementById('message-thread');
  thread.innerHTML = `<div class="msg-bubble recv">Bonjour, je suis intéressé(e) par votre annonce. Est-elle toujours disponible ?<div class="msg-time">Suggestion</div></div>`;
  openModal('message-modal');
}

function openCallModal() {
  if (!Session.isAuthenticated()) { requireAuth(() => openCallModal()); return; }
  openModal('call-modal');
}

function sendMessage() {
  const input = document.getElementById('msg-input');
  const msg = Security.sanitize(input.value);
  if (!msg) return;
  const thread = document.getElementById('message-thread');
  const bubble = document.createElement('div');
  bubble.className = 'msg-bubble sent';
  bubble.innerHTML = `${Security.escapeHtml(msg)}<div class="msg-time">À l'instant</div>`;
  thread.appendChild(bubble);
  thread.scrollTop = thread.scrollHeight;
  input.value = '';

  if (Session.isAuthenticated() && App.currentListing) {
    const session = Session.get();
    DB.addMessage({ from: session.id, to: App.currentListing.userId, listingId: App.currentListing.id, content: msg, toUserId: App.currentListing.userId });
  }

  setTimeout(() => {
    const reply = document.createElement('div');
    reply.className = 'msg-bubble recv';
    reply.innerHTML = `Merci pour votre message ! Je reviendrai vers vous très rapidement. 📞<div class="msg-time">Maintenant</div>`;
    thread.appendChild(reply);
    thread.scrollTop = thread.scrollHeight;
  }, 1500);
}

// ─── CONTACT GENERAL ─────────────────────────────────
function openContactGeneral() {
  if (Session.isAuthenticated()) {
    const s = Session.get();
    document.getElementById('contact-nom').value = Security.escapeHtml((s.prenom||'') + ' ' + (s.nom||''));
    document.getElementById('contact-email').value = Security.escapeHtml(s.email||'');
  }
  openModal('contact-modal');
}

async function submitContact() {
  const nom = Security.sanitize(document.getElementById('contact-nom').value);
  const email = document.getElementById('contact-email').value.trim();
  const sujet = document.getElementById('contact-sujet').value;
  const msg = Security.sanitize(document.getElementById('contact-msg').value);
  let valid = true;
  if (nom.length < 2) { setFieldError('fg-contact-nom','err-contact-nom'); valid = false; }
  if (!Security.isValidEmail(email)) { setFieldError('fg-contact-email','err-contact-email'); valid = false; }
  if (!sujet) { setFieldError('fg-contact-sujet','err-contact-sujet'); valid = false; }
  if (msg.length < 20) { setFieldError('fg-contact-msg','err-contact-msg'); valid = false; }
  if (!valid) return;
  const btn = document.querySelector('#contact-modal .btn-primary');
  setLoading(btn, true);
  await delay(1000);
  setLoading(btn, false);
  closeModal('contact-modal');
  toast('Message envoyé ! Nous vous répondrons sous 24h.', 'success');
}

// ─── PUBLISH FLOW ─────────────────────────────────────
function openPublishModal() {
  App.currentPublishStep = 1;
  App.selectedCategory = null;
  App.uploadedPhotos = [];
  App.selectedPlan = { plan: 'starter', price: 5000 };
  resetPublishForm();
  goToPublishStep(1);
  openModal('publish-modal');
}

function resetPublishForm() {
  ['pub-titre','pub-prix','pub-surface','pub-desc','pub-commune','pub-contact'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  ['pub-offre-type','pub-subcat','pub-ville','pub-pieces','pub-meuble'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  document.getElementById('photo-preview-grid').innerHTML = '';
  document.getElementById('desc-counter').textContent = '0/2000';
  // Reset plan selection
  document.querySelectorAll('#pub-plans .plan-card').forEach(c => c.classList.remove('selected')); 
  const first = document.querySelector('#pub-plans .plan-card[data-plan="starter"]');
  if (first) first.classList.add('selected');
  // Reset category
  ['cat-immo','cat-veh','cat-btp'].forEach(id => {
    document.getElementById(id)?.style.removeProperty('border');
  });
}

function selectCategory(cat) {
  App.selectedCategory = cat;
  ['immo','veh','btp','tech'].forEach(c => {
    const el = document.getElementById(`cat-${c}`);
    if (el) el.style.border = c === cat ? '2px solid var(--gold)' : '';
  });
  // Update subcategories
  const subcat = document.getElementById('pub-subcat');
  subcat.innerHTML = '<option value="">Sélectionnez</option>';
  SUBCATS[cat].forEach(s => {
    const opt = document.createElement('option');
    opt.value = s; opt.textContent = s;
    subcat.appendChild(opt);
  });
  
  // --- LOGIQUE DYNAMIQUE DES CHAMPS (AMÉLIORÉE) ---
  const titreInput = document.getElementById('pub-titre');
  const placeholders = {
    immo: 'Ex: Appartement 3 pièces meublé – Cocody',
    veh: 'Ex: Toyota Prado 2022 en location',
    btp: 'Ex: Location Pelle hydraulique 20T',
    tech: 'Ex: Électricien certifié - Yopougon'
  };
  titreInput.placeholder = placeholders[cat] || 'Titre de votre annonce';

  // 1. Cacher tous les groupes de champs
  document.querySelectorAll('.form-fields-group').forEach(group => {
    group.style.display = 'none';
  });

  // 2. Afficher le groupe correspondant
  const fieldsToShow = document.querySelector(`.form-fields-${cat}`);
  if (fieldsToShow) {
    fieldsToShow.style.display = 'block';
  }
  // --- FIN DE LA LOGIQUE DYNAMIQUE ---  

  // Mettre à jour les options qui varient
  const priceUnitSelect = document.getElementById('pub-offre-type');
  const s2Title = document.getElementById('pub-s2-title');
  const step2Label = document.getElementById('step-label-2');

  if (cat === 'tech') {
    priceUnitSelect.innerHTML = `<option value="service">Prestation de service</option>`;
    s2Title.textContent = 'Informations sur le technicien';
    step2Label.textContent = 'Profil';
  } else {
    priceUnitSelect.innerHTML = `<option value="">Sélectionnez</option><option value="location">Location</option><option value="vente">Vente</option>`;
    s2Title.textContent = 'Informations sur le bien';
    step2Label.textContent = 'Détails';
  }
}

function selectPlan(el) {
  document.querySelectorAll('#plans-grid .plan-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
}

function selectPubPlan(el) {
  document.querySelectorAll('#pub-plans .plan-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  const plan = el.dataset.plan;
  const price = parseInt(el.dataset.price);
  App.selectedPlan = { plan, price };
}

// STEP NAVIGATION
function goToPublishStep(step) {
  [1,2,3,4].forEach(s => {
    document.getElementById(`pub-s${s}`).style.display = s===step ? '' : 'none';
    const stepEl = document.getElementById(`pub-step-${s}`);
    stepEl.classList.remove('active','done');
    if (s < step) stepEl.classList.add('done');
    else if (s === step) stepEl.classList.add('active');
    if (s < 4) {
      const line = document.getElementById(`line-${s}-${s+1}`);
      if (line) line.classList.toggle('done', s < step);
    }
  });
  App.currentPublishStep = step;
  document.getElementById('pub-btn-back').style.display = step > 1 ? '' : 'none';
  const nextBtn = document.getElementById('pub-btn-next');
  nextBtn.style.display = step < 4 ? '' : 'none';
}

async function pubNextStep() {
  const step = App.currentPublishStep;
  if (step === 1 && !validatePubStep1()) return;
  if (step === 2 && !validatePubStep2()) return;
  if (step === 3) buildPublishSummary();
  if (step < 4) goToPublishStep(step + 1);
}

function pubPrevStep() {
  if (App.currentPublishStep > 1) goToPublishStep(App.currentPublishStep - 1);
}

function validatePubStep1() {
  let valid = true;
  if (!App.selectedCategory) { toast('Sélectionnez une catégorie', 'warning'); valid = false; }
  const offreType = document.getElementById('pub-offre-type').value;
  const subcat = document.getElementById('pub-subcat').value;
  if (!offreType) { setFieldError('fg-cat-type','err-pub-type'); valid = false; }
  else clearFieldError('fg-cat-type');
  if (!subcat) { setFieldError('fg-cat-sub','err-pub-subcat'); valid = false; }
  else clearFieldError('fg-cat-sub');
  return valid;
}

function validatePubStep2() {
  let valid = true;
  const titre = document.getElementById('pub-titre').value.trim();
  const ville = document.getElementById('pub-ville').value;
  const prix = document.getElementById('pub-prix').value;
  const desc = document.getElementById('pub-desc').value.trim();
  const contact = document.getElementById('pub-contact').value.trim();

  if (titre.length < 10 || titre.length > 150) { setFieldError('fg-pub-titre','err-pub-titre'); valid = false; } else clearFieldError('fg-pub-titre');
  if (!ville) { setFieldError('fg-pub-ville','err-pub-ville'); valid = false; } else clearFieldError('fg-pub-ville');
  if (!prix || isNaN(prix) || Number(prix) <= 0) { setFieldError('fg-pub-prix','err-pub-prix'); valid = false; } else clearFieldError('fg-pub-prix');
  if (desc.length < 50) { setFieldError('fg-pub-desc','err-pub-desc'); valid = false; } else clearFieldError('fg-pub-desc');
  if (!contact || contact.length < 8) { setFieldError('fg-pub-contact','err-pub-contact'); valid = false; } else clearFieldError('fg-pub-contact');

  // Update desc counter
  document.getElementById('pub-desc').addEventListener('input', function() {
    document.getElementById('desc-counter').textContent = this.value.length + '/2000';
  });
  return valid;
}

function buildPublishSummary() {
  const sel = document.querySelector('#pub-plans .plan-card.selected');
  App.selectedPlan = { plan: sel?.dataset.plan || 'starter', price: parseInt(sel?.dataset.price || '5000') };

  const titre = Security.sanitize(document.getElementById('pub-titre').value);
  const ville = document.getElementById('pub-ville').value;
  const commune = Security.sanitize(document.getElementById('pub-commune').value);
  const prix = document.getElementById('pub-prix').value;
  const desc = Security.sanitize(document.getElementById('pub-desc').value.substring(0, 200));

  const summaryEl = document.getElementById('pub-summary');
  summaryEl.innerHTML = `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;font-size:0.85rem">
      <div><span style="color:var(--muted)">Titre:</span><br><strong>${Security.escapeHtml(titre)}</strong></div>
      <div><span style="color:var(--muted)">Localisation:</span><br><strong>${Security.escapeHtml(ville + (commune ? ', '+commune : ''))}</strong></div>
      <div><span style="color:var(--muted)">Prix:</span><br><strong>${Number(prix).toLocaleString('fr-FR')} FCFA</strong></div>
      <div><span style="color:var(--muted)">Photos:</span><br><strong>${App.uploadedPhotos.length} photo(s)</strong></div>
    </div>
    <div style="margin-top:0.75rem;font-size:0.82rem;color:var(--muted);line-height:1.5">${Security.escapeHtml(desc)}${desc.length >= 200 ? '...' : ''}</div>`;

  const payEl = document.getElementById('pub-payment-summary');
  const planNames = { starter: 'Starter – 30 jours', pro: 'Pro – 60 jours', business: 'Business – 90 jours' };
  payEl.innerHTML = `
    <div class="payment-row"><span>Publication – ${planNames[App.selectedPlan.plan]||'Starter'}</span><span class="amount">${App.selectedPlan.price.toLocaleString('fr-FR')} FCFA</span></div>
    <div class="payment-row"><span>Frais de service</span><span class="amount">0 FCFA</span></div>
    <div class="payment-row"><span style="font-weight:700">Total à payer</span><span class="amount" style="font-size:1.2rem">${App.selectedPlan.price.toLocaleString('fr-FR')} FCFA</span></div>`;
}

// ─── PHOTO UPLOAD ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  const input = document.getElementById('photo-input');
  const zone = document.getElementById('upload-zone');
  if (!input || !zone) return;

  input.addEventListener('change', handleFileSelect);

  zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
  zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
  zone.addEventListener('drop', e => {
    e.preventDefault();
    zone.classList.remove('drag-over');
    const files = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
    processFiles(files);
  });
});

function handleFileSelect(e) {
  processFiles(Array.from(e.target.files));
  e.target.value = '';
}

function processFiles(files) {
  const allowed = ['image/jpeg','image/png','image/webp'];
  const maxSize = 5 * 1024 * 1024;
  const maxPhotos = 10;

  files.forEach(file => {
    if (App.uploadedPhotos.length >= maxPhotos) { toast(`Maximum ${maxPhotos} photos`, 'warning'); return; }
    if (!allowed.includes(file.type)) { toast(`${file.name}: type de fichier non supporté`, 'error'); return; }
    if (file.size > maxSize) { toast(`${file.name}: fichier trop lourd (max 5MB)`, 'error'); return; }
    const reader = new FileReader();
    reader.onload = e => {
      // Validate it's actually an image
      const img = new Image();
      img.onload = () => {
        App.uploadedPhotos.push(e.target.result);
        renderPhotoPreview();
      };
      img.onerror = () => toast(`${file.name}: fichier invalide`, 'error');
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  });
}

function renderPhotoPreview() {
  const grid = document.getElementById('photo-preview-grid');
  grid.innerHTML = App.uploadedPhotos.map((ph, i) => `
    <div class="photo-preview">
      <img src="${ph}" alt="Photo ${i+1}">
      ${i===0 ? '<div class="main-badge">Principal</div>' : ''}
      <button class="remove-photo" onclick="removePhoto(${i})" type="button">✕</button>
    </div>`).join('');
}

function removePhoto(idx) {
  App.uploadedPhotos.splice(idx, 1);
  renderPhotoPreview();
}

// ─── PAYSTACK PAYMENT ─────────────────────────────────
async function initiatePaystackPayment() { // NOUVEAU FLUX DE PAIEMENT CÔTÉ SERVEUR
  if (!Session.isAuthenticated()) {
    toast('Connectez-vous pour payer', 'error');
    return;
  }
  const session = Session.get();
  const btn = document.getElementById('btn-pay-publish') || document.getElementById('btn-pay-final');
  setLoading(btn, true);

  try {
    // Étape 1 : Sauvegarder les données de l'annonce en session PHP avant de rediriger vers le paiement.
    // Cela garantit que si le paiement réussit, nous aurons les données pour créer l'annonce.
    const listingData = collectListingData(); // Fonction pour rassembler les données du formulaire
    const saveResponse = await fetch('save_listing_to_session.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(listingData)
    });
    const saveResult = await saveResponse.json();

    if (!saveResult.success) {
      throw new Error(saveResult.message || 'Erreur lors de la sauvegarde de l\'annonce avant paiement.');
    }

    // Étape 2 : Créer un formulaire dynamique en mémoire pour envoyer les données à initialize_payment.php.
    // Cette méthode est plus propre qu'une redirection avec des paramètres GET.
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'initialize_payment.php'; // Le script PHP qui initie la redirection vers Paystack
    form.style.display = 'none'; // Le formulaire est invisible

    // Créer les champs (inputs) pour le formulaire
    const data = {
      email: session.email,
      category: App.selectedCategory, // La catégorie est utilisée pour déterminer le prix côté serveur
      csrf_token: App.csrfToken // Pour une sécurité renforcée
    };

    for (const key in data) {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = key;
      input.value = data[key];
      form.appendChild(input);
    }

    // Ajouter le formulaire à la page et le soumettre pour déclencher la redirection
    document.body.appendChild(form);
    form.submit();

  } catch (error) {
    console.error('ERREUR FLUX DE PAIEMENT:', error);
    alert("Une erreur technique est survenue. Le paiement n'a pas pu être initié.");
    setLoading(btn, false);
  }
}

/**
 * Rassemble toutes les données du formulaire de publication dans un objet.
 * @returns {object} Les données de l'annonce.
 */
function collectListingData() {
  const session = Session.get();
  const listingData = {
    user_id: session.id,
    title: document.getElementById('pub-titre').value,
    description: document.getElementById('pub-desc').value,
    price: document.getElementById('pub-prix').value,
    location: `${document.getElementById('pub-ville').value}, ${document.getElementById('pub-commune').value}`,
    category: App.selectedCategory,
    subcat: document.getElementById('pub-subcat').value,
    badge: document.getElementById('pub-offre-type').value,
    contact: document.getElementById('pub-contact').value,
    plan: App.selectedPlan.plan,
    photos: App.uploadedPhotos,
    details: {} // Pour les champs spécifiques à la catégorie
  };

  // Exemple pour la catégorie 'immo'
  if (listingData.category === 'immo') {
    listingData.details.surface = document.getElementById('pub-immo-surface')?.value;
    listingData.details.pieces = document.getElementById('pub-immo-pieces')?.value;
    listingData.details.etage = document.getElementById('pub-immo-etage')?.value;
  }
  return listingData;
}
// ─── CONFETTI ─────────────────────────────────────────
function launchConfetti() {
  const container = document.getElementById('confetti');
  container.innerHTML = '';
  const colors = ['var(--gold)','#1D9E75','#378ADD','#E5A03A','#F0D080'];
  for (let i = 0; i < 60; i++) {
    const piece = document.createElement('div');
    piece.className = 'confetti-piece';
    piece.style.cssText = `
      left: ${Math.random()*100}vw;
      background: ${colors[Math.floor(Math.random()*colors.length)]};
      width: ${4 + Math.random()*6}px;
      height: ${4 + Math.random()*6}px;
      border-radius: ${Math.random() > 0.5 ? '50%' : '0'};
      animation-duration: ${1.5 + Math.random()*2}s;
      animation-delay: ${Math.random()*0.5}s;`;
    container.appendChild(piece);
  }
  setTimeout(() => container.innerHTML = '', 4000);
}

// ─── DASHBOARD ────────────────────────────────────────
function openDashboard(tab) {
  if (!Session.isAuthenticated()) { requireAuth(() => openDashboard(tab)); return; }
  App.dashTab = tab || 'annonces';
  showPage('dashboard');
}

function switchDashTab(tab) {
  App.dashTab = tab;
  ['annonces','favoris','messages','profil'].forEach(t => {
    document.getElementById(`dtab-${t}`)?.classList.toggle('active', t===tab);
  });
  renderDashboard();
}

function renderDashboard() {
  if (!Session.isAuthenticated()) return;
  const session = Session.get();
  document.getElementById('dash-welcome').textContent = `Bonjour, ${Security.escapeHtml(session.prenom || 'Utilisateur')} 👋`;

  const tab = App.dashTab;
  const content = document.getElementById('dash-content');

  if (tab === 'annonces') {
    const myListings = DB.getUserListings(session.id);
    const stats = `
      <div class="dash-stats">
        <div class="dash-stat"><div class="dash-stat-label">Annonces actives</div><div class="dash-stat-val" style="color:var(--immo)">${myListings.filter(l=>l.status==='active').length}</div></div>
        <div class="dash-stat"><div class="dash-stat-label">Total annonces</div><div class="dash-stat-val">${myListings.length}</div></div>
        <div class="dash-stat"><div class="dash-stat-label">Vues totales</div><div class="dash-stat-val" style="color:var(--veh)">${myListings.reduce((a,l)=>a+(l.views||0),0)}</div></div>
        <div class="dash-stat"><div class="dash-stat-label">Messages reçus</div><div class="dash-stat-val" style="color:var(--gold)">${DB.getMessages().filter(m=>m.toUserId===session.id).length}</div></div>
      </div>`;

    const listingsHtml = myListings.length === 0 ? `
      <div style="text-align:center;padding:3rem;color:var(--muted)">
        <div style="font-size:3rem;margin-bottom:1rem">📋</div>
        <p>Vous n'avez pas encore d'annonces.</p>
        <button class="btn btn-primary" style="margin-top:1rem" onclick="requireAuth(()=>openPublishModal())">+ Publier ma première annonce</button>
      </div>` : `
      <h3 style="font-family:Inter,sans-serif;font-size:1rem;font-weight:700;margin-bottom:1.25rem">Mes annonces</h3>
      <div class="dash-annonces">
        ${myListings.map(l => `
          <div class="ann-row" onclick="openDetail('${l.id}')">
            <div class="ann-thumb">${l.photos?.length ? `<img src="${Security.escapeHtml(l.photos[0])}" alt="">` : Security.escapeHtml(l.emoji||'🏠')}</div>
            <div class="ann-info">
              <div class="ann-title">${Security.escapeHtml(l.title)}</div>
              <div class="ann-meta">📍 ${Security.escapeHtml(l.location)} · ${new Date(l.createdAt).toLocaleDateString('fr-FR')}</div>
            </div>
            <span class="ann-status ${l.status==='active'?'status-active':'status-pending'}">${l.status==='active'?'Active':'En attente'}</span>
            <div class="ann-price">${Security.escapeHtml(l.price)} FCFA</div>
            <div class="ann-actions">
              <button class="btn btn-icon btn-ghost" onclick="event.stopPropagation(); openDetail('${l.id}')" title="Voir">👁</button>
              <button class="btn btn-icon btn-danger" onclick="event.stopPropagation(); deleteListing('${l.id}')" title="Supprimer">🗑</button>
            </div>
          </div>`).join('')}
      </div>`;

    content.innerHTML = stats + listingsHtml;
  } else if (tab === 'favoris') {
    const favIds = DB.getFavorites(session.id);
    const allListings = App.allListings; // Use listings from PHP
    const favListings = allListings.filter(l => favIds.includes(l.id));
    content.innerHTML = favListings.length === 0 ? `
      <div style="text-align:center;padding:3rem;color:var(--muted)">
        <div style="font-size:3rem;margin-bottom:1rem">❤️</div>
        <p>Aucun favori pour le moment.</p>
        <button class="btn btn-ghost" style="margin-top:1rem" onclick="showPage('home')">Parcourir les annonces</button>
      </div>` : `
      <div class="card-grid">${favListings.map(l => `
        <div class="listing-card" onclick="openDetail('${l.id}');showPage('detail')">
          <div class="card-thumb ${l.type}">${l.photos?.length ? `<img src="${Security.escapeHtml(l.photos[0])}" alt="${Security.escapeHtml(l.title)}">` : `<span>${Security.escapeHtml(l.emoji||'🏠')}</span>`}
            <span class="card-badge ${Security.escapeHtml(l.badgeClass)}">${Security.escapeHtml(l.badge)}</span>
          </div>
          <div class="card-body">
            <div class="card-title">${Security.escapeHtml(l.title)}</div>
            <div class="card-location">📍 ${Security.escapeHtml(l.location)}</div>
            <div class="card-footer"><div class="card-price">${Security.escapeHtml(l.price)} FCFA</div></div>
          </div>
        </div>`).join('')}</div>`;
  } else if (tab === 'messages') {
    const msgs = DB.getMessages().filter(m => m.toUserId === session.id || m.from === session.id);
    content.innerHTML = msgs.length === 0 ? `
      <div style="text-align:center;padding:3rem;color:var(--muted)">
        <div style="font-size:3rem;margin-bottom:1rem">💬</div>
        <p>Aucun message pour le moment.</p>
      </div>` : `
      <div class="dash-annonces">${msgs.map(m => `
        <div class="ann-row">
          <div class="ann-thumb">💬</div>
          <div class="ann-info">
            <div class="ann-title">${Security.escapeHtml(m.content.substring(0,60))}${m.content.length>60?'...':''}</div>
            <div class="ann-meta">${new Date(m.createdAt).toLocaleString('fr-FR')}</div>
          </div>
          <span class="ann-status status-active">Reçu</span>
        </div>`).join('')}</div>`;
  } else if (tab === 'profil') {
    content.innerHTML = `
      <div style="max-width:500px">
        <h3 style="font-family:Inter,sans-serif;margin-bottom:1.5rem">Mon profil</h3>
        <div class="form-row">
          <div class="form-group"><label class="form-label">Prénom</label><input type="text" class="form-input" value="${Security.escapeHtml(session.prenom||'')}" id="prof-prenom"></div>
          <div class="form-group"><label class="form-label">Nom</label><input type="text" class="form-input" value="${Security.escapeHtml(session.nom||'')}" id="prof-nom"></div>
        </div>
        <div class="form-group"><label class="form-label">Email</label><input type="email" class="form-input" value="${Security.escapeHtml(session.email||'')}" disabled style="opacity:0.6"></div>
        <div class="form-group"><label class="form-label">Téléphone</label><input type="tel" class="form-input" value="${Security.escapeHtml(session.tel||'')}" id="prof-tel"></div>
        <button class="btn btn-primary" onclick="saveProfile()">Enregistrer les modifications</button>
      </div>`;
  }
}

function saveProfile() {
  toast('Profil mis à jour avec succès ✅', 'success');
}

function deleteListing(id) {
  if (!confirm('Supprimer cette annonce ? Cette action est irréversible.')) return;
  const listings = DB.getListings();
  const idx = listings.findIndex(l => l.id === id);
  if (idx >= 0) { listings.splice(idx, 1); DB.saveListings(listings); }
  toast('Annonce supprimée', 'success');
  renderDashboard();
};

// ─── UTILITY FUNCTIONS ───────────────────────────────
const setFieldError = (groupId, errorId) => {
  document.getElementById(groupId)?.classList.add('has-error');
  document.getElementById(errorId)?.style && (document.getElementById(errorId).style.display = 'block');
}
function clearFieldError(groupId) {
  document.getElementById(groupId)?.classList.remove('has-error');
}
function setLoading(btn, loading) {
  if (!btn) return;
  btn.classList.toggle('loading', loading);
  btn.disabled = loading;
}
function delay(ms) { return new Promise(r => setTimeout(r, ms)); }

// ─── INITIALIZATION ───────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  setTimeout(() => {
    document.getElementById('app-loader').classList.add('hide');
    setTimeout(() => document.getElementById('app-loader').style.display = 'none', 500);
  }, 1600);

  // Le "PHP to JS Data Bridge" est maintenant dans locaplus.php
  // On utilise la variable `phpData` qui a été créée.
  App.currentListingTab = phpData.currentListingType || 'immo';
  App.currentSearchTab = phpData.currentListingType || 'immo';
  App.allListings = phpData.allListings || [];
  App.paystackPublicKey = phpData.paystackPublicKey;
  App.csrfToken = phpData.csrfToken; // Récupère le token CSRF

  // Initial setup for search input placeholder based on current tab
  const ph = { immo:'Appartement 3P à Cocody...', veh:'Toyota Prado 2022 location...', btp:'Pelle hydraulique 20T...', tech:'Électricien qualifié...' };
  document.getElementById('search-input').placeholder = ph[App.currentSearchTab];
  updateNavAuth();
});