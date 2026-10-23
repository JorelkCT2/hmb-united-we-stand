<?php
namespace Joomla\Component\Artistas\Administrator\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

class NoticiaController extends AdminController
{
    public function getModel($name='Noticias', $prefix='Administrator', $config=['ignore_request'=>true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function apply(): void
    {
        $this->_save();
        $app = Factory::getApplication();
        // Volver a la misma página (el formulario de edición)
        $referer = $app->input->server->getString('HTTP_REFERER', '');
        $app->redirect($referer ?: 'index.php?option=com_artistas&view=noticias');
    }

    public function save(): void
    {
        $this->_save();
        Factory::getApplication()->redirect('index.php?option=com_artistas&view=noticias');
    }

    public function cancel(): void
    {
        Factory::getApplication()->redirect('index.php?option=com_artistas&view=noticias');
    }

    private function _save(): int
    {
        $app    = Factory::getApplication();
        $db     = Factory::getContainer()->get('DatabaseDriver');
        $input  = $app->getInput();
        $filter = InputFilter::getInstance();

        $id          = $input->getInt('id');
        $tipo        = $input->getString('tipo') === 'album' ? 'album' : 'concierto';
        // Auto-generate title like frontend
        $db->setQuery('SELECT a.name FROM #__artistas a JOIN #__artistas_noticias n ON n.artista_id=a.id WHERE n.id=' . $id);
        $artistaNombre = $db->loadResult() ?: 'Artista';
        $fecha       = $filter->clean($input->getString('fecha_evento'), 'string');
        $lugar       = $filter->clean($input->getString('lugar'), 'string');
        $provincia   = $filter->clean($input->getString('provincia'), 'string');
        $nombreDisco = $filter->clean($input->getString('nombre_disco'), 'string');
        $descripcion = $filter->clean($input->getRaw('descripcion'), 'html');
        $now         = date('Y-m-d H:i:s');

        // Imagen
        $db->setQuery('SELECT imagen FROM #__artistas_noticias WHERE id=' . $id);
        $imgPath = $db->loadResult() ?: '';
        $imgFile = $input->files->get('imagen');
        if (!empty($imgFile) && isset($imgFile['error']) && $imgFile['error'] === UPLOAD_ERR_OK) {
            $uploadDir = JPATH_ROOT . '/images/noticiasbandas/';
            if (!Folder::exists($uploadDir)) { Folder::create($uploadDir); }
            $ext = strtolower(pathinfo($imgFile['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp'])) { $ext = 'jpg'; }
            $db->setQuery('SELECT a.name FROM #__artistas a JOIN #__artistas_noticias n ON n.artista_id=a.id WHERE n.id=' . $id);
            $nombreArtista = $db->loadResult() ?: 'artista';
            $grupoSlug = preg_replace('/[^a-z0-9]/', '', strtolower($nombreArtista));
            $fname = $grupoSlug . '_' . date('dmy') . '_' . date('Hi') . '.' . $ext;
            if (File::upload($imgFile['tmp_name'], $uploadDir . $fname)) {
                $imgPath = 'images/noticiasbandas/' . $fname;
            }
        }

        $db->setQuery("UPDATE #__artistas_noticias SET
            tipo=" . $db->quote($tipo) . ",
            titulo=" . $db->quote($titulo) . ",
            fecha_evento=" . $db->quote($fecha) . ",
            lugar=" . $db->quote($lugar) . ",
            provincia=" . $db->quote($provincia) . ",
            nombre_disco=" . $db->quote($nombreDisco) . ",
            descripcion=" . $db->quote($descripcion) . ",
            imagen=" . $db->quote($imgPath) . "
            WHERE id=$id")->execute();

        // Actualizar artículo Joomla
        $db->setQuery('SELECT article_id FROM #__artistas_noticias WHERE id=' . $id);
        $articleId = (int)$db->loadResult();
        if ($articleId) {
            $introHtml = '';
            if ($tipo==='album' && $nombreDisco) $introHtml .= '<p><strong>💿 Disco / Single:</strong> ' . htmlspecialchars($nombreDisco) . '</p>';
            if ($fecha) $introHtml .= '<p><strong>📅 Fecha:</strong> ' . htmlspecialchars(date('d/m/Y', strtotime($fecha))) . '</p>';
            if ($lugar) $introHtml .= '<p><strong>📍 Lugar:</strong> ' . htmlspecialchars($lugar) . '</p>';
            if ($provincia) $introHtml .= '<p><strong>🗺️ Provincia:</strong> ' . htmlspecialchars($provincia) . '</p>';
            if ($imgPath) $introHtml .= '<p><img src="' . \Joomla\CMS\Uri\Uri::root() . htmlspecialchars($imgPath) . '" style="max-width:100%;border-radius:8px"/></p>';
            $introHtml .= $descripcion;
            $db->setQuery("UPDATE #__content SET title=" . $db->quote($titulo) . ", introtext=" . $db->quote($introHtml) . ", modified=" . $db->quote($now) . " WHERE id=$articleId")->execute();
        }

        $app->enqueueMessage('Noticia guardada correctamente.', 'success');
        return $id;
    }
}
