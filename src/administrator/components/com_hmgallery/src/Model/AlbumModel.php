<?php
namespace Joomla\Component\Hmgallery\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;

class AlbumModel extends AdminModel
{
    public $typeAlias = 'com_hmgallery.album';

    public function getForm($data = [], $loadData = true): \Joomla\CMS\Form\Form|bool
    {
        $form = $this->loadForm('com_hmgallery.album', 'album', ['control' => 'jform', 'load_data' => $loadData]);
        return empty($form) ? false : $form;
    }

    protected function loadFormData(): mixed
    {
        $data = Factory::getApplication()->getUserState('com_hmgallery.edit.album.data', []);
        return empty($data) ? $this->getItem() : $data;
    }

    public function getTable($name = 'Album', $prefix = 'Administrator', $options = []): Table
    {
        return parent::getTable($name, $prefix, $options);
    }
}
