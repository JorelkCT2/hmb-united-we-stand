<?php
/**
 * Layout: TICKER (barra de noticias animada)
 * mod_hmb_latestnews — Heavy Metal Brothers
 */
\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if (empty($articles)) {
    return;
}

// Duplicar los artículos para que el scroll sea infinito y sin saltos
$tickerArticles = array_merge($articles, $articles);
?>
<div class="hmb-latestnews<?php echo $moduleClassSfx ? ' ' . htmlspecialchars($moduleClassSfx, ENT_QUOTES, 'UTF-8') : ''; ?>" role="region" aria-label="<?php echo Text::_('MOD_HMB_LATESTNEWS_TICKER_ARIA'); ?>">
    <div class="hmb-ticker">
        <div class="hmb-ticker__wrap">
            <span class="hmb-ticker__label" aria-hidden="true">
                <?php echo Text::_('MOD_HMB_LATESTNEWS_TICKER_LABEL'); ?>
            </span>
            <div class="hmb-ticker__track">
                <div class="hmb-ticker__inner" aria-live="off">
                    <?php foreach ($tickerArticles as $i => $article) : ?>
                    <span class="hmb-ticker__item">
                        <?php if ($showCategoryBadge && !empty($article->category_title)) : ?>
                        <a href="<?php echo htmlspecialchars($article->category_link, ENT_QUOTES, 'UTF-8'); ?>" class="hmb-badge" style="--hmb-badge-color: <?php echo htmlspecialchars($article->category_color, ENT_QUOTES, 'UTF-8'); ?>; font-size:.6rem">
                            <?php echo htmlspecialchars($article->category_title, ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo htmlspecialchars($article->link, ENT_QUOTES, 'UTF-8'); ?>">
                            <?php echo htmlspecialchars($article->title, ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                    </span>
                    <span class="hmb-ticker__sep" aria-hidden="true">✦</span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
