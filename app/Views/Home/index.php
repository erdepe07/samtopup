<?php $this->extend('template'); ?>

<?php $this->section('css'); ?>
<style>
    .home-page { padding-top: var(--nav-h); }

    /* =========================================================
       BANNER FULL LAYAR (kiri-kanan mentok)
    ========================================================= */
    .hero-banner {
        position: relative;
        width: 100%;
        background: var(--bg-soft);
        overflow: hidden;
    }
    .hero-banner .swiper-container {
        width: 100% !important;
        max-width: 100%;
        margin: 0;
        padding: 0;
    }
    .hero-banner .swiper-slide {
        width: 100%;
        height: auto;
        padding: 0;
    }
    .hero-banner .swiper-slide img {
        display: block;
        width: 100%;
        height: auto;
        max-height: 560px;       /* batas tinggi di layar lebar; ubah sesuai selera */
        object-fit: cover;
        object-position: center;
        margin: 0;
        border-radius: 0;
        box-shadow: none;
    }
    .hero-banner .swiper-pagination {
        position: absolute;
        bottom: 52px;
        left: 0;
        width: 100%;
        text-align: center;
        z-index: 10;
    }
    .hero-banner .swiper-pagination-bullet {
        width: 8px;
        height: 8px;
        margin: 0 4px;
        border: 0;
        border-radius: 999px;
        background: rgba(255, 255, 255, .7);
        opacity: 1;
        transition: all .25s ease;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .35);
    }
    .hero-banner .swiper-pagination-bullet-active {
        width: 26px;
        height: 8px;
        background: var(--primary);
    }

    @media (max-width: 575.98px) {
        .hero-banner .swiper-pagination { bottom: 44px; }
    }

    /* =========================================================
       KATEGORI + PENCARIAN
    ========================================================= */
    .cat-card {
        position: relative;
        z-index: 5;
        margin-top: -34px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 8px;
        box-shadow: var(--shadow-lg);
    }
    .tab-category {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        gap: 6px;
        scrollbar-width: none;
    }
    .tab-category::-webkit-scrollbar { display: none; }
    .tab-category .nav-item { flex: 1 0 76px; }

    .tab-category.nav-pills .nav-link {
        margin: 0 !important;
        padding: 12px 6px;
        border-radius: 14px;
        color: var(--muted);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        text-align: center;
        background: transparent;
        border: 0;
        transition: all .2s ease;
    }
    .tab-category.nav-pills .nav-link i {
        display: block;
        font-size: 24px;
        margin-bottom: 6px;
    }
    .tab-category.nav-pills .nav-link:hover {
        color: var(--primary);
        background: var(--primary-soft);
    }
    .tab-category.nav-pills .nav-link.active,
    .tab-category.nav-pills .show > .nav-link {
        color: #fff !important;
        background: var(--primary);
        border: 0;
        box-shadow: 0 8px 18px -8px rgba(29, 78, 216, .7);
    }

    .search-box {
        display: flex;
        gap: 10px;
        margin: 22px 0 34px;
    }
    .search-box .form-control {
        flex: 1;
        height: 50px;
        border-radius: 999px;
        padding: 0 22px;
        box-shadow: var(--shadow-sm);
    }
    .search-box .btn {
        height: 50px;
        padding: 0 30px;
        border-radius: 999px;
    }

    /* =========================================================
       DAFTAR GAME
    ========================================================= */
    .section-title {
        display: block;
        margin-bottom: 22px;
    }
    .section-title h5 {
        display: inline-block;
        margin: 0;
        font-size: 22px;
        font-weight: 800;
        line-height: 1.2;
    }
    .section-title .title-bar {
        display: block;
        position: static;
        width: 100%;
        max-width: 44px;
        height: 4px;
        margin-top: 4px;
        border-radius: 999px;
        background: var(--primary);
    }

    .game-col { margin-bottom: 22px; }
    .game-card {
        display: block;
        height: 100%;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 18px;
        padding: 12px 12px 14px;
        text-align: center;
        text-decoration: none !important;
        box-shadow: var(--shadow-sm);
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    }
    .game-card:hover {
        transform: translateY(-5px);
        border-color: var(--primary);
        box-shadow: var(--shadow-lg);
    }
    .game-card img {
        display: block;
        width: 100%;
        aspect-ratio: 1 / 1;
        object-fit: cover;
        border-radius: 14px;
        margin-bottom: 12px;
    }
    .game-card .game-name {
        color: var(--ink);
        font-size: 13.5px;
        font-weight: 700;
        line-height: 1.35;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.7em;
    }

    /* =========================================================
       METODE PEMBAYARAN
    ========================================================= */
    .pay-title { text-align: center; margin: 40px 0 22px; }
    .pay-title h2 { font-size: 26px; font-weight: 800; margin: 0; }
    .pay-title p { margin: 6px 0 0; font-size: 14.5px; }

    .owl-carousel { position: relative; width: 100%; }
    .owl-carousel .owl-stage { position: relative; }
    .owl-carousel .owl-stage:after {
        content: ".";
        display: block;
        clear: both;
        visibility: hidden;
        line-height: 0;
        height: 0;
    }
    .owl-carousel .owl-stage-outer { position: relative; overflow: hidden; }
    .owl-carousel.owl-loaded { display: block; }

    .item .metode {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 72px;
        padding: 10px 14px;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 14px;
    }
    .item .metode img {
        max-height: 100%;
        width: auto;
        max-width: 100%;
        object-fit: contain;
    }
    .metode-bottom { margin-bottom: 60px !important; }
</style>
<?php $this->endSection(); ?>


<?php $this->section('content'); ?>
<script>
    var demoImage = '<?= base_url(); ?>/assets/images/<?= $popup['gambarpopup']; ?>';
</script>

<div class="content home-page">

    <!-- Banner full layar -->
    <section class="hero-banner">
        <div class="swiper-container hero-swiper">
            <div class="swiper-wrapper">
                <?php $no = 1; foreach ($banner as $loop): ?>
                <div class="swiper-slide">
                    <img src="<?= base_url(); ?>/assets/images/banner/<?= $loop['image']; ?>" alt="Banner <?= $no; ?>">
                </div>
                <?php $no++; endforeach ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </section>

    <!-- Kategori + pencarian -->
    <div class="container">
        <div class="cat-card">
            <ul class="nav nav-pills tab-category" role="tablist">
                <?php $no = 1; foreach ($games as $game): ?>
                <li class="nav-item text-center">
                    <a class="nav-link <?= $no == 1 ? 'active' : ''; ?>"
                       data-bs-toggle="pill"
                       href="#pills-<?= url_title($game['category'], '-', true); ?>"
                       role="tab">
                        <?php if ($game['category'] == 'Games'): ?>
                            <i class="fa fa-gamepad"></i>
                        <?php elseif ($game['category'] == 'Voucher'): ?>
                            <i class="fa fa-ticket"></i>
                        <?php elseif ($game['category'] == 'Pulsa'): ?>
                            <i class="fa fa-phone"></i>
                        <?php elseif ($game['category'] == 'Data'): ?>
                            <i class="fa fa-mobile"></i>
                        <?php elseif ($game['category'] == 'PLN'): ?>
                            <i class="fa fa-flash"></i>
                        <?php else: ?>
                            <i class="fa fa-credit-card"></i>
                        <?php endif; ?>
                        <?= $game['category']; ?>
                    </a>
                </li>
                <?php $no++; endforeach ?>
            </ul>
        </div>

        <form action="" method="POST" class="search-box">
            <input type="text" class="form-control" autocomplete="off"
                   placeholder="Mau topup games apa?"
                   name="search" value="<?= esc($search ?? ''); ?>">
            <button class="btn btn-primary" type="submit">Cari</button>
        </form>
    </div>

    <!-- Daftar game per kategori -->
    <div class="container">
        <div class="tab-content">
            <?php $no = 1; foreach ($games as $game): ?>
            <div class="tab-pane fade <?= $no == 1 ? 'show active' : ''; ?>"
                 id="pills-<?= url_title($game['category'], '-', true); ?>"
                 role="tabpanel">

                <div class="section-title">
                    <h5><?= $game['category']; ?></h5>
                    <span class="title-bar"></span>
                </div>

                <div class="row game">
                    <?php foreach ($game['games'] as $loop): ?>
                    <?php if ($loop['status'] == 'On'): ?>
                    <div class="col-lg-2 col-md-3 col-4 game-col">
                        <a href="<?= base_url(); ?>/games/<?= $loop['slug']; ?>" class="game-card product_list">
                            <img src="<?= base_url(); ?>/assets/images/games/<?= $loop['image']; ?>"
                                 alt="<?= $loop['games']; ?>" loading="lazy">
                            <div class="game-name"><?= $loop['games']; ?></div>
                        </a>
                    </div>
                    <?php endif; endforeach ?>
                </div>
            </div>
            <?php $no++; endforeach ?>
        </div>
    </div>

    <!-- Metode pembayaran -->
    <div class="container pay-title">
        <h2>Metode Pembayaran</h2>
        <p>Bayar dengan berbagai pilihan yang aman dan praktis.</p>
    </div>

    <div class="owl-carousel metode-top owl-theme">
        <?php foreach ($method as $loop): ?>
        <div class="item">
            <div class="metode">
                <img src="<?= base_url(); ?>/assets/images/method/<?= $loop['image']; ?>" alt="">
            </div>
        </div>
        <?php endforeach ?>
    </div>

    <div class="owl-carousel metode-bottom owl-theme mt-3">
        <?php foreach ($method as $loop): ?>
        <div class="item">
            <div class="metode">
                <img src="<?= base_url(); ?>/assets/images/method/<?= $loop['image']; ?>" alt="">
            </div>
        </div>
        <?php endforeach ?>
    </div>

</div>
<?php $this->endSection(); ?>


<?php $this->section('js'); ?>
<script>
    // Banner geser otomatis, satu slide penuh lebar layar
    var swiper = new Swiper('.hero-swiper', {
        pagination: '.swiper-pagination',
        paginationClickable: true,
        loop: true,
        autoplay: 3500,
        autoplayDisableOnInteraction: false,
        speed: 600,
        grabCursor: true,
        slidesPerView: 1,
        spaceBetween: 0
    });
</script>
<script>
    $('.metode-top').owlCarousel({
        loop: true,
        margin: 12,
        autoplay: true,
        autoplayTimeout: 1800,
        smartSpeed: 600,
        responsive: {
            0:    { items: 3 },
            600:  { items: 4 },
            1000: { items: 7 }
        }
    });
    $('.metode-bottom').owlCarousel({
        loop: true,
        margin: 12,
        autoplay: true,
        autoplayTimeout: 1800,
        smartSpeed: 600,
        rtl: true,
        responsive: {
            0:    { items: 3 },
            600:  { items: 4 },
            1000: { items: 7 }
        }
    });
</script>
<?php $this->endSection(); ?>