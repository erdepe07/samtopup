<?php
// Logo & favicon dinamis.
// - Logo utama diunggah lewat admin (Konfigurasi > Umum > Logo).
// - Logo mode terang (opsional): taruh "logo-light.svg" / "logo-light.png" di assets/images/ (menggantikan logo dari admin).
// - Logo mode gelap (opsional): taruh file "logo-dark.svg" / "logo-dark.png" di assets/images/.
// - Favicon (opsional): taruh "favicon.svg" / "favicon.png" / "favicon.ico" di assets/images/.
$imgDir   = FCPATH . 'assets/images/';
$lightLogo = $web['logo'];
foreach (['logo-light.svg', 'logo-light.png', 'logo-light.webp'] as $f) {
    if (is_file($imgDir . $f)) { $lightLogo = $f; break; }
}
$darkLogo = null;
foreach (['logo-dark.svg', 'logo-dark.png', 'logo-dark.webp'] as $f) {
    if (is_file($imgDir . $f)) { $darkLogo = $f; break; }
}
$favicon = $lightLogo;
foreach (['favicon.svg', 'favicon.png', 'favicon.ico'] as $f) {
    if (is_file($imgDir . $f)) { $favicon = $f; break; }
}
$faviconExt  = strtolower(pathinfo($favicon, PATHINFO_EXTENSION));
$faviconType = ['svg' => 'image/svg+xml', 'png' => 'image/png', 'ico' => 'image/x-icon', 'webp' => 'image/webp'][$faviconExt] ?? 'image/png';

$logoImg = function ($extraClass = '', $style = '') use ($web, $darkLogo, $lightLogo) {
    $base = base_url() . '/assets/images/';
    $alt  = htmlspecialchars($web['name'], ENT_QUOTES);
    $st   = $style !== '' ? ' style="' . $style . '"' : '';
    $html = '<img src="' . $base . $lightLogo . '" alt="' . $alt . '" class="brand-logo' . ($darkLogo ? ' logo-light' : '') . ' ' . $extraClass . '"' . $st . '>';
    if ($darkLogo) {
        $html .= '<img src="' . $base . $darkLogo . '" alt="' . $alt . '" class="brand-logo logo-dark ' . $extraClass . '"' . $st . '>';
    }
    return $html;
};
?>
<!DOCTYPE html>
<html lang="id" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title><?= $title; ?> - <?= $web['title']; ?></title>

        <meta name="description" content="<?= strip_tags($web['description']); ?>">
        <meta name="keywords" content="<?= strip_tags($web['keywords']); ?>">

        <link rel="icon" type="<?= $faviconType; ?>" href="<?= base_url(); ?>/assets/images/<?= $favicon; ?>">
        <link rel="shortcut icon" href="<?= base_url(); ?>/assets/images/<?= $favicon; ?>">

        <!-- Terapkan tema tersimpan sebelum render (cegah kedip) -->
        <script>
            (function () {
                try {
                    var t = localStorage.getItem('theme');
                    if (t === 'dark' || t === 'light') document.documentElement.setAttribute('data-theme', t);
                } catch (e) {}
            })();
        </script>

        <!-- Font -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Vendor CSS -->
        <link rel="stylesheet" href="<?= base_url(); ?>/assets/plugins/simplebar/css/simplebar.css">
        <link rel="stylesheet" href="<?= base_url(); ?>/assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?= base_url(); ?>/assets/plugins/bootstrap-datatable/css/dataTables.bootstrap4.min.css">
        <link rel="stylesheet" href="<?= base_url(); ?>/assets/css/animate.css">
        <link rel="stylesheet" href="<?= base_url(); ?>/assets/css/icons.css">
        <link rel="stylesheet" href="<?= base_url(); ?>/assets/css/horizontal-menu.css">
        <link rel="stylesheet" href="<?= base_url(); ?>/assets/css/app-style.css">
        <link rel="stylesheet" href="<?= base_url(); ?>/assets/css/style-main.css">

        <link rel="stylesheet" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
        <link href="https://unpkg.com/bootstrap-table@1.21.0/dist/bootstrap-table.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.4.1/css/swiper.min.css">
        <link rel="stylesheet" href="https://unpkg.com/flickity@2/dist/flickity.min.css">

        <style>
            /* =========================================================
               DESIGN TOKENS
            ========================================================= */
            :root {
                --bg: #f3f7ff;
                --bg-soft: #eaf1ff;
                --surface: #ffffff;
                --surface-2: #f6f9ff;
                --ink: #0b1b3a;
                --muted: #5b6b8a;
                --border: #e0e8f6;
                --primary: #1d4ed8;
                --primary-hover: #1e40af;
                --primary-soft: #dbe7ff;
                --accent: #d97706;
                --success: #16a34a;
                --shadow-sm: 0 1px 2px rgba(11, 27, 58, .06);
                --shadow-md: 0 8px 24px -8px rgba(29, 78, 216, .18);
                --shadow-lg: 0 20px 40px -16px rgba(29, 78, 216, .25);
                --radius: 14px;
                --nav-h: 72px;

                /* alias lama agar view anak tetap jalan */
                --warna: var(--bg);
                --warna_2: var(--surface);
                --warna_3: var(--primary);
            }

            [data-theme="dark"] {
                --bg: #071327;
                --bg-soft: #0b1c38;
                --surface: #0e2142;
                --surface-2: #112a52;
                --ink: #eaf1ff;
                --muted: #93a6c9;
                --border: #1b3560;
                --primary: #3b82f6;
                --primary-hover: #2563eb;
                --primary-soft: #14305c;
                --accent: #f59e0b;
                --shadow-sm: 0 1px 2px rgba(0, 0, 0, .3);
                --shadow-md: 0 8px 24px -8px rgba(0, 0, 0, .5);
                --shadow-lg: 0 20px 40px -16px rgba(0, 0, 0, .6);
            }

            /* =========================================================
               BASE
            ========================================================= */
            html { scroll-behavior: smooth; }

            body {
                font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
                background: var(--bg);
                color: var(--ink);
                -webkit-font-smoothing: antialiased;
                transition: background .25s ease, color .25s ease;
            }

            h1, h2, h3, h4, h5, h6,
            .h1, .h2, .h3, .h4, .h5, .h6 {
                color: var(--ink) !important;
                font-weight: 700;
                letter-spacing: -.02em;
            }

            a { color: var(--primary); transition: color .2s ease; }
            a:hover { color: var(--primary-hover); text-decoration: none; }

            p, li, label, .text-muted { color: var(--muted); }
            label { font-weight: 600; text-transform: none; color: var(--ink); }

            .content {
                padding-top: calc(var(--nav-h) + 40px);
                min-height: 446px;
            }

            .cursor-pointer { cursor: pointer; }
            .text-decoration-none { text-decoration: none !important; }

            ::selection { background: var(--primary); color: #fff; }

            /* =========================================================
               NAVBAR
            ========================================================= */
            .site-nav {
                height: var(--nav-h);
                background: color-mix(in srgb, var(--bg) 82%, transparent);
                -webkit-backdrop-filter: saturate(160%) blur(14px);
                backdrop-filter: saturate(160%) blur(14px);
                border-bottom: 1px solid var(--border);
                box-shadow: none !important;
                padding: 0;
            }
            .site-nav .container { height: 100%; }
            .site-nav .navbar-brand img {
                height: 44px !important;
                border-radius: 10px !important;
            }

            .menu-utama .navbar-nav { align-items: center; gap: 2px; }

            .menu-utama .nav-link {
                color: var(--muted) !important;
                font-size: 14.5px;
                font-weight: 500;
                padding: 8px 14px !important;
                border-radius: 999px;
                transition: color .2s ease, background .2s ease;
            }
            .menu-utama .nav-link:hover {
                color: var(--ink) !important;
                background: var(--primary-soft);
            }
            .menu-utama .nav-link.active {
                color: var(--ink) !important;
                font-weight: 700;
                background: transparent;
            }

            .menu-utama .dropdown-toggle::after { display: none; }
            .menu-utama .dropdown-menu {
                background: var(--surface);
                border: 1px solid var(--border);
                border-radius: 14px;
                box-shadow: var(--shadow-lg);
                padding: 8px;
                margin-top: 10px;
                min-width: 200px;
            }
            .menu-utama .dropdown-item {
                color: var(--ink) !important;
                font-size: 14px;
                font-weight: 500;
                border-radius: 9px;
                padding: 9px 12px;
            }
            .menu-utama .dropdown-item:hover,
            .menu-utama .dropdown-item.active {
                background: var(--primary-soft);
                color: var(--primary) !important;
            }

            .btn-pill {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                background: var(--surface);
                color: var(--ink);
                border: 1px solid var(--border);
                border-radius: 999px;
                padding: 8px 18px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                transition: all .2s ease;
                box-shadow: var(--shadow-sm);
            }
            .btn-pill:hover {
                border-color: var(--primary);
                color: var(--primary);
                transform: translateY(-1px);
            }

            .navbar-toggler {
                border: 1px solid var(--border);
                border-radius: 10px;
                padding: 6px 10px;
            }
            .navbar-toggler-icon {
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%235b6b8a' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
            }

            @media (max-width: 991.98px) {
                .navbar-collapse {
                    background: var(--surface);
                    border: 1px solid var(--border);
                    border-radius: 16px;
                    box-shadow: var(--shadow-lg);
                    margin-top: 10px;
                    padding: 12px;
                }
                .menu-utama .navbar-nav { align-items: stretch; }
                .menu-utama .dropdown-menu { box-shadow: none; }
            }

            /* =========================================================
               KOMPONEN UMUM (kompatibel dengan view lama)
            ========================================================= */
            .card, .section {
                background: var(--surface);
                border: 1px solid var(--border);
                border-radius: var(--radius);
                box-shadow: var(--shadow-sm);
                color: var(--ink);
            }
            .card-header {
                background: transparent;
                border-bottom: 1px solid var(--border);
                font-weight: 700;
                color: var(--ink);
            }
            .card-tools { float: right; margin-top: -25px; }

            .btn { border-radius: 12px; font-weight: 600; transition: all .2s ease; }
            .btn-primary, .btn-topup {
                background: var(--primary) !important;
                border-color: var(--primary) !important;
                color: #fff !important;
                box-shadow: 0 6px 16px -6px rgba(29, 78, 216, .55);
            }
            .btn-primary:hover, .btn-topup:hover {
                background: var(--primary-hover) !important;
                border-color: var(--primary-hover) !important;
                transform: translateY(-1px);
            }
            .btn-topup { display: none; }

            .strip-primary { background: var(--primary); }
            .bg-theme1, .bg-custom { background: var(--surface) !important; }

            .form-control, .custom-select, .select2-container--default .select2-selection--single {
                background: var(--surface);
                color: var(--ink);
                border: 1px solid var(--border);
                border-radius: 12px;
                min-height: 44px;
            }
            .form-control:focus {
                background: var(--surface);
                color: var(--ink);
                border-color: var(--primary);
                box-shadow: 0 0 0 4px color-mix(in srgb, var(--primary) 18%, transparent);
            }
            .form-control::placeholder { color: var(--muted); opacity: .8; }
            .col-form-label { padding-top: calc(.375rem + 3px); }

            .table { color: var(--ink); }
            .table th, .table td { border-color: var(--border); }
            .table thead th { background: var(--surface-2); color: var(--ink); font-weight: 700; }
            .table-white tr th, .table-white tr td { color: var(--ink); border-color: var(--border); }

            .daterangepicker td, .daterangepicker th { color: #4b5563; }

            /* Pilihan nominal */
            .radio-nominal + label, .radio-nominale + label {
                background: var(--surface);
                color: var(--ink);
                border: 1px solid var(--border) !important;
                border-radius: 12px;
                transition: all .2s ease;
            }
            .radio-nominal + label:hover, .radio-nominale + label:hover {
                border-color: var(--primary) !important;
                transform: translateY(-2px);
                box-shadow: var(--shadow-md);
            }
            .radio-nominale:checked + label, .radio-nominal:checked + label {
                background: var(--primary-soft);
                color: var(--ink);
                border-color: var(--primary) !important;
            }
            .radio-nominale:checked + label:after,
            .radio-nominal:checked + label:after {
                position: absolute;
                top: 0;
                left: 0.7rem;
                width: 28px;
                height: 28px;
                content: "";
                background: url(/assets/images/checkedblue.png) top/cover;
            }

            /* Kartu produk / game */
            .phy-card {
                border-radius: var(--radius);
                min-height: 90px;
                position: relative;
                margin-top: 30px;
                background: var(--surface);
                border: 1px solid var(--border);
                transition: all .25s ease;
            }
            .phy-card:hover { transform: translateY(-4px); box-shadow: var(--shadow-lg); border-color: var(--primary); }
            .phy-img {
                border-radius: var(--radius);
                display: block;
                transform: translate(-50%, -50%) !important;
                left: 50% !important;
                position: absolute !important;
                width: 70%;
                box-shadow: var(--shadow-md);
            }
            .phy-card-title { margin-top: 35px; padding: 10px 10px 20px; }

            .icon-diamondx { width: 40px; top: 10%; right: 10%; position: absolute; }
            .col-lg-2 { margin-bottom: 20px; }
            .img-fluid2 { max-width: 100%; height: auto; padding-bottom: 15px; }

            /* Menu samping user */
            .menu-user { margin-bottom: 26px; }
            .menu-user a { padding: 10px 16px; border-radius: 10px; color: var(--ink); }
            .menu-user a:hover { background: var(--primary-soft); color: var(--primary); }
            .menu-user a i { font-size: 19px; width: 20px; }
            .sidenav { background: var(--surface); }

            /* Carousel */
            .carousel { position: relative; margin-top: 30px; }
            .owl-dot { display: none; }
            .owl-carousel.owl-hidden { opacity: 0; }
            .owl-carousel.owl-rtl { direction: rtl; }
            .owl-carousel .owl-stage-outer { position: relative; overflow: hidden; -webkit-transform: translate3d(0,0,0); }
            .owl-carousel .owl-nav.disabled { display: none; }
            .owl-carousel .owl-item { min-height: 1px; float: left; -webkit-backface-visibility: hidden; -webkit-touch-callout: none; }
            .owl-carousel .owl-item img { display: block; width: 100%; border-radius: var(--radius); }
            .item .metode {
                margin: 5px 0;
                background: var(--surface);
                border: 1px solid var(--border);
                border-radius: 12px;
                padding: .75rem;
                box-shadow: var(--shadow-sm) !important;
            }
            .metode-bottom { margin-bottom: 100px !important; }

            /* Teks putih bawaan tema gelap lama → ikut tema */
            [data-theme="light"] .card .text-white:not(.btn):not(.badge),
            [data-theme="light"] .section .text-white:not(.btn):not(.badge) {
                color: var(--ink) !important;
            }

            /* =========================================================
               FOOTER
            ========================================================= */
            .footer-wave { display: block; width: 100%; height: 70px; color: var(--primary-soft); margin-bottom: -1px; }
            .bg-footer { background: var(--bg); }
            .footer-main {
                background: var(--primary-soft);
                padding: 48px 0 40px;
            }
            .footer-main p, .footer-main li { color: var(--muted); font-size: 14.5px; line-height: 1.7; }
            .footer-main h5 { font-size: 16px; margin-bottom: 14px; }
            .menu-list { list-style: none; padding-left: 0; margin: 0; }
            .menu-list li a {
                display: inline-block;
                padding: 6px 0;
                color: var(--muted);
                font-size: 14.5px;
                transition: all .25s ease;
            }
            .menu-list li a:hover { color: var(--primary); transform: translateX(4px); }

            .social-links { display: flex; gap: 10px; flex-wrap: wrap; }
            .social-links a {
                width: 42px; height: 42px;
                display: inline-flex; align-items: center; justify-content: center;
                background: var(--surface);
                color: var(--ink);
                border: 1px solid var(--border);
                border-radius: 12px;
                font-size: 20px;
                transition: all .2s ease;
            }
            .social-links a:hover { background: var(--primary); color: #fff; transform: translateY(-3px); box-shadow: var(--shadow-md); }
            .social-links img { width: 20px; height: 20px; object-fit: contain; }

            .footer-bottom {
                background: var(--bg-soft);
                border-top: 1px solid var(--border);
                color: var(--muted);
                font-size: 13.5px;
                padding: 16px 0;
                text-align: center;
            }

            /* =========================================================
               FLOATING BUTTONS
            ========================================================= */
            .fab-container { position: fixed; bottom: 70px; right: 14px; z-index: 999; cursor: pointer; }
            .fab-icon-holder {
                width: 52px; height: 52px;
                background: var(--surface);
                border: 1px solid var(--border);
                border-radius: 16px;
                text-align: center;
                box-shadow: var(--shadow-lg);
                transition: transform .2s ease;
                padding: 8px;
            }
            .fab-icon-holder:hover { transform: translateY(-2px) scale(1.04); }
            .fab-icon-holder i {
                display: flex; align-items: center; justify-content: center;
                height: 100%; font-size: 24px; color: #fff;
            }
            .fab-options {
                list-style: none;
                margin: 0; padding: 0;
                position: absolute;
                bottom: 60px; left: 0;
                opacity: 0;
                transition: all .25s ease;
                transform: scale(0);
                transform-origin: 50% bottom;
            }
            .fab:hover + .fab-options,
            .fab-options:hover { opacity: 1; transform: scale(1); }
            .fab-options li { display: flex; padding: 5px 0; }
            .fab-options .fab-icon-holder { padding: 0; border: 0; }

            .back-to-top {
                position: fixed;
                right: 18px; bottom: 18px;
                width: 42px; height: 42px;
                display: none;
                align-items: center; justify-content: center;
                background: var(--primary);
                color: #fff !important;
                border-radius: 12px;
                box-shadow: var(--shadow-md);
                z-index: 998;
            }
            .back-to-top:hover { background: var(--primary-hover); }

            /* =========================================================
               PERBAIKAN: keterbacaan teks & banner full-width
            ========================================================= */
            body { overflow-x: hidden; }

            /* Logo: versi gelap hanya tampil di mode Gelap (jika file logo-dark ada) */
            .brand-logo.logo-dark { display: none; }
            [data-theme="dark"] .brand-logo.logo-light { display: none; }
            [data-theme="dark"] .brand-logo.logo-dark { display: inline-block; }

            /* Teks yang kontrasnya terlalu rendah dikoreksi otomatis oleh JS (lihat fixContrast) */
            .ink-fix { color: var(--ink) !important; opacity: 1 !important; }

            /* Banner/slider mentok kiri-kanan layar */
            .content .carousel,
            .content .owl-carousel,
            .content .flickity-enabled,
            .content .slick-slider {
                width: 100vw;
                max-width: 100vw;
                margin-left: calc(50% - 50vw);
                margin-right: calc(50% - 50vw);
            }
            .content .carousel { margin-top: -40px; }
            .content .carousel .owl-item img,
            .content .flickity-slider img {
                border-radius: 0;
                box-shadow: none;
                width: 100%;
            }
        </style>

        <?php $this->renderSection('css'); ?>
    </head>
    <body>
        <div id="wrapper">
            <header>
                <nav class="navbar navbar-expand-lg fixed-top site-nav">
                    <div class="container">
                        <a class="navbar-brand" href="<?= base_url(); ?>">
                            <?= $logoImg(); ?>
                        </a>

                        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                            <span class="navbar-toggler-icon"></span>
                        </button>

                        <div class="collapse navbar-collapse menu-utama" id="navbarNavAltMarkup">
                            <div class="navbar-nav ml-auto ms-auto">
                                <a class="nav-item nav-link <?= $menu_active == 'Home' ? 'active' : ''; ?>" href="<?= base_url(); ?>">Home</a>
                                <a class="nav-item nav-link <?= $menu_active == 'Cek' ? 'active' : ''; ?>" href="<?= base_url(); ?>/payment">Cek Pesanan</a>
                                <a class="nav-item nav-link <?= $menu_active == 'API' ? 'active' : ''; ?>" href="<?= base_url(); ?>/api/docs" hidden>API</a>
                                <a class="nav-item nav-link <?= $menu_active == 'Price' ? 'active' : ''; ?>" href="<?= base_url(); ?>/price">Daftar Harga</a>
                                <a class="nav-item nav-link <?= $menu_active == 'Method' ? 'active' : ''; ?>" href="<?= base_url(); ?>/method" hidden>Metode</a>

                                <div class="nav-item dropdown">
                                    <a class="nav-link dropdown-toggle" href="#" id="dropdownKalkulator" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Kalkulator ML
                                        <img src="<?= base_url(); ?>/assets/images/down-arrow.png" alt="" width="12" style="vertical-align: middle; margin-left: 4px; opacity: .6;">
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownKalkulator">
                                        <a class="dropdown-item <?= $menu_active == 'kalkulatorwr' ? 'active' : ''; ?>" href="<?= base_url(); ?>/kalkulatorwr">Kalkulator WR</a>
                                        <a class="dropdown-item <?= $menu_active == 'hpmagicwheel' ? 'active' : ''; ?>" href="<?= base_url(); ?>/hpmagicwheel">HP Magic Wheel</a>
                                    </div>
                                </div>

                                <a class="nav-item nav-link <?= $menu_active == 'Login' ? 'active' : ''; ?>" href="<?= base_url(); ?>/login">Login</a>
                                <?php if ($admin !== false): ?>
                                <a class="nav-item nav-link" href="<?= base_url(); ?>/admin">Administrator</a>
                                <?php endif ?>
                                <?php if ($users !== false): ?>
                                <a class="nav-item nav-link" href="<?= base_url(); ?>/user">Beranda</a>
                                <?php endif ?>

                                <button type="button" class="btn-pill ml-lg-2 ms-lg-2 mt-2 mt-lg-0" id="themeToggle" aria-label="Ganti tema">
                                    <span id="themeToggleLabel">Gelap</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </nav>
            </header>

            <?php $this->renderSection('content'); ?>

            <footer id="aboutus" class="bg-footer">
                <svg class="footer-wave" viewBox="0 0 1440 70" preserveAspectRatio="none" aria-hidden="true">
                    <path fill="currentColor" d="M0,40 C240,80 480,0 720,28 C960,56 1200,76 1440,30 L1440,70 L0,70 Z"></path>
                </svg>

                <div class="footer-main">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-6 col-sm-12 mb-4">
                                <?= $logoImg('mb-3 rounded', 'height:48px'); ?>
                                <h5><?= $web['name']; ?></h5>
                                <div><?= $web['description']; ?></div>
                            </div>

                            <div class="col-lg-3 col-sm-6 mb-4">
                                <h5>Halaman</h5>
                                <ul class="menu-list">
                                    <li><a href="<?= base_url(); ?>/">Halaman Utama</a></li>
                                    <li><a href="<?= base_url(); ?>/payment">Cek Pesanan</a></li>
                                    <li><a href="<?= base_url(); ?>/price">Daftar Harga</a></li>
                                    <li><a href="<?= base_url(); ?>/syarat-ketentuan">Syarat &amp; Ketentuan</a></li>
                                </ul>
                            </div>

                            <div class="col-lg-3 col-sm-6 mb-4">
                                <h5>Sosial Media Kami</h5>
                                <div class="social-links">
                                    <a href="<?= $sm['wa']; ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fa fa-whatsapp"></i></a>
                                    <a href="<?= $sm['ig']; ?>" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa fa-instagram"></i></a>
                                    <a href="<?= $sm['yt']; ?>" target="_blank" rel="noopener" aria-label="YouTube" hidden><i class="fa fa-youtube"></i></a>
                                    <a href="<?= $sm['fb']; ?>" target="_blank" rel="noopener" aria-label="Facebook" hidden><i class="fa fa-facebook"></i></a>
                                    <a href="<?= $sm['tw']; ?>" target="_blank" rel="noopener" aria-label="TikTok">
                                        <img src="https://lsgtopup.com/assets/images/tik-tok.png" alt="TikTok">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="fab-container">
                    <div class="fab fab-icon-holder">
                        <svg viewBox="0 0 24 24" width="100%" height="100%" fill="none" stroke="#1d4ed8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-label="Bantuan">
                            <path d="M4 14v-2a8 8 0 0 1 16 0v2"></path>
                            <rect x="3" y="14" width="4" height="6" rx="1.5"></rect>
                            <rect x="17" y="14" width="4" height="6" rx="1.5"></rect>
                            <path d="M19 20c0 1.2-1.5 2-4 2h-2"></path>
                        </svg>
                    </div>

                    <ul class="fab-options">
                        <li>
                            <a href="<?= $sm['ig']; ?>" class="text-decoration-none" target="_blank" rel="noopener">
                                <div class="fab-icon-holder" style="background: radial-gradient(circle farthest-corner at 35% 90%, #fec564, transparent 50%), radial-gradient(circle farthest-corner at 0 140%, #fec564, transparent 50%), radial-gradient(ellipse farthest-corner at 0 -25%, #5258cf, transparent 50%), radial-gradient(ellipse farthest-corner at 100% 0, #893dc2, transparent 50%), radial-gradient(ellipse farthest-corner at 100% 100%, #d9317a, transparent), linear-gradient(#6559ca, #bc318f 30%, #e33f5f 50%, #f77638 70%, #fec66d 100%);">
                                    <i class="fa fa-instagram"></i>
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="<?= $sm['wa']; ?>" class="text-decoration-none" target="_blank" rel="noopener">
                                <div class="fab-icon-holder" style="background-color: #25D366;">
                                    <i class="fa fa-whatsapp"></i>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="footer-bottom">
                    Copyright © <?= date('Y'); ?> <?= $web['name']; ?>. All Rights Reserved.
                </div>
            </footer>
        </div>

        <a href="javascript:void(0);" class="back-to-top" aria-label="Kembali ke atas">
            <i class="fa fa-angle-double-up"></i>
        </a>

        <!--End wrapper-->
        <!-- Bootstrap core JavaScript-->
        <script src="<?= base_url(); ?>/assets/js/jquery.min.js"></script>
        <script src="<?= base_url(); ?>/assets/js/popper.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.2.1/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js"></script>
        <!-- simplebar js -->
        <script src="<?= base_url(); ?>/assets/plugins/simplebar/js/simplebar.js"></script>
        <!-- Custom scripts -->
        <script src="<?= base_url(); ?>/assets/js/app-script.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@activix/double-scroll@1.0.2/jquery.doubleScroll.min.js"></script>
        <script src="<?= base_url(); ?>/assets/plugins/summernote/dist/summernote-bs4.min.js"></script>
        <!--Select Plugins Js-->
        <script src="<?= base_url(); ?>/assets/plugins/select2/js/select2.min.js"></script>
        <!--Data Tables js-->
        <script src="<?= base_url(); ?>/assets/plugins/bootstrap-datatable/js/jquery.dataTables.min.js"></script>
        <script src="<?= base_url(); ?>/assets/plugins/bootstrap-datatable/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.ckeditor.com/4.19.0/standard/ckeditor.js"></script>
        <script src="https://unpkg.com/flickity@2/dist/flickity.pkgd.min.js"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
        <script src="https://code.iconify.design/iconify-icon/1.0.0/iconify-icon.min.js"></script>
        <script src="https://tokovalorant.com/assets/js/promobox.js"></script>
        <script src="https://unpkg.com/bootstrap-table@1.21.0/dist/bootstrap-table.min.js"></script>
        <script src="https://unpkg.com/bootstrap-table@1.21.0/dist/extensions/filter-control/bootstrap-table-filter-control.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Swiper/3.4.1/js/swiper.min.js"></script>

        <script>
            function openNav() {
                var el = document.getElementById("mySidenav");
                if (el) el.style.width = "300px";
            }

            function closeNav() {
                var el = document.getElementById("mySidenav");
                if (el) el.style.width = "0";
            }

            function goBack() {
                window.history.back();
            }

            // Tema terang / gelap
            (function () {
                var root = document.documentElement;
                var label = document.getElementById('themeToggleLabel');
                var btn = document.getElementById('themeToggle');

                function paint() {
                    var dark = root.getAttribute('data-theme') === 'dark';
                    if (label) label.textContent = dark ? 'Terang' : 'Gelap';
                }

                if (btn) {
                    btn.addEventListener('click', function () {
                        var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
                        root.setAttribute('data-theme', next);
                        try { localStorage.setItem('theme', next); } catch (e) {}
                        paint();
                        if (window.fixContrast) window.fixContrast();
                    });
                }
                paint();
            })();

            // Koreksi otomatis teks berkontras rendah (mis. teks putih di latar terang)
            (function () {
                function parse(c) {
                    var m = c && c.match(/rgba?\(([^)]+)\)/);
                    if (!m) return null;
                    var p = m[1].split(',').map(parseFloat);
                    return { r: p[0], g: p[1], b: p[2], a: p.length > 3 ? p[3] : 1 };
                }
                function lum(c) {
                    function f(v) { v /= 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); }
                    return 0.2126 * f(c.r) + 0.7152 * f(c.g) + 0.0722 * f(c.b);
                }
                function ratio(a, b) {
                    var l1 = lum(a), l2 = lum(b);
                    return (Math.max(l1, l2) + 0.05) / (Math.min(l1, l2) + 0.05);
                }
                function bgOf(el) {
                    while (el && el.nodeType === 1) {
                        var cs = getComputedStyle(el);
                        if (cs.backgroundImage && cs.backgroundImage !== 'none') return null;
                        var c = parse(cs.backgroundColor);
                        if (c && c.a > 0.5) return c;
                        el = el.parentElement;
                    }
                    return parse(getComputedStyle(document.body).backgroundColor);
                }
                function hasText(el) {
                    for (var i = 0; i < el.childNodes.length; i++) {
                        var n = el.childNodes[i];
                        if (n.nodeType === 3 && n.nodeValue.trim()) return true;
                    }
                    return false;
                }

                window.fixContrast = function () {
                    document.querySelectorAll('.ink-fix').forEach(function (e) { e.classList.remove('ink-fix'); });
                    if (document.documentElement.getAttribute('data-theme') === 'dark') return;

                    document.querySelectorAll('.content *, #aboutus *').forEach(function (el) {
                        if (!hasText(el)) return;
                        if (el.closest('.btn, .badge, .navbar, .fab-container, .back-to-top, .swal2-container')) return;
                        var cs = getComputedStyle(el);
                        var fg = parse(cs.color), bg = bgOf(el);
                        if (!fg || !bg) return;
                        if (ratio(fg, bg) < 3) el.classList.add('ink-fix');
                    });
                };

                var timer;
                function schedule() { clearTimeout(timer); timer = setTimeout(window.fixContrast, 150); }

                document.addEventListener('DOMContentLoaded', schedule);
                window.addEventListener('load', function () { schedule(); setTimeout(window.fixContrast, 800); });

                var target = document.querySelector('.content') || document.body;
                new MutationObserver(schedule).observe(target, { childList: true, subtree: true });
            })();

            // Tombol kembali ke atas
            $(function () {
                var $btn = $('.back-to-top');
                $(window).on('scroll', function () {
                    if ($(this).scrollTop() > 300) { $btn.css('display', 'inline-flex'); }
                    else { $btn.hide(); }
                });
                $btn.on('click', function () {
                    $('html, body').animate({ scrollTop: 0 }, 400);
                    return false;
                });
            });

            <?php if ($admin !== false): ?>
            function hapus(link) {
                Swal.fire({
                    title: 'Anda yakin?',
                    text: "Data akan dihapus permanen",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#1d4ed8',
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'Batal',
                    confirmButtonText: 'Tetap hapus'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = link;
                    }
                });
            }
            <?php endif; ?>
        </script>

        <?php $this->renderSection('js'); ?>
    </body>
</html>