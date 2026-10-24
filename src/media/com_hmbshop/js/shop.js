/* HMB Tienda: efecto de las tarjetas de categoria (antes en el override de Phoca) */
(function () {
    document.querySelectorAll('.hmb-cat-premium').forEach(function (c) {
        var img = c.querySelector('.hmb-cat-p-img');
        if (!img || img.classList.contains('hmb-cat-p-noimg')) { return; }
        c.addEventListener('mousemove', function (e) {
            var r = c.getBoundingClientRect();
            img.style.transform = 'scale(1.08) translate(' + ((e.clientX - r.left) / r.width - .5) * 12 + 'px,' + ((e.clientY - r.top) / r.height - .5) * 8 + 'px)';
        });
        c.addEventListener('mouseleave', function () { img.style.transform = ''; });
    });
})();
