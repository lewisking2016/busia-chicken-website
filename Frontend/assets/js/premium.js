/**
 * Busia Chicken Farm — Premium Motion Layer
 * Navigation, scroll reveals, counters, parallax, magnetic buttons,
 * hero + product sliders, back-to-top, scroll progress.
 * Vanilla JS + GSAP where available. No external dependencies.
 */
(function () {
    'use strict';

    const hasGSAP = typeof gsap !== 'undefined';

    /* ── Scroll progress bar ─────────────────────────────────── */
    const progress = document.getElementById('scroll-progress');
    if (progress) {
        const update = () => {
            const doc = document.documentElement;
            const max = doc.scrollHeight - window.innerHeight;
            progress.style.width = (max > 0 ? (window.scrollY / max) * 100 : 0) + '%';
        };
        window.addEventListener('scroll', update, { passive: true });
        update();
    }

    /* ── Navbar scroll state ─────────────────────────────────── */
    const nav = document.getElementById('site-nav');
    if (nav) {
        const onScroll = () => nav.classList.toggle('nav-scrolled', window.scrollY > 40);
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    /* ── Mobile drawer ───────────────────────────────────────── */
    const menuBtn = document.getElementById('mobile-menu-btn');
    const mainNav = document.getElementById('main-nav');
    if (menuBtn && mainNav) {
        const close = () => {
            mainNav.classList.remove('active');
            menuBtn.classList.remove('active');
            menuBtn.setAttribute('aria-expanded', 'false');
            document.body.style.overflow = '';
        };
        const open = () => {
            mainNav.classList.add('active');
            menuBtn.classList.add('active');
            menuBtn.setAttribute('aria-expanded', 'true');
            document.body.style.overflow = 'hidden';
        };

        menuBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            mainNav.classList.contains('active') ? close() : open();
        });

        mainNav.querySelectorAll('a').forEach((link) => link.addEventListener('click', close));

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.premium-nav') && mainNav.classList.contains('active')) close();
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') close();
        });
    }

    /* ── Back to top ─────────────────────────────────────────── */
    const toTop = document.createElement('button');
    toTop.className = 'back-to-top';
    toTop.setAttribute('aria-label', 'Back to top');
    toTop.innerHTML = '<i data-lucide="arrow-up" style="width:20px;height:20px;"></i>';
    document.body.appendChild(toTop);
    if (typeof lucide !== 'undefined') lucide.createIcons();

    toTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    window.addEventListener('scroll', () => {
        toTop.classList.toggle('visible', window.scrollY > 600);
    }, { passive: true });

    /* ── Scroll reveals ──────────────────────────────────────── */
    const revealEls = document.querySelectorAll('[data-reveal]');
    if (revealEls.length) {
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const el = entry.target;
                const delay = parseFloat(el.dataset.revealDelay || 0);
                setTimeout(() => el.classList.add('revealed'), delay);
                revealObserver.unobserve(el);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });

        revealEls.forEach((el) => revealObserver.observe(el));
    }

    /* Staggered groups: reveal children with per-index delay */
    document.querySelectorAll('[data-reveal-group]').forEach((group) => {
        const children = group.children;
        Array.from(children).forEach((child, i) => {
            child.setAttribute('data-reveal', '');
            child.setAttribute('data-reveal-delay', String(i * 110));
        });
        const gObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                Array.from(entry.target.children).forEach((child) => child.classList.add('revealed'));
                gObserver.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });
        gObserver.observe(group);
    });

    /* ── Counters ────────────────────────────────────────────── */
    const counters = document.querySelectorAll('[data-counter]');
    if (counters.length) {
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                const el = entry.target;
                const target = parseFloat(el.dataset.counter || '0');
                const decimals = (el.dataset.decimals ? parseInt(el.dataset.decimals, 10) : 0);
                const prefix = el.dataset.prefix || '';
                const suffix = el.dataset.suffix || '';
                const duration = parseInt(el.dataset.duration || '2200', 10);
                const start = performance.now();

                const tick = (now) => {
                    const p = Math.min((now - start) / duration, 1);
                    const eased = 1 - Math.pow(1 - p, 4); // easeOutQuart
                    const value = target * eased;
                    el.textContent = prefix + value.toFixed(decimals) + suffix;
                    if (p < 1) requestAnimationFrame(tick);
                };
                requestAnimationFrame(tick);
                counterObserver.unobserve(el);
            });
        }, { threshold: 0.4 });
        counters.forEach((c) => counterObserver.observe(c));
    }

    /* ── Parallax drift on [data-parallax] images ────────────── */
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches === false) {
        const parallaxEls = document.querySelectorAll('[data-parallax]');
        if (parallaxEls.length) {
            const parObserver = new IntersectionObserver((entries) => {
                entries.forEach((entry) => {
                    if (!entry.isIntersecting) return;
                    const el = entry.target;
                    const speed = parseFloat(el.dataset.parallax || '0.12');
                    let raf = null;

                    const onScroll = () => {
                        if (raf) return;
                        raf = requestAnimationFrame(() => {
                            const rect = el.getBoundingClientRect();
                            const vh = window.innerHeight;
                            const offset = (rect.top + rect.height / 2 - vh / 2) * speed;
                            el.style.transform = 'translate3d(0, ' + offset.toFixed(1) + 'px, 0)';
                            raf = null;
                        });
                    };

                    window.addEventListener('scroll', onScroll, { passive: true });
                    onScroll();
                    parObserver.unobserve(el);
                });
            }, { threshold: 0.05 });
            parallaxEls.forEach((el) => parObserver.observe(el));
        }
    }

    /* ── Magnetic buttons ────────────────────────────────────── */
    if (hasGSAP && window.matchMedia('(pointer: fine)').matches) {
        document.querySelectorAll('[data-magnetic]').forEach((btn) => {
            btn.addEventListener('mousemove', (e) => {
                const rect = btn.getBoundingClientRect();
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;
                gsap.to(btn, { x: x * 0.25, y: y * 0.25, duration: 0.4, ease: 'power2.out' });
            });
            btn.addEventListener('mouseleave', () => {
                gsap.to(btn, { x: 0, y: 0, duration: 0.7, ease: 'elastic.out(1, 0.4)' });
            });
        });
    }

    /* ── Hero slider (single init) ───────────────────────────── */
    const heroSwiper = document.querySelector('.hero-swiper');
    if (heroSwiper && typeof Swiper !== 'undefined') {
        const animateSlide = (slide) => {
            if (!slide || !hasGSAP) return;
            const els = slide.querySelectorAll('.hero-anim');
            gsap.fromTo(els,
                { opacity: 0, y: 36 },
                { opacity: 1, y: 0, duration: 1, stagger: 0.12, ease: 'power3.out', delay: 0.15 }
            );
        };

        new Swiper('.hero-swiper', {
            loop: true,
            speed: 1300,
            effect: 'fade',
            fadeEffect: { crossFade: true },
            autoplay: { delay: 7000, disableOnInteraction: false },
            pagination: { el: '.hero-pagination', clickable: true },
            on: {
                init: function () { animateSlide(this.slides[this.activeIndex]); },
                slideChangeTransitionStart: function () {
                    animateSlide(this.slides[this.activeIndex]);
                }
            }
        });
    }

    /* ── Featured products slider ────────────────────────────── */
    const creativeSlider = document.querySelector('.creative-slider');
    if (creativeSlider && typeof Swiper !== 'undefined') {
        new Swiper('.creative-slider', {
            slidesPerView: 1,
            spaceBetween: 26,
            centeredSlides: false,
            loop: true,
            speed: 800,
            autoplay: { delay: 5000, disableOnInteraction: false },
            navigation: {
                nextEl: '.creative-nav-next',
                prevEl: '.creative-nav-prev'
            },
            breakpoints: {
                640: { slidesPerView: 2 },
                1024: { slidesPerView: 3 },
                1280: { slidesPerView: 4, spaceBetween: 32 }
            }
        });
    }

    /* ── Footer newsletter ───────────────────────────────────── */
    document.querySelectorAll('.f-newsletter form').forEach((form) => {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const input = form.querySelector('input');
            const btn = form.querySelector('button');
            if (!input || !input.value.trim()) return;
            const original = btn.textContent;
            btn.textContent = '✓ Subscribed';
            input.value = '';
            setTimeout(() => { btn.textContent = original; }, 2600);
        });
    });
})();
