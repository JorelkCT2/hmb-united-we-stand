<?php
namespace Joomla\Component\Artistas\Administrator\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

class ImportController extends BaseController
{
    public function csv(): void
    {
        $app   = Factory::getApplication();
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $file  = $app->getInput()->files->get('csv_file');

        if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
            $app->enqueueMessage('Error al subir el archivo CSV.', 'error');
            $app->redirect(Route::_('index.php?option=com_artistas&view=cpanel', false));
            return;
        }

        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            $app->enqueueMessage('No se puede leer el archivo CSV.', 'error');
            $app->redirect(Route::_('index.php?option=com_artistas&view=cpanel', false));
            return;
        }

        $headers = fgetcsv($handle, 0, ';');
        if (!$headers) {
            $app->enqueueMessage('El CSV está vacío o tiene formato incorrecto.', 'error');
            $app->redirect(Route::_('index.php?option=com_artistas&view=cpanel', false));
            return;
        }

        // Normalize headers
        $headers = array_map(fn($h) => strtolower(trim($h)), $headers);

        $imported = 0; $skipped = 0; $now = date('Y-m-d H:i:s');
        $adminId = (int)$app->getIdentity()->id;

        $tiposBanda = ['solista','propias','versiones','ambas'];

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) < 2) continue;
            $data = array_combine($headers, array_pad($row, count($headers), ''));

            $name = trim($data['name'] ?? $data['nombre'] ?? '');
            if (empty($name)) { $skipped++; continue; }

            $alias = preg_replace('/[^a-z0-9-]/', '-', strtolower($name));
            $alias = preg_replace('/-+/', '-', trim($alias, '-')) . '-' . time() . rand(10,99);

            $tipoBanda = trim($data['tipo_banda'] ?? 'propias');
            if (!in_array($tipoBanda, $tiposBanda)) $tipoBanda = 'propias';

            $db->setQuery("INSERT INTO #__artistas
                (name, alias, genre, tipo_banda, solista, provincia_origen, location, short_desc, description,
                 email, phone, website, social_instagram, social_facebook, social_youtube,
                 metadesc, metakey, state, created, created_by, rating, review_count)
                VALUES (" .
                $db->quote($name) . ',' .
                $db->quote($alias) . ',' .
                $db->quote(trim($data['genre'] ?? $data['genero'] ?? '')) . ',' .
                $db->quote($tipoBanda) . ',' .
                (isset($data['solista']) && $data['solista'] == '1' ? '1' : '0') . ',' .
                $db->quote(trim($data['provincia_origen'] ?? $data['provincia'] ?? '')) . ',' .
                $db->quote(trim($data['provincia_origen'] ?? $data['provincia'] ?? '')) . ',' .
                $db->quote(trim($data['short_desc'] ?? $data['descripcion_breve'] ?? '')) . ',' .
                $db->quote(trim($data['description'] ?? $data['descripcion'] ?? '')) . ',' .
                $db->quote(trim($data['email'] ?? '')) . ',' .
                $db->quote(trim($data['phone'] ?? $data['telefono'] ?? '')) . ',' .
                $db->quote(trim($data['website'] ?? $data['web'] ?? '')) . ',' .
                $db->quote(trim($data['social_instagram'] ?? $data['instagram'] ?? '')) . ',' .
                $db->quote(trim($data['social_facebook'] ?? $data['facebook'] ?? '')) . ',' .
                $db->quote(trim($data['social_youtube'] ?? $data['youtube'] ?? '')) . ',' .
                $db->quote(trim($data['metadesc'] ?? '')) . ',' .
                $db->quote(trim($data['metakey'] ?? '')) . ',' .
                "1," . $db->quote($now) . ",$adminId,0,0)"
            )->execute();
            $imported++;
        }

        fclose($handle);
        $app->enqueueMessage("Importación completada: $imported artistas importados, $skipped omitidos.", 'success');
        $app->redirect(Route::_('index.php?option=com_artistas&view=cpanel', false));
    }

    public function ejemplo(): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="artistas_ejemplo.csv"');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM for Excel
        fputcsv($out, ['name','genre','tipo_banda','solista','provincia_origen','short_desc','description','email','phone','website','social_instagram','social_facebook','social_youtube','metadesc','metakey'], ';');
        fputcsv($out, ['Iron Maiden Tribute','Heavy Metal','propias','0','Madrid','Tributo oficial a Iron Maiden con más de 20 años de experiencia','Banda tributo a Iron Maiden formada en Madrid en 2003. Hemos actuado en más de 500 conciertos por toda España.','contacto@ironmaidentribute.com','600123456','https://ironmaidentribute.com','https://instagram.com/ironmaidentribute','https://facebook.com/ironmaidentribute','','tributo iron maiden España,banda heavy metal Madrid,contratar tributo iron maiden','heavy metal, tributo, iron maiden, Madrid'], ';');
        fputcsv($out, ['Carlos García','Folk Metal','solista','1','Murcia','Solista de folk metal con influencias celtas y medievales','Músico solista con 15 años de experiencia en folk metal. Disponible para festivales, ferias medievales y eventos privados.','carlos@carlosgarcia.es','677889900','','https://instagram.com/carlosgarcia','','','Solista folk metal Murcia, música medieval, ferias medievales España','folk metal, solista, Murcia, música medieval'], ';');
        fputcsv($out, ['Los Verdugos','Thrash Metal','versiones','0','Barcelona','Banda de versiones de thrash metal clásico','Especializados en versiones de Metallica, Slayer, Megadeth y Anthrax. Más de 10 años en escena.','info@losverdugos.com','612345678','','','https://facebook.com/losverdugos','https://youtube.com/@losverdugos','Banda versiones thrash metal Barcelona, tributo Metallica, contratar banda','thrash metal, versiones, Barcelona, Metallica, Slayer'], ';');
        fputcsv($out, ['Valkiria','Symphonic Metal','ambas','0','Valencia','Banda de metal sinfónico con vocalista femenina','Valkiria combina metal sinfónico con composiciones propias y versiones de Nightwish y Epica. Formación completa con orquesta.','valkiria@valkiria.es','','https://valkiria.es','https://instagram.com/valkiria_band','','','metal sinfónico Valencia, banda femenina metal, contratar grupo metal sinfónico','symphonic metal, Valencia, vocalista femenina, Nightwish'], ';');
        fclose($out);
        Factory::getApplication()->close();
    }
}
