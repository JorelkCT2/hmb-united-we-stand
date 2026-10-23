<?php
namespace Joomla\Component\Efemerides\Administrator\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\Component\Efemerides\Administrator\Helper\AutoPoblador;
use Joomla\CMS\Router\Route;

class FrasesController extends AdminController
{
    protected $text_prefix = 'COM_EFEMERIDES_FRASE';

    public function getModel($name='Frase',$prefix='Administrator',$config=['ignore_request'=>true])
    {
        return parent::getModel($name,$prefix,$config);
    }

    public function importCsv(): void
    {
        $this->checkToken();
        $app = Factory::getApplication();

        // Anti-duplicación: verificar que viene un fichero real
        $file = $this->input->files->get('csv_frases', null, 'raw');
        if (empty($file) || $file['error'] !== UPLOAD_ERR_OK || $file['size'] === 0) {
            $app->enqueueMessage('Error: archivo CSV no válido o vacío.','error');
            $this->setRedirect(Route::_('index.php?option=com_efemerides&view=frases',false));
            return;
        }

        // Anti-duplicación: usar nombre+tamaño del fichero como clave de sesión
        $fileKey = 'csv_import_' . md5($file['name'] . $file['size']);
        $session = $app->getSession();
        if ($session->get($fileKey)) {
            $app->enqueueMessage('Este archivo ya fue importado recientemente.','warning');
            $this->setRedirect(Route::_('index.php?option=com_efemerides&view=frases',false));
            return;
        }
        $session->set($fileKey, true);

        $db     = Factory::getContainer()->get('DatabaseDriver');
        $handle = fopen($file['tmp_name'],'r');
        $first  = fgets($handle); rewind($handle);
        $sep    = substr_count($first,';') > substr_count($first,',') ? ';' : ',';
        $header = fgetcsv($handle,0,$sep);

        if (!$header) {
            $app->enqueueMessage('CSV vacío o sin cabecera.','error');
            $this->setRedirect(Route::_('index.php?option=com_efemerides&view=frases',false));
            return;
        }

        if (!empty($header[0])) { $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]); }
        $header = array_map(static function($h){ return mb_strtolower(trim($h)); }, $header);
        $ok = $skip = 0;
        $now = (new \DateTime())->format('Y-m-d H:i:s');

        while (($row = fgetcsv($handle,0,$sep)) !== false) {
            if (count($row) < 2) { $skip++; continue; }
            $d     = array_combine($header, array_pad($row,count($header),''));
            $frase = trim($d['frase'] ?? '');
            $autor = trim($d['autor'] ?? ($d['author'] ?? ''));
            if (!$frase || !$autor) { $skip++; continue; }

            $obj = (object)[
                'frase'            => $frase,
                'autor'            => $autor,
                'fecha_nacimiento' => $this->parseDate(trim($d['fecha_nacimiento'] ?? '')),
                'fecha_defuncion'  => $this->parseDate(trim($d['fecha_defuncion']  ?? '')),
                'state'   => 1,
                'ordering'=> 0,
                'created' => $now,
                'modified'=> $now,
            ];
            // Auto-alimentar músicos
            AutoPoblador::addMusico($autor, $db);
            try { $db->insertObject('#__efemerides_frases',$obj); $ok++; }
            catch(\Exception $e) { $skip++; }
        }
        fclose($handle);

        $app->enqueueMessage(
            "{$ok} frases importadas." . ($skip > 0 ? " {$skip} omitidas." : ''),
            'success'
        );
        $this->setRedirect(Route::_('index.php?option=com_efemerides&view=frases',false));
    }

    private function parseDate(string $d): ?string
    {
        if (!$d || $d === '0000-00-00') return null;
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/',$d)) return $d;
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/',$d,$m))
            return sprintf('%04d-%02d-%02d',$m[3],$m[2],$m[1]);
        return null;
    }
}
