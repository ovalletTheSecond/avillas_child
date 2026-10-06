/**
 * Bannière vidéo sticky - pan du haut vers le bas de la vidéo
 * et assombrissement progressif, pilotés par le scroll.
 */
(function () {
    'use strict';

    var banner = document.querySelector('.page-head--video');
    if (!banner) {
        return;
    }

    var video = banner.querySelector('.page-head__video-bg');
    var overlay = banner.querySelector('.page-head__video-overlay');
    if (!video || !overlay) {
        return;
    }

    var maxScroll = 1500; // distance de scroll (px) pour atteindre l'état final (augmentée pour disparaître plus tard)
    var maxOpacity = 1;
    var panFactor = 1.4; // correspond au 240% de hauteur défini en CSS (100% + 140%)
    var ticking = false;
    var videoHidden = false;

    function update() {
        var scrollY = window.pageYOffset || window.scrollY;
        var progress = Math.min(1, Math.max(0, scrollY / maxScroll));

        overlay.style.opacity = String(progress * maxOpacity);

        // La vidéo est plus haute que le cadre : on la déplace vers le haut
        // pour révéler progressivement le bas de la vidéo pendant le scroll.
        var containerHeight = banner.getBoundingClientRect().height;
        var extraHeight = containerHeight * panFactor;
        video.style.transform = 'translateY(-' + (progress * extraHeight) + 'px)';

        // Une fois l'assombrissement complet, on masque le conteneur et met en pause la vidéo.
        if (progress >= 1 && !videoHidden) {
            banner.classList.add('is-fully-covered');
            video.pause();
            videoHidden = true;
        } else if (progress < 1 && videoHidden) {
            banner.classList.remove('is-fully-covered');
            video.play();
            videoHidden = false;
        }

        ticking = false;
    }

    function onScroll() {
        if (!ticking) {
            window.requestAnimationFrame(update);
            ticking = true;
        }
    }

    update();
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll, { passive: true });
})();
