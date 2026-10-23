<?php defined('_JEXEC') or die; ?>
<style>
.hmb-colab{font-family:'Oswald',sans-serif;padding:2rem 0 1rem;}
.hmb-colab-title{text-align:center;font-size:11px;letter-spacing:4px;text-transform:uppercase;color:#555;margin:0 0 1.5rem;position:relative;}
.hmb-colab-title::before,.hmb-colab-title::after{content:'';display:inline-block;width:40px;height:1px;background:#2a0000;vertical-align:middle;margin:0 12px;}
.hmb-colab-grid{display:flex;flex-wrap:wrap;justify-content:center;align-items:center;gap:1.2rem;max-width:1100px;margin:0 auto;padding:0 1rem;}
.hmb-colab-item{display:flex;flex-direction:column;align-items:center;justify-content:center;background:rgba(255,255,255,.03);border:1px solid #1a0000;border-radius:6px;padding:.8rem;transition:all .3s ease;text-decoration:none;position:relative;}
.hmb-colab-item:hover{background:rgba(204,34,0,.08);border-color:#8B0000;transform:translateY(-3px);text-decoration:none;}
.hmb-colab-item img{display:block;object-fit:contain;}
.hmb-colab-nombre{font-size:11px;line-height:1.3;letter-spacing:1px;color:#aaa;text-align:center;margin-top:.5rem;max-width:100%;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;opacity:0;height:28px;transition:opacity .25s;}
.hmb-colab-item:hover .hmb-colab-nombre{opacity:1;}
.hmb-colab-gris img{filter:grayscale(1) brightness(1.4) contrast(.9);opacity:.55;transition:filter .35s,opacity .35s;}
.hmb-colab-gris .hmb-colab-item:hover img{filter:grayscale(0) brightness(1) contrast(1);opacity:1;}
@media(max-width:600px){.hmb-colab-grid{gap:.8rem;}}
</style>

<div class="hmb-colab">
    <?php if ($mostrarTitulo && $titulo): ?>
    <p class="hmb-colab-title"><?php echo htmlspecialchars($titulo, ENT_QUOTES); ?></p>
    <?php endif; ?>

    <?php if (!empty($colaboradores)): ?>
    <div class="hmb-colab-grid<?php echo $efectoGris ? ' hmb-colab-gris' : ''; ?>">
        <?php foreach ($colaboradores as $c):
            $tag = $c['url'] ? 'a' : 'div';
        ?>
        <<?php echo $tag; ?>
            class="hmb-colab-item"
            style="width:<?php echo $tamano + 24; ?>px;height:<?php echo $tamano + 24 + 34; ?>px;"
            <?php if ($c['url']): ?>href="<?php echo htmlspecialchars($c['url'], ENT_QUOTES); ?>" target="_blank" rel="noopener noreferrer"<?php endif; ?>
        >
            <img src="<?php echo htmlspecialchars($c['logo'], ENT_QUOTES); ?>"
                 alt="<?php echo htmlspecialchars($c['nombre'] ?: 'Colaborador', ENT_QUOTES); ?>"
                 style="max-width:<?php echo $tamano; ?>px;max-height:<?php echo $tamano; ?>px;"
                 loading="lazy">
            <span class="hmb-colab-nombre"><?php echo $c['nombre'] ? htmlspecialchars($c['nombre'], ENT_QUOTES) : ''; ?></span>
        </<?php echo $tag; ?>>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
