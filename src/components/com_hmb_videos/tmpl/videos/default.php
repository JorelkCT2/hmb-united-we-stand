<?php
defined('_JEXEC') or die;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$folderMap   = [];
$subByParent = [];
foreach ($this->folderTree as $f) {
    $folderMap[$f->id] = $f;
    if ((int)$f->parent_id > 0) {
        $subByParent[$f->parent_id][] = $f;
    }
}
$rootFolders = array_filter($this->folderTree, fn($f) => (int)$f->parent_id === 0);
$totalVideos = array_sum(array_column($this->folderTree, 'video_count'));
$currentUrl  = Uri::current();
?>

<div class="hmb-videos-wrap" id="hmb-videos-component">

    <!-- HEADER -->
    <div class="hmb-header">
        <div class="hmb-header-inner">
            <div class="hmb-lightning left">⚡</div>
            <h1 class="hmb-main-title">
                <span class="hmb-skull">☠</span>
                <?= Text::_('COM_HMB_VIDEOS_PAGE_TITLE') ?>
                <span class="hmb-skull">☠</span>
            </h1>
            <p class="hmb-subtitle"><?= Text::_('COM_HMB_VIDEOS_PAGE_SUBTITLE') ?></p>
            <div class="hmb-lightning right">⚡</div>
        </div>
        <div class="hmb-flames-bar"></div>
    </div>

    <!-- SEARCH -->
    <div class="hmb-search-wrap">
        <form action="<?= $currentUrl ?>" method="get" class="hmb-search-form">
            <input type="hidden" name="option" value="com_hmb_videos">
            <input type="hidden" name="view" value="videos">
            <input type="hidden" name="folder_id" value="<?= (int)$this->currentFolderId ?>">
            <div class="hmb-search-inner">
                <span class="hmb-search-icon">🔍</span>
                <input type="text" name="search" class="hmb-search-input"
                    value="<?= htmlspecialchars($this->state->get('filter.search', ''), ENT_QUOTES) ?>"
                    placeholder="<?= Text::_('COM_HMB_VIDEOS_SEARCH_PLACEHOLDER') ?>">
                <button type="submit" class="hmb-btn-search"><?= Text::_('COM_HMB_VIDEOS_SEARCH_BTN') ?></button>
                <?php if ($this->state->get('filter.search')) : ?>
                <a href="<?= Route::_('index.php?option=com_hmb_videos&view=videos&folder_id=' . (int)$this->currentFolderId) ?>" class="hmb-btn-clear">✖</a>
                <?php endif ?>
            </div>
        </form>
    </div>

    <div class="hmb-layout">

        <!-- SIDEBAR -->
        <aside class="hmb-sidebar">
            <div class="hmb-sidebar-header">
                <span>📂</span>
                <h3><?= Text::_('COM_HMB_VIDEOS_FOLDERS') ?></h3>
            </div>
            <nav class="hmb-folder-nav">
                <a href="<?= Route::_('index.php?option=com_hmb_videos&view=videos') ?>"
                   class="hmb-folder-item <?= $this->currentFolderId === 0 ? 'active' : '' ?>">
                    <span class="hmb-fi-icon">🎸</span>
                    <span class="hmb-fi-title"><?= Text::_('COM_HMB_VIDEOS_ALL_VIDEOS') ?></span>
                    <span class="hmb-fi-count"><?= $totalVideos ?></span>
                </a>
                <?php foreach ($rootFolders as $folder) : ?>
                <div class="hmb-folder-group">
                    <a href="<?= Route::_('index.php?option=com_hmb_videos&view=videos&folder_id=' . $folder->id) ?>"
                       class="hmb-folder-item <?= $this->currentFolderId === (int)$folder->id ? 'active' : '' ?>">
                        <span class="hmb-fi-icon">📁</span>
                        <span class="hmb-fi-title"><?= htmlspecialchars($folder->title) ?></span>
                        <span class="hmb-fi-count"><?= (int)$folder->video_count ?></span>
                    </a>
                    <?php if (isset($subByParent[$folder->id])) : ?>
                    <div class="hmb-subfolder-list">
                        <?php foreach ($subByParent[$folder->id] as $sub) : ?>
                        <a href="<?= Route::_('index.php?option=com_hmb_videos&view=videos&folder_id=' . $sub->id) ?>"
                           class="hmb-folder-item hmb-subfolder <?= $this->currentFolderId === (int)$sub->id ? 'active' : '' ?>">
                            <span class="hmb-fi-icon">↳</span>
                            <span class="hmb-fi-title"><?= htmlspecialchars($sub->title) ?></span>
                            <span class="hmb-fi-count"><?= (int)$sub->video_count ?></span>
                        </a>
                        <?php endforeach ?>
                    </div>
                    <?php endif ?>
                </div>
                <?php endforeach ?>
            </nav>
        </aside>

        <!-- MAIN -->
        <main class="hmb-main">
            <?php if ($this->currentFolderId && isset($folderMap[$this->currentFolderId])) : ?>
            <div class="hmb-folder-banner">
                <h2 class="hmb-folder-title">📂 <?= htmlspecialchars($folderMap[$this->currentFolderId]->title) ?></h2>
                <?php if ($folderMap[$this->currentFolderId]->description) : ?>
                <p class="hmb-folder-desc"><?= htmlspecialchars($folderMap[$this->currentFolderId]->description) ?></p>
                <?php endif ?>
            </div>
            <?php endif ?>

            <?php if (empty($this->items)) : ?>
            <div class="hmb-empty">
                <div class="hmb-empty-icon">🤘</div>
                <p><?= Text::_('COM_HMB_VIDEOS_NO_VIDEOS') ?></p>
            </div>
            <?php else : ?>
            <div class="hmb-video-grid" id="hmb-video-grid">
                <?php foreach ($this->items as $video) : ?>
                <?php $ytId = htmlspecialchars($video->youtube_id, ENT_QUOTES); ?>
                <article class="hmb-video-card"
                    data-youtube-id="<?= $ytId ?>"
                    data-title="<?= htmlspecialchars($video->title, ENT_QUOTES) ?>"
                    data-band="<?= htmlspecialchars($video->band ?? '', ENT_QUOTES) ?>"
                    data-desc="<?= htmlspecialchars(strip_tags($video->description ?? ''), ENT_QUOTES) ?>"
                    data-mobile="<?= (int)($video->mobile ?? 0) ?>">

                    <div class="hmb-card-thumb" style="overflow:hidden;width:100%;height:0;padding-bottom:56.25%;position:relative;background:#000;">
                        <img src="https://img.youtube.com/vi/<?= $ytId ?>/hqdefault.jpg"
                             alt="<?= htmlspecialchars($video->title, ENT_QUOTES) ?>"
                             loading="lazy" class="hmb-thumb-img" style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;display:block;">
                        <div class="hmb-hover-player"
                             data-src="https://www.youtube.com/embed/<?= $ytId ?>?autoplay=1&mute=1&controls=0&loop=1&playlist=<?= $ytId ?>&rel=0&modestbranding=1">
                            <iframe class="hmb-hover-iframe" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                        </div>
                        <div class="hmb-card-overlay">
                            <button class="hmb-play-btn" aria-label="Play"><span class="hmb-play-icon">▶</span></button>
                        </div>
                        <?php if ($video->featured) : ?><div class="hmb-featured-badge">⭐</div><?php endif ?>
                        <?php if ($video->genre) : ?><div class="hmb-genre-badge"><?= htmlspecialchars($video->genre) ?></div><?php endif ?>
                    </div>
                    <div class="hmb-card-info">
                        <h3 class="hmb-card-title"><?= htmlspecialchars($video->title) ?></h3>
                        <?php if ($video->band) : ?><p class="hmb-card-band">🎸 <?= htmlspecialchars($video->band) ?></p><?php endif ?>
                        <?php if ($video->year) : ?><p class="hmb-card-year">📅 <?= (int)$video->year ?></p><?php endif ?>
                    </div>
                </article>
                <?php endforeach ?>
            </div>
            <div class="hmb-pagination"><?= $this->pagination->getListFooter() ?></div>
            <?php endif ?>
        </main>
    </div>

</div>



    <!-- VIDEO MODAL -->
    <div class="hmb-modal-overlay" id="hmbModal" role="dialog" aria-modal="true">
        <div class="hmb-modal-container">
            <div class="hmb-modal-header">
                <div class="hmb-modal-title-wrap">
                    <h2 class="hmb-modal-title" id="hmbModalTitle">—</h2>
                    <p class="hmb-modal-band" id="hmbModalBand"></p>
                </div>
                <button class="hmb-modal-close" id="hmbModalClose">✖</button>
            </div>
            <div class="hmb-modal-body">
                <iframe id="hmbModalIframe" src="" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen></iframe>
            </div>
            <div class="hmb-modal-footer">
                <button class="hmb-btn-prev" id="hmbModalPrev">◀ Anterior</button>
                <button class="hmb-btn-next" id="hmbModalNext">Siguiente ▶</button>
            </div>
        </div>
    </div>

</div>
