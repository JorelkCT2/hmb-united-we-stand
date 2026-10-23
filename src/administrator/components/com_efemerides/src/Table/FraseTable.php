<?php
namespace Joomla\Component\Efemerides\Administrator\Table;
defined('_JEXEC') or die;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;

class FraseTable extends Table
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__efemerides_frases', 'id', $db);
        $this->setColumnAlias('published', 'state');
    }

    public function check(): bool
    {
        if (empty($this->frase)) { $this->setError('La frase es obligatoria.'); return false; }
        if (empty($this->autor)) { $this->setError('El autor es obligatorio.'); return false; }
        return parent::check();
    }

    public function bind($array, $ignore = '')
    {
        $dateCols = ['fecha_nacimiento', 'fecha_defuncion'];
        if (is_array($array)) {
            foreach ($dateCols as $col) {
                if (isset($array[$col]) && (trim($array[$col]) === '' || $array[$col] === '0000-00-00')) {
                    $array[$col] = null;
                }
            }
        }
        return parent::bind($array, $ignore);
    }

    public function store($updateNulls = true): bool
    {
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        if (empty($this->id)) $this->created = $now;
        $this->modified = $now;
        foreach (['fecha_nacimiento', 'fecha_defuncion'] as $col) {
            if (isset($this->$col) && (trim((string)$this->$col) === '' || $this->$col === '0000-00-00')) {
                $this->$col = null;
            }
        }
        return parent::store($updateNulls);
    }
}
