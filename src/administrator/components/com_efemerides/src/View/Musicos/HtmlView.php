<?php
namespace Joomla\Component\Efemerides\Administrator\View\Musicos;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
class HtmlView extends BaseHtmlView
{
    protected $items; protected $pagination; protected $state;
    protected $form;  protected $item;
    public function display($tpl=null): void
    {
        $v = strtolower('Musicos');
        if (in_array($v,['bandas','musicos'])) {
            $this->items=$this->get('Items');$this->pagination=$this->get('Pagination');$this->state=$this->get('State');
            $single = rtrim($v,'s'); // bandas→banda, musicos→musico
            ToolbarHelper::title(ucfirst($v),'users-2');
            ToolbarHelper::addNew($single.'.add');
            ToolbarHelper::publish($v.'.publish','JTOOLBAR_PUBLISH',true);
            ToolbarHelper::unpublish($v.'.unpublish','JTOOLBAR_UNPUBLISH',true);
            ToolbarHelper::deleteList('',$v.'.delete','JTOOLBAR_DELETE');
        } else {
            $this->form=$this->get('Form');$this->item=$this->get('Item');
            $isNew=empty($this->item->id);
            ToolbarHelper::title(($isNew?'Nuevo ':'Editar ').rtrim($v,'s'),'user');
            ToolbarHelper::apply($v.'.apply');ToolbarHelper::save($v.'.save');
            ToolbarHelper::cancel($v.'.cancel',$isNew?'JTOOLBAR_CANCEL':'JTOOLBAR_CLOSE');
        }
        parent::display($tpl);
    }
}
