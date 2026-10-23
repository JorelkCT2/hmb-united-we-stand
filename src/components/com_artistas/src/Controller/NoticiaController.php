<?php
namespace Joomla\Component\Artistas\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\Component\Artistas\Site\Helper\RouteHelper;

class NoticiaController extends BaseController
{
    public function save(): void
    {
        $app    = Factory::getApplication();
        $user   = $app->getIdentity();
        $db     = Factory::getContainer()->get('DatabaseDriver');
        $input  = $app->getInput();
        $filter = InputFilter::getInstance();

        if ($user->guest) { $app->redirect('index.php?option=com_users&view=login'); return; }

        $artistaId = $input->getInt('artista_id');
        $db->setQuery('SELECT id, name FROM #__artistas WHERE id=' . $artistaId . ' AND created_by=' . (int)$user->id . ' LIMIT 1');
        $artista = $db->loadObject();
        if (!$artista) { $app->enqueueMessage('Sin permiso.', 'error'); $app->redirect(RouteHelper::getUrl('miperfil')); return; }

        $noticiaId    = $input->getInt('noticia_id', 0);
        $tipo         = $input->getString('tipo') === 'album' ? 'album' : 'concierto';

        // CAMPOS DEL FORMULARIO
        // Nombre del evento → guardado en columna `lugar`
        $nombreEvento = $filter->clean($input->getString('lugar'), 'string');
        // Ciudad → guardado en columna `nombre_disco` (libre en conciertos)
        $ciudad       = $filter->clean($input->getString('nombre_evento'), 'string');
        // Fecha + Hora combinadas en `fecha_evento`
        $fechaRaw     = $filter->clean($input->getString('fecha_evento'), 'string');
        $horaRaw      = $filter->clean($input->getString('hora_evento'), 'string');
        $fecha        = $fechaRaw ? ($horaRaw ? $fechaRaw . ' ' . $horaRaw : $fechaRaw) : '';
        $provincia    = $filter->clean($input->getString('provincia'), 'string');
        // Para álbumes: nombre del disco → columna `nombre_disco`
        $nombreDisco  = $tipo === 'album' ? $filter->clean($input->getString('nombre_disco'), 'string') : '';
        $descripcion  = $filter->clean($input->getRaw('introtext'), 'html');

        // Lo que se guarda en nombre_disco según tipo
        $dbNombreDisco = $tipo === 'concierto' ? $ciudad : $nombreDisco;

        // Título autogenerado
        if ($tipo === 'album') {
            $titulo = ($nombreDisco ? '"' . $nombreDisco . '", nuevo disco' : 'Nuevo disco') . ' de ' . $artista->name;
        } else {
            $titulo = ($nombreEvento ?: 'Nuevo concierto') . ' de ' . $artista->name;
            if ($ciudad) $titulo .= ' en ' . $ciudad;
            elseif ($provincia) $titulo .= ' en ' . $provincia;
        }
        if ($fechaRaw) $titulo .= ' previsto para ' . date('d/m/Y', strtotime($fechaRaw));

        // Imagen
        $imgPath = $filter->clean($input->getString('imagen_existing'), 'string');
        $imgFile = $input->files->get('imagen');
        if (!empty($imgFile) && isset($imgFile['error']) && $imgFile['error'] === UPLOAD_ERR_OK) {
            $uploadDir = JPATH_ROOT . '/images/noticiasbandas/';
            if (!Folder::exists($uploadDir)) { Folder::create($uploadDir); }
            $ext = strtolower(pathinfo($imgFile['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg','jpeg','png','webp'])) { $ext = 'jpg'; }
            $grupoSlug = preg_replace('/[^a-z0-9]/', '', strtolower($artista->name));
            $fname = $grupoSlug . '_' . date('dmy') . '_' . date('Hi') . '.' . $ext;
            if (File::upload($imgFile['tmp_name'], $uploadDir . $fname)) {
                $imgPath = 'images/noticiasbandas/' . $fname;
            }
        }

        // HTML del artículo Joomla
        $introHtml = '';
        if ($tipo === 'concierto' && $nombreEvento) $introHtml .= '<p><strong>🎪 Evento:</strong> ' . htmlspecialchars($nombreEvento) . '</p>';
        if ($tipo === 'album' && $nombreDisco)      $introHtml .= '<p><strong>💿 Disco:</strong> ' . htmlspecialchars($nombreDisco) . '</p>';
        if ($fechaRaw) {
            $horaStr = $horaRaw ? ' a las ' . $horaRaw . 'h' : '';
            $introHtml .= '<p><strong>📅 Fecha:</strong> ' . date('d/m/Y', strtotime($fechaRaw)) . $horaStr . '</p>';
        }
        if ($tipo === 'concierto' && $ciudad)    $introHtml .= '<p><strong>🏙️ Ciudad:</strong> ' . htmlspecialchars($ciudad) . '</p>';
        if ($tipo === 'concierto' && $provincia) $introHtml .= '<p><strong>🗺️ Provincia:</strong> ' . htmlspecialchars($provincia) . '</p>';
        if ($imgPath) $introHtml .= '<p><img src="' . \Joomla\CMS\Uri\Uri::root() . htmlspecialchars($imgPath) . '" style="max-width:100%;border-radius:8px"/></p>';
        $introHtml .= $descripcion;

        $now  = date('Y-m-d H:i:s');
        $uid  = (int)$user->id;
        $miItemid = RouteHelper::getItemid('miperfil');
        $redirect = 'index.php?option=com_artistas&view=miperfil' . ($miItemid ? '&Itemid='.$miItemid : '') . '#noticias';

        try {
            if ($noticiaId > 0) {
                // Editar
                $db->setQuery('SELECT article_id FROM #__artistas_noticias WHERE id=' . $noticiaId . ' AND artista_id=' . $artistaId . ' AND created_by=' . $uid);
                $articleId = (int)$db->loadResult();
                if ($articleId) {
                    $db->setQuery("UPDATE #__content SET title=" . $db->quote($titulo) . ", introtext=" . $db->quote($introHtml) . ", modified=" . $db->quote($now) . ", modified_by=$uid WHERE id=$articleId")->execute();
                }
                $db->setQuery("UPDATE #__artistas_noticias SET
                    tipo=" . $db->quote($tipo) . ",
                    titulo=" . $db->quote($titulo) . ",
                    fecha_evento=" . $db->quote($fecha) . ",
                    lugar=" . $db->quote($nombreEvento) . ",
                    nombre_disco=" . $db->quote($dbNombreDisco) . ",
                    provincia=" . $db->quote($provincia) . ",
                    descripcion=" . $db->quote($descripcion) . ",
                    imagen=" . $db->quote($imgPath) . "
                    WHERE id=$noticiaId")->execute();
                $app->enqueueMessage('Noticia actualizada.', 'success');
            } else {
                // Crear artículo en Joomla catid=19
                $alias = trim(preg_replace('/-+/','-',preg_replace('/[^a-z0-9-]/','-',strtolower($titulo))),'-').'-'.time();
                $db->setQuery("INSERT INTO #__content (title,alias,introtext,`fulltext`,state,catid,created,created_by,created_by_alias,modified,modified_by,checked_out,publish_up,access,featured,ordering,language,note,hits,version,images,urls,attribs,metadata,metakey,metadesc) VALUES (" .
                    $db->quote($titulo)."," . $db->quote($alias)."," . $db->quote($introHtml).",'',1,19," .
                    $db->quote($now).",$uid,''," . $db->quote($now).",$uid,0," . $db->quote($now).",1,0,0,'*','',0,1,'','','','','','')")->execute();
                $articleId = (int)$db->insertid();

                $db->setQuery("INSERT INTO #__artistas_noticias (artista_id,article_id,tipo,titulo,fecha_evento,lugar,nombre_disco,provincia,descripcion,imagen,state,created,created_by) VALUES ($artistaId,$articleId," .
                    $db->quote($tipo)."," . $db->quote($titulo)."," . $db->quote($fecha)."," .
                    $db->quote($nombreEvento)."," . $db->quote($dbNombreDisco)."," . $db->quote($provincia)."," .
                    $db->quote($descripcion)."," . $db->quote($imgPath).",1," . $db->quote($now).",$uid)")->execute();

                $app->enqueueMessage('Noticia publicada.', 'success');
            }
        } catch (\Exception $e) {
            $app->enqueueMessage('Error: ' . $e->getMessage(), 'error');
        }

        $app->redirect($redirect);
    }

    public function delete(): void
    {
        $app  = Factory::getApplication();
        $user = $app->getIdentity();
        $db   = Factory::getContainer()->get('DatabaseDriver');
        if ($user->guest) { $app->redirect('index.php?option=com_users&view=login'); return; }
        $id = $app->getInput()->getInt('id');
        $db->setQuery('SELECT article_id FROM #__artistas_noticias WHERE id=' . $id . ' AND created_by=' . (int)$user->id);
        $articleId = (int)$db->loadResult();
        if ($articleId) {
            $db->setQuery('DELETE FROM #__content WHERE id=' . $articleId)->execute();
            $db->setQuery('DELETE FROM #__artistas_noticias WHERE id=' . $id)->execute();
            $app->enqueueMessage('Noticia eliminada.', 'success');
        }
        $miItemid = RouteHelper::getItemid('miperfil');
        $app->redirect('index.php?option=com_artistas&view=miperfil' . ($miItemid ? '&Itemid='.$miItemid : '') . '#noticias');
    }
}
