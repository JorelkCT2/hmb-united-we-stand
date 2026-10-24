<?php
namespace HeavyMetalBrothers\Component\Hmbfiles\Administrator\Helper;

defined('_JEXEC') or die;

/**
 * Todas las reglas de seguridad del gestor de archivos viven aqui: que carpetas se pueden tocar,
 * que ficheros se aceptan y como se resuelve una ruta sin salirse nunca de images/ y media/.
 */
class FsHelper
{
    /** Carpetas raiz permitidas (relativas a la raiz de Joomla). */
    public const RAICES = ['images', 'media'];

    /** Extensiones que se pueden subir / renombrar a. Nada ejecutable (php, js, html, svg, htaccess...). */
    public const TIPOS = [
        'imagen' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'bmp', 'ico'],
        'video'  => ['mp4', 'webm', 'mov', 'm4v', 'ogv'],
        'audio'  => ['mp3', 'ogg', 'wav', 'flac', 'm4a', 'aac'],
        'doc'    => ['pdf', 'txt', 'csv', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp', 'zip', 'rar', '7z'],
    ];

    /** Nombres que nunca se muestran ni se tocan. */
    private const OCULTOS = ['index.html', 'index.htm', 'web.config'];

    public static function extensionesPermitidas(): array
    {
        return array_merge(...array_values(self::TIPOS));
    }

    public static function tipoDe(string $nombre): string
    {
        $e = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));

        foreach (self::TIPOS as $tipo => $lista) {
            if (in_array($e, $lista, true)) {
                return $tipo;
            }
        }

        return 'otro';
    }

    public static function permitido(string $nombre): bool
    {
        return in_array(strtolower(pathinfo($nombre, PATHINFO_EXTENSION)), self::extensionesPermitidas(), true);
    }

    public static function oculto(string $nombre): bool
    {
        return $nombre === '' || $nombre[0] === '.' || in_array(strtolower($nombre), self::OCULTOS, true);
    }

    /** Normaliza una ruta relativa. Devuelve null si es rara o no cuelga de images/ o media/. */
    public static function limpiar(string $rel): ?string
    {
        if (strpos($rel, "\0") !== false) {
            return null;
        }

        $partes = [];

        foreach (explode('/', str_replace('\\', '/', $rel)) as $p) {
            if ($p === '' || $p === '.') {
                continue;
            }

            if ($p === '..' || $p[0] === '.' || preg_match('/[\x00-\x1f<>:"|?*]/', $p)) {
                return null;
            }

            $partes[] = $p;
        }

        if (!$partes || !in_array($partes[0], self::RAICES, true)) {
            return null;
        }

        return implode('/', $partes);
    }

    public static function esRaiz(string $rel): bool
    {
        return strpos($rel, '/') === false;
    }

    /**
     * Ruta absoluta de $rel, comprobando con realpath() que no se sale de su raiz (ni por enlaces simbolicos).
     * Devuelve null si no es valida.
     */
    public static function resolver(string $rel): ?string
    {
        $rel = self::limpiar($rel);

        if ($rel === null) {
            return null;
        }

        $raiz = realpath(JPATH_ROOT . '/' . explode('/', $rel)[0]);

        if ($raiz === false) {
            return null;
        }

        $abs  = JPATH_ROOT . '/' . $rel;
        $real = file_exists($abs) || is_link($abs)
            ? realpath($abs)
            : (($p = realpath(dirname($abs))) === false ? false : $p . '/' . basename($abs));

        if ($real === false) {
            return null;
        }

        $real = str_replace('\\', '/', $real);
        $raiz = str_replace('\\', '/', $raiz);

        return ($real === $raiz || strpos($real, $raiz . '/') === 0) ? $real : null;
    }

    /** Nombre de fichero/carpeta seguro: sin acentos, sin simbolos, un solo punto (la extension). */
    public static function nombreSeguro(string $nombre, bool $esCarpeta = false): string
    {
        $nombre = trim(str_replace(['\\', '/'], '-', $nombre));
        $t      = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nombre);
        $nombre = $t !== false && $t !== '' ? $t : $nombre;

        if ($esCarpeta) {
            $base = preg_replace('/[^A-Za-z0-9_\- ]+/', '-', $nombre);

            return trim(substr(preg_replace('/[\s-]+/', '-', $base), 0, 80), '-_ ');
        }

        $ext  = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
        $base = pathinfo($nombre, PATHINFO_FILENAME);
        $base = preg_replace('/[^A-Za-z0-9_\-]+/', '-', $base);
        $base = trim(substr(preg_replace('/-+/', '-', $base), 0, 80), '-_');

        return ($base === '' ? 'archivo' : $base) . ($ext !== '' ? '.' . preg_replace('/[^a-z0-9]/', '', $ext) : '');
    }

    /** Si ya existe un fichero con ese nombre en la carpeta, anade -1, -2... */
    public static function nombreLibre(string $dirAbs, string $nombre): string
    {
        if (!file_exists($dirAbs . '/' . $nombre)) {
            return $nombre;
        }

        $base = pathinfo($nombre, PATHINFO_FILENAME);
        $ext  = pathinfo($nombre, PATHINFO_EXTENSION);

        for ($i = 1; $i < 1000; $i++) {
            $n = $base . '-' . $i . ($ext !== '' ? '.' . $ext : '');

            if (!file_exists($dirAbs . '/' . $n)) {
                return $n;
            }
        }

        return $base . '-' . bin2hex(random_bytes(3)) . ($ext !== '' ? '.' . $ext : '');
    }

    /**
     * Revisa el contenido de un fichero subido: que sea lo que dice su extension y que no lleve codigo.
     * Devuelve un mensaje de error, o '' si es correcto.
     */
    public static function revisarContenido(string $tmp, string $nombre): string
    {
        $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));

        if (in_array($ext, self::TIPOS['imagen'], true) && $ext !== 'ico') {
            $info = @getimagesize($tmp);
            $ok   = [IMAGETYPE_JPEG => ['jpg', 'jpeg'], IMAGETYPE_PNG => ['png'], IMAGETYPE_GIF => ['gif'], IMAGETYPE_WEBP => ['webp'], IMAGETYPE_BMP => ['bmp']];

            if (defined('IMAGETYPE_AVIF')) {
                $ok[IMAGETYPE_AVIF] = ['avif'];
            }

            if (!$info || !isset($ok[$info[2]]) || !in_array($ext, $ok[$info[2]], true)) {
                return 'El contenido no es una imagen ' . strtoupper($ext) . ' valida.';
            }
        }

        $cabeza = (string) @file_get_contents($tmp, false, null, 0, 16384);

        // Solo marcas que no aparecen por azar en un fichero binario (audio, video, zip...);
        // en texto plano se exige ademas que no haya etiquetas cortas de codigo.
        $patron = in_array($ext, ['txt', 'csv'], true) ? '/<\?(php|=)|<script\b|<%|#!\s*\/(usr|bin)/i' : '/<\?php|<script\b/i';

        if (preg_match($patron, $cabeza)) {
            return 'El fichero contiene codigo ejecutable.';
        }

        return '';
    }

    /** Tamano maximo de un archivo subido por trozos (el proxy del hosting corta peticiones de mas de ~10 MB). */
    public const MAX_SUBIDA = 512 * 1048576;

    /** Tamano maximo de cada trozo, y el que usa el navegador (por debajo del limite del proxy). */
    public const TROZO = 4 * 1048576;

    /** Carpeta temporal de subidas por trozos; borra lo que lleve mas de un dia. */
    public static function dirUploads(): string
    {
        $d = JPATH_ROOT . '/tmp/hmb_uploads';

        if (!is_dir($d)) {
            @mkdir($d, 0755, true);
        }

        if (!is_file($d . '/.htaccess')) {
            @file_put_contents($d . '/.htaccess', "Require all denied\n");
            @file_put_contents($d . '/index.html', '<!DOCTYPE html><title></title>');
        }

        foreach (scandir($d) ?: [] as $x) {
            if ($x[0] !== '.' && $x !== 'index.html' && is_dir($d . '/' . $x) && filemtime($d . '/' . $x) < time() - 86400) {
                self::borrarRecursivo($d . '/' . $x);
            }
        }

        return $d;
    }

    public static function dirTrash(): string
    {
        $d = JPATH_ROOT . '/tmp/hmb_trash';

        if (!is_dir($d)) {
            @mkdir($d, 0755, true);
        }

        if (!is_file($d . '/.htaccess')) {
            @file_put_contents($d . '/.htaccess', "Require all denied\n");
            @file_put_contents($d . '/index.html', '<!DOCTYPE html><title></title>');
        }

        return $d;
    }

    /** Borra un fichero o carpeta entera (solo se usa dentro de la papelera). */
    public static function borrarRecursivo(string $ruta): void
    {
        if (is_link($ruta) || is_file($ruta)) {
            @unlink($ruta);

            return;
        }

        if (!is_dir($ruta)) {
            return;
        }

        foreach (scandir($ruta) ?: [] as $f) {
            if ($f !== '.' && $f !== '..') {
                self::borrarRecursivo($ruta . '/' . $f);
            }
        }

        @rmdir($ruta);
    }

    public static function tamanoCarpeta(string $ruta): int
    {
        $t = 0;

        if (!is_dir($ruta)) {
            return 0;
        }

        $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($ruta, \FilesystemIterator::SKIP_DOTS));

        foreach ($it as $f) {
            $t += $f->isFile() ? $f->getSize() : 0;
        }

        return $t;
    }
}
