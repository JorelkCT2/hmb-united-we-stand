<?php
namespace Joomla\Component\Artistas\Site\View\Musicos;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView
{
    public $items = [];
    public $busquedas = [];

    public function display($tpl = null): void
    {
        $app   = Factory::getApplication();
        $input = $app->getInput();
        $db    = Factory::getContainer()->get('DatabaseDriver');

        // Filters
        $instrumento = $input->getString('filter_instrumento', '');
        $provincia   = $input->getString('filter_provincia', '');
        $estilo      = $input->getString('filter_estilo', '');

        // Musicians list
        $q = 'SELECT m.*, u.username FROM #__artistas_musicos m LEFT JOIN #__users u ON u.id=m.user_id WHERE m.state=1';
        if ($instrumento) $q .= ' AND m.instrumentos LIKE ' . $db->quote('%' . $instrumento . '%');
        if ($provincia)   $q .= ' AND m.provincia = ' . $db->quote($provincia);
        if ($estilo)      $q .= ' AND m.estilos LIKE ' . $db->quote('%' . $estilo . '%');
        $q .= ' ORDER BY m.created DESC';
        $db->setQuery($q);
        $this->items = $db->loadObjectList() ?: [];

        $app->getDocument()->setTitle('Músicos buscando banda | Heavy Metal Brothers');
        parent::display($tpl);
    }
}
