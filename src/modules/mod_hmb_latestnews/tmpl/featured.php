<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if (empty($articles)) {
    echo '<p class="hmb-no-results">' . Text::_('MOD_HMB_LATESTNEWS_NO_RESULTS') . '</p>';
    return;
}

$hero      = array_shift($articles);
$secondary = $articles;

// Helper inline para el bloque de imagen
function hmbImgWrap($article, $link, $width = null, $height = null) {
    $url = htmlspecialchars($article->image_intro, ENT_QUOTES, 'UTF-8');
    $alt = htmlspecialchars($article->image_intro_alt, ENT_QUOTES, 'UTF-8');
    $lnk = htmlspecialchars($link, ENT_QUOTES, 'UTF-8');
    if (!empty($article->image_intro)) {
        $cap = htmlspecialchars($article->image_intro_caption ?? '', ENT_QUOTES, 'UTF-8');
        $capHtml = $cap ? '<div class="hmb-img-caption"><strong>Fuente:</strong>' . $cap . '</div>' : '';
        return '<div class="hmb-card__image-wrap" style="background-image:url(\'' . $url . '\')">'
            . '<a href="' . $lnk . '" class="hmb-img-link" tabindex="-1" aria-hidden="true"></a>'
            . '<img src="' . $url . '" alt="' . $alt . '" loading="lazy" class="hmb-seo">'
            . $capHtml
            . '</div>';
    }
    return '<div class="hmb-card__image-placeholder"><svg viewBox="0 0 24 24" width="48" height="48"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg></div>';
}
?>
<div class="hmb-latestnews<?php echo $darkMode ? ' hmb-dark' : ''; ?><?php echo $moduleClassSfx ? ' ' . htmlspecialchars($moduleClassSfx, ENT_QUOTES, 'UTF-8') : ''; ?>">
    <div class="hmb-featured hmb-featured--<?php echo $columns; ?>">

        <!-- Hero -->
        <article class="hmb-card hmb-card--hero">
            <?php if ($showImage) echo hmbImgWrap($hero, $hero->link); ?>
            <div class="hmb-card__body">
                <?php if ($showCategoryBadge && !empty($hero->category_title)) : ?>
                <div><a href="<?php echo htmlspecialchars($hero->category_link, ENT_QUOTES, 'UTF-8'); ?>" class="hmb-badge" style="--hmb-badge-color:<?php echo htmlspecialchars($hero->category_color, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($hero->category_title, ENT_QUOTES, 'UTF-8'); ?></a></div>
                <?php endif; ?>
                <h2 class="hmb-card__title" style="font-size:1.3rem">
                    <a href="<?php echo htmlspecialchars($hero->link, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($hero->title, ENT_QUOTES, 'UTF-8'); ?></a>
                </h2>
                <?php if ($showDate && !empty($hero->display_date)) : ?>
                <div class="hmb-date"><span class="hmb-date__line"></span><time class="hmb-date__text"><?php echo htmlspecialchars($hero->display_date, ENT_QUOTES, 'UTF-8'); ?></time></div>
                <?php endif; ?>
                <?php if ($showIntrotext && !empty($hero->introtext_clean)) : ?>
                <p class="hmb-card__intro"><?php echo htmlspecialchars(ModHmbLatestNewsHelper::truncateText($hero->introtext_clean, 220), ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
                <?php if ($showReadmore) : ?>
                <a href="<?php echo htmlspecialchars($hero->link, ENT_QUOTES, 'UTF-8'); ?>" class="hmb-readmore"><?php echo htmlspecialchars($readmoreText, ENT_QUOTES, 'UTF-8'); ?></a>
                <?php endif; ?>
            </div>
        </article>

        <!-- Secundarios -->
        <?php if (!empty($secondary)) : ?>
        <div class="hmb-cards-secondary">
            <?php foreach ($secondary as $article) : ?>
            <article class="hmb-card">
                <?php if ($showImage) echo hmbImgWrap($article, $article->link); ?>
                <div class="hmb-card__body">
                    <?php if ($showCategoryBadge && !empty($article->category_title)) : ?>
                    <div><a href="<?php echo htmlspecialchars($article->category_link, ENT_QUOTES, 'UTF-8'); ?>" class="hmb-badge" style="--hmb-badge-color:<?php echo htmlspecialchars($article->category_color, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($article->category_title, ENT_QUOTES, 'UTF-8'); ?></a></div>
                    <?php endif; ?>
                    <h3 class="hmb-card__title">
                        <a href="<?php echo htmlspecialchars($article->link, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($article->title, ENT_QUOTES, 'UTF-8'); ?></a>
                    </h3>
                    <?php if ($showDate && !empty($article->display_date)) : ?>
                    <div class="hmb-date"><span class="hmb-date__line"></span><time class="hmb-date__text"><?php echo htmlspecialchars($article->display_date, ENT_QUOTES, 'UTF-8'); ?></time></div>
                    <?php endif; ?>
                    <?php if ($showReadmore) : ?>
                    <a href="<?php echo htmlspecialchars($article->link, ENT_QUOTES, 'UTF-8'); ?>" class="hmb-readmore"><?php echo htmlspecialchars($readmoreText, ENT_QUOTES, 'UTF-8'); ?></a>
                    <?php endif; ?>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
</div>
