<?php
namespace HeavyMetalBrothers\Component\Hmbfiles\Administrator\Controller;

defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbfiles\Administrator\Helper\FsHelper as Fs;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

/**
 * Acciones del gestor (task=ajax.xxx). Solo super administradores; todas las que cambian algo
 * exigen token. Nunca se sale de images/ y media/ (ver FsHelper::resolver).
 */
class AjaxController extends BaseController
{
    private function app()
    {
        return Factory::getApplication();
    }

    private function salir(array $datos): void
    {
        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            header('Cache-Control: no-store');
        }

        echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $this->app()->close();
    }

    private function fallo(string $m, int $codigo = 200): void
    {
        $this->log('rechazo', $m . (isset($_FILES['archivo']['name']) ? ' [' . $_FILES['archivo']['name'] . ', ' . (int) ($_FILES['archivo']['size'] ?? 0) . ' B, error ' . ($_FILES['archivo']['error'] ?? '-') . ']' : ''));
        if ($codigo !== 200 && !headers_sent()) {
            http_response_code($codigo);
        }

        $this->salir(['success' => false, 'message' => $m]);
    }

    /** Comprueba permisos (super admin) y, si cambia algo, el token. */
    private function guardia(bool $cambia = true): void
    {
        $u = $this->app()->getIdentity();

        if (!$u || !$u->id || !$u->authorise('core.admin')) {
            $this->fallo('Sin permiso: solo super administradores.', 403);
        }

        if ($cambia && !Session::checkToken('post') && !Session::checkToken('get')) {
            $this->fallo('Token no valido. Recarga la pagina.', 403);
        }
    }

    private function log(string $accion, string $detalle): void
    {
        try {
            Log::addLogger(['text_file' => 'hmb_files.log', 'text_entry_format' => '{DATETIME}	{MESSAGE}'], Log::ALL, ['hmbfiles']);
            $u = $this->app()->getIdentity();
            Log::add($u->username . '(#' . $u->id . ')	' . $accion . '	' . $detalle, Log::INFO, 'hmbfiles');
        } catch (\Throwable $e) {
        }
    }

    private function listaPost(string $campo): array
    {
        $l = json_decode((string) $this->app()->getInput()->post->getRaw($campo), true);

        return is_array($l) ? array_slice(array_filter($l, 'is_string'), 0, 200) : [];
    }

    private function urlPublica(string $rel): string
    {
        return Uri::root(true) . '/' . implode('/', array_map('rawurlencode', explode('/', $rel)));
    }

    // ── Listado ──────────────────────────────────────────────────────────

    public function lista(): void
    {
        $this->guardia(false);
        $dir = Fs::limpiar((string) $this->app()->getInput()->getString('dir', 'images'));
        $abs = $dir !== null ? Fs::resolver($dir) : null;

        if ($abs === null || !is_dir($abs)) {
            $this->fallo('Carpeta no valida.');
        }

        $carpetas = [];
        $ficheros = [];

        foreach (scandir($abs) ?: [] as $n) {
            if ($n === '.' || $n === '..' || Fs::oculto($n) || is_link($abs . '/' . $n)) {
                continue;
            }

            $p   = $abs . '/' . $n;
            $rel = $dir . '/' . $n;

            if (is_dir($p)) {
                $carpetas[] = ['nombre' => $n, 'rel' => $rel, 'fecha' => filemtime($p), 'elementos' => max(0, count(scandir($p) ?: []) - 2)];
            } elseif (is_file($p)) {
                $ficheros[] = [
                    'nombre' => $n, 'rel' => $rel, 'tamano' => filesize($p), 'fecha' => filemtime($p),
                    'tipo' => Fs::tipoDe($n), 'url' => $this->urlPublica($rel),
                ];
            }
        }

        usort($carpetas, static fn($a, $b) => strcasecmp($a['nombre'], $b['nombre']));
        usort($ficheros, static fn($a, $b) => strcasecmp($a['nombre'], $b['nombre']));

        $this->salir([
            'success' => true, 'dir' => $dir, 'padre' => Fs::esRaiz($dir) ? '' : dirname($dir),
            'carpetas' => $carpetas, 'ficheros' => $ficheros,
            'limite' => Fs::MAX_SUBIDA, 'trozo' => min(Fs::TROZO, $this->aBytes(ini_get('upload_max_filesize')), $this->aBytes(ini_get('post_max_size'))),
        ]);
    }

    private function aBytes(string $v): int
    {
        $n = (int) $v;

        switch (strtolower(substr(trim($v), -1))) {
            case 'g': return $n * 1073741824;
            case 'm': return $n * 1048576;
            case 'k': return $n * 1024;
        }

        return $n;
    }

    /** Todas las carpetas (para elegir destino al mover). */
    public function arbol(): void
    {
        $this->guardia(false);
        $salida = [];

        $rec = function (string $rel, int $nivel) use (&$rec, &$salida): void {
            $salida[] = ['rel' => $rel, 'nivel' => $nivel];

            if ($nivel >= 6 || count($salida) > 600) {
                return;
            }

            $abs = Fs::resolver($rel);

            foreach (($abs ? scandir($abs) : []) ?: [] as $n) {
                if ($n !== '.' && $n !== '..' && !Fs::oculto($n) && !is_link($abs . '/' . $n) && is_dir($abs . '/' . $n)) {
                    $rec($rel . '/' . $n, $nivel + 1);
                }
            }
        };

        foreach (Fs::RAICES as $r) {
            $rec($r, 0);
        }

        $this->salir(['success' => true, 'carpetas' => $salida]);
    }

    // ── Cambios ──────────────────────────────────────────────────────────

    public function carpeta(): void
    {
        $this->guardia();
        $in     = $this->app()->getInput()->post;
        $dir    = Fs::limpiar($in->getString('dir', ''));
        $abs    = $dir !== null ? Fs::resolver($dir) : null;
        $nombre = Fs::nombreSeguro($in->getString('nombre', ''), true);

        if ($abs === null || !is_dir($abs) || $nombre === '') {
            $this->fallo('Nombre o carpeta no validos.');
        }

        if (file_exists($abs . '/' . $nombre)) {
            $this->fallo('Ya existe "' . $nombre . '".');
        }

        if (!@mkdir($abs . '/' . $nombre, 0755)) {
            $this->fallo('No se pudo crear la carpeta (permisos).');
        }

        $this->log('mkdir', $dir . '/' . $nombre);
        $this->salir(['success' => true]);
    }

    /** Un fichero por peticion (el navegador sube la cola de uno en uno). */
    public function subir(): void
    {
        $this->guardia();
        $dir = Fs::limpiar((string) $this->app()->getInput()->post->getString('dir', ''));
        $abs = $dir !== null ? Fs::resolver($dir) : null;
        $f   = $_FILES['archivo'] ?? null;

        if ($abs === null || !is_dir($abs)) {
            $this->fallo('Carpeta de destino no valida.');
        }

        if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->fallo('No se recibio el archivo (' . ($f['error'] ?? '?') . '). Puede superar el limite del servidor.');
        }

        $nombre = Fs::nombreSeguro((string) $f['name']);

        if (!Fs::permitido($nombre)) {
            $this->fallo('Tipo de archivo no permitido: .' . strtolower(pathinfo($nombre, PATHINFO_EXTENSION)));
        }

        if (!is_uploaded_file($f['tmp_name'])) {
            $this->fallo('Subida no valida.');
        }

        $err = Fs::revisarContenido($f['tmp_name'], $nombre);

        if ($err !== '') {
            $this->fallo($err);
        }

        $nombre = Fs::nombreLibre($abs, $nombre);

        if (!move_uploaded_file($f['tmp_name'], $abs . '/' . $nombre)) {
            $this->fallo('No se pudo guardar el archivo (permisos).');
        }

        @chmod($abs . '/' . $nombre, 0644);
        $this->log('subir', $dir . '/' . $nombre . ' (' . (int) $f['size'] . ' B)');
        $this->salir(['success' => true, 'nombre' => $nombre]);
    }

    /**
     * Subida de archivos grandes en trozos de 4 MB (el proxy del hosting rechaza peticiones de ~10 MB o mas).
     * El navegador envia los trozos en orden; con el ultimo se ensambla, se revisa el contenido y se coloca.
     */
    public function subirTrozo(): void
    {
        $this->guardia();
        $in = $this->app()->getInput()->post;
        $id = (string) $in->getString('id', '');
        $i  = $in->getInt('i', -1);
        $n  = $in->getInt('n', 0);
        $f  = $_FILES['trozo'] ?? null;

        if (!preg_match('/^[a-f0-9]{24}$/', $id) || $n < 2 || $n > 256 || $i < 0 || $i >= $n) {
            $this->fallo('Datos de subida no validos.');
        }

        if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name']) || $f['size'] > Fs::TROZO + 1024) {
            $this->fallo('El trozo ' . ($i + 1) . ' no se recibio correctamente.');
        }

        $uid  = (int) $this->app()->getIdentity()->id;
        $base = Fs::dirUploads();
        $dir  = $base . '/' . $id;
        $metaF = $dir . '/meta.json';

        if ($i === 0) {
            $rel    = Fs::limpiar((string) $in->getString('dir', ''));
            $nombre = Fs::nombreSeguro((string) $in->getString('nombre', ''));

            if ($rel === null || ($d = Fs::resolver($rel)) === null || !is_dir($d)) {
                $this->fallo('Carpeta de destino no valida.');
            }

            if (!Fs::permitido($nombre)) {
                $this->fallo('Tipo de archivo no permitido: .' . strtolower(pathinfo($nombre, PATHINFO_EXTENSION)));
            }

            if (is_dir($dir) || !@mkdir($dir, 0755)) {
                $this->fallo('No se pudo iniciar la subida.');
            }

            file_put_contents($metaF, json_encode(['uid' => $uid, 'dir' => $rel, 'nombre' => $nombre, 'n' => $n]));
        }

        $meta = is_file($metaF) ? json_decode((string) file_get_contents($metaF), true) : null;

        if (!is_array($meta) || (int) $meta['uid'] !== $uid || (int) $meta['n'] !== $n) {
            $this->fallo('Subida desconocida o caducada. Vuelve a empezar.');
        }

        if (!move_uploaded_file($f['tmp_name'], $dir . '/p' . sprintf('%04d', $i))) {
            $this->fallo('No se pudo guardar el trozo.');
        }

        $total = 0;

        foreach (glob($dir . '/p*') ?: [] as $p) {
            $total += filesize($p);
        }

        if ($total > Fs::MAX_SUBIDA) {
            Fs::borrarRecursivo($dir);
            $this->fallo('El archivo supera el maximo de ' . (Fs::MAX_SUBIDA / 1048576) . ' MB.');
        }

        if ($i < $n - 1) {
            $this->salir(['success' => true, 'parcial' => true]);
        }

        // Ultimo trozo: ensamblar
        $final = $dir . '/final';
        $out   = fopen($final, 'wb');

        for ($k = 0; $k < $n; $k++) {
            $p = $dir . '/p' . sprintf('%04d', $k);

            if (!is_file($p)) {
                fclose($out);
                Fs::borrarRecursivo($dir);
                $this->fallo('Falta el trozo ' . ($k + 1) . '. Vuelve a subir el archivo.');
            }

            $src = fopen($p, 'rb');
            stream_copy_to_stream($src, $out);
            fclose($src);
        }

        fclose($out);

        $err = Fs::revisarContenido($final, $meta['nombre']);
        $dAbs = Fs::resolver($meta['dir']);

        if ($err !== '' || $dAbs === null || !is_dir($dAbs)) {
            Fs::borrarRecursivo($dir);
            $this->fallo($err !== '' ? $err : 'La carpeta de destino ya no existe.');
        }

        $nombre = Fs::nombreLibre($dAbs, $meta['nombre']);

        if (!@rename($final, $dAbs . '/' . $nombre) && !(@copy($final, $dAbs . '/' . $nombre))) {
            Fs::borrarRecursivo($dir);
            $this->fallo('No se pudo guardar el archivo (permisos).');
        }

        @chmod($dAbs . '/' . $nombre, 0644);
        Fs::borrarRecursivo($dir);
        $this->log('subir (trozos)', $meta['dir'] . '/' . $nombre . ' (' . $total . ' B)');
        $this->salir(['success' => true, 'nombre' => $nombre]);
    }

    public function renombrar(): void
    {
        $this->guardia();
        $in  = $this->app()->getInput()->post;
        $rel = Fs::limpiar($in->getString('rel', ''));
        $abs = $rel !== null ? Fs::resolver($rel) : null;

        if ($rel === null || $abs === null || Fs::esRaiz($rel) || !file_exists($abs)) {
            $this->fallo('Elemento no valido.');
        }

        $esDir = is_dir($abs);
        $nuevo = Fs::nombreSeguro($in->getString('nombre', ''), $esDir);

        if ($nuevo === '' || (!$esDir && !Fs::permitido($nuevo))) {
            $this->fallo('Nombre no valido o extension no permitida.');
        }

        $destino = dirname($abs) . '/' . $nuevo;

        if (file_exists($destino)) {
            $this->fallo('Ya existe "' . $nuevo . '".');
        }

        if (!@rename($abs, $destino)) {
            $this->fallo('No se pudo renombrar.');
        }

        $this->log('renombrar', $rel . ' -> ' . $nuevo);
        $this->salir(['success' => true]);
    }

    public function mover(): void
    {
        $this->guardia();
        $dest = Fs::limpiar((string) $this->app()->getInput()->post->getString('dest', ''));
        $dAbs = $dest !== null ? Fs::resolver($dest) : null;

        if ($dAbs === null || !is_dir($dAbs)) {
            $this->fallo('Carpeta de destino no valida.');
        }

        $hechos = 0;
        $errores = [];

        foreach ($this->listaPost('rels') as $r) {
            $rel = Fs::limpiar($r);
            $abs = $rel !== null ? Fs::resolver($rel) : null;

            if ($rel === null || $abs === null || Fs::esRaiz($rel) || !file_exists($abs)) {
                $errores[] = $r . ': no valido';
                continue;
            }

            if (is_dir($abs) && ($dAbs === $abs || strpos($dAbs . '/', $abs . '/') === 0)) {
                $errores[] = basename($abs) . ': no se puede mover dentro de si misma';
                continue;
            }

            $nombre = basename($abs);

            if (file_exists($dAbs . '/' . $nombre)) {
                $errores[] = $nombre . ': ya existe en el destino';
                continue;
            }

            if (@rename($abs, $dAbs . '/' . $nombre)) {
                $hechos++;
                $this->log('mover', $rel . ' -> ' . $dest);
            } else {
                $errores[] = $nombre . ': fallo al mover';
            }
        }

        $this->salir(['success' => $hechos > 0 || !$errores, 'movidos' => $hechos, 'errores' => $errores, 'message' => $errores ? implode(' · ', $errores) : '']);
    }

    // ── Papelera ─────────────────────────────────────────────────────────

    public function borrar(): void
    {
        $this->guardia();
        $trash  = Fs::dirTrash();
        $hechos = 0;
        $errores = [];

        foreach ($this->listaPost('rels') as $r) {
            $rel = Fs::limpiar($r);
            $abs = $rel !== null ? Fs::resolver($rel) : null;

            if ($rel === null || $abs === null || Fs::esRaiz($rel) || !file_exists($abs)) {
                $errores[] = $r . ': no valido';
                continue;
            }

            $id   = date('YmdHis') . '-' . bin2hex(random_bytes(3));
            $dest = $trash . '/' . $id;

            if (!@mkdir($dest, 0755) || !@rename($abs, $dest . '/contenido')) {
                @rmdir($dest);
                $errores[] = basename($abs) . ': no se pudo mover a la papelera';
                continue;
            }

            file_put_contents($dest . '/meta.json', json_encode([
                'rel' => $rel, 'nombre' => basename($abs), 'esDir' => is_dir($dest . '/contenido'),
                'fecha' => time(), 'usuario' => $this->app()->getIdentity()->username,
                'tamano' => is_dir($dest . '/contenido') ? Fs::tamanoCarpeta($dest . '/contenido') : filesize($dest . '/contenido'),
            ], JSON_UNESCAPED_UNICODE));
            $hechos++;
            $this->log('papelera', $rel);
        }

        $this->salir(['success' => $hechos > 0 || !$errores, 'borrados' => $hechos, 'errores' => $errores, 'message' => $errores ? implode(' · ', $errores) : '']);
    }

    public function papelera(): void
    {
        $this->guardia(false);
        $trash = Fs::dirTrash();
        $items = [];

        foreach (scandir($trash) ?: [] as $id) {
            if ($id[0] === '.' || !is_file($trash . '/' . $id . '/meta.json')) {
                continue;
            }

            $m = json_decode((string) file_get_contents($trash . '/' . $id . '/meta.json'), true);

            // Lo que lleva mas de 30 dias se elimina definitivamente
            if (!is_array($m) || ($m['fecha'] ?? 0) < time() - 30 * 86400) {
                Fs::borrarRecursivo($trash . '/' . $id);
                continue;
            }

            $items[] = ['id' => $id] + $m;
        }

        usort($items, static fn($a, $b) => $b['fecha'] <=> $a['fecha']);
        $this->salir(['success' => true, 'items' => $items]);
    }

    public function restaurar(): void
    {
        $this->guardia();
        $id = (string) $this->app()->getInput()->post->getString('id', '');

        if (!preg_match('/^\d{14}-[0-9a-f]{6}$/', $id)) {
            $this->fallo('Elemento no valido.');
        }

        $dir = Fs::dirTrash() . '/' . $id;
        $m   = is_file($dir . '/meta.json') ? json_decode((string) file_get_contents($dir . '/meta.json'), true) : null;
        $rel = is_array($m) ? Fs::limpiar((string) $m['rel']) : null;

        if ($rel === null) {
            $this->fallo('Elemento corrupto.');
        }

        $padre = Fs::resolver(dirname($rel));

        if ($padre === null || !is_dir($padre)) {
            @mkdir(JPATH_ROOT . '/' . dirname($rel), 0755, true);
            $padre = Fs::resolver(dirname($rel));
        }

        if ($padre === null) {
            $this->fallo('La carpeta original ya no existe.');
        }

        $nombre = Fs::nombreLibre($padre, basename($rel));

        if (!@rename($dir . '/contenido', $padre . '/' . $nombre)) {
            $this->fallo('No se pudo restaurar.');
        }

        Fs::borrarRecursivo($dir);
        $this->log('restaurar', dirname($rel) . '/' . $nombre);
        $this->salir(['success' => true, 'rel' => dirname($rel) . '/' . $nombre]);
    }

    public function vaciar(): void
    {
        $this->guardia();
        $trash = Fs::dirTrash();
        $n     = 0;

        foreach (scandir($trash) ?: [] as $id) {
            if (preg_match('/^\d{14}-[0-9a-f]{6}$/', $id)) {
                Fs::borrarRecursivo($trash . '/' . $id);
                $n++;
            }
        }

        $this->log('vaciar papelera', $n . ' elementos');
        $this->salir(['success' => true, 'eliminados' => $n]);
    }

    // ── Descarga ─────────────────────────────────────────────────────────

    public function descargar(): void
    {
        $this->guardia(false);

        if (!Session::checkToken('get')) {
            $this->fallo('Token no valido.', 403);
        }

        $rel = Fs::limpiar((string) $this->app()->getInput()->getString('rel', ''));
        $abs = $rel !== null ? Fs::resolver($rel) : null;

        if ($abs === null || !is_file($abs)) {
            $this->fallo('Archivo no encontrado.', 404);
        }

        while (ob_get_level() > 0) {
            @ob_end_clean();
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . rawurlencode(basename($abs)) . '"; filename*=UTF-8\'\'' . rawurlencode(basename($abs)));
        header('Content-Length: ' . filesize($abs));
        header('X-Content-Type-Options: nosniff');
        readfile($abs);
        $this->log('descargar', $rel);
        $this->app()->close();
    }
}
