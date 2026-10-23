<?php defined('_JEXEC') or die; ?>

<link rel="stylesheet" href="/modules/mod_fotos_noticia/media/css/style.css">

<?php if (!empty($images)) : ?>
<table class="tabla-fotos">
    <tr>
        <?php foreach ($images as $index => $img) : ?>
            <td>
                <img src="<?php echo $img; ?>" 
                     loading="lazy" 
                     class="zoomable" 
                     data-index="<?php echo $index; ?>" 
                     alt="">
            </td>
        <?php endforeach; ?>
    </tr>
</table>
<?php endif; ?>

<!-- Lightbox avanzado (SOLO UNO) -->
<div id="lightbox" class="lightbox">
  
  <!-- Flecha izquierda -->
  <span class="arrow arrow-left">⟨</span>

  <!-- Imagen -->
  <img class="lightbox-content" id="lightbox-img">

  <!-- Flecha derecha -->
  <span class="arrow arrow-right">⟩</span>

  <!-- Botón cerrar -->
  <span class="close">&times;</span>

  <!-- Contador -->
  <div class="counter" id="lightbox-counter"></div>

  <!-- Botón descargar -->
  <a id="download-btn" class="download-btn" download>⬇</a>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const images = <?php echo json_encode($images); ?>;
    let currentIndex = 0;
    let scale = 1;
    let slideshowInterval = null;

    const lightbox = document.getElementById('lightbox');
    const lightboxImg = document.getElementById('lightbox-img');
    const closeBtn = document.querySelector('.close');
    const counter = document.getElementById('lightbox-counter');
    const downloadBtn = document.getElementById('download-btn');
    const arrowLeft = document.querySelector('.arrow-left');
    const arrowRight = document.querySelector('.arrow-right');
    const zoomables = document.querySelectorAll('.zoomable');

    function openLightbox(index) {
        currentIndex = index;
        lightbox.style.display = 'flex';
        updateImage();
        startSlideshow(); // AUTO SLIDESHOW
    }

    function updateImage() {
        const src = images[currentIndex];

        // Transición metálica
        lightboxImg.classList.remove("metal-transition");
        void lightboxImg.offsetWidth; 
        lightboxImg.classList.add("metal-transition");

        lightboxImg.src = src;
        counter.textContent = (currentIndex + 1) + " / " + images.length;
        downloadBtn.href = src;
        scale = 1;
        lightboxImg.style.transform = "scale(1)";
    }

    function closeLightbox() {
        lightbox.style.display = 'none';
        stopSlideshow();
    }

    function nextImage() {
        currentIndex = (currentIndex + 1) % images.length;
        updateImage();
    }

    function prevImage() {
        currentIndex = (currentIndex - 1 + images.length) % images.length;
        updateImage();
    }

    zoomables.forEach(img => {
        img.addEventListener('click', () => {
            openLightbox(parseInt(img.dataset.index));
        });
    });

    closeBtn.addEventListener('click', closeLightbox);
    arrowRight.addEventListener('click', nextImage);
    arrowLeft.addEventListener('click', prevImage);

    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) closeLightbox();
    });

    document.addEventListener('keydown', (e) => {
        if (lightbox.style.display === 'flex') {
            if (e.key === 'ArrowRight') nextImage();
            if (e.key === 'ArrowLeft') prevImage();
            if (e.key === 'Escape') closeLightbox();
        }
    });

    // Zoom con scroll
    lightboxImg.addEventListener('wheel', (e) => {
        e.preventDefault();
        scale += e.deltaY * -0.001;
        scale = Math.min(Math.max(.5, scale), 3);
        lightboxImg.style.transform = `scale(${scale})`;
    });

    // SLIDESHOW AUTOMÁTICO
    function startSlideshow() {
        stopSlideshow();
        slideshowInterval = setInterval(nextImage, 4000); // Cambia cada 4 segundos
    }

    function stopSlideshow() {
        if (slideshowInterval) clearInterval(slideshowInterval);
    }

});
</script>

