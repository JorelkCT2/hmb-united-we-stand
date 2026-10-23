<?php
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$app    = Factory::getApplication();
$doc    = $app->getDocument();
$base   = Uri::base(true);
$itemId = $app->input->getInt('Itemid', 0);
$doc->addStyleSheet($base . '/components/com_concerts/assets/css/concerts.css');
$doc->addScript($base . '/components/com_concerts/assets/js/concerts.js', [], ['defer'=>true]);

$mon = ['','ene','feb','mar','abr','may','jun','jul','ago','sep','oct','nov','dic'];
$agendaUrl  = 'index.php?option=com_concerts&view=concerts' . ($itemId?'&Itemid='.$itemId:'');
$postersUrl = 'index.php?option=com_concerts&view=posters'  . ($itemId?'&Itemid='.$itemId:'');

function pflag(string $c): string {
    $c = strtoupper(trim($c));
    if (strlen($c)!==2) return '🌍';
    $f=''; foreach(str_split($c) as $ch){$f.=mb_chr(0x1F1E6+ord($ch)-ord('A'));} return $f;
}
?>
<div class="cc-wrap">
<div class="cc-tabs">
    <a href="<?= htmlspecialchars($agendaUrl) ?>" class="cc-tab">📅 Agenda</a>
    <a href="<?= htmlspecialchars($postersUrl) ?>" class="cc-tab active">🖼️ Carteles</a>
</div>
<p style="color:#666;font-size:.82rem;margin-bottom:1rem"><?= count($this->items) ?> carteles · Clic en el cartel para ver la ficha</p>

<?php if (empty($this->items)): ?>
<div class="cc-empty"><div class="ce-ico">🖼️</div><p>No hay carteles disponibles.</p></div>
<?php else: ?>
<div class="cc-grid">
<?php foreach ($this->items as $c):
    $ps  = htmlspecialchars($c->poster_url ?? '');
    $det = 'index.php?option=com_concerts&view=concert&id='.$c->id.($itemId?'&Itemid='.$itemId:'');
    $fl  = pflag($c->country_code);
    $ts  = strtotime($c->date_start);
    $dlbl = date('j',$ts).' '.$mon[(int)date('n',$ts)].' '.date('Y',$ts);
    if (!empty($c->date_end)&&$c->date_end!==$c->date_start) {
        $ts2=strtotime($c->date_end); $dlbl.=' – '.date('j',$ts2).' '.$mon[(int)date('n',$ts2)];
    }
?>
<a href="<?= htmlspecialchars($det) ?>" class="cc-poster-item" title="<?= htmlspecialchars($c->title ?: $c->band) ?>">
    <?php if ($ps): ?>
    <img src="<?= $ps ?>" alt="<?= htmlspecialchars($c->title ?: $c->band) ?>" loading="lazy" />
    <?php else: ?>
    <div class="cc-no-poster">
        <span style="font-size:2.5rem">🎸</span>
        <span style="font-weight:700;color:#ccc;font-size:.82rem;margin-top:.3rem"><?= htmlspecialchars($c->title ?: $c->band) ?></span>
        <span style="color:var(--cc-accent);font-size:.72rem"><?= $dlbl ?></span>
    </div>
    <?php endif; ?>
    <div class="cc-poster-overlay">
        <span class="cc-poster-band"><?= $fl ?> <?= htmlspecialchars($c->title ?: $c->band) ?></span>
        <span class="cc-poster-date"><?= $dlbl ?></span>
        <span class="cc-poster-city"><?= htmlspecialchars($c->city) ?></span>
    </div>
</a>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>
