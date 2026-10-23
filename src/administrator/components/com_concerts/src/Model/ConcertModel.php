<?php
namespace Joomla\Component\Concerts\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;

class ConcertModel extends AdminModel
{
    public function getForm($data = [], $loadData = true)
    {
        return $this->loadForm('com_concerts.concert', 'concert', [
            'control'   => 'jform',
            'load_data' => $loadData,
        ]);
    }

    protected function loadFormData()
    {
        $data = Factory::getApplication()->getUserState('com_concerts.edit.concert.data', []);
        if (empty($data)) {
            $data = $this->getItem();
            if ($data === false || empty($data)) {
                return new \stdClass();
            }
            // genre_ids: "1,2,3" → array so sql multiple field pre-selects them
            if (!empty($data->genre_ids)) {
                $data->genre_ids = array_filter(array_map('intval', explode(',', $data->genre_ids)));
            }
            // headliners: keep as JSON - edit.php does json_decode()
        }
        return $data;
    }

    public function getTable($name = 'Concert', $prefix = 'Administrator', $options = [])
    {
        return parent::getTable($name, $prefix, $options);
    }
}
