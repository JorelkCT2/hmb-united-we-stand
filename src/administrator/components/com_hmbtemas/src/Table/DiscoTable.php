<?php
namespace HeavyMetalBrothers\Component\HmbTemas\Administrator\Table;
defined('_JEXEC') or die;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

class DiscoTable extends Table
{
    public function __construct(DatabaseDriver $db)
    {
        parent::__construct('#__hmb_discos', 'id', $db);
    }
    public function check(): bool
    {
        if (empty($this->titulo)) {
            $this->setError('El nombre del álbum es obligatorio.');
            return false;
        }
        return parent::check();
    }
}
