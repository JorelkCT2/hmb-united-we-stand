<?php
namespace Joomla\Component\Artistas\Administrator\Table;
defined('_JEXEC') or die;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class GeneroTable extends Table
{
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__artistas_generos', 'id', $db);
        $this->setColumnAlias('published', 'state');
    }
}
