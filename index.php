<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-Content-Type-Options" content="nosniff">
<meta http-equiv="X-Frame-Options" content="DENY">
<meta http-equiv="X-XSS-Protection" content="1; mode=block">
<meta name="referrer" content="strict-origin-when-cross-origin">
<meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' 'unsafe-inline' https://js.paystack.co https://fonts.googleapis.com; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src https://fonts.gstatic.com; img-src 'self' data: blob:; connect-src 'self' https://api.paystack.co;">
<title>LocaPlus — Plateforme Multiservices</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://js.paystack.co/v1/inline.js"></script>
<style>
*{margin:0;padding:0;box-sizing:border-box}
:root{
  --gold:#007AFF;--gold-light:#58A6FF;--gold-dim:rgba(0,122,255,0.1);
  --dark:#FFFFFF;--dark2:#F8F9FA;--dark3:#E9ECEF;--dark4:#DEE2E6;
  --text:#212529;--muted:#6C757D;--muted2:#ADB5BD;
  --immo:#1D9E75;--veh:#378ADD;--btp:#BA7517;--tech:#6F42C1;
  --danger:#E24B4A;--success:#1D9E75;
  --radius:10px;--radius-lg:16px;--radius-xl:24px;
  --shadow:0 2px 8px rgba(0,0,0,0.06), 0 8px 24px rgba(0,0,0,0.08);
  --transition:0.22s cubic-bezier(0.4,0,0.2,1);
}
html{scroll-behavior:smooth}
body{background:var(--dark2);color:var(--text);font-family:'Inter',sans-serif;overflow-x:hidden;min-height:100vh;-webkit-font-smoothing:antialiased}
img{max-width:100%;height:auto;display:block}
a{text-decoration:none;color:inherit}
button{font-family:'Inter',sans-serif}
input,select,textarea{font-family:'Inter',sans-serif}

/* ───── SCROLLBAR ───── */
::-webkit-scrollbar{width:8px;height:8px}
::-webkit-scrollbar-track{background:var(--dark2)}
::-webkit-scrollbar-thumb{background:var(--dark4);border-radius:3px}
::-webkit-scrollbar-thumb:hover{background:var(--gold)}

/* ───── TOAST ───── */
#toast-container{position:fixed;bottom:2rem;right:2rem;z-index:9999;display:flex;flex-direction:column;gap:0.75rem;pointer-events:none}
.toast{display:flex;align-items:center;gap:0.75rem;background:var(--dark3);border:0.5px solid rgba(255,255,255,0.1);border-radius:var(--radius);padding:0.85rem 1.25rem;min-width:280px;max-width:380px;box-shadow:var(--shadow);opacity:0;transform:translateX(30px);transition:all var(--transition);pointer-events:all}
.toast.show{opacity:1;transform:translateX(0);background:var(--dark);border-color:var(--dark4)}
.toast.success{border-left:3px solid var(--success)}
.toast.error{border-left:3px solid var(--danger)}
.toast.info{border-left:3px solid var(--veh)}
.toast-icon{font-size:1.1rem;flex-shrink:0}
.toast-msg{font-size:0.88rem;line-height:1.5}

/* ───── LOADER ───── */
#app-loader{position:fixed;inset:0;background:var(--dark);z-index:9998;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:1rem;transition:opacity 0.5s}
#app-loader.hide{opacity:0;pointer-events:none}
.loader-logo{font-family:'Inter',sans-serif;font-size:2rem;font-weight:700;color:var(--text)}
.loader-logo span{color:var(--gold)}
.loader-bar{width:200px;height:2px;background:var(--dark3);border-radius:2px;overflow:hidden}
.loader-progress{height:100%;background:var(--gold);border-radius:2px;animation:loadBar 1.5s ease forwards}
@keyframes loadBar{from{width:0}to{width:100%}}

/* ───── MODAL BASE ───── */
.modal-overlay{position:fixed;inset:0;background:rgba(33,37,41,0.6);backdrop-filter:blur(4px);z-index:1000;display:flex;align-items:center;justify-content:center;padding:1rem;opacity:0;pointer-events:none;transition:opacity var(--transition)}
.modal-overlay.open{opacity:1;pointer-events:all}
.modal{background:var(--dark);border:1px solid var(--dark4);border-radius:var(--radius-xl);width:100%;max-width:520px;max-height:90vh;overflow-y:auto;transform:translateY(20px) scale(0.97);transition:transform var(--transition)}
.modal-overlay.open .modal{transform:translateY(0) scale(1)}
.modal-header{padding:1.5rem 2rem 1rem;display:flex;align-items:center;justify-content:space-between;border-bottom:1px solid var(--dark3)}
.modal-title{font-family:'Inter',sans-serif;font-size:1.2rem;font-weight:700}
.modal-close{width:32px;height:32px;border-radius:50%;background:var(--dark3);border:none;color:var(--muted);cursor:pointer;font-size:1rem;display:flex;align-items:center;justify-content:center;transition:all var(--transition);flex-shrink:0}
.modal-close:hover{background:var(--dark4);color:var(--text)}
.modal-body{padding:1.5rem 2rem}
.modal-footer{padding:1rem 2rem 1.5rem;display:flex;gap:0.75rem;justify-content:flex-end;border-top:1px solid var(--dark3)}

/* ───── FORM ELEMENTS ───── */
.form-group{margin-bottom:1.2rem}
.form-label{display:block;font-size:0.82rem;color:var(--muted);margin-bottom:0.4rem;font-weight:500}
.form-label .required{color:var(--danger)}
.form-input,.form-select,.form-textarea{width:100%;background:var(--dark2);border:1px solid var(--dark4);border-radius:var(--radius);color:var(--text);padding:0.75rem 1rem;font-size:0.9rem;outline:none;transition:border-color var(--transition)}
.form-input:focus,.form-select:focus,.form-textarea:focus{border-color:var(--gold);box-shadow:0 0 0 3px var(--gold-dim)}
.form-input::placeholder,.form-textarea::placeholder{color:var(--muted2)}
.form-select{cursor:pointer;appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%236C757D' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 1rem center}
.form-select option{background:var(--dark)}
.form-textarea{min-height:100px;resize:vertical;line-height:1.6}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
.form-error{font-size:0.78rem;color:var(--danger);margin-top:0.3rem;display:none}
.form-group.has-error .form-input,.form-group.has-error .form-select,.form-group.has-error .form-textarea{border-color:var(--danger)}
.form-group.has-error .form-error{display:block}

/* ───── BUTTONS ───── */
.btn{display:inline-flex;align-items:center;justify-content:center;gap:0.5rem;padding:0.65rem 1.4rem;border-radius:var(--radius);font-size:0.9rem;font-weight:500;cursor:pointer;transition:all var(--transition);border:none;white-space:nowrap}
.btn:disabled{opacity:0.5;cursor:not-allowed}
.btn-primary{background:var(--gold);color:var(--dark);font-weight:600}
.btn-primary:hover:not(:disabled){background:var(--gold-light);transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,122,255,0.25)}
.btn-secondary{background:var(--dark3);color:var(--text);border:1px solid var(--dark4)}
.btn-secondary:hover:not(:disabled){background:var(--dark4)}
.btn-ghost{background:transparent;color:var(--muted);border:1px solid var(--dark3)}
.btn-ghost:hover:not(:disabled){color:var(--text);border-color:var(--dark4)}
.btn-danger{background:rgba(226,75,74,0.1);color:var(--danger);border:1px solid rgba(226,75,74,0.2)}
.btn-danger:hover:not(:disabled){background:rgba(226,75,74,0.15)}
.btn-lg{padding:0.85rem 2rem;font-size:1rem;border-radius:14px}
.btn-full{width:100%}
.btn-icon{width:36px;height:36px;padding:0;border-radius:8px}
.btn-spinner{width:16px;height:16px;border:2px solid rgba(255,255,255,0.2);border-top-color:currentColor;border-radius:50%;animation:spin 0.7s linear infinite;display:none}
.btn.loading .btn-spinner{display:block}
.btn.loading .btn-text{display:none}
@keyframes spin{to{transform:rotate(360deg)}}

/* ───── NAV ───── */
#navbar{display:flex;align-items:center;justify-content:space-between;padding:1rem 2.5rem;background:rgba(255,255,255,0.85);backdrop-filter:blur(12px);position:sticky;top:0;z-index:500;border-bottom:1px solid var(--dark3);transition:all var(--transition)}
.nav-logo{font-family:'Inter',sans-serif;font-weight:700;font-size:1.45rem;letter-spacing:-0.02em;cursor:pointer}
.nav-logo span{color:var(--gold)}
.nav-links{display:flex;align-items:center;gap:1.75rem;list-style:none}
.nav-links a{font-size:0.87rem;color:var(--muted);font-weight:500;transition:color var(--transition);cursor:pointer}
.nav-links a:hover,.nav-links a.active{color:var(--text)}
.nav-right{display:flex;align-items:center;gap:0.75rem}
.nav-user{display:none;align-items:center;gap:0.75rem}
.user-avatar{width:34px;height:34px;border-radius:50%;background:var(--gold-dim);border:1.5px solid var(--gold);display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:600;color:var(--gold);cursor:pointer}
.nav-notif{position:relative}
.notif-dot{position:absolute;top:-2px;right:-2px;width:8px;height:8px;background:var(--danger);border-radius:50%;border:1.5px solid var(--dark2)}
.hamburger{display:none;flex-direction:column;gap:4px;cursor:pointer;padding:4px}
.hamburger span{display:block;width:22px;height:1.5px;background:var(--text);border-radius:2px;transition:all var(--transition)}

/* ───── HERO ───── */
#hero{min-height:95vh;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:6rem 2rem 4rem;position:relative;overflow:hidden;background:var(--dark)}
.hero-mesh{position:absolute;inset:0;pointer-events:none;overflow:hidden}
.hero-mesh::before{content:'';position:absolute;width:800px;height:800px;border-radius:50%;background:radial-gradient(circle,rgba(201,168,76,0.05) 0%,transparent 70%);top:-200px;left:50%;transform:translateX(-50%)}
.hero-mesh::after{content:'';position:absolute;inset:0;background:repeating-linear-gradient(0deg,transparent,transparent 80px,rgba(0,0,0,0.02) 80px,rgba(0,0,0,0.02) 81px),repeating-linear-gradient(90deg,transparent,transparent 80px,rgba(0,0,0,0.02) 80px,rgba(0,0,0,0.02) 81px)}
.hero-badge{display:inline-flex;align-items:center;gap:0.5rem;background:var(--gold-dim);border:1px solid var(--gold-dim);border-radius:100px;padding:0.4rem 1.1rem;font-size:0.78rem;font-weight:500;color:var(--gold);margin-bottom:2rem;letter-spacing:0.04em;text-transform:uppercase;animation:fadeUp 0.6s ease both}
#hero h1{font-family:'Inter',sans-serif;font-weight:700;font-size:clamp(2.5rem,5.5vw,4.5rem);line-height:1.06;letter-spacing:-0.03em;max-width:820px;margin-bottom:1.5rem;animation:fadeUp 0.6s 0.1s ease both}
#hero h1 em{font-style:normal;color:var(--gold)}
#hero p{font-size:1.05rem;color:var(--muted);max-width:580px;line-height:1.6;margin-bottom:3rem;animation:fadeUp 0.6s 0.2s ease both}
.hero-cta{display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;margin-bottom:4rem;animation:fadeUp 0.6s 0.3s ease both}
.hero-stats{display:flex;justify-content:space-around;flex-wrap:wrap;gap:2rem;animation:fadeUp 0.6s 0.4s ease both;max-width:700px;width:100%;margin:0 auto}
.stat-item{text-align:center;display:flex;flex-direction:column;align-items:center;justify-content:center}
.stat-num{font-family:'Inter',sans-serif;font-size:1.9rem;font-weight:700;color:var(--text)}
.stat-label{font-size:0.78rem;color:var(--muted);margin-top:0.15rem;letter-spacing:0.02em}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}

/* ───── SEARCH SECTION ───── */
#search-section{background:var(--dark2);border-top:1px solid var(--dark3);border-bottom:1px solid var(--dark3);padding:2.5rem}
.search-inner{max-width:1100px;margin:0 auto}
.search-tabs{display:flex;gap:0.5rem;margin-bottom:1.5rem;overflow-x:auto;padding-bottom:0.25rem}
.s-tab{display:inline-flex;align-items:center;gap:0.5rem;padding:0.55rem 1.2rem;border-radius:9px;border:1px solid var(--dark3);background:transparent;color:var(--muted);font-size:0.85rem;cursor:pointer;transition:all var(--transition);white-space:nowrap}
.s-tab.active{background:var(--gold-dim);border-color:var(--gold);color:var(--gold)}
.s-tab:hover:not(.active){border-color:var(--dark4);color:var(--text)}
.search-box{display:flex;align-items:center;gap:1rem;background:var(--dark);border:1px solid var(--dark4);border-radius:14px;padding:0.85rem 1.25rem;transition:border-color var(--transition)}
.search-box:focus-within{border-color:var(--gold)}
.search-box input{flex:1;background:transparent;border:none;outline:none;color:var(--text);font-size:0.95rem}
.search-box input::placeholder{color:var(--muted2)}
.search-filters{display:flex;gap:0.75rem;flex-wrap:wrap;margin-top:0.85rem}
.filter-select{background:var(--dark);border:1px solid var(--dark3);border-radius:8px;color:var(--muted);padding:0.4rem 0.75rem;font-size:0.82rem;cursor:pointer;outline:none;transition:border-color var(--transition);appearance:none;padding-right:1.8rem;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%236C757D' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 0.6rem center}
.filter-select option{background:var(--dark);color:var(--text)}

/* ───── SECTIONS SHARED ───── */
.section{padding:5rem 2.5rem}
.section-inner{max-width:1200px;margin:0 auto}
.section-tag{font-size:0.72rem;text-transform:uppercase;letter-spacing:0.12em;color:var(--gold);margin-bottom:0.6rem}
.section-title{font-family:'Inter',sans-serif;font-weight:700;font-size:clamp(1.6rem, 4vw, 2.2rem);letter-spacing:-0.02em;margin-bottom:0.75rem}
.section-sub{color:var(--muted);font-size:1rem;line-height:1.6;max-width:600px;margin-left:auto;margin-right:auto}

/* ───── SECTORS ───── */
.sector-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1.5rem;margin-top:3rem}
.sector-card{background:var(--dark);border:1px solid var(--dark3);border-radius:var(--radius-xl);padding:2rem;cursor:pointer;transition:all var(--transition);position:relative;overflow:hidden}
.sector-card::after{content:'';position:absolute;inset:0;opacity:0;transition:opacity var(--transition)}
.sector-card.immo::after{background:radial-gradient(circle at 0% 0%,rgba(29,158,117,0.07) 0%,transparent 55%)}
.sector-card.veh::after{background:radial-gradient(circle at 0% 0%,rgba(55,138,221,0.07) 0%,transparent 55%)}
.sector-card.btp::after{background:radial-gradient(circle at 0% 0%,rgba(186,117,23,0.07) 0%,transparent 55%)}
.sector-card.tech::after{background:radial-gradient(circle at 0% 0%,rgba(111,66,193,0.07) 0%,transparent 55%)}
.sector-card:hover{transform:translateY(-4px);border-color:var(--dark4)}
.sector-card:hover::after{opacity:1}
.sector-icon-wrap{width:52px;height:52px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;margin-bottom:1.5rem;position:relative;z-index:1}
.sector-card.immo .sector-icon-wrap{background:rgba(29,158,117,0.12)}
.sector-card.veh .sector-icon-wrap{background:rgba(55,138,221,0.12)}
.sector-card.btp .sector-icon-wrap{background:rgba(186,117,23,0.12)}
.sector-card.tech .sector-icon-wrap{background:rgba(111,66,193,0.12)}
.sector-card h3{font-family:'Inter',sans-serif;font-size:1.2rem;font-weight:700;margin-bottom:0.6rem;position:relative;z-index:1}
.sector-card p{color:var(--muted);font-size:0.87rem;line-height:1.6;margin-bottom:1.5rem;position:relative;z-index:1}
.sector-bottom{display:flex;align-items:flex-end;justify-content:space-between;position:relative;z-index:1}
.sector-count-big{font-family:'Inter',sans-serif;font-size:2rem;font-weight:700}
.sector-card.immo .sector-count-big{color:var(--immo)}
.sector-card.veh .sector-count-big{color:var(--veh)}
.sector-card.btp .sector-count-big{color:var(--btp)}
.sector-card.tech .sector-count-big{color:var(--tech)}
.sector-count-sub{font-size:0.75rem;color:var(--muted)}
.sector-arr{width:38px;height:38px;border-radius:50%;border:1px solid var(--dark3);display:flex;align-items:center;justify-content:center;color:var(--muted);transition:all var(--transition)}
.sector-card:hover .sector-arr{border-color:var(--gold);color:var(--gold);transform:translateX(3px)}

/* ───── LISTINGS ───── */
#listings{background:var(--dark2);border-top:1px solid var(--dark3)}
.listing-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:2.5rem;flex-wrap:wrap;gap:1rem}
.listing-tabs{display:flex;gap:0.4rem;overflow-x:auto}
.l-tab{padding:0.48rem 1rem;border-radius:8px;border:1px solid var(--dark3);background:transparent;color:var(--muted);font-size:0.84rem;cursor:pointer;transition:all var(--transition);white-space:nowrap}
.l-tab.active{background:var(--gold-dim);border-color:var(--gold);color:var(--gold);font-weight:500}
.card-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1.25rem}
.listing-card{background:var(--dark);border:1px solid var(--dark3);border-radius:var(--radius-lg);overflow:hidden;cursor:pointer;transition:all var(--transition)}
.listing-card:hover{border-color:var(--dark4);transform:translateY(-4px);box-shadow:var(--shadow)}
.card-thumb{height:180px;display:flex;align-items:center;justify-content:center;font-size:3rem;position:relative;overflow:hidden}
.card-thumb.immo{background:linear-gradient(135deg,#E8F5E9,#C8E6C9)}
.card-thumb.veh{background:linear-gradient(135deg,#E3F2FD,#BBDEFB)}
.card-thumb.btp{background:linear-gradient(135deg,#FFF3E0,#FFE0B2)}
.card-thumb.tech{background:linear-gradient(135deg,#F3E5F5,#E1BEE7)}
.card-thumb img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0}
.card-badge{position:absolute;top:0.75rem;left:0.75rem;padding:0.22rem 0.6rem;border-radius:6px;font-size:0.7rem;font-weight:500;letter-spacing:0.03em}
.badge-loc{background:rgba(29,158,117,0.15);color:#1D9E75;border:1px solid rgba(29,158,117,0.2)}
.badge-vte{background:rgba(55,138,221,0.15);color:#378ADD;border:1px solid rgba(55,138,221,0.2)}
.badge-btp{background:rgba(186,117,23,0.15);color:#BA7517;border:1px solid rgba(186,117,23,0.2)}
.badge-tech{background:rgba(111,66,193,0.15);color:#6F42C1;border:1px solid rgba(111,66,193,0.2)}
.card-fav{position:absolute;top:0.75rem;right:0.75rem;width:30px;height:30px;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.8rem;transition:all var(--transition)}
.card-fav:hover{background:rgba(201,168,76,0.2);transform:scale(1.15)}
.card-fav.active{background:rgba(226,75,74,0.2)}
.card-body{padding:1.1rem 1.25rem}
.card-title{font-family:'Inter',sans-serif;font-size:0.95rem;font-weight:600;margin-bottom:0.3rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.card-location{display:flex;align-items:center;gap:0.3rem;color:var(--muted);font-size:0.77rem;margin-bottom:0.85rem}
.card-footer{display:flex;align-items:center;justify-content:space-between}
.card-price{font-family:'Inter',sans-serif;font-size:1.05rem;font-weight:700;color:var(--gold)}
.card-price-unit{font-size:0.72rem;color:var(--muted);font-family:'Inter',sans-serif;font-weight:400;margin-left:2px}
.card-chips{display:flex;gap:0.45rem}
.chip{background:rgba(255,255,255,0.06);padding:0.2rem 0.5rem;border-radius:5px;font-size:0.72rem;color:var(--muted)}
.card-verified{position:absolute;bottom:0.75rem;right:0.75rem;display:flex;align-items:center;gap:0.25rem;font-size:0.7rem;color:var(--immo);background:rgba(29,158,117,0.1);padding:0.15rem 0.4rem;border-radius:4px;backdrop-filter:blur(2px)}

/* ───── PUBLISH SECTION ───── */
#publish{background:var(--dark);border-top:1px solid var(--dark3);border-bottom:1px solid var(--dark3)}
.publish-grid{display:grid;grid-template-columns:1fr 1.6fr;gap:4rem;margin-top:3rem;align-items:start}
.publish-left h3{font-family:'Inter',sans-serif;font-size:1.5rem;font-weight:700;margin-bottom:1rem}
.publish-left p{color:var(--muted);line-height:1.6;margin-bottom:2rem}
.publish-features{display:flex;flex-direction:column;gap:1rem}
.pub-feat{display:flex;align-items:flex-start;gap:1rem;padding:1.1rem;background:var(--dark2);border:1px solid var(--dark3);border-radius:var(--radius)}
.pub-feat-icon{width:38px;height:38px;border-radius:10px;background:var(--gold-dim);border:1px solid var(--gold-dim);display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.pub-feat-text h4{font-size:0.88rem;font-weight:600;margin-bottom:0.25rem}
.pub-feat-text p{font-size:0.8rem;color:var(--muted);line-height:1.5}
.plans-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-top:1.5rem}
.plan-card{background:var(--dark2);border:1px solid var(--dark3);border-radius:var(--radius-lg);padding:1.5rem;cursor:pointer;transition:all var(--transition);position:relative}
.plan-card:hover{border-color:var(--dark4);transform:translateY(-2px)}
.plan-card.selected{border-color:var(--gold);background:var(--gold-dim);border-width:1.5px}
.plan-popular{position:absolute;top:-10px;left:50%;transform:translateX(-50%);background:var(--gold);color:var(--dark);font-size:0.68rem;font-weight:600;padding:0.2rem 0.6rem;border-radius:100px;white-space:nowrap}
.plan-name{font-family:'Inter',sans-serif;font-size:0.95rem;font-weight:700;margin-bottom:0.4rem}
.plan-price{font-family:'Inter',sans-serif;font-size:1.6rem;font-weight:700;color:var(--gold)}
.plan-price span{font-size:0.78rem;color:var(--muted);font-family:'Inter',sans-serif;font-weight:400}
.plan-features{margin-top:1rem;display:flex;flex-direction:column;gap:0.4rem}
.plan-feat{font-size:0.78rem;color:var(--muted);display:flex;align-items:center;gap:0.4rem}
.plan-feat::before{content:'✓';color:var(--gold);font-weight:600}

/* ───── PHOTO UPLOAD ───── */
.upload-zone{border:1.5px dashed var(--dark4);border-radius:var(--radius-lg);padding:2.5rem;text-align:center;cursor:pointer;transition:all var(--transition);background:var(--dark2);position:relative}
.upload-zone:hover,.upload-zone.drag-over{border-color:var(--gold);background:var(--gold-dim)}
.upload-zone input[type="file"]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}
.upload-icon{font-size:2.5rem;margin-bottom:0.75rem}
.upload-text{color:var(--muted);font-size:0.88rem;line-height:1.6}
.upload-text strong{color:var(--gold)}
.photo-preview-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:0.75rem;margin-top:1rem}
.photo-preview{position:relative;aspect-ratio:1;border-radius:var(--radius);overflow:hidden;background:var(--dark4)}
.photo-preview img{width:100%;height:100%;object-fit:cover}
.photo-preview .remove-photo{position:absolute;top:4px;right:4px;width:22px;height:22px;background:rgba(0,0,0,0.7);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.7rem;cursor:pointer;transition:all var(--transition);border:none;color:var(--dark)}
.photo-preview .remove-photo:hover{background:var(--danger)}
.photo-preview .main-badge{position:absolute;bottom:4px;left:4px;background:var(--gold);color:var(--dark);font-size:0.62rem;font-weight:600;padding:0.15rem 0.4rem;border-radius:4px}

/* ───── STEPS ───── */
.steps-bar{display:flex;align-items:center;margin-bottom:2.5rem;gap:0;overflow-x:auto;padding-bottom:0.5rem}
.step{display:flex;align-items:center;gap:0.6rem;flex-shrink:0}
.step-num{width:30px;height:30px;border-radius:50%;border:1.5px solid var(--dark4);display:flex;align-items:center;justify-content:center;font-size:0.78rem;color:var(--muted);font-weight:600;transition:all var(--transition)}
.step.active .step-num{border-color:var(--gold);color:var(--gold);background:var(--gold-dim)}
.step.done .step-num{background:var(--gold);border-color:var(--gold);color:var(--dark);font-weight:700}
.step-label{font-size:0.8rem;color:var(--muted);font-weight:500;transition:all var(--transition)}
.step.active .step-label{color:var(--text)}
.step-line{flex:1;min-width:40px;height:1px;background:var(--dark3);margin:0 0.5rem}
.step-line.done{background:var(--gold)}

/* ───── FEATURES GRID ───── */
.feat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1.25rem;margin-top:3rem}
.feat-item{background:var(--dark);border:1px solid var(--dark3);border-radius:var(--radius-lg);padding:1.5rem;transition:all var(--transition)}
.feat-item:hover{border-color:var(--dark4);transform:translateY(-2px)}
.feat-icon{font-size:1.5rem;margin-bottom:1rem;color:var(--gold)}
.feat-item h4{font-family:'Inter',sans-serif;font-size:0.92rem;font-weight:600;margin-bottom:0.4rem}
.feat-item p{font-size:0.8rem;color:var(--muted);line-height:1.6}

/* ───── DASHBOARD ───── */
#dashboard{display:none}
.dash-header{padding:2rem 2.5rem;border-bottom:0.5px solid rgba(255,255,255,0.06)}
.dash-title{font-family:'Syne',sans-serif;font-size:1.5rem;font-weight:700}
.dash-tabs{display:flex;gap:0.4rem;padding:1.5rem 2.5rem;border-bottom:1px solid var(--dark3)}
.dash-tab{padding:0.5rem 1rem;border-radius:8px;border:1px solid var(--dark3);background:transparent;color:var(--muted);font-size:0.85rem;cursor:pointer;transition:all var(--transition)}
.dash-tab.active{background:var(--gold-dim);border-color:var(--gold);color:var(--gold)}
.dash-content{padding:2rem 2.5rem}
.dash-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2.5rem}
.dash-stat{background:var(--dark2);border:1px solid var(--dark3);border-radius:var(--radius-lg);padding:1.25rem}
.dash-stat-label{font-size:0.78rem;color:var(--muted);margin-bottom:0.4rem}
.dash-stat-val{font-family:'Inter',sans-serif;font-size:1.6rem;font-weight:700}
.dash-stat-sub{font-size:0.75rem;color:var(--immo);margin-top:0.2rem}
.dash-annonces{display:flex;flex-direction:column;gap:1rem}
.ann-row{display:flex;align-items:center;gap:1rem;padding:1rem 1.25rem;background:var(--dark);border:1px solid var(--dark3);border-radius:var(--radius-lg);transition:all var(--transition)}
.ann-row:hover{border-color:var(--dark4)}
.ann-thumb{width:60px;height:60px;border-radius:8px;background:var(--dark3);display:flex;align-items:center;justify-content:center;font-size:1.5rem;flex-shrink:0;overflow:hidden;border:1px solid var(--dark4)}
.ann-thumb img{width:100%;height:100%;object-fit:cover}
.ann-info{flex:1;min-width:0}
.ann-title{font-weight:600;font-size:0.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:0.25rem}
.ann-meta{font-size:0.78rem;color:var(--muted)}
.ann-status{padding:0.22rem 0.65rem;border-radius:100px;font-size:0.72rem;font-weight:500}
.status-active{background:rgba(29,158,117,0.1);color:var(--immo);border:1px solid rgba(29,158,117,0.2)}
.status-pending{background:rgba(186,117,23,0.1);color:var(--btp);border:1px solid rgba(186,117,23,0.2)}
.status-expired{background:rgba(226,75,74,0.1);color:var(--danger);border:1px solid rgba(226,75,74,0.15)}
.ann-price{font-family:'Inter',sans-serif;font-size:1rem;font-weight:700;color:var(--gold);text-align:right;white-space:nowrap}
.ann-actions{display:flex;gap:0.5rem}

/* ───── DETAIL PAGE ───── */
#detail-page{display:none;min-height:100vh;background:var(--dark)}
.detail-back{display:inline-flex;align-items:center;gap:0.5rem;color:var(--muted);font-size:0.88rem;cursor:pointer;padding:1.5rem 2.5rem;transition:color var(--transition)}
.detail-back:hover{color:var(--text)}
.detail-main{max-width:1100px;margin:0 auto;padding:0 2.5rem 5rem;display:grid;grid-template-columns:1fr 360px;gap:3rem;align-items:start}
.detail-gallery{border-radius:var(--radius-xl);overflow:hidden;background:var(--dark2);aspect-ratio:16/9;display:flex;align-items:center;justify-content:center;font-size:5rem;margin-bottom:1.5rem;position:relative}
.detail-gallery img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0}
.gallery-thumbs{display:flex;gap:0.75rem;overflow-x:auto}
.gallery-thumb{width:80px;height:60px;border-radius:8px;background:var(--dark2);flex-shrink:0;cursor:pointer;border:2px solid transparent;transition:all var(--transition);overflow:hidden;display:flex;align-items:center;justify-content:center;font-size:1.5rem}
.gallery-thumb.active{border-color:var(--gold)}
.gallery-thumb img{width:100%;height:100%;object-fit:cover}
.detail-title{font-family:'Inter',sans-serif;font-size:1.8rem;font-weight:700;letter-spacing:-0.02em;margin-bottom:0.5rem}
.detail-loc{display:flex;align-items:center;gap:0.4rem;color:var(--muted);margin-bottom:1.5rem}
.detail-tags{display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:2rem}
.detail-tag{background:var(--dark2);border:1px solid var(--dark3);border-radius:8px;padding:0.35rem 0.75rem;font-size:0.8rem;color:var(--muted)}
.detail-desc h3{font-family:'Inter',sans-serif;font-size:1rem;font-weight:700;margin-bottom:0.75rem}
.detail-desc p{color:var(--muted);line-height:1.6;font-size:0.92rem}
.detail-sidebar{position:sticky;top:90px}
.detail-price-card{background:var(--dark2);border:1px solid var(--dark3);border-radius:var(--radius-xl);padding:1.75rem;margin-bottom:1.25rem}
.detail-price{font-family:'Inter',sans-serif;font-size:2rem;font-weight:700;color:var(--gold);margin-bottom:0.25rem}
.detail-seller{display:flex;align-items:center;gap:0.85rem;padding:1rem;background:var(--dark3);border-radius:var(--radius);margin-bottom:1.25rem;border:1px solid var(--dark4)}
.seller-avatar{width:42px;height:42px;border-radius:50%;background:var(--gold-dim);border:1.5px solid var(--gold);display:flex;align-items:center;justify-content:center;font-size:0.9rem;font-weight:600;color:var(--gold);flex-shrink:0}
.seller-name{font-weight:600;font-size:0.9rem;margin-bottom:0.15rem}
.seller-since{font-size:0.75rem;color:var(--muted)}
.verified-badge{display:inline-flex;align-items:center;gap:0.25rem;font-size:0.72rem;color:var(--immo);background:rgba(29,158,117,0.1);padding:0.2rem 0.5rem;border-radius:4px;margin-top:0.25rem}
.detail-contact-btns{display:flex;flex-direction:column;gap:0.75rem}
.security-badge{display:flex;align-items:center;gap:0.75rem;padding:0.75rem 1rem;background:var(--gold-dim);border:1px solid var(--gold-dim);border-radius:var(--radius);font-size:0.78rem;color:var(--muted)}
.security-badge strong{color:var(--text)}

/* ───── AUTH MODAL ───── */
.auth-tabs{display:flex;gap:0;margin-bottom:1.5rem;background:var(--dark3);border-radius:10px;padding:4px}
.auth-tab{flex:1;padding:0.5rem;border-radius:8px;border:none;background:transparent;color:var(--muted);font-size:0.88rem;cursor:pointer;transition:all var(--transition)}
.auth-tab.active{background:var(--dark);color:var(--text);border:1px solid var(--dark4);box-shadow:0 1px 3px rgba(0,0,0,0.05)}
.social-auth{display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-bottom:1.25rem}
.social-btn{display:flex;align-items:center;justify-content:center;gap:0.5rem;padding:0.65rem;background:var(--dark2);border:1px solid var(--dark3);border-radius:var(--radius);cursor:pointer;font-size:0.85rem;color:var(--text);transition:all var(--transition)}
.social-btn:hover{border-color:var(--dark4);background:var(--dark3)}
.auth-divider{display:flex;align-items:center;gap:0.75rem;margin-bottom:1.25rem}
.auth-divider::before,.auth-divider::after{content:'';flex:1;height:1px;background:var(--dark3)}
.auth-divider span{font-size:0.78rem;color:var(--muted)}
.input-icon-wrap{position:relative}
.input-icon-wrap .form-input{padding-left:2.5rem}
.input-icon{position:absolute;left:0.85rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:0.95rem;pointer-events:none;line-height:1}
.password-toggle{position:absolute;right:0.85rem;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--muted);cursor:pointer;font-size:0.85rem;transition:color var(--transition)}
.password-toggle:hover{color:var(--text)}
.strength-bar{height:3px;background:var(--dark4);border-radius:2px;margin-top:0.4rem;overflow:hidden}
.strength-fill{height:100%;border-radius:2px;transition:all 0.3s;width:0}
.strength-label{font-size:0.72rem;color:var(--muted);margin-top:0.25rem}
.terms-check{display:flex;align-items:flex-start;gap:0.75rem;font-size:0.82rem;color:var(--muted);cursor:pointer}
.terms-check input{margin-top:2px;accent-color:var(--gold);flex-shrink:0}
.terms-check a{color:var(--gold)}
.forgot-link{font-size:0.8rem;color:var(--gold);cursor:pointer;text-align:right;display:block;margin-top:0.3rem}

/* ───── PUBLISH MODAL ───── */
.publish-modal .modal{max-width:700px}
.form-section-title{font-family:'Inter',sans-serif;font-size:0.95rem;font-weight:700;color:var(--text);margin-bottom:1rem;padding-bottom:0.6rem;border-bottom:0.5px solid rgba(255,255,255,0.06)}
.form-section-title{border-bottom:1px solid var(--dark3)}

/* ───── PAYMENT MODAL ───── */
.payment-modal .modal{max-width:460px}
.payment-summary{background:var(--dark3);border-radius:var(--radius-lg);padding:1.25rem;margin-bottom:1.5rem}
.payment-row{display:flex;justify-content:space-between;align-items:center;font-size:0.88rem;margin-bottom:0.6rem}
.payment-row:last-child{margin-bottom:0;padding-top:0.6rem;border-top:1px solid var(--dark4);font-weight:600;font-size:1rem}
.payment-row .amount{color:var(--gold);font-family:'Inter',sans-serif;font-weight:700}
.paystack-logo{display:flex;align-items:center;justify-content:center;gap:0.5rem;padding:0.75rem;background:rgba(0,155,85,0.08);border:1px solid rgba(0,155,85,0.15);border-radius:var(--radius);margin-bottom:1.5rem;font-size:0.82rem;color:var(--muted)}
.paystack-logo strong{color:#009B55}
.security-info{display:flex;flex-direction:column;gap:0.5rem;margin-bottom:1.5rem}
.sec-item{display:flex;align-items:center;gap:0.5rem;font-size:0.78rem;color:var(--muted)}
.sec-item::before{content:'🔒';font-size:0.8rem;flex-shrink:0}

/* ───── CONTACT MODAL ───── */
.contact-modal .modal{max-width:440px;background:var(--dark2)}

/* ───── MESSAGE MODAL ───── */
.message-thread{max-height:280px;overflow-y:auto;display:flex;flex-direction:column;gap:0.75rem;margin-bottom:1rem;padding-right:0.25rem}
.msg-bubble{max-width:80%;padding:0.65rem 0.9rem;border-radius:12px;font-size:0.85rem;line-height:1.5}
.msg-bubble.sent{align-self:flex-end;background:var(--gold);color:var(--dark)}
.msg-bubble.recv{align-self:flex-start;background:var(--dark3);color:var(--text)}
.msg-time{font-size:0.65rem;color:var(--muted);margin-top:0.25rem;text-align:right}

/* ───── SUCCESS PAGE ───── */
.success-page{text-align:center;padding:4rem 2rem}
.success-icon{font-size:4rem;margin-bottom:1.5rem;animation:bounceIn 0.6s ease both}
.success-title{font-family:'Inter',sans-serif;font-size:1.6rem;font-weight:700;margin-bottom:0.75rem}
.success-sub{color:var(--muted);max-width:400px;margin:0 auto 2rem;line-height:1.65}
.confetti{position:fixed;inset:0;pointer-events:none;z-index:9997;overflow:hidden}
.confetti-piece{position:absolute;width:8px;height:8px;top:-10px;animation:confettiFall linear forwards}
@keyframes bounceIn{0%{transform:scale(0.3);opacity:0}50%{transform:scale(1.15)}100%{transform:scale(1);opacity:1}}
@keyframes confettiFall{to{transform:translateY(110vh) rotate(720deg);opacity:0}}

/* ───── CTA SECTION ───── */
.cta-wrap{text-align:center;padding:5rem 2.5rem}
.cta-inner{max-width:580px;margin:0 auto;background:linear-gradient(135deg,var(--dark),var(--gold-dim));border:1px solid var(--dark3);border-radius:var(--radius-xl);padding:3.5rem 2.5rem}
.cta-inner h2{font-family:'Inter',sans-serif;font-size:1.9rem;font-weight:700;margin-bottom:0.75rem}
.cta-inner p{color:var(--muted);line-height:1.6;margin-bottom:2.5rem}
.cta-btns{display:flex;gap:0.75rem;justify-content:center;flex-wrap:wrap}

/* ───── FOOTER ───── */
footer{background:var(--dark);border-top:1px solid var(--dark3);padding:3.5rem 2.5rem 2rem}
.footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:3rem;max-width:1200px;margin:0 auto;margin-bottom:3rem}
.footer-brand .nav-logo{font-size:1.3rem;margin-bottom:0.75rem;display:block}
.footer-brand p{color:var(--muted);font-size:0.85rem;line-height:1.65;max-width:260px}
.footer-socials{display:flex;gap:0.75rem;margin-top:1.25rem;font-family:sans-serif}
.social-link{width:34px;height:34px;border-radius:8px;background:var(--dark);border:1px solid var(--dark3);display:flex;align-items:center;justify-content:center;font-size:0.8rem;color:var(--muted);cursor:pointer;transition:all var(--transition)}
.social-link:hover{border-color:var(--gold);color:var(--gold)}
.footer-col h4{font-family:'Inter',sans-serif;font-size:0.88rem;font-weight:700;margin-bottom:1.1rem;color:var(--text)}
.footer-links-list{list-style:none;display:flex;flex-direction:column;gap:0.6rem}
.footer-links-list li a{font-size:0.82rem;color:var(--muted);transition:color var(--transition);cursor:pointer}
.footer-links-list li a:hover{color:var(--text)}
.footer-bottom{max-width:1200px;margin:0 auto;padding-top:1.5rem;border-top:1px solid var(--dark3);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem}
.footer-bottom p{font-size:0.78rem;color:var(--muted)}
.footer-badges{display:flex;gap:0.75rem}
.footer-badge{display:flex;align-items:center;gap:0.3rem;font-size:0.72rem;color:var(--muted);background:var(--dark);padding:0.3rem 0.65rem;border-radius:6px;border:1px solid var(--dark3)}
.payment-logos{display:flex;align-items:center;justify-content:center;gap:1.25rem;margin-top:2rem}
.payment-logos img{height:24px;filter:grayscale(100%) opacity(0.6);transition:all var(--transition)}
.payment-logos img:hover{filter:grayscale(0) opacity(1)}
.payment-logos-title{font-size:0.75rem;color:var(--muted);text-align:center;margin-top:1.5rem;text-transform:uppercase;letter-spacing:0.05em}


/* ───── RESPONSIVE ───── */
@media(max-width:1024px){
  .sector-grid{grid-template-columns:1fr 1fr}
  .card-grid{grid-template-columns:1fr 1fr}
  .feat-grid{grid-template-columns:repeat(2,1fr)}
  .publish-grid{grid-template-columns:1fr}
  .detail-main{grid-template-columns:1fr;padding:0 1.5rem 3rem}
  .detail-sidebar{position:static}
  .footer-grid{grid-template-columns:1fr 1fr}
  .dash-stats{grid-template-columns:repeat(2,1fr)}
  .plans-grid{grid-template-columns:1fr}
}
@media(max-width:768px){
  #navbar{padding:1rem 1.5rem}
  .nav-links{display:none}
  .hamburger{display:flex}
  .sector-grid,.card-grid,.feat-grid{grid-template-columns:1fr}
  .section{padding:3.5rem 1.5rem}
  #search-section{padding:1.5rem}
  #hero{padding:4rem 1.5rem 3rem;min-height:auto}
  .hero-stats{gap:2rem}
  .form-row{grid-template-columns:1fr}
  .footer-grid{grid-template-columns:1fr}
  .dash-stats{grid-template-columns:1fr 1fr}
  .photo-preview-grid{grid-template-columns:repeat(3,1fr)}
  .steps-bar{gap:0}
  .modal{border-radius:var(--radius-lg)}
}
@media(max-width:480px){
  .plans-grid,.social-auth{grid-template-columns:1fr}
  .hero-cta{flex-direction:column;align-items:center}
  .footer-badges{flex-direction:column}
}
</style>
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
      <div class="stat-item"><div class="stat-num" id="stat-annonces">12 400+</div><div class="stat-label">Annonces actives</div></div> 
      <div class="stat-item"><div class="stat-num">8 200</div><div class="stat-label">Clients satisfaits</div></div>
      <div class="stat-item"><div class="stat-num">4</div><div class="stat-label">Secteurs couverts</div></div>
      <div class="stat-item"><div class="stat-num">24/7</div><div class="stat-label">Support sécurisé</div></div>
    </div>
  </section>

  <!-- SEARCH -->
  <section id="search-section">
    <div class="search-inner">
      <div class="search-tabs">
        <button class="s-tab active" id="stab-immo" onclick="switchSearchTab('immo')">🏠 Immobilier</button>
        <button class="s-tab" id="stab-veh" onclick="switchSearchTab('veh')">🚗 Véhicules</button>
        <button class="s-tab" id="stab-btp" onclick="switchSearchTab('btp')">🏗️ BTP & Matériel</button>
        <button class="s-tab" id="stab-tech" onclick="switchSearchTab('tech')">🛠️ Techniciens</button>
      </div>
      <div class="search-box">
        <span style="color:var(--muted);font-size:1.1rem;flex-shrink:0">🔍</span>
        <input type="text" id="search-input" placeholder="Appartement 3 pièces à Cocody..." oninput="filterListings()" autocomplete="off" maxlength="200">
        <button class="btn btn-primary" onclick="filterListings()"><span class="btn-text">Rechercher</span><span class="btn-spinner"></span></button>
      </div>
      <div class="search-filters" id="search-filters">
        <select class="filter-select" id="filter-type" onchange="filterListings()"><option value="">Type</option><option>Appartement</option><option>Maison</option><option>Bureau</option><option>Terrain</option><option>Villa</option></select>
        <select class="filter-select" id="filter-ville" onchange="filterListings()"><option value="">Ville</option><option>Abidjan</option><option>Bouaké</option><option>Yamoussoukro</option><option>San-Pédro</option><option>Daloa</option></select>
        <select class="filter-select" id="filter-budget" onchange="filterListings()"><option value="">Budget</option><option value="0-100">Moins de 100k</option><option value="100-500">100k – 500k</option><option value="500-2000">500k – 2M</option><option value="2000+">Plus de 2M</option></select>
        <select class="filter-select" id="filter-offre" onchange="filterListings()"><option value="">Type d'offre</option><option>Location</option><option>Vente</option></select>
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
        <div class="listing-tabs">
          <button class="l-tab active" id="ltab-immo" onclick="switchListingTab('immo')">🏠 Immobilier</button>
          <button class="l-tab" id="ltab-veh" onclick="switchListingTab('veh')">🚗 Véhicules</button>
          <button class="l-tab" id="ltab-btp" onclick="switchListingTab('btp')">🏗️ BTP</button>
          <button class="l-tab" id="ltab-tech" onclick="switchListingTab('tech')">🛠️ Techniciens</button>
        </div>
      </div>
      <div class="card-grid" id="listings-grid"></div>
      <div id="no-results" style="display:none;text-align:center;padding:3rem;color:var(--muted)">
        <div style="font-size:3rem;margin-bottom:1rem">🔍</div>
        <p>Aucune annonce trouvée pour cette recherche.</p>
        <button class="btn btn-ghost" style="margin-top:1rem" onclick="resetFilters()">Réinitialiser les filtres</button>
      </div>
      <div style="text-align:center;margin-top:2.5rem">
        <button class="btn btn-secondary" id="load-more-btn" onclick="loadMore()">Voir plus d'annonces →</button>
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

<script>
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

// ─── SEED DATA ───────────────────────────────────────
const SEED_LISTINGS = [
  { id:'s1', type:'immo', emoji:'🏢', badge:'Location', badgeClass:'badge-loc', title:'Appartement 3P meublé — Angré', location:'Abidjan, Cocody', price:'250 000', priceUnit:'/mois', chips:['3 pièces','80 m²'], desc:'Bel appartement meublé de 3 pièces situé à Cocody Angré, dans une résidence sécurisée. Climatisation, eau chaude, parking. Idéal pour famille ou cadres.', seller:'Konan Yves', sellerSince:'Membre depuis 2022', contact:'+225 07 12 34 56 78', verified:true, status:'active', userId:'seed' },
  { id:'s2', type:'immo', emoji:'🏡', badge:'Vente', badgeClass:'badge-vte', title:'Villa duplex avec piscine — Riviera', location:'Abidjan, Riviera', price:'95 000 000', priceUnit:'FCFA', chips:['5 pièces','300 m²'], desc:'Magnifique villa duplex avec piscine privée, jardin et garage double. Sécurité 24h/24. Finitions haut de gamme. Idéale pour investissement ou résidence principale.', seller:'Kouamé Adjoua', sellerSince:'Membre depuis 2021', contact:'+225 05 98 76 54 32', verified:true, status:'active', userId:'seed' },
  { id:'s3', type:'immo', emoji:'🏗️', badge:'Location', badgeClass:'badge-loc', title:'Bureau open-space — Plateau', location:'Abidjan, Plateau', price:'450 000', priceUnit:'/mois', chips:['Bureau','120 m²'], desc:'Espace de bureau professionnel au cœur du Plateau. Accès internet fibre, climatisation centralisée, parking sécurisé. Idéal pour PME ou startups.', seller:'Ouattara Drissa', sellerSince:'Membre depuis 2023', contact:'+225 01 23 45 67 89', verified:false, status:'active', userId:'seed' },
  { id:'s4', type:'veh', emoji:'🚙', badge:'Location', badgeClass:'badge-loc', title:'Toyota Land Cruiser V8 2023', location:'Abidjan, Marcory', price:'65 000', priceUnit:'/jour', chips:['SUV','Automatique'], desc:'Toyota Land Cruiser V8 2023 en parfait état. Disponible pour location courte et longue durée. Kilométrage libre, assurance incluse.', seller:'Bamba Transport', sellerSince:'Professionnel vérifié', contact:'+225 07 55 44 33 22', verified:true, status:'active', userId:'seed' },
  { id:'s5', type:'veh', emoji:'🚗', badge:'Vente', badgeClass:'badge-vte', title:'Hyundai Tucson — 62 000 km', location:'Abidjan, Yopougon', price:'12 500 000', priceUnit:'FCFA', chips:['SUV','62 000 km'], desc:'Hyundai Tucson 2021 en excellent état, 62 000 km. Entretien régulier, carnet de bord complet. Toit ouvrant, GPS, caméra de recul. Première main.', seller:'Traoré Moussa', sellerSince:'Membre depuis 2023', contact:'+225 05 11 22 33 44', verified:false, status:'active', userId:'seed' },
  { id:'s6', type:'veh', emoji:'🚐', badge:'Location', badgeClass:'badge-loc', title:'Minibus 15 places climatisé', location:'Abidjan, Port-Bouet', price:'80 000', priceUnit:'/jour', chips:['Minibus','15 places'], desc:'Minibus 15 places climatisé, idéal pour excursions, séminaires ou transferts aéroport. Chauffeur disponible en option.', seller:'Diallo Location', sellerSince:'Professionnel vérifié', contact:'+225 07 66 77 88 99', verified:true, status:'active', userId:'seed' },
  { id:'s7', type:'btp', emoji:'🚜', badge:'Location', badgeClass:'badge-btp', title:'Pelle hydraulique 20T — CAT', location:'Abidjan, Yopougon', price:'120 000', priceUnit:'/jour', chips:['Engin','20 tonnes'], desc:'Pelle hydraulique Caterpillar 20 tonnes disponible à la location. Opérateur qualifié fourni sur demande. Idéal pour terrassement, démolition et construction.', seller:'BTP Pro CI', sellerSince:'Professionnel vérifié', contact:'+225 07 30 20 10 00', verified:true, status:'active', userId:'seed' },
  { id:'s8', type:'btp', emoji:'🏗️', badge:'Location', badgeClass:'badge-btp', title:'Grue tour 40m — POTAIN MDT', location:'Abidjan, Abobo', price:'350 000', priceUnit:'/semaine', chips:['Grue','40 mètres'], desc:'Grue tour POTAIN MDT 40m disponible à la location. Montage/démontage inclus. Capacité 8 tonnes en pointe. Idéale pour constructions en hauteur.', seller:'Matériel Grands Travaux', sellerSince:'Professionnel vérifié', contact:'+225 05 40 50 60 70', verified:true, status:'active', userId:'seed' },
  { id:'s9', type:'btp', emoji:'🔧', badge:'Location', badgeClass:'badge-btp', title:'Centrale à béton 60m³/h', location:'Abidjan, Zone industrielle', price:'85 000', priceUnit:'/jour', chips:['Équipement','60 m³/h'], desc:'Centrale à béton mobile LIEBHERR 60 m³/h. Location journalière ou mensuelle. Technicien de maintenance disponible. Livraison sur site possible.', seller:'BTP Pro CI', sellerSince:'Professionnel vérifié', contact:'+225 07 30 20 10 00', verified:true, status:'active', userId:'seed' }
];

// ─── APP STATE ────────────────────────────────────────
const App = {
  currentPage: 'home',
  currentListingTab: 'immo',
  currentSearchTab: 'immo',
  uploadedPhotos: [],
  selectedCategory: null, 
  selectedPlan: { plan: 'starter', price: 5000 },
  currentPublishStep: 1,
  currentListing: null,
  pendingAction: null,
  dashTab: 'annonces',
};



// ─── SUBCAT DATA ─────────────────────────────────────
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

// ─── LISTINGS RENDERING ──────────────────────────────
let displayCount = 6;
function renderListings(tab, filtered) {
  const allListings = [...SEED_LISTINGS, ...DB.getListings().filter(l=>l.status==='active')];
  const data = (filtered !== undefined ? filtered : allListings).filter(l => l.type === tab);
  const grid = document.getElementById('listings-grid');
  const session = Session.get();
  const favorites = session ? DB.getFavorites(session.id) : [];
  const shown = data.slice(0, displayCount);

  if (shown.length === 0) {
    grid.innerHTML = '';
    document.getElementById('no-results').style.display = '';
    document.getElementById('load-more-btn').style.display = 'none';
    return;
  }
  document.getElementById('no-results').style.display = 'none';
  document.getElementById('load-more-btn').style.display = data.length > displayCount ? '' : 'none';

  grid.innerHTML = shown.map(l => {
    const isFav = favorites.includes(l.id);
    const imgHtml = l.photos && l.photos.length ? `<img src="${Security.escapeHtml(l.photos[0])}" alt="${Security.escapeHtml(l.title)}" loading="lazy">` : '';
    return `
    <div class="listing-card" onclick="openDetail('${l.id}')">
      <div class="card-thumb ${l.type}">
        ${imgHtml}
        ${!l.photos?.length ? `<span>${Security.escapeHtml(l.emoji||'🏢')}</span>` : ''}
        <span class="card-badge ${Security.escapeHtml(l.badgeClass)}">${Security.escapeHtml(l.badge)}</span>
        <div class="card-fav ${isFav?'active':''}" onclick="event.stopPropagation();toggleFav('${l.id}',this)">
          ${isFav ? '❤️' : '🤍'}
        </div>
        ${l.verified ? '<div class="card-verified">✅ Vérifié</div>' : ''}
      </div>
      <div class="card-body">
        <div class="card-title">${Security.escapeHtml(l.title)}</div>
        <div class="card-location">📍 ${Security.escapeHtml(l.location)}</div>
        <div class="card-footer">
          <div><div class="card-price">${Security.escapeHtml(l.price)} <span class="card-price-unit">FCFA ${Security.escapeHtml(l.priceUnit || '')}</span></div></div>
          <div class="card-chips">${(l.chips||[]).map(c=>`<span class="chip">${Security.escapeHtml(c)}</span>`).join('')}</div>
        </div>
      </div>
    </div>`;
  }).join('');
}

function switchListingTab(tab) { 
  App.currentListingTab = tab;
  displayCount = 6; 
  ['immo','veh','btp','tech'].forEach(t => {
    document.getElementById(`ltab-${t}`)?.classList.toggle('active', t===tab);
  });
  renderListings(tab);
}

function switchSearchTab(tab) {
  App.currentSearchTab = tab; 
  ['immo','veh','btp','tech'].forEach(t => {
    document.getElementById(`stab-${t}`)?.classList.toggle('active', t===tab);
  });
  const ph = { immo:'Appartement 3P à Cocody...', veh:'Toyota Prado 2022 location...', btp:'Pelle hydraulique 20T...' };
  document.getElementById('search-input').placeholder = ph[tab];
  switchListingTab(tab);
}

function filterListings() {
  const q = Security.sanitize(document.getElementById('search-input').value).toLowerCase();
  const type = document.getElementById('filter-type')?.value || '';
  const ville = document.getElementById('filter-ville')?.value || '';
  const offre = document.getElementById('filter-offre')?.value || '';

  const allListings = [...SEED_LISTINGS, ...DB.getListings().filter(l=>l.status==='active')];
  const filtered = allListings.filter(l => {
    if (l.type !== App.currentListingTab) return false;
    if (q && !l.title.toLowerCase().includes(q) && !l.location.toLowerCase().includes(q) && !l.desc?.toLowerCase().includes(q)) return false;
    if (type && l.subcat && !l.subcat.toLowerCase().includes(type.toLowerCase())) return false;
    if (ville && !l.location.toLowerCase().includes(ville.toLowerCase())) return false;
    if (offre && !l.badge.toLowerCase().includes(offre.toLowerCase())) return false;
    return true;
  });
  renderListings(App.currentListingTab, filtered);
}

function resetFilters() {
  document.getElementById('search-input').value = '';
  ['filter-type','filter-ville','filter-budget','filter-offre'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  renderListings(App.currentListingTab);
}

function loadMore() {
  displayCount += 3;
  filterListings();
}

// ─── FAVORITES ────────────────────────────────────────
function toggleFav(listingId, el) {
  if (!Session.isAuthenticated()) {
    requireAuth(() => {});
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
  const allListings = [...SEED_LISTINGS, ...DB.getListings()];
  const listing = allListings.find(l => l.id === id);
  if (!listing) { toast('Annonce introuvable', 'error'); return; }
  App.currentListing = listing;
  
  document.getElementById('detail-emoji').textContent = listing.emoji || '🏢';
  document.getElementById('detail-title').textContent = listing.title;
  document.getElementById('detail-loc').textContent = '📍 ' + listing.location;
  document.getElementById('detail-price').textContent = listing.price + ' FCFA';
  document.getElementById('detail-price-unit').textContent = listing.priceUnit;
  document.getElementById('detail-desc').textContent = listing.desc || '';
  document.getElementById('seller-name').textContent = listing.seller;
  document.getElementById('seller-since').textContent = listing.sellerSince;
  const av = document.getElementById('seller-avatar'); 
  av.textContent = (listing.seller || 'V')[0].toUpperCase();

  // Photo gallery
  const mainImg = document.getElementById('detail-main-img');
  const thumbs = document.getElementById('gallery-thumbs');
  mainImg.innerHTML = '';
  thumbs.innerHTML = '';
  if (listing.photos && listing.photos.length) {
    const mainPhoto = document.createElement('img'); 
    mainPhoto.src = listing.photos[0];
    mainPhoto.alt = listing.title;
    mainPhoto.style.cssText = 'width:100%;height:100%;object-fit:cover;position:absolute;inset:0';
    mainImg.appendChild(mainPhoto);
    listing.photos.forEach((ph, i) => {
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
  tagsEl.innerHTML = [
    listing.badge, listing.subcat, ...(listing.chips || [])
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
  // Show/hide immo-specific fields
  document.getElementById('pub-immo-fields').style.display = cat === 'immo' ? '' : 'none';
  const surfaceLabel = document.getElementById('lbl-surface');
  const priceUnitSelect = document.getElementById('pub-offre-type');
  const s2Title = document.getElementById('pub-s2-title');
  const step2Label = document.getElementById('step-label-2');

  if (cat === 'tech') {
    surfaceLabel.textContent = 'Années d\'expérience';
    priceUnitSelect.innerHTML = `<option value="service">Prestation de service</option>`;
    s2Title.textContent = 'Informations sur le technicien';
    step2Label.textContent = 'Profil';
  } else {
    surfaceLabel.textContent = cat === 'btp' ? 'Capacité / Tonnage' : (cat === 'veh' ? 'Kilométrage' : 'Surface (m²)');
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
async function initiatePaystackPayment() {
  if (!Session.isAuthenticated()) { toast('Connectez-vous pour payer', 'error'); return; }
  const session = Session.get();

  // Validate final form
  const titre = Security.sanitize(document.getElementById('pub-titre').value);
  if (!titre) { toast('Veuillez compléter le formulaire', 'error'); return; }

  const btn = document.getElementById('btn-pay-publish') || document.getElementById('btn-pay-final');
  setLoading(btn, true);

  const amount = App.selectedPlan.price * 100; // Paystack utilise les sous-unités (kobo, centimes)
  const email = session.email;
  const ref = 'LP_' + Security.generateToken().substring(0, 20).toUpperCase();

  setLoading(btn, false);

  // Vérifie si Paystack est chargé
  if (typeof PaystackPop === 'undefined') {
    // Solution de repli pour l'environnement de démo
    simulatePayment(ref, titre, session);
    return;
  }

  const handler = PaystackPop.setup({
    key: 'pk_test_YOUR_PAYSTACK_PUBLIC_KEY', // IMPORTANT: Remplacez par votre clé publique Paystack
    email: email,
    amount: amount,
    currency: 'XOF', // Devise pour la Côte d'Ivoire
    ref: ref,
    channels: ['mobile_money', 'card'], // Priorise le mobile money et la carte
    metadata: {
      custom_fields: [
        { display_name: 'Annonce', variable_name: 'listing_title', value: titre },
        { display_name: 'Plan', variable_name: 'plan', value: App.selectedPlan.plan },
        { display_name: 'User ID', variable_name: 'user_id', value: session.id },
        { display_name: 'CSRF Token', variable_name: 'csrf', value: Session._csrfToken }
      ]
    },
    callback: response => {
      if (response.status === 'success' || response.reference) {
        onPaymentSuccess(response.reference, titre, session);
      }
    },
    onClose: () => toast('Paiement annulé', 'info')
  });
  handler.openIframe();
}

function simulatePayment(ref, titre, session) {
  // Simulation pour la démo uniquement — en production, la vraie clé Paystack sera utilisée
  const amount = App.selectedPlan.price;
  const msg = `
    <div style="text-align:center;padding:1rem">
      <div style="font-size:2.5rem;margin-bottom:1rem">💳</div>
      <div style="font-family:Inter,sans-serif;font-weight:700;margin-bottom:0.5rem">Simulation de paiement</div>
      <p style="color:var(--muted);font-size:0.85rem;margin-bottom:1.5rem">
        En production, Paystack gérera le paiement sécurisé.<br>
        Montant: <strong style="color:var(--gold)">${amount.toLocaleString('fr-FR')} FCFA</strong>
      </p>
      <p style="font-size:0.78rem;color:var(--muted)">Référence: ${Security.escapeHtml(ref)}</p>
    </div>`;

  const confirmModal = document.createElement('div');
  confirmModal.className = 'modal-overlay open';
  confirmModal.innerHTML = `<div class="modal" style="max-width:400px"><div class="modal-body">${msg}<button class="btn btn-primary btn-full" id="demo-pay-confirm" style="margin-top:1rem">✅ Confirmer le paiement (Demo)</button><button class="btn btn-ghost btn-full" style="margin-top:0.5rem" id="demo-pay-cancel">Annuler</button></div></div>`;
  document.body.appendChild(confirmModal);
  document.body.style.overflow = 'hidden';

  document.getElementById('demo-pay-confirm').onclick = () => {
    document.body.removeChild(confirmModal);
    document.body.style.overflow = '';
    onPaymentSuccess(ref, titre, session);
  };
  document.getElementById('demo-pay-cancel').onclick = () => {
    document.body.removeChild(confirmModal);
    document.body.style.overflow = '';
    toast('Paiement annulé', 'info');
  };
}

function onPaymentSuccess(ref, titre, session) {
  closeModal('publish-modal');
  closeModal('payment-modal');

  // Save listing to DB
  const offreType = document.getElementById('pub-offre-type').value;
  const subcat = document.getElementById('pub-subcat').value;
  const ville = document.getElementById('pub-ville').value;
  const commune = Security.sanitize(document.getElementById('pub-commune').value);
  const prix = document.getElementById('pub-prix').value;
  const surface = document.getElementById('pub-surface').value;
  const desc = Security.sanitize(document.getElementById('pub-desc').value);
  const contact = Security.sanitize(document.getElementById('pub-contact').value);

  const typeEmojis = { immo:'🏠', veh:'🚗', btp:'🏗️', tech:'🛠️' };
  const badgeMap = { location: { text:'Location', cls:'badge-loc' }, vente: { text:'Vente', cls:'badge-vte' }, service: { text:'Service', cls:'badge-tech' } };
  const bdg = badgeMap[offreType] || { text:'Location', cls:'badge-loc' };

  const newListing = {
    type: App.selectedCategory,
    emoji: typeEmojis[App.selectedCategory],
    badge: App.selectedCategory === 'tech' ? 'Service' : bdg.text,
    badgeClass: App.selectedCategory === 'btp' ? 'badge-btp' : bdg.cls,
    title: Security.sanitize(titre),
    location: Security.escapeHtml(ville + (commune ? ', '+commune : '')),
    price: Number(prix).toLocaleString('fr-FR'),
    priceUnit: offreType === 'location' ? '/mois' : 'FCFA',
    chips: [subcat, surface ? surface + (App.selectedCategory==='immo'?' m²':App.selectedCategory==='veh'?' km':' T') : ''].filter(Boolean),
    desc,
    seller: Security.escapeHtml((session.prenom||'') + ' ' + (session.nom||'')),
    sellerSince: 'Membre depuis ' + new Date().getFullYear(),
    contact,
    verified: false,
    photos: [...App.uploadedPhotos],
    userId: session.id,
    plan: App.selectedPlan.plan,
    paymentRef: ref,
    subcat
  };

  const saved = DB.addListing(newListing);

  // Confetti
  launchConfetti();

  // Success toast
  toast(`🎉 Annonce "${Security.escapeHtml(titre.substring(0,40))}" publiée avec succès !`, 'success', 5000);
  toast(`Référence paiement: ${ref.substring(0,20)}`, 'info', 4000);

  // Refresh
  renderListings(App.currentListingTab);
  updateNavAuth();
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
    const allListings = [...SEED_LISTINGS, ...DB.getListings()];
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

  renderListings('immo');
  updateNavAuth();

  // Auto-reconnect session
  const session = Session.get();
  if (session) updateNavAuth();
});

// Prevent form submission on enter in most fields
document.addEventListener('keydown', e => {
  if (e.key === 'Enter' && e.target.tagName === 'INPUT' && !e.target.closest('#message-modal')) {
    if (!['btn-login','btn-register'].includes(document.activeElement?.id)) e.preventDefault?.();
  }
});
</script>
</body>
</html>
