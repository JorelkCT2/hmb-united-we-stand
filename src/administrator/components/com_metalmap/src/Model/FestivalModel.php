<?php
/**
 * @package     com_metalmap
 * @copyright   (C) 2026 Heavy Metal Brothers
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Component\Metalmap\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;

final class FestivalModel extends AdminModel
{
    protected $text_prefix = 'COM_METALMAP';

    public function getTable($name = 'Event', $prefix = 'Administrator', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }

    public function getForm($data = [], $loadData = true)
    {
        $form = $this->loadForm(
            'com_metalmap.festival',
            'festival',
            ['control' => 'jform', 'load_data' => $loadData]
        );
        return empty($form) ? false : $form;
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_metalmap.edit.festival.data', []);
        if (empty($data)) {
            $data = $this->getItem();
        }
        return $data;
    }
}
