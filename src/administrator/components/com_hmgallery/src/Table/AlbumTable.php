<?php
namespace Joomla\Component\Hmgallery\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class AlbumTable extends Table
{
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__hmgallery_albums', 'id', $db);
        $this->setColumnAlias('published', 'state');
    }

    public function check(): bool
    {
        if (trim($this->alias) === '') {
            $this->alias = ApplicationHelper::stringURLSafe($this->title);
        }
        if (trim($this->alias) === '') {
            $this->alias = date('Y-m-d-H-i-s');
        }
        return parent::check();
    }
}
