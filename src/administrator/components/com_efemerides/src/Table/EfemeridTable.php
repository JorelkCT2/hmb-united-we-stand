<?php
namespace Joomla\Component\Efemerides\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;

class EfemeridTable extends Table
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__efemerides', 'id', $db);
        $this->setColumnAlias('published', 'state');
    }

    public function check(): bool
    {
        if (empty($this->titulo)) {
            $this->setError('El título es obligatorio.');
            return false;
        }
        if (empty($this->fecha_evento)) {
            $this->setError('La fecha del evento es obligatoria.');
            return false;
        }
        return parent::check();
    }
}
