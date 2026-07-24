/**
 * Hero Slider Initialization
 * Separate file to ensure slider loads properly
 */

document.addEventListener('DOMContentLoaded', function() {
    // Hero Swiper
    const heroSwiper = new Swiper('.hero-swiper', {
        loop: true,
        autoplay: {
            delay: 6000,
            disableOnInteraction: false,
        },
        speed: 1000,
        effect: 'fade',
        fadeEffect: {
            crossFade: true
        },
        pagination: {
            el: '.hero-pagination',
            clickable: true,
            renderBullet: function (index, className) {
                return '<span class="' + className + '"></span>';
            },
        },
    });

    // Trust/Brand Words Slider
    const trustSwiper = new Swiper('.trust-swiper', {
        loop: true,
        autoplay: {
            delay: 0,
            disableOnInteraction: false,
        },
        speed: 5000,
        slidesPerView: 'auto',
        spaceBetween: 60,
        allowTouchMove: false,
        freeMode: true,
        freeModeMomentum: false,
    });

    // Products Creative Slider
    const productsSwiper = new Swiper('.swiper-products', {
        slidesPerView: 1,
        spaceBetween: 24,
        loop: false,
        navigation: {
            nextEl: '.creative-nav-next',
            prevEl: '.creative-nav-prev',
        },
        breakpoints: {
            640: {
                slidesPerView: 2,
                spaceBetween: 24,
            },
            1024: {
                slidesPerView: 3,
                spaceBetween: 32,
            },
            1280: {
                slidesPerView: 4,
                spaceBetween: 32,
            },
        },
        on: {
            init: function() {
                updateProgressBar(this);
            },
            slideChange: function() {
                updateProgressBar(this);
            },
        },
    });

    function updateProgressBar(swiper) {
        const progressBar = document.querySelector('.slider-progress-bar');
        if (!progressBar) return;
        
        const progress = ((swiper.realIndex + 1) / swiper.slides.length) * 100;
        progressBar.style.width = progress + '%';
    }

    console.log('✓ Hero slider initialized');
});
