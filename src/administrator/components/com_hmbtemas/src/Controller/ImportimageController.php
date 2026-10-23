<?php
namespace HeavyMetalBrothers\Component\HmbTemas\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;

class ImportimageController extends BaseController
{
    public function import(): void
    {
        // Verificar token CSRF
        Session::checkToken('request') or jexit(json_encode(['success'=>false,'error'=>'Token inválido']));

        $app   = Factory::getApplication();
        $input = $app->getInput();
        $url   = trim($input->getString('url', ''));

        header('Content-Type: application/json; charset=utf-8');

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            echo json_encode(['success'=>false,'error'=>'URL no válida']);
            $app->close();
            return;
        }

        // Solo permitir URLs de imagen
        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        $extPermitidas = ['jpg','jpeg','png','gif','webp'];
        if (!in_array($ext, $extPermitidas, true)) {
            // Intentar detectar por Content-Type
            $ext = 'jpg';
        }

        // Crear carpeta destino
        $destDir = JPATH_ROOT . '/images/hmb/portadas';
        if (!is_dir($destDir) && !@mkdir($destDir, 0755, true)) {
            echo json_encode(['success'=>false,'error'=>'No se pudo crear el directorio destino']);
            $app->close();
            return;
        }

        // Descargar imagen
        $ctx = stream_context_create([
            'http' => [
                'timeout'     => 15,
                'user_agent'  => 'Mozilla/5.0 (compatible; HMBImporter/1.0)',
                'follow_location' => true,
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ]);

        $datos = @file_get_contents($url, false, $ctx);
        if ($datos === false || strlen($datos) < 100) {
            echo json_encode(['success'=>false,'error'=>'No se pudo descargar la imagen']);
            $app->close();
            return;
        }

        // Detectar tipo real por cabecera de bytes
        $sig = substr($datos, 0, 4);
        if (substr($sig, 0, 3) === "\xFF\xD8\xFF") $ext = 'jpg';
        elseif ($sig === "\x89PNG")                 $ext = 'png';
        elseif (substr($sig, 0, 3) === 'GIF')       $ext = 'gif';
        elseif (substr($sig, 0, 4) === 'RIFF')      $ext = 'webp';

        // Guardar temporal
        $tmpFile = tempnam(sys_get_temp_dir(), 'hmb_img_');
        file_put_contents($tmpFile, $datos);

        // Cargar con GD
        $imgSrc = match($ext) {
            'png'  => @imagecreatefrompng($tmpFile),
            'gif'  => @imagecreatefromgif($tmpFile),
            'webp' => @imagecreatefromwebp($tmpFile),
            default => @imagecreatefromjpeg($tmpFile),
        };

        @unlink($tmpFile);

        if (!$imgSrc) {
            echo json_encode(['success'=>false,'error'=>'No se pudo procesar la imagen (GD)']);
            $app->close();
            return;
        }

        $ancho  = imagesx($imgSrc);
        $alto   = imagesy($imgSrc);
        $maxPx  = 500;

        // Redimensionar manteniendo proporción
        if ($ancho > $maxPx || $alto > $maxPx) {
            $ratio     = min($maxPx / $ancho, $maxPx / $alto);
            $nuevoW    = (int) round($ancho * $ratio);
            $nuevoH    = (int) round($alto  * $ratio);
            $imgDest   = imagecreatetruecolor($nuevoW, $nuevoH);
            // Fondo blanco para PNG transparentes
            imagefill($imgDest, 0, 0, imagecolorallocate($imgDest, 255, 255, 255));
            imagecopyresampled($imgDest, $imgSrc, 0, 0, 0, 0, $nuevoW, $nuevoH, $ancho, $alto);
            imagedestroy($imgSrc);
        } else {
            $imgDest = $imgSrc;
            $nuevoW  = $ancho;
            $nuevoH  = $alto;
        }

        // Guardar como JPG
        $nombre   = 'portada_' . uniqid() . '.jpg';
        $destFile = $destDir . '/' . $nombre;
        $ok = imagejpeg($imgDest, $destFile, 88);
        imagedestroy($imgDest);

        if (!$ok) {
            echo json_encode(['success'=>false,'error'=>'Error al guardar la imagen redimensionada']);
            $app->close();
            return;
        }

        $rutaRelativa = 'images/hmb/portadas/' . $nombre;
        echo json_encode([
            'success' => true,
            'path'    => $rutaRelativa,
            'url'     => \Joomla\CMS\Uri\Uri::root() . $rutaRelativa,
            'size'    => $nuevoW . 'x' . $nuevoH,
        ]);
        $app->close();
    }
}
