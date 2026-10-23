<?php
namespace Joomla\Component\Efemerides\Administrator\Table;
defined('_JEXEC') or die;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;

class MusicoTable extends Table
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__efemerides_musicos', 'id', $db);
        $this->setColumnAlias('published', 'state');
    }

    public function bind($array, $ignore = '')
    {
        // Si solista=1 → limpiar banda antes de guardar
        if (isset($array['solista']) && (int)$array['solista'] === 1) {
            $array['banda_id']     = null;
            $array['banda_texto']  = '';
            $array['banda_id_2']   = null;
            $array['banda_texto_2']= '';
        }
        // banda_id vacío o 0 → null
        if (isset($array['banda_id']) && ($array['banda_id'] === '' || $array['banda_id'] === '0' || $array['banda_id'] === 0)) {
            $array['banda_id'] = null;
        }
        if (isset($array['banda_id_2']) && ($array['banda_id_2'] === '' || $array['banda_id_2'] === '0' || $array['banda_id_2'] === 0)) {
            $array['banda_id_2'] = null;
        }
        return parent::bind($array, $ignore);
    }

    public function check(): bool
    {
        // Doble seguro: si solista, limpiar banda
        if ((int)($this->solista ?? 0) === 1) {
            $this->banda_id    = null;
            $this->banda_texto = '';
        }
        if (empty($this->nombre)) {
            $this->setError('El nombre del músico es obligatorio.');
            return false;
        }
        return parent::check();
    }

    public function store($updateNulls = true): bool
    {
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        if (empty($this->id)) {
            $this->created = $now;
        }
        $this->modified = $now;

        foreach (['fecha_nacimiento', 'fecha_defuncion'] as $col) {
            if (isset($this->$col) && (trim((string)$this->$col) === '' || $this->$col === '0000-00-00')) {
                $this->$col = null;
            }
        }
        if (empty($this->banda_id)) {
            $this->banda_id = null;
        }

        return parent::store($updateNulls);
    }
}
