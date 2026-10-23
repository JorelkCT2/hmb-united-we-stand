<?php
namespace Joomla\Component\Efemerides\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;

class CategoriaTable extends Table
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__efemerides_categorias', 'id', $db);
        $this->setColumnAlias('published', 'state');
    }

    public function check(): bool
    {
        if (empty($this->name)) {
            $this->setError('El nombre de la categoría es obligatorio.');
            return false;
        }
        return parent::check();
    }
}
