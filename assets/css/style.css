/* ============================================================
   assets/css/style.css
   BGY206 – Web Programlama-I Proje Ödevi
   Tüm sayfalar için ortak CSS stilleri
   ============================================================ */

/* ─── Temel Sıfırlama & Değişkenler ─────────────────────────────────────────── */
:root {
    --renk-birincil:  #4f46e5;
    --renk-tehlike:   #dc2626;
    --renk-basari:    #16a34a;
    --renk-uyari:     #d97706;
    --renk-bilgi:     #0284c7;
    --renk-ikincil:   #6b7280;
    --renk-arka:      #f3f4f6;
    --renk-yuzey:     #ffffff;
    --renk-metin:     #111827;
    --renk-sinir:     #e5e7eb;
    --golge:          0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -2px rgba(0,0,0,.1);
    --radius:         .5rem;
    --gecis:          .2s ease;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: var(--renk-arka);
    color: var(--renk-metin);
    line-height: 1.6;
    min-height: 100vh;
}

/* ─── Container ─────────────────────────────────────────────────────────────── */
.container {
    max-width: 900px;
    margin: 0 auto;
    padding: 0 1rem;
}

/* ─── Yardımcı Boşluk Sınıfları ─────────────────────────────────────────────── */
.mt-15  { margin-top: .9375rem; }
.mt-30  { margin-top: 1.875rem; }
.mt-60  { margin-top: 3.75rem;  }
.mb-30  { margin-bottom: 1.875rem; }
.ml-15  { margin-left: .9375rem; }

/* ─── Kart Bileşeni ──────────────────────────────────────────────────────────── */
.card {
    background: var(--renk-yuzey);
    border-radius: var(--radius);
    box-shadow: var(--golge);
    padding: 2rem;
    max-width: 480px;
    margin-left: auto;
    margin-right: auto;
}

.card.mb-30 { max-width: 100%; }   /* Geniş kartlar için kısıtlamayı kaldır */

.card-title {
    font-size: 1.375rem;
    font-weight: 700;
    margin-bottom: 1.25rem;
    color: var(--renk-birincil);
}

/* ─── Uygulama Başlığı ───────────────────────────────────────────────────────── */
.app-title {
    font-size: 2rem;
    font-weight: 800;
    color: var(--renk-birincil);
    margin-bottom: .5rem;
}

.subtitle {
    color: var(--renk-ikincil);
    margin-bottom: 1.5rem;
}

.text-center  { text-align: center; }
.text-muted   { color: var(--renk-ikincil); font-style: italic; }

/* ─── Butonlar ───────────────────────────────────────────────────────────────── */
.btn {
    display: inline-block;
    padding: .55rem 1.25rem;
    border-radius: var(--radius);
    font-size: .9375rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    border: none;
    transition: background var(--gecis), transform var(--gecis);
}

.btn:hover   { transform: translateY(-1px); }
.btn:active  { transform: translateY(0); }

.btn-primary   { background: var(--renk-birincil); color: #fff; }
.btn-primary:hover { background: #4338ca; }

.btn-secondary { background: var(--renk-ikincil); color: #fff; }
.btn-secondary:hover { background: #4b5563; }

.btn-danger    { background: var(--renk-tehlike); color: #fff; }
.btn-danger:hover { background: #b91c1c; }

.btn-sm        { padding: .35rem .85rem; font-size: .85rem; }
.btn-block     { display: block; width: 100%; text-align: center; }

/* Buton grubu yan yana */
.btn-group { display: flex; gap: 1rem; justify-content: center; }

/* ─── Form Elemanları ────────────────────────────────────────────────────────── */
.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: .3rem;
    color: var(--renk-metin);
}

.form-control {
    width: 100%;
    padding: .55rem .85rem;
    border: 1.5px solid var(--renk-sinir);
    border-radius: var(--radius);
    font-size: .9375rem;
    transition: border-color var(--gecis), box-shadow var(--gecis);
    outline: none;
}

.form-control:focus {
    border-color: var(--renk-birincil);
    box-shadow: 0 0 0 3px rgba(79,70,229,.15);
}

/* ─── Alert Bildirimleri ─────────────────────────────────────────────────────── */
.alert {
    padding: .85rem 1rem;
    border-radius: var(--radius);
    margin-bottom: 1rem;
    font-size: .9375rem;
}

.alert ul { padding-left: 1.25rem; }

.alert-danger  { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
.alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
.alert-warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }

/* ─── Navbar ─────────────────────────────────────────────────────────────────── */
.navbar {
    background: var(--renk-birincil);
    color: #fff;
    padding: .85rem 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 4px rgba(0,0,0,.15);
}

.navbar-brand { font-size: 1.15rem; font-weight: 700; }
.navbar-right { display: flex; align-items: center; gap: .75rem; }

/* ─── Tablo ──────────────────────────────────────────────────────────────────── */
.table {
    width: 100%;
    border-collapse: collapse;
    font-size: .9rem;
    margin-top: .5rem;
}

.table th, .table td {
    padding: .65rem .85rem;
    border-bottom: 1px solid var(--renk-sinir);
    text-align: left;
    vertical-align: top;
}

.table th { font-weight: 700; color: var(--renk-ikincil); width: 160px; }
.table tbody tr:hover { background: var(--renk-arka); }

/* ─── Badge (Durum Etiketi) ──────────────────────────────────────────────────── */
.badge {
    display: inline-block;
    padding: .25rem .6rem;
    border-radius: 9999px;
    font-size: .78rem;
    font-weight: 600;
    white-space: nowrap;
}

.badge-success   { background: #dcfce7; color: #166534; }
.badge-danger    { background: #fef2f2; color: #991b1b; }
.badge-warning   { background: #fffbeb; color: #92400e; }
.badge-info      { background: #e0f2fe; color: #075985; }
.badge-secondary { background: #f3f4f6; color: #374151; }

/* ─── Duyarlı Tasarım ────────────────────────────────────────────────────────── */
@media (max-width: 600px) {
    .card           { padding: 1.25rem; }
    .app-title      { font-size: 1.5rem; }
    .btn-group      { flex-direction: column; }
    .navbar         { flex-direction: column; gap: .5rem; }
    .table th       { width: auto; }
}