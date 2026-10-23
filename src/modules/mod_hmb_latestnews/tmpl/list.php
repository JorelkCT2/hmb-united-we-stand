<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if (empty($articles)) {
    echo '<p class="hmb-no-results">' . Text::_('MOD_HMB_LATESTNEWS_NO_RESULTS') . '</p>';
    return;
}
?>
<div class="hmb-latestnews<?php echo $darkMode ? ' hmb-dark' : ''; ?><?php echo $moduleClassSfx ? ' ' . htmlspecialchars($moduleClassSfx, ENT_QUOTES, 'UTF-8') : ''; ?>">
    <div class="hmb-list">
        <?php foreach ($articles as $article) : ?>
        <article class="hmb-card">

            <?php if ($showImage) : ?>
                <?php if (!empty($article->image_intro)) : ?>
                <div class="hmb-card__image-wrap" style="background-image:url('<?php echo htmlspecialchars($article->image_intro, ENT_QUOTES, 'UTF-8'); ?>')">
                    <a href="<?php echo htmlspecialchars($article->link, ENT_QUOTES, 'UTF-8'); ?>" class="hmb-img-link" tabindex="-1" aria-hidden="true"></a>
                    <img src="<?php echo htmlspecialchars($article->image_intro, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($article->image_intro_alt, ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" class="hmb-seo">
                </div>
                <?php else : ?>
                <div class="hmb-card__image-placeholder" style="flex:0 0 180px;width:180px;">
                    <svg viewBox="0 0 24 24" width="32" height="32"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="hmb-card__body">
                <div style="display:flex;align-items:center;gap:.5rem;flex-wrap:wrap">
                    <?php if ($showCategoryBadge && !empty($article->category_title)) : ?>
                    <a href="<?php echo htmlspecialchars($article->category_link, ENT_QUOTES, 'UTF-8'); ?>" class="hmb-badge" style="--hmb-badge-color:<?php echo htmlspecialchars($article->category_color, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($article->category_title, ENT_QUOTES, 'UTF-8'); ?></a>
                    <?php endif; ?>
                    <?php if ($showDate && !empty($article->display_date)) : ?>
                    <span class="hmb-meta" style="margin:0"><?php echo htmlspecialchars($article->display_date, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endif; ?>
                </div>
                <h3 class="hmb-card__title">
                    <a href="<?php echo htmlspecialchars($article->link, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($article->title, ENT_QUOTES, 'UTF-8'); ?></a>
                </h3>
                <?php if ($showIntrotext && !empty($article->introtext_clean)) : ?>
                <p class="hmb-card__intro"><?php echo htmlspecialchars(ModHmbLatestNewsHelper::truncateText($article->introtext_clean, $introLimit), ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <?php if ($showReadmore) : ?>
                <a href="<?php echo htmlspecialchars($article->link, ENT_QUOTES, 'UTF-8'); ?>" class="hmb-readmore"><?php echo htmlspecialchars($readmoreText, ENT_QUOTES, 'UTF-8'); ?></a>
                <?php endif; ?>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
</div>
