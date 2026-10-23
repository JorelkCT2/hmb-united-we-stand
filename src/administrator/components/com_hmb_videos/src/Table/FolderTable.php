<?php
namespace Joomla\Component\HmbVideos\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;

class FolderTable extends Table
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__hmb_folders', 'id', $db);
        $this->_columnAlias = ['published' => 'state'];
    }

    public function check(): bool
    {
        if (!parent::check()) return false;

        if ($this->parent_id === null || $this->parent_id === '') {
            $this->parent_id = 0;
        }
        $this->parent_id = (int)$this->parent_id;

        if (empty($this->alias)) {
            $this->alias = \Joomla\CMS\Application\ApplicationHelper::stringURLSafe($this->title);
        }
        if (empty(trim(str_replace('-', '', $this->alias)))) {
            $this->alias = \Joomla\CMS\Factory::getDate()->format('Y-m-d-H-i-s');
        }

        $now = \Joomla\CMS\Factory::getDate()->toSql();
        if (empty($this->created))  $this->created  = $now;
        if (empty($this->modified)) $this->modified = $now;

        return true;
    }
}
