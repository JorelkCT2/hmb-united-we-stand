<?php
namespace HeavyMetalBrothers\Component\HmbTemas\Site\View\Temas;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

class HtmlView extends BaseHtmlView
{
    public array  $temas   = [];
    public array  $albums  = [];
    public object $params;

    public function display($tpl = null): void
    {
        $app          = Factory::getApplication();
        $this->params = $app->getParams('com_hmbtemas');

        // Reutilizar el helper del módulo si está disponible,
        // o hacer la consulta directamente
        $db       = Factory::getContainer()->get('DatabaseDriver');
        $siteRoot = Uri::root();

        $orden = 'album_year ASC, track_number ASC';
        $query = $db->getQuery(true)
            ->select(['id','title','album','album_year','track_number',
                      'duration','cover_image','audio_file',
                      'spotify_url','youtube_url','description'])
            ->from($db->quoteName('#__hmb_temas'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($orden);

        $db->setQuery($query);

        try {
            $items = $db->loadObjectList();
        } catch (\Exception $e) {
            $items = [];
        }

        foreach ($items as $item) {
            $s = (int) $item->duration;
            $item->duration_fmt = $s ? sprintf('%d:%02d', intdiv($s, 60), $s % 60) : '--:--';

            $item->audio_url = $item->audio_file
                ? (str_starts_with($item->audio_file, 'http') ? $item->audio_file
                   : $siteRoot . implode('/', array_map('rawurlencode', explode('/', $item->audio_file))))
                : '';

            $item->cover_url = $item->cover_image
                ? (str_starts_with($item->cover_image, 'http') ? $item->cover_image
                   : $siteRoot . implode('/', array_map('rawurlencode', explode('/', $item->cover_image))))
                : '';
        }

        $this->temas  = $items;
        $this->albums = array_values(array_unique(array_filter(array_column($items, 'album'))));

        // Título de página desde el ítem de menú
        $menu = $app->getMenu()->getActive();
        if ($menu) {
            $app->getDocument()->setTitle($menu->title);
        }

        parent::display($tpl);
    }
}
