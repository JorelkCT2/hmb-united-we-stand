<?php
namespace Joomla\Component\Efemerides\Administrator\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;

class MusicosController extends AdminController
{
    protected $text_prefix = 'COM_EFEMERIDES_MUSICO';

    public function getModel($n='Musico',$p='Administrator',$c=['ignore_request'=>true])
    {
        return parent::getModel($n,$p,$c);
    }

    public function importCsv(): void
    {
        $this->checkToken();
        $app  = Factory::getApplication();
        $file = $this->input->files->get('csv_musicos', null, 'raw');

        if (empty($file) || $file['error'] !== UPLOAD_ERR_OK || $file['size'] === 0) {
            $app->enqueueMessage('Archivo CSV no válido.', 'error');
            $this->setRedirect(Route::_('index.php?option=com_efemerides&view=musicos', false));
            return;
        }

        $db     = Factory::getContainer()->get('DatabaseDriver');
        $handle = fopen($file['tmp_name'], 'r');
        $first  = fgets($handle); rewind($handle);
        $sep    = substr_count($first, ';') > substr_count($first, ',') ? ';' : ',';
        $header = fgetcsv($handle, 0, $sep);
        if (!$header) {
            $app->enqueueMessage('CSV sin cabecera.', 'error');
            fclose($handle);
            $this->setRedirect(Route::_('index.php?option=com_efemerides&view=musicos', false));
            return;
        }

        $header = array_map(static fn($h) => mb_strtolower(trim($h)), $header);
        $ok = $skip = 0;
        $now = (new \DateTime())->format('Y-m-d H:i:s');

        while (($row = fgetcsv($handle, 0, $sep)) !== false) {
            if (count($row) < 1) { $skip++; continue; }
            $d = array_combine($header, array_slice(array_pad($row, count($header), ''), 0, count($header)));

            $nombre = trim($d['nombre'] ?? ($d['name'] ?? ($d['artista'] ?? '')));
            if (!$nombre) { $skip++; continue; }

            // Banda
            $solistaVal = trim($d['solista'] ?? '');
            $esSolista  = ($solistaVal === '1' || mb_strtolower($solistaVal) === 'si'
                          || mb_strtolower($solistaVal) === 'sí' || mb_strtolower($solistaVal) === 'yes');

            $bandaTexto = trim($d['banda'] ?? ($d['band'] ?? ''));
            $bandaId    = null;

            if (!$esSolista && $bandaTexto) {
                $this->ensureBanda($bandaTexto, $db, $now);
                $db->setQuery('SELECT id FROM #__efemerides_bandas WHERE nombre = ' . $db->quote($bandaTexto));
                $bandaId = $db->loadResult();
            } elseif ($esSolista) {
                $bandaTexto = '';  // limpiar banda si es solista
            }

            $fn  = trim($d['fecha_nacimiento'] ?? ($d['nacimiento'] ?? ''));
            $fd  = trim($d['fecha_defuncion']  ?? ($d['defuncion']  ?? ''));
            $wk  = trim($d['wikipedia_url'] ?? ($d['wikipedia'] ?? ''));
            $ft  = trim($d['foto']          ?? ($d['photo']    ?? ''));
            $ps  = trim($d['pais']          ?? ($d['country']  ?? ($d['país'] ?? '')));
            $ins = trim($d['instrumento']   ?? ($d['instrument'] ?? ($d['instrumentos'] ?? '')));
            $ln  = trim($d['lugar_nacimiento'] ?? ($d['lugar nacimiento'] ?? ''));
            $ld  = trim($d['lugar_defuncion']  ?? ($d['lugar defuncion']  ?? ($d['lugar_muerte'] ?? '')));

            $vals = [
                'nombre'            => $db->quote($nombre),
                'wikipedia_url'     => $wk  ? $db->quote($wk)  : 'NULL',
                'foto'              => $ft  ? $db->quote($ft)  : 'NULL',
                'fecha_nacimiento'  => $this->pd($fn) ? $db->quote($this->pd($fn)) : 'NULL',
                'fecha_defuncion'   => $this->pd($fd) ? $db->quote($this->pd($fd)) : 'NULL',
                'lugar_nacimiento'  => $ln  ? $db->quote($ln)  : 'NULL',
                'lugar_defuncion'   => $ld  ? $db->quote($ld)  : 'NULL',
                'pais'              => $ps  ? $db->quote($ps)  : 'NULL',
                'instrumento'       => $ins ? $db->quote($ins) : 'NULL',
                'solista'           => $esSolista ? 1 : 0,
                'banda_id'          => ($bandaId && !$esSolista) ? (int)$bandaId : 'NULL',
                'banda_texto'       => $bandaTexto ? $db->quote($bandaTexto) : 'NULL',
                'state'             => 1,
                'created'           => $db->quote($now),
                'modified'          => $db->quote($now),
            ];

            $cols = implode(', ', array_map(fn($c) => "`$c`", array_keys($vals)));
            $ins_vals = implode(', ', array_values($vals));

            // ON DUPLICATE KEY: actualizar todos los campos que tengan valor
            $updates = [];
            foreach (['wikipedia_url','foto','fecha_nacimiento','fecha_defuncion','lugar_nacimiento','lugar_defuncion','pais','instrumento','banda_id','banda_texto'] as $col) {
                $v = $vals[$col];
                if ($v !== 'NULL') {
                    $updates[] = "`$col` = IF(VALUES(`$col`) IS NOT NULL, VALUES(`$col`), `$col`)";
                }
            }
            $updates[] = '`modified` = ' . $db->quote($now);

            try {
                $db->setQuery("INSERT INTO #__efemerides_musicos ($cols) VALUES ($ins_vals)"
                    . ' ON DUPLICATE KEY UPDATE ' . implode(', ', $updates))
                   ->execute();
                $ok++;
            } catch (\Exception $e) {
                $skip++;
            }
        }
        fclose($handle);

        $app->enqueueMessage("$ok músicos importados/actualizados. $skip omitidos.", 'success');
        $this->setRedirect(Route::_('index.php?option=com_efemerides&view=musicos', false));
    }

    private function ensureBanda(string $nombre, $db, string $now): void
    {
        try {
            $db->setQuery('INSERT IGNORE INTO #__efemerides_bandas (nombre,state,created,modified) VALUES ('
                . $db->quote($nombre) . ',1,' . $db->quote($now) . ',' . $db->quote($now) . ')')->execute();
        } catch (\Exception $e) {}
    }

    private function pd(string $d): ?string
    {
        if (!$d || $d === '0000-00-00') return null;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) return $d;
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $d, $m))
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        if (preg_match('/^\d{4}$/', $d)) return $d . '-01-01';
        return null;
    }
}
