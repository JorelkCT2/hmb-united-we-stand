<?php
defined('_JEXEC') or die;

$layout      = in_array($layout, ['grid', 'list', 'featured', 'ticker']) ? $layout : 'grid';
$aspect      = htmlspecialchars($imageAspect, ENT_QUOTES, 'UTF-8');
$accentHex   = htmlspecialchars($accentColor, ENT_QUOTES, 'UTF-8');

// CSS del efecto según parámetro — usando background-image para pan puro sin deformación
$effectCSS = match($imageEffect) {
    'pan_down' =>
        ".hmb-card__image-wrap{background-position:top center;transition:background-position 3s ease;}
         .hmb-card:hover .hmb-card__image-wrap{background-position:bottom center;}",
    'pan_up' =>
        ".hmb-card__image-wrap{background-position:bottom center;transition:background-position 3s ease;}
         .hmb-card:hover .hmb-card__image-wrap{background-position:top center;}",
    'zoom_in' =>
        ".hmb-card__image-wrap{background-size:100% auto;background-position:center;transition:background-size .6s ease;}
         .hmb-card:hover .hmb-card__image-wrap{background-size:110% auto;}",
    'zoom_out' =>
        ".hmb-card__image-wrap{background-size:110% auto;background-position:center;transition:background-size .6s ease;}
         .hmb-card:hover .hmb-card__image-wrap{background-size:100% auto;}",
    default =>
        ".hmb-card__image-wrap{background-position:center;background-size:cover;}",
};
?>
<style>
.hmb-latestnews{
    --hmb-accent:    <?php echo $accentHex; ?>;
    --hmb-dark:      #0d0d0d;
    --hmb-dark2:     #1a1a1a;
    --hmb-dark3:     #222;
    --hmb-border:    #333;
    --hmb-text:      #e8e8e8;
    --hmb-text-muted:#999;
    --hmb-white:     #fff;
    font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;
}
<?php if ($darkMode): ?>.hmb-latestnews{background:var(--hmb-dark);color:var(--hmb-text);}<?php endif; ?>

/* ── Grid ── */
.hmb-latestnews .hmb-grid{display:grid;gap:1.25rem;}
.hmb-latestnews .hmb-grid--2{grid-template-columns:repeat(2,1fr);}
.hmb-latestnews .hmb-grid--3{grid-template-columns:repeat(3,1fr);}
.hmb-latestnews .hmb-grid--4{grid-template-columns:repeat(4,1fr);}
.hmb-latestnews .hmb-list{display:flex;flex-direction:column;gap:.75rem;}

/* ── Card ── */
.hmb-latestnews .hmb-card{background:var(--hmb-dark2);border:1px solid var(--hmb-border);border-top:3px solid var(--hmb-accent);border-radius:4px;overflow:hidden;display:flex;flex-direction:column;height:100%;transition:box-shadow .25s;}
.hmb-latestnews .hmb-card:hover{box-shadow:0 8px 24px rgba(0,0,0,.5);}
.hmb-latestnews .hmb-list .hmb-card{flex-direction:row;border-top:none;border-left:3px solid var(--hmb-accent);}

/* ── Imagen: background-image para efectos puros ── */
.hmb-latestnews .hmb-card__image-wrap{
    width:100%;
    aspect-ratio:<?php echo $aspect; ?>;
    position:relative;
    overflow:hidden;
    background-color:var(--hmb-dark3);
    background-repeat:no-repeat;
    background-size:100% auto;
    flex-shrink:0;
}
.hmb-latestnews .hmb-list .hmb-card__image-wrap{
    flex:0 0 180px;
    width:180px;
    aspect-ratio:unset;
    min-height:120px;
}
/* Enlace invisible que cubre toda la imagen */
.hmb-latestnews .hmb-card__image-wrap .hmb-img-link{
    display:block;
    position:absolute;
    inset:0;
    z-index:1;
}
/* Img oculta para SEO */
.hmb-latestnews .hmb-card__image-wrap img.hmb-seo{
    position:absolute;
    width:1px;height:1px;opacity:0;pointer-events:none;
}
/* Placeholder sin imagen */
.hmb-latestnews .hmb-card__image-placeholder{
    width:100%;aspect-ratio:<?php echo $aspect; ?>;
    display:flex;align-items:center;justify-content:center;
    background:linear-gradient(135deg,var(--hmb-dark3),#111);
    color:var(--hmb-border);
}

/* ── Efecto elegido ── */
<?php echo $effectCSS; ?>

/* ── Badge categoría ── */
.hmb-latestnews .hmb-badge{display:inline-block;text-decoration:none;cursor:pointer;padding:2px 8px;font-size:.68rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;border-radius:2px;color:#fff;background:var(--hmb-accent);}
.hmb-latestnews .hmb-badge:hover{opacity:.8;color:#fff;}

/* ── Body ── */
.hmb-latestnews .hmb-card__body{padding:1rem;display:flex;flex-direction:column;flex:1;gap:.5rem;}
.hmb-latestnews .hmb-meta{display:flex;flex-wrap:wrap;gap:.75rem;font-size:.75rem;color:var(--hmb-text-muted);}
.hmb-latestnews .hmb-date{display:flex;align-items:center;gap:.6rem;margin:.1rem 0 .2rem;}
.hmb-latestnews .hmb-date__line{display:inline-block;width:22px;height:2px;background:var(--hmb-accent);flex-shrink:0;}
.hmb-latestnews .hmb-date__text{font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--hmb-accent);}
.hmb-latestnews .hmb-card__title{font-size:1rem;font-weight:700;line-height:1.35;margin:0;}
.hmb-latestnews .hmb-card__title a{color:var(--hmb-white);text-decoration:none;transition:color .2s;}
.hmb-latestnews .hmb-card__title a:hover{color:var(--hmb-accent);}
.hmb-latestnews .hmb-card__intro{font-size:.85rem;color:var(--hmb-text-muted);line-height:1.55;flex:1;}
.hmb-latestnews .hmb-readmore{display:inline-flex;align-items:center;gap:.3rem;font-size:.78rem;font-weight:600;text-transform:uppercase;color:var(--hmb-accent);text-decoration:none;}
.hmb-latestnews .hmb-readmore:hover{color:var(--hmb-white);}
.hmb-latestnews .hmb-readmore::after{content:' →';}

/* ── Hero (featured) ── */
.hmb-latestnews .hmb-card--hero{flex-direction:row;border-top:none;border-left:4px solid var(--hmb-accent);}
.hmb-latestnews .hmb-card--hero .hmb-card__image-wrap{flex:0 0 45%;width:45%;aspect-ratio:unset;min-height:220px;}
.hmb-latestnews .hmb-cards-secondary{display:grid;gap:1.25rem;margin-top:1.25rem;}
.hmb-latestnews .hmb-featured--2 .hmb-cards-secondary{grid-template-columns:repeat(2,1fr);}
.hmb-latestnews .hmb-featured--3 .hmb-cards-secondary{grid-template-columns:repeat(3,1fr);}
.hmb-latestnews .hmb-featured--4 .hmb-cards-secondary{grid-template-columns:repeat(4,1fr);}

/* ── Ticker ── */
.hmb-latestnews .hmb-ticker{overflow:hidden;background:var(--hmb-dark2);border:1px solid var(--hmb-border);border-left:4px solid var(--hmb-accent);}
.hmb-latestnews .hmb-ticker__wrap{display:flex;align-items:stretch;}
.hmb-latestnews .hmb-ticker__label{background:var(--hmb-accent);color:#fff;font-weight:700;font-size:.75rem;text-transform:uppercase;padding:.6rem 1rem;white-space:nowrap;}
.hmb-latestnews .hmb-ticker__track{overflow:hidden;flex:1;}
.hmb-latestnews .hmb-ticker__inner{display:flex;gap:2.5rem;white-space:nowrap;animation:hmb-scroll 40s linear infinite;padding:.6rem 1rem;}
.hmb-latestnews .hmb-ticker:hover .hmb-ticker__inner{animation-play-state:paused;}
.hmb-latestnews .hmb-ticker__item a{color:var(--hmb-text);text-decoration:none;font-size:.85rem;}
.hmb-latestnews .hmb-ticker__item a:hover{color:var(--hmb-accent);}
.hmb-latestnews .hmb-ticker__sep{color:var(--hmb-accent);}
@keyframes hmb-scroll{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}

/* ── Share ── */
.hmb-latestnews .hmb-share{display:flex;align-items:center;gap:.4rem;padding-top:.5rem;border-top:1px solid var(--hmb-border);margin-top:auto;flex-wrap:wrap;}
.hmb-latestnews .hmb-share__label{font-size:.65rem;text-transform:uppercase;letter-spacing:.08em;color:var(--hmb-text-muted);}


/* ── Caption "Fuente:" ── */
.hmb-latestnews .hmb-card__image-wrap .hmb-img-caption{
    position:absolute;bottom:0;left:0;right:0;z-index:2;
    background:rgba(0,0,0,.65);
    color:#ccc;
    font-size:.72rem;
    padding:4px 10px;
    line-height:1.4;
    backdrop-filter:blur(2px);
}
.hmb-latestnews .hmb-card__image-wrap .hmb-img-caption strong{
    color:#fff;
    font-weight:700;
    margin-right:3px;
}

/* ── Texto más grande y claro ── */
.hmb-latestnews .hmb-card__title{font-size:1.1rem;}
.hmb-latestnews .hmb-card__intro{font-size:.92rem;color:#bbb;}
.hmb-latestnews .hmb-date__text{font-size:.78rem;}
.hmb-latestnews .hmb-badge{font-size:.72rem;padding:3px 10px;}
.hmb-latestnews .hmb-readmore{font-size:.84rem;}
.hmb-latestnews .hmb-meta{font-size:.8rem;}
/* ── Responsive ── */
@media(max-width:991px){
    .hmb-latestnews .hmb-grid--4,.hmb-latestnews .hmb-grid--3{grid-template-columns:repeat(2,1fr);}
}
@media(max-width:639px){
    .hmb-latestnews .hmb-grid--4,.hmb-latestnews .hmb-grid--3,.hmb-latestnews .hmb-grid--2{grid-template-columns:1fr;}
    .hmb-latestnews .hmb-list .hmb-card{flex-direction:column;border-left:none;border-top:3px solid var(--hmb-accent);}
    .hmb-latestnews .hmb-list .hmb-card__image-wrap{flex:0 0 auto;width:100%;min-height:unset;}
    .hmb-latestnews .hmb-card--hero{flex-direction:column;}
    .hmb-latestnews .hmb-card--hero .hmb-card__image-wrap{flex:0 0 auto;width:100%;min-height:180px;}
    .hmb-latestnews .hmb-featured--2 .hmb-cards-secondary,
    .hmb-latestnews .hmb-featured--3 .hmb-cards-secondary,
    .hmb-latestnews .hmb-featured--4 .hmb-cards-secondary{grid-template-columns:1fr;}
}
</style>
<?php
require __DIR__ . '/' . $layout . '.php';
