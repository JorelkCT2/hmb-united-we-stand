<?php
namespace Joomla\Component\Artistas\Administrator\View\Cpanel;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView
{
    public int   $totalArtistas       = 0;
    public int   $artistasPublicados  = 0;
    public int   $artistasPendientes  = 0;
    public int   $resenasPendientes   = 0;
    public int   $resenasAprobadas    = 0;
    public int   $presupuestosNuevos  = 0;
    public int   $presupuestosTotal   = 0;
    public array $pendientesAprobacion= [];
    public array $ultimosArtistas     = [];
    public array $ultimosPresupuestos = [];

    public function display($tpl = null): void
    {
        ToolbarHelper::title('Panel de Control · Artistas', 'home-2');

        $db = Factory::getContainer()->get('DatabaseDriver');

        $db->setQuery('SELECT COUNT(*) FROM #__artistas');
        $this->totalArtistas = (int)$db->loadResult();

        $db->setQuery('SELECT COUNT(*) FROM #__artistas WHERE state=1');
        $this->artistasPublicados = (int)$db->loadResult();

        $db->setQuery('SELECT COUNT(*) FROM #__artistas WHERE state=0');
        $this->artistasPendientes = (int)$db->loadResult();

        $db->setQuery('SELECT COUNT(*) FROM #__artistas_resenas WHERE state=0');
        $this->resenasPendientes = (int)$db->loadResult();

        $db->setQuery('SELECT COUNT(*) FROM #__artistas_resenas WHERE state=1');
        $this->resenasAprobadas = (int)$db->loadResult();

        $db->setQuery('SELECT COUNT(*) FROM #__artistas_presupuestos WHERE state=0');
        $this->presupuestosNuevos = (int)$db->loadResult();

        $db->setQuery('SELECT COUNT(*) FROM #__artistas_presupuestos');
        $this->presupuestosTotal = (int)$db->loadResult();

        // Artistas pendientes de aprobación (registrados desde web)
        $db->setQuery('SELECT id, name, genre, location, email, phone, created FROM #__artistas WHERE state=0 ORDER BY created DESC LIMIT 10');
        $this->pendientesAprobacion = $db->loadObjectList() ?: [];

        // Últimos 5 publicados
        $db->setQuery('SELECT id, name, genre, location, state, created FROM #__artistas WHERE state=1 ORDER BY created DESC LIMIT 5');
        $this->ultimosArtistas = $db->loadObjectList() ?: [];

        // Últimas 5 solicitudes
        $db->setQuery('SELECT artista_name, client_name, client_email, event_type, created, state FROM #__artistas_presupuestos ORDER BY created DESC LIMIT 5');
        $this->ultimosPresupuestos = $db->loadObjectList() ?: [];

        // Noticias recientes (últimas 5) - safe query with fallback for titulo
        try {
            $db->setQuery("SELECT n.id, n.tipo, n.created, a.name AS artista_name,
                IFNULL(n.lugar, IFNULL(c.title, 'Sin titulo')) AS titulo
                FROM #__artistas_noticias n
                LEFT JOIN #__artistas a ON a.id = n.artista_id
                LEFT JOIN #__content c ON c.id = n.article_id
                ORDER BY n.created DESC LIMIT 5");
            $this->noticiasRecientes = $db->loadObjectList() ?: [];

            $db->setQuery('SELECT COUNT(*) FROM #__artistas_noticias');
            $this->totalNoticias = (int)$db->loadResult();
        } catch (\Exception $e) {
            $this->noticiasRecientes = [];
            $this->totalNoticias = 0;
        }

        parent::display($tpl);
    }
}
