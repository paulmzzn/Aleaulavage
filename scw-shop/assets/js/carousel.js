/**
 * Promo Carousel JavaScript
 * Gère le défilement automatique du carousel
 *
 * @package SCW_Shop
 */

(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {

        // ═══════════════════════════════════════════════════════════
        // PRODUCT SLIDERS NAVIGATION
        // ═══════════════════════════════════════════════════════════
        document.querySelectorAll('.product-slider-section').forEach(section => {
            const viewport = section.querySelector('.slider-viewport');
            const prevBtn = section.querySelector('.nav-btn.prev');
            const nextBtn = section.querySelector('.nav-btn.next');

            if (!viewport) return;

            const scrollAmount = 300; // Scroll by ~1 card width

            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    viewport.scrollBy({
                        left: -scrollAmount,
                        behavior: 'smooth'
                    });
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    viewport.scrollBy({
                        left: scrollAmount,
                        behavior: 'smooth'
                    });
                });
            }
        });

        // ═══════════════════════════════════════════════════════════
        // PROMO CAROUSEL
        // ═══════════════════════════════════════════════════════════
        const carousel = document.querySelector('.promo-carousel');
        if (!carousel) return;

        const slides = carousel.querySelectorAll('.promo-slide-content');
        const dots = carousel.querySelectorAll('.dot');
        const timerCircle = carousel.querySelector('.timer-progress');
        const pauseIcon = carousel.querySelector('.pause-icon');

        let currentIndex = 0;
        let isPaused = false;
        let progress = 0;
        let intervalId = null;
        let progressId = null;

        const DURATION = 8000; // 8 secondes
        const INTERVAL = 50; // Mise à jour toutes les 50ms

        // Configuration du cercle SVG
        const radius = 18;
        const circumference = 2 * Math.PI * radius;

        /**
         * Afficher une slide spécifique
         */
        function showSlide(index) {
            // Cacher toutes les slides
            slides.forEach(slide => {
                slide.classList.add('hidden');
            });

            // Afficher la slide active
            if (slides[index]) {
                slides[index].classList.remove('hidden');
                // Forcer le reflow pour relancer l'animation
                slides[index].style.animation = 'none';
                setTimeout(() => {
                    slides[index].style.animation = '';
                }, 10);
            }

            // Mettre à jour les dots
            dots.forEach((dot, i) => {
                if (i === index) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });

            // Changer le thème du carousel
            carousel.classList.remove('theme-winter', 'theme-eco', 'theme-tech');
            const themes = ['theme-winter', 'theme-eco', 'theme-tech'];
            carousel.classList.add(themes[index]);

            currentIndex = index;
            progress = 0;
        }

        /**
         * Slide suivante
         */
        function nextSlide() {
            const nextIndex = (currentIndex + 1) % slides.length;
            showSlide(nextIndex);
        }

        /**
         * Mettre à jour la barre de progression
         */
        function updateProgress() {
            if (isPaused) return;

            progress += (INTERVAL / DURATION) * 100;

            if (progress >= 100) {
                nextSlide();
                progress = 0;
            }

            // Mettre à jour le cercle SVG
            if (timerCircle) {
                const strokeDashoffset = circumference - (progress / 100) * circumference;
                timerCircle.style.strokeDashoffset = strokeDashoffset;
            }
        }

        /**
         * Démarrer le carousel
         */
        function startCarousel() {
            if (progressId) return;

            progressId = setInterval(updateProgress, INTERVAL);
        }

        /**
         * Arrêter le carousel
         */
        function stopCarousel() {
            if (progressId) {
                clearInterval(progressId);
                progressId = null;
            }
        }

        /**
         * Pause au survol
         */
        carousel.addEventListener('mouseenter', function() {
            isPaused = true;
            if (pauseIcon) {
                pauseIcon.style.opacity = '1';
            }
        });

        carousel.addEventListener('mouseleave', function() {
            isPaused = false;
            if (pauseIcon) {
                pauseIcon.style.opacity = '0';
            }
        });

        /**
         * Navigation par dots
         */
        dots.forEach((dot, index) => {
            dot.addEventListener('click', function() {
                stopCarousel();
                showSlide(index);
                startCarousel();
            });
        });

        // Initialiser le cercle SVG
        if (timerCircle) {
            timerCircle.style.strokeDasharray = circumference;
            timerCircle.style.strokeDashoffset = circumference;
        }

        // Afficher la première slide
        showSlide(0);

        // Démarrer le carousel
        startCarousel();
    });

})();
