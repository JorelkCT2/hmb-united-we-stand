<?php
namespace HeavyMetalBrothers\Component\HmbTemas\Site\View\Discos;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

class HtmlView extends BaseHtmlView
{
    public array  $discos  = [];
    public object $params;

    public function display($tpl = null): void
    {
        $app          = Factory::getApplication();
        $this->params = $app->getParams('com_hmbtemas');
        $db           = Factory::getContainer()->get('DatabaseDriver');
        $siteRoot     = Uri::root();

        $query = $db->getQuery(true)
            ->select(['id','grupo','titulo','productora','genero','anno','portada','audio_file','youtube_audio_url','youtube_links','descripcion'])
            ->from($db->quoteName('#__hmb_discos'))
            ->where($db->quoteName('published') . ' = 1')
            ->order('CAST(RIGHT(TRIM(anno), 4) AS UNSIGNED) DESC, titulo ASC');
        $db->setQuery($query);

        try { $items = $db->loadObjectList(); }
        catch (\Exception $e) { $items = []; }

        foreach ($items as $d) {
            $portadaRaw = $d->portada ?? '';
            $d->portada_url = $portadaRaw
                ? ($this->startsWith($portadaRaw,'http') ? $portadaRaw : $siteRoot.$portadaRaw) : '';
            $d->audio_url   = $d->audio_file
                ? ($this->startsWith($d->audio_file,'http') ? $d->audio_file
                   : $siteRoot.implode('/',array_map('rawurlencode',explode('/',$d->audio_file)))) : '';
            $d->yt_links = $d->youtube_links ? (json_decode($d->youtube_links,true) ?: []) : [];
            // Extraer ID de YouTube para el player embed
            $d->youtube_audio_id = '';
            if (!empty($d->youtube_audio_url)) {
                preg_match('/(?:v=|youtu\.be\/|embed\/)([a-zA-Z0-9_-]{11})/', $d->youtube_audio_url, $m);
                $d->youtube_audio_id = $m[1] ?? '';
            }
        }

        $this->discos = $items;

        $menu = $app->getMenu()->getActive();
        if ($menu) $app->getDocument()->setTitle($menu->title);

        parent::display($tpl);
    }

    private function startsWith(string $s, string $p): bool { return str_starts_with($s,$p); }
}
