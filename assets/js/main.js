/**
 * assets/js/main.js
 * İstemci tarafı JavaScript işlemleri.
 * - Form doğrulama (client-side, sunucu tarafını destekler)
 * - Bildirim otomatik kapanma
 * - UI yardımcı fonksiyonları
 */

'use strict';

// ─── DOM Yüklendikten Sonra Çalıştır ──────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {

    // Alert bildirimlerini 5 saniye sonra otomatik kapat
    otomatikKapatBildirimleri(5000);

    // Şifre eşleşme kontrolü (register sayfasında varsa)
    sifreTekrarKontrol();

    // E-posta doğrulama ön kontrolü
    emailDogrulamaKontrol();
});

/**
 * Sayfadaki .alert elementlerini belirtilen süre sonra gizler.
 * @param {number} gecikmeMs - Milisaniye cinsinden gecikme
 */
function otomatikKapatBildirimleri(gecikmeMs) {
    var alertler = document.querySelectorAll('.alert');
    if (alertler.length === 0) return;

    setTimeout(function () {
        alertler.forEach(function (el) {
            el.style.transition = 'opacity .5s ease';
            el.style.opacity    = '0';
            setTimeout(function () {
                el.style.display = 'none';
            }, 500);
        });
    }, gecikmeMs);
}

/**
 * Kayıt formundaki şifre ve şifre tekrar alanlarını anlık olarak karşılaştırır.
 * Eşleşmiyorsa alanın kenarını kırmızı yapar.
 */
function sifreTekrarKontrol() {
    var sifre       = document.getElementById('sifre');
    var sifreTekrar = document.getElementById('sifre_tekrar');

    if (!sifre || !sifreTekrar) return; // Sayfa bu alanları içermiyorsa çık

    function kontrol() {
        if (sifreTekrar.value === '') {
            sifreTekrar.style.borderColor = '';
            return;
        }
        if (sifre.value === sifreTekrar.value) {
            sifreTekrar.style.borderColor = '#16a34a'; // yeşil
        } else {
            sifreTekrar.style.borderColor = '#dc2626'; // kırmızı
        }
    }

    sifre.addEventListener('input', kontrol);
    sifreTekrar.addEventListener('input', kontrol);
}

/**
 * E-posta alanı için temel format kontrolü.
 * Geçersiz formatta uyarı rengi gösterir.
 */
function emailDogrulamaKontrol() {
    var emailAlan = document.getElementById('email');
    if (!emailAlan) return;

    emailAlan.addEventListener('blur', function () {
        var desen = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (emailAlan.value && !desen.test(emailAlan.value)) {
            emailAlan.style.borderColor = '#dc2626';
        } else {
            emailAlan.style.borderColor = '';
        }
    });
}

/**
 * Belirtilen bir butonun yüklenme durumunu yönetir.
 * Form gönderiminde çift tıklamayı önlemek için kullanılır.
 *
 * @param {HTMLElement} btn         - Buton elementi
 * @param {string}      yuklemeText - Yüklenirken gösterilecek metin
 */
function butonYukleniyor(btn, yuklemeText) {
    if (!btn) return;
    btn.disabled    = true;
    btn.dataset.eski = btn.textContent;
    btn.textContent = yuklemeText || 'Lütfen bekleyin...';
}

/**
 * Butonun yüklenme durumunu sıfırlar.
 * @param {HTMLElement} btn - Buton elementi
 */
function butonSifirla(btn) {
    if (!btn) return;
    btn.disabled    = false;
    btn.textContent = btn.dataset.eski || btn.textContent;
}