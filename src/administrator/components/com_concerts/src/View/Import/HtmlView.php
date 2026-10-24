<?php
namespace Joomla\Component\Concerts\Administrator\View\Import;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Concerts\Administrator\Helper\SyncHelper;

class HtmlView extends BaseHtmlView
{
    public array $fuentes = [];
    public array $stats = [];
    public array $duplicados = [];
    public string $cronUrl = '';
    public bool $hayClaveTm = false;

    public function display($tpl = null): void
    {
        ToolbarHelper::title('Importar y actualizar la agenda', 'upload');
        ToolbarHelper::link(Route::_('index.php?option=com_concerts&view=concerts'), 'Ver todos los eventos', 'calendar');
        ToolbarHelper::preferences('com_concerts');

        $db = Factory::getContainer()->get('DatabaseDriver');

        $this->fuentes = $db->setQuery($db->getQuery(true)->select('*')->from('#__concerts_sources')->order('ordering, id'))->loadObjectList() ?: [];

        $this->stats = [
            'proximos'   => (int) $db->setQuery("SELECT COUNT(*) FROM #__concerts WHERE state = 1 AND COALESCE(date_end, date_start) >= CURDATE()")->loadResult(),
            'pendientes' => (int) $db->setQuery("SELECT COUNT(*) FROM #__concerts WHERE state = 0 AND source IS NOT NULL AND source <> 'manual'")->loadResult(),
            'sin_geo'    => (int) $db->setQuery("SELECT COUNT(*) FROM #__concerts WHERE city <> '' AND (latitude IS NULL OR latitude = 0) AND (geo_status IS NULL OR geo_status <> 'fail') AND COALESCE(date_end, date_start) >= CURDATE()")->loadResult(),
            'geo_fallo'  => (int) $db->setQuery("SELECT COUNT(*) FROM #__concerts WHERE geo_status = 'fail' AND COALESCE(date_end, date_start) >= CURDATE()")->loadResult(),
            'sin_cartel' => (int) $db->setQuery("SELECT COUNT(*) FROM #__concerts WHERE state = 1 AND (poster_url IS NULL OR poster_url = '') AND COALESCE(date_end, date_start) >= CURDATE()")->loadResult(),
        ];

        $cfg = SyncHelper::config();
        $this->hayClaveTm = trim((string) $cfg->get('ticketmaster_key', '')) !== '';
        $clave = trim((string) $cfg->get('cron_key', ''));
        $this->cronUrl = $clave !== '' ? Uri::root() . '?option=com_concerts&task=sync.cron&key=' . rawurlencode($clave) : '';

        $this->duplicados = $this->buscarDuplicados($db);

        parent::display($tpl);
    }

    /** Parejas de eventos futuros que parecen el mismo (misma fecha y ciudad, titulo parecido). */
    private function buscarDuplicados($db): array
    {
        $filas = $db->setQuery(
            "SELECT id, title, date_start, city, state, source FROM #__concerts
             WHERE state >= 0 AND COALESCE(date_end, date_start) >= CURDATE() ORDER BY date_start, id"
        )->loadObjectList() ?: [];

        $porDia = [];

        foreach ($filas as $f) {
            $porDia[$f->date_start][] = $f;
        }

        $pares = [];

        foreach ($porDia as $lista) {
            for ($i = 0, $n = count($lista); $i < $n; $i++) {
                for ($j = $i + 1; $j < $n; $j++) {
                    $a = $lista[$i];
                    $b = $lista[$j];

                    if (SyncHelper::norm((string) $a->city) === SyncHelper::norm((string) $b->city) && SyncHelper::parecido($a->title, $b->title) >= 0.6) {
                        $pares[] = [$a, $b];
                    }
                }
            }
        }

        return array_slice($pares, 0, 40);
    }
}
