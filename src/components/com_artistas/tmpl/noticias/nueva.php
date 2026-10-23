<?php
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\Component\Artistas\Site\Helper\RouteHelper;

$app  = Factory::getApplication();
$user = $app->getIdentity();

if ($user->guest) {
    // No logueado - redirigir a login
    $app->redirect('https://heavymetalbrothers.com/index.php/registro');
} else {
    // Logueado - verificar si tiene artista
    $db = Factory::getContainer()->get('DatabaseDriver');
    $db->setQuery('SELECT id, state FROM #__artistas WHERE created_by=' . (int)$user->id . ' LIMIT 1');
    $artista = $db->loadObject();

    if (!$artista) {
        // No tiene artista registrado
        $registroItemid = RouteHelper::getItemid('registro');
        $app->redirect('index.php?option=com_artistas&view=registro' . ($registroItemid ? '&Itemid='.$registroItemid : ''));
    } elseif ($artista->state != 1) {
        // Artista pendiente de aprobación
        $app->enqueueMessage('Tu ficha está pendiente de aprobación. Podrás publicar noticias cuando sea aprobada.', 'warning');
        $miItemid = RouteHelper::getItemid('miperfil');
        $app->redirect('index.php?option=com_artistas&view=miperfil' . ($miItemid ? '&Itemid='.$miItemid : ''));
    } else {
        // Redirigir a Mi ficha → sección noticias
        $miItemid = RouteHelper::getItemid('miperfil');
        $app->redirect('index.php?option=com_artistas&view=miperfil' . ($miItemid ? '&Itemid='.$miItemid : '') . '#noticias');
    }
}
