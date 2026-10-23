<?php
namespace Joomla\Component\Concerts\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ItemModel;

class ConcertModel extends ItemModel
{
    public function getItem($pk = null): ?\stdClass
    {
        $pk   = $pk ?? (int)Factory::getApplication()->input->get('id', 0);
        $item = $this->getDatabase()->setQuery(
            $this->getDatabase()->getQuery(true)->select('*')->from('#__concerts')
                 ->where('id='.(int)$pk)->where('state=1')
        )->loadObject();
        if ($item && !empty($item->headliners) && $item->headliners[0] === '[') {
            $item->headlinersList = json_decode($item->headliners, true) ?? [];
        } else {
            if ($item) $item->headlinersList = [];
        }
        return $item;
    }
}
