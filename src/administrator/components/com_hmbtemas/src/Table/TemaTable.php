<?php
namespace HeavyMetalBrothers\Component\HmbTemas\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class TemaTable extends Table
{
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__hmb_temas', 'id', $db);
        $this->setColumnAlias('published', 'published');
    }

    public function check(): bool
    {
        if (empty($this->title)) {
            $this->setError('El título del tema es obligatorio.');
            return false;
        }
        return parent::check();
    }
}
