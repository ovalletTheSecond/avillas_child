/**
 * Avillas Parallax - JavaScript
 * Applique un effet parallax doux aux sections explicitement marquées
 * avec data-parallax="true".
 */
(function () {
    'use strict';

    var sections = [];

    function initParallax() {
        sections = Array.prototype.slice.call(
            document.querySelectorAll('.avillas-parallax-section[data-parallax="true"]')
        );

        if (!sections.length || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return;
        }

        if (window.innerWidth <= 1024) {
            return;
        }

        updatePositions();
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onResize, { passive: true });
    }

    function onScroll() {
        window.requestAnimationFrame(updatePositions);
    }

    function onResize() {
        if (window.innerWidth <= 1024) {
            window.removeEventListener('scroll', onScroll, { passive: true });
            resetPositions();
            return;
        }
        updatePositions();
    }

    function updatePositions() {
        var scrollY = window.pageYOffset || window.scrollY;

        sections.forEach(function (section) {
            var rect = section.getBoundingClientRect();
            var speed = parseFloat(section.getAttribute('data-parallax-speed')) || 0.5;
            var sectionTop = rect.top + scrollY;
            var offset = (scrollY - sectionTop) * speed * 0.1;

            offset = Math.max(-80, Math.min(80, offset));

            section.style.backgroundPositionY = 'calc(50% + ' + offset + 'px)';
        });
    }

    function resetPositions() {
        sections.forEach(function (section) {
            section.style.backgroundPositionY = 'center';
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initParallax);
    } else {
        initParallax();
    }
})();
