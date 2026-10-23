<?php
namespace Joomla\Component\Concerts\Administrator\View\Concert;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
    protected $form;
    protected $item;

    public function display($tpl = null): void
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        // Redirect if item was deleted or not found
        if (empty($this->item) || (isset($this->item->id) && $this->item->id === null && !Factory::getApplication()->input->getInt('id'))) {
            Factory::getApplication()->enqueueMessage('Concierto no encontrado.', 'warning');
            Factory::getApplication()->redirect(Route::_('index.php?option=com_concerts&view=concerts', false));
            return;
        }

        $isNew = ((int)($this->item->id ?? 0) === 0);
        ToolbarHelper::title($isNew ? 'Nuevo Concierto' : 'Editar: ' . htmlspecialchars($this->item->title ?? ''));
        ToolbarHelper::apply('concert.apply');
        ToolbarHelper::save('concert.save');
        ToolbarHelper::cancel('concert.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');

        parent::display($tpl);
    }
}
