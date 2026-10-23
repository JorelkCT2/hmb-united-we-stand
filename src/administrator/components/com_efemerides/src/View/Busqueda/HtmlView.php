<?php
namespace Joomla\Component\Efemerides\Administrator\View\Busqueda;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

class HtmlView extends BaseHtmlView
{
    public string $query      = '';
    public array  $efemerides = [];
    public array  $fallecidos = [];
    public array  $musicos    = [];
    public array  $frases     = [];
    public int    $total      = 0;

    public function display($tpl = null): void
    {
        $app = Factory::getApplication();
        $db  = Factory::getContainer()->get('DatabaseDriver');
        $q   = trim((string)$app->getInput()->getString('q', ''));
        $this->query = $q;

        if (strlen($q) >= 2) {
            $like = $db->quote('%' . $db->escape($q, true) . '%');

            $db->setQuery(
                'SELECT e.id, e.titulo, e.artista, e.fecha_evento, e.descripcion,
                        c.name AS categoria
                 FROM #__efemerides e
                 LEFT JOIN #__efemerides_categorias c ON c.id = e.categoria_id
                 WHERE e.state = 1
                   AND (e.titulo LIKE ' . $like . ' OR e.artista LIKE ' . $like . '
                        OR e.descripcion LIKE ' . $like . ')
                 ORDER BY e.fecha_evento DESC LIMIT 30'
            );
            $this->efemerides = $db->loadObjectList() ?: [];

            $db->setQuery(
                'SELECT f.id, f.artista, f.fecha_fallecimiento, f.foto,
                        m.id AS musico_id
                 FROM #__efemerides_fallecidos f
                 LEFT JOIN #__efemerides_musicos m ON LOWER(m.nombre) = LOWER(f.artista) AND m.state = 1
                 WHERE f.state = 1 AND f.artista LIKE ' . $like . '
                 ORDER BY f.fecha_fallecimiento DESC LIMIT 20'
            );
            $this->fallecidos = $db->loadObjectList() ?: [];

            $db->setQuery(
                'SELECT m.id, m.nombre, m.foto, m.instrumento, m.fecha_defuncion,
                        COALESCE(b.nombre, m.banda_texto, "") AS banda_nombre
                 FROM #__efemerides_musicos m
                 LEFT JOIN #__efemerides_bandas b ON b.id = m.banda_id
                 WHERE m.state = 1
                   AND (m.nombre LIKE ' . $like . ' OR m.descripcion LIKE ' . $like . '
                        OR m.instrumento LIKE ' . $like . ')
                 ORDER BY m.nombre ASC LIMIT 20'
            );
            $this->musicos = $db->loadObjectList() ?: [];

            $db->setQuery(
                'SELECT f.id, f.frase, f.autor,
                        m.id AS musico_id
                 FROM #__efemerides_frases f
                 LEFT JOIN #__efemerides_musicos m ON LOWER(m.nombre) = LOWER(f.autor) AND m.state = 1
                 WHERE f.state = 1
                   AND (f.frase LIKE ' . $like . ' OR f.autor LIKE ' . $like . ')
                 ORDER BY f.id DESC LIMIT 20'
            );
            $this->frases = $db->loadObjectList() ?: [];

            $this->total = count($this->efemerides) + count($this->fallecidos)
                         + count($this->musicos) + count($this->frases);
        }

        parent::display($tpl);
    }
}
