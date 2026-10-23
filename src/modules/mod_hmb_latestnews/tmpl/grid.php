<?php
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

if (empty($articles)) {
    echo '<p class="hmb-no-results">' . Text::_('MOD_HMB_LATESTNEWS_NO_RESULTS') . '</p>';
    return;
}
?>
<div class="hmb-latestnews<?php echo $darkMode ? ' hmb-dark' : ''; ?><?php echo $moduleClassSfx ? ' ' . htmlspecialchars($moduleClassSfx, ENT_QUOTES, 'UTF-8') : ''; ?>">
    <div class="hmb-grid hmb-grid--<?php echo $columns; ?>">
        <?php foreach ($articles as $article) : ?>
        <article class="hmb-card">

            <?php if ($showImage) : ?>
                <?php if (!empty($article->image_intro)) : ?>
                <div class="hmb-card__image-wrap" style="background-image:url('<?php echo htmlspecialchars($article->image_intro, ENT_QUOTES, 'UTF-8'); ?>')">
                    <a href="<?php echo htmlspecialchars($article->link, ENT_QUOTES, 'UTF-8'); ?>" class="hmb-img-link" tabindex="-1" aria-hidden="true"></a>
                    <img src="<?php echo htmlspecialchars($article->image_intro, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($article->image_intro_alt, ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" class="hmb-seo">
                    <?php if (!empty($article->image_intro_caption)) : ?>
                    <div class="hmb-img-caption"><strong>Fuente:</strong><?php echo htmlspecialchars($article->image_intro_caption, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                </div>
                <?php else : ?>
                <div class="hmb-card__image-placeholder">
                    <svg viewBox="0 0 24 24" width="48" height="48"><path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 14.5v-9l6 4.5-6 4.5z"/></svg>
                </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="hmb-card__body">

                <?php if ($showCategoryBadge && !empty($article->category_title)) : ?>
                <div>
                    <a href="<?php echo htmlspecialchars($article->category_link, ENT_QUOTES, 'UTF-8'); ?>" class="hmb-badge" style="--hmb-badge-color:<?php echo htmlspecialchars($article->category_color, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($article->category_title, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </div>
                <?php endif; ?>

                <h3 class="hmb-card__title">
                    <a href="<?php echo htmlspecialchars($article->link, ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($article->title, ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                </h3>

                <?php if ($showDate && !empty($article->display_date)) : ?>
                <div class="hmb-date">
                    <span class="hmb-date__line"></span>
                    <time class="hmb-date__text"><?php echo htmlspecialchars($article->display_date, ENT_QUOTES, 'UTF-8'); ?></time>
                </div>
                <?php endif; ?>

                <?php if ($showAuthor && !empty($article->author)) : ?>
                <div class="hmb-meta"><span><?php echo htmlspecialchars($article->author, ENT_QUOTES, 'UTF-8'); ?></span></div>
                <?php endif; ?>

                <?php if ($showIntrotext && !empty($article->introtext_clean)) : ?>
                <p class="hmb-card__intro"><?php echo htmlspecialchars(ModHmbLatestNewsHelper::truncateText($article->introtext_clean, $introLimit), ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>

                <?php if ($showReadmore) : ?>
                <a href="<?php echo htmlspecialchars($article->link, ENT_QUOTES, 'UTF-8'); ?>" class="hmb-readmore"><?php echo htmlspecialchars($readmoreText, ENT_QUOTES, 'UTF-8'); ?></a>
                <?php endif; ?>

                <?php if (!empty($showShare)) : ?>
                <div class="hmb-share">
                    <span class="hmb-share__label">Compartir:</span>
                    <?php
                    $su = (strpos($article->link, 'http') === 0) ? $article->link : \Joomla\CMS\Uri\Uri::root() . ltrim($article->link, '/');
                    $eu = rawurlencode($su);
                    $et = rawurlencode($article->title);
                    $sn = [
                        'facebook' => ['Facebook', '#1877F2', 'https://www.facebook.com/sharer/sharer.php?u=' . $eu, 'M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.41c0-3.025 1.792-4.697 4.533-4.697 1.312 0 2.686.235 2.686.235v2.97h-1.513c-1.491 0-1.956.93-1.956 1.874v2.25h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073z'],
                        'x'        => ['X', '#000', 'https://x.com/intent/tweet?url=' . $eu . '&text=' . $et, 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z'],
                        'whatsapp' => ['WhatsApp', '#25D366', 'https://wa.me/?text=' . $et . '%20' . $eu, 'M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z'],
                        'telegram' => ['Telegram', '#2AABEE', 'https://t.me/share/url?url=' . $eu . '&text=' . $et, 'M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z'],
                        'linkedin' => ['LinkedIn', '#0A66C2', 'https://www.linkedin.com/sharing/share-offsite/?url=' . $eu, 'M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z'],
                    ];
                    foreach ($sn as $key => $s):
                        if (empty($shareNets[$key])) continue;
                    ?>
                    <button type="button" onclick="window.open('<?php echo $s[2]; ?>','_blank','width=600,height=450')" title="<?php echo $s[0]; ?>" style="background:<?php echo $s[1]; ?>;display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:50%;border:none;cursor:pointer;flex-shrink:0">
                        <svg viewBox="0 0 24 24" width="14" height="14"><path fill="#fff" d="<?php echo $s[3]; ?>"/></svg>
                    </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

            </div>
        </article>
        <?php endforeach; ?>
    </div>
</div>
