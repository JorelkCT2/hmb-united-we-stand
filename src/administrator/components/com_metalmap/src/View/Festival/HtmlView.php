<?php
/**
 * @package     com_metalmap
 * @copyright   (C) 2026 Heavy Metal Brothers
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Component\Metalmap\Administrator\View\Festival;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

final class HtmlView extends BaseHtmlView
{
    public $form;
    public $item;

    public function display($tpl = null): void
    {
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');

        $this->addToolbar();
        parent::display($tpl);
    }

    private function addToolbar(): void
    {
        Factory::getApplication()->getInput()->set('hidemainmenu', true);

        $isNew = ($this->item->id ?? 0) == 0;

        ToolbarHelper::title(
            Text::_($isNew ? 'COM_METALMAP_FESTIVAL_NEW' : 'COM_METALMAP_FESTIVAL_EDIT'),
            'map-marker'
        );

        $toolbar = Toolbar::getInstance('toolbar');
        $saveGroup = $toolbar->dropdownButton('save-group');
        $saveChildBar = $saveGroup->configure(function (Toolbar $childBar) {
            $childBar->apply('festival.apply');
            $childBar->save('festival.save');
            $childBar->save2new('festival.save2new');
        });

        $toolbar->cancel('festival.cancel');
    }
}
