<?php
namespace Joomla\Component\Efemerides\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;

class FallecidoTable extends Table
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__efemerides_fallecidos', 'id', $db);
        $this->setColumnAlias('published', 'state');
    }

    public function check(): bool
    {
        if (empty($this->artista)) {
            $this->setError('El nombre del artista es obligatorio.');
            return false;
        }
        return parent::check();
    }

    public function bind($array, $ignore = '')
    {
        // foto_interna es solo campo auxiliar del form — no existe en la tabla
        // Si foto está vacía pero foto_interna tiene valor, lo usamos como foto
        if (is_array($array)) {
            $fotoInterna = trim((string)($array['foto_interna'] ?? ''));
            $foto        = trim((string)($array['foto'] ?? ''));
            if ($foto === '' && $fotoInterna !== '') {
                $array['foto'] = $fotoInterna;
            }
            // Eliminar el campo auxiliar antes de hacer bind
            unset($array['foto_interna']);
        }
        return parent::bind($array, $ignore);
    }
}
