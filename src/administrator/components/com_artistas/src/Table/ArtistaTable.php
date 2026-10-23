<?php
namespace Joomla\Component\Artistas\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\CMS\Application\ApplicationHelper;

class ArtistaTable extends Table
{
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__artistas', 'id', $db);
        $this->setColumnAlias('published', 'state');
    }

    public function check(): bool
    {
        if (empty($this->alias)) {
            $this->alias = ApplicationHelper::stringURLSafe($this->name);
        }
        if (empty($this->created)) {
            $this->created = (new \DateTime())->format('Y-m-d H:i:s');
        }
        return parent::check();
    }
}
