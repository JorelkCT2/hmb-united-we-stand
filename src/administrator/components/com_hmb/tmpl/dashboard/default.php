<?php
defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmb\Administrator\Helper\AreasHelper;
use Joomla\CMS\Router\Route;

/** @var \HeavyMetalBrothers\Component\Hmb\Administrator\View\Dashboard\HtmlView $this */
?>
<style>
.hmb-panel .hmb-area { border-top: 3px solid #8b0000; }
.hmb-panel .hmb-area h2 { font-size: 1.1rem; letter-spacing: .08em; text-transform: uppercase; }
.hmb-panel .hmb-tile { display: flex; justify-content: space-between; align-items: center; gap: .75rem; padding: .6rem .75rem; border-radius: .35rem; text-decoration: none; color: inherit; }
.hmb-panel .hmb-tile:hover { background: rgba(139, 0, 0, .08); }
.hmb-panel .hmb-tile small { display: block; opacity: .7; }
.hmb-panel .hmb-count { background: #8b0000; color: #fff; border-radius: 1rem; padding: .1rem .6rem; font-size: .8rem; min-width: 2rem; text-align: center; }
</style>

<div class="hmb-panel container-fluid p-3">
    <div class="row g-3">
        <?php foreach ($this->areas as $area) : ?>
            <div class="col-12 col-md-6 col-xl-4">
                <div class="card hmb-area h-100 shadow-sm">
                    <div class="card-body">
                        <h2 class="mb-3"><span class="<?php echo $area['icono']; ?> me-2" aria-hidden="true"></span><?php echo htmlspecialchars($area['titulo'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        <?php foreach ($area['items'] as [$titulo, $desc, $enlace, $tabla]) :
                            $n = AreasHelper::contar($tabla); ?>
                            <a class="hmb-tile" href="<?php echo Route::_('index.php?' . $enlace); ?>">
                                <span>
                                    <strong><?php echo htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></strong>
                                    <small><?php echo htmlspecialchars($desc, ENT_QUOTES, 'UTF-8'); ?></small>
                                </span>
                                <?php if ($n !== null) : ?><span class="hmb-count"><?php echo $n; ?></span><?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
