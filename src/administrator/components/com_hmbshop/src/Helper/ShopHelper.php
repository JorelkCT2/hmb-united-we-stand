<?php
namespace HeavyMetalBrothers\Component\Hmbshop\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

/**
 * Logica de la tienda HMB compartida por el admin y el sitio: catalogo, variantes,
 * carrito (en sesion) y pedidos. Los precios son PVP con IVA incluido.
 */
class ShopHelper
{
    public const ESTADOS = [
        'pendiente' => 'Pendiente',
        'preparado' => 'Listo para recoger',
        'entregado' => 'Entregado',
        'cancelado' => 'Cancelado',
    ];

    public static function db(): DatabaseInterface
    {
        return Factory::getContainer()->get(DatabaseInterface::class);
    }

    public static function params(): Registry
    {
        return ComponentHelper::getParams('com_hmbshop');
    }

    public static function dinero(float $n): string
    {
        return number_format($n, 2, ',', '.') . ' ' . (string) self::params()->get('moneda', '€');
    }

    public static function alias(string $texto): string
    {
        $a = ApplicationHelper::stringURLSafe($texto);

        return $a !== '' ? $a : 'x-' . date('His');
    }

    /** insertObject exige una variable (por referencia): este envoltorio acepta un array literal. */
    private static function insertar(string $tabla, array $fila): int
    {
        $db  = self::db();
        $obj = (object) $fila;
        $db->insertObject($tabla, $obj);

        return (int) $db->insertid();
    }

    // ---------------------------------------------------------------- categorias

    /** Arbol aplanado con ->nivel (0 = raiz). */
    public static function categorias(bool $soloPublicadas = false): array
    {
        $db = self::db();
        $q  = $db->getQuery(true)->select('*')->from('#__hmbshop_categorias')->order('ordering, id');

        if ($soloPublicadas) {
            $q->where('published = 1');
        }

        $filas = $db->setQuery($q)->loadObjectList() ?: [];
        $hijos = [];

        foreach ($filas as $f) {
            $hijos[(int) $f->parent_id][] = $f;
        }

        $salida = [];
        $subir  = function (int $padre, int $nivel) use (&$subir, &$salida, $hijos) {
            foreach ($hijos[$padre] ?? [] as $f) {
                $f->nivel = $nivel;
                $salida[] = $f;
                $subir((int) $f->id, $nivel + 1);
            }
        };
        $subir(0, 0);

        // categorias cuyo padre no esta publicado: no se pierden en el admin
        if (!$soloPublicadas) {
            $vistos = array_column($salida, 'id');

            foreach ($filas as $f) {
                if (!in_array($f->id, $vistos)) {
                    $f->nivel = 0;
                    $salida[] = $f;
                }
            }
        }

        return $salida;
    }

    public static function categoria(int $id): ?object
    {
        $db = self::db();

        return $db->setQuery($db->getQuery(true)->select('*')->from('#__hmbshop_categorias')->where('id = ' . $id))->loadObject() ?: null;
    }

    public static function guardarCategoria(array $d): int
    {
        $db     = self::db();
        $id     = (int) ($d['id'] ?? 0);
        $nombre = trim((string) ($d['nombre'] ?? ''));

        if ($nombre === '') {
            throw new \RuntimeException('La categoría necesita un nombre.');
        }

        $padre = (int) ($d['parent_id'] ?? 0);

        if ($id && $padre === $id) {
            $padre = 0;
        }

        $fila = (object) [
            'parent_id'   => $padre,
            'nombre'      => $nombre,
            'alias'       => self::alias(trim((string) ($d['alias'] ?? '')) ?: $nombre),
            'descripcion' => (string) ($d['descripcion'] ?? ''),
            'published'   => !empty($d['published']) ? 1 : 0,
            'ordering'    => (int) ($d['ordering'] ?? 0),
        ];

        if (!empty($d['imagen'])) {
            $fila->imagen = (string) $d['imagen'];
        }

        if ($id) {
            $fila->id = $id;
            $db->updateObject('#__hmbshop_categorias', $fila, 'id');

            return $id;
        }

        $fila->created = (new Date())->toSql();
        $db->insertObject('#__hmbshop_categorias', $fila);

        return (int) $db->insertid();
    }

    public static function borrarCategoria(int $id): string
    {
        $db = self::db();
        $n  = (int) $db->setQuery('SELECT COUNT(*) FROM #__hmbshop_categorias WHERE parent_id = ' . $id)->loadResult();

        if ($n) {
            return 'La categoría tiene subcategorías.';
        }

        $n = (int) $db->setQuery('SELECT COUNT(*) FROM #__hmbshop_producto_categorias WHERE categoria_id = ' . $id)->loadResult();

        if ($n) {
            return 'La categoría tiene productos.';
        }

        $db->setQuery('DELETE FROM #__hmbshop_categorias WHERE id = ' . $id)->execute();

        return '';
    }

    // ---------------------------------------------------------------- productos

    /** Productos con categoria, foto principal y nº de variantes. $f: categoria, solo_publicados, buscar. */
    public static function productos(array $f = []): array
    {
        $db = self::db();
        $q  = $db->getQuery(true)
            ->select('p.*, c.nombre AS categoria_nombre')
            ->select('(SELECT fichero FROM #__hmbshop_fotos WHERE producto_id = p.id ORDER BY principal DESC, ordering, id LIMIT 1) AS foto')
            ->select('(SELECT COUNT(*) FROM #__hmbshop_variantes WHERE producto_id = p.id AND published = 1) AS n_variantes')
            ->select('(SELECT SUM(stock) FROM #__hmbshop_variantes WHERE producto_id = p.id) AS stock_total')
            ->from('#__hmbshop_productos p')
            ->join('LEFT', '#__hmbshop_categorias c ON c.id = p.categoria_id')
            ->order('p.ordering, p.id');

        if (!empty($f['solo_publicados'])) {
            $q->where('p.published = 1');
        }

        if (!empty($f['categoria'])) {
            // la categoria y (salvo 'exacta') todas las que cuelgan de ella
            $ids   = [(int) $f['categoria']];
            $todas = empty($f['exacta']) ? self::categorias() : [];

            do {
                $antes = count($ids);

                foreach ($todas as $c) {
                    if (in_array((int) $c->parent_id, $ids, true) && !in_array((int) $c->id, $ids, true)) {
                        $ids[] = (int) $c->id;
                    }
                }
            } while (count($ids) > $antes);

            $lista = implode(',', $ids);
            $q->where('(p.categoria_id IN (' . $lista . ') OR p.id IN (SELECT producto_id FROM #__hmbshop_producto_categorias WHERE categoria_id IN (' . $lista . ')))');
        }

        if (!empty($f['buscar'])) {
            $q->where('p.nombre LIKE ' . $db->quote('%' . str_replace(['%', '_'], ['\\%', '\\_'], (string) $f['buscar']) . '%'));
        }

        return $db->setQuery($q)->loadObjectList() ?: [];
    }

    public static function producto(int $id): ?object
    {
        $db = self::db();
        $p  = $db->setQuery($db->getQuery(true)->select('*')->from('#__hmbshop_productos')->where('id = ' . $id))->loadObject();

        if (!$p) {
            return null;
        }

        $p->fotos = $db->setQuery('SELECT * FROM #__hmbshop_fotos WHERE producto_id = ' . $id . ' ORDER BY principal DESC, ordering, id')->loadObjectList() ?: [];
        $p->ejes  = $db->setQuery('SELECT * FROM #__hmbshop_ejes WHERE producto_id = ' . $id . ' ORDER BY ordering, id')->loadObjectList() ?: [];

        foreach ($p->ejes as $e) {
            $e->valores = $db->setQuery('SELECT * FROM #__hmbshop_eje_valores WHERE eje_id = ' . (int) $e->id . ' ORDER BY ordering, id')->loadObjectList() ?: [];
        }

        $p->variantes = $db->setQuery('SELECT * FROM #__hmbshop_variantes WHERE producto_id = ' . $id . ' ORDER BY id')->loadObjectList() ?: [];
        $p->categorias = array_map('intval', $db->setQuery('SELECT categoria_id FROM #__hmbshop_producto_categorias WHERE producto_id = ' . $id)->loadColumn() ?: []);

        return $p;
    }

    public static function guardarProducto(array $d): int
    {
        $db     = self::db();
        $id     = (int) ($d['id'] ?? 0);
        $nombre = trim((string) ($d['nombre'] ?? ''));

        if ($nombre === '') {
            throw new \RuntimeException('El producto necesita un nombre.');
        }

        $precio = max(0.0, (float) str_replace(',', '.', (string) ($d['precio'] ?? '0')));
        $iva    = (string) ($d['iva_pct'] ?? '') === '' ? (float) self::params()->get('iva_defecto', 21) : (float) $d['iva_pct'];
        $cats   = array_values(array_unique(array_filter(array_map('intval', (array) ($d['categorias'] ?? [])))));

        $fila = (object) [
            'categoria_id' => $cats[0] ?? (int) ($d['categoria_id'] ?? 0),
            'nombre'       => $nombre,
            'alias'        => self::alias(trim((string) ($d['alias'] ?? '')) ?: $nombre),
            'descripcion'  => (string) ($d['descripcion'] ?? ''),
            'precio'       => $precio,
            'iva_pct'      => max(0.0, min(30.0, $iva)),
            'sku'          => trim((string) ($d['sku'] ?? '')) ?: null,
            'destacado'    => !empty($d['destacado']) ? 1 : 0,
            'published'    => !empty($d['published']) ? 1 : 0,
            'ordering'     => (int) ($d['ordering'] ?? 0),
            'modified'     => (new Date())->toSql(),
        ];

        if ($id) {
            $fila->id = $id;
            $db->updateObject('#__hmbshop_productos', $fila, 'id', true);
        } else {
            $fila->created = (new Date())->toSql();
            $db->insertObject('#__hmbshop_productos', $fila);
            $id = (int) $db->insertid();
            self::asegurarVariante($id);
        }

        $db->setQuery('DELETE FROM #__hmbshop_producto_categorias WHERE producto_id = ' . $id)->execute();

        foreach ($cats as $c) {
            $db->setQuery('INSERT IGNORE INTO #__hmbshop_producto_categorias (producto_id, categoria_id) VALUES (' . $id . ', ' . (int) $c . ')')->execute();
        }

        return $id;
    }

    public static function borrarProducto(int $id): void
    {
        $db = self::db();

        foreach (self::producto($id)->fotos ?? [] as $f) {
            self::borrarFichero($f->fichero);
        }

        foreach (['hmbshop_fotos', 'hmbshop_variantes', 'hmbshop_producto_categorias'] as $t) {
            $db->setQuery('DELETE FROM #__' . $t . ' WHERE producto_id = ' . $id)->execute();
        }

        $db->setQuery('DELETE FROM #__hmbshop_variante_valores WHERE eje_id IN (SELECT id FROM #__hmbshop_ejes WHERE producto_id = ' . $id . ')')->execute();
        $db->setQuery('DELETE FROM #__hmbshop_eje_valores WHERE eje_id IN (SELECT id FROM #__hmbshop_ejes WHERE producto_id = ' . $id . ')')->execute();
        $db->setQuery('DELETE FROM #__hmbshop_ejes WHERE producto_id = ' . $id)->execute();
        $db->setQuery('DELETE FROM #__hmbshop_productos WHERE id = ' . $id)->execute();
    }

    // ---------------------------------------------------------------- ejes y variantes

    /** $valores: "S, M, L, 3XL:3" (el numero tras ":" es el recargo en euros). */
    public static function anadirEje(int $productoId, string $nombre, string $valores, bool $obligatorio = true): void
    {
        $db     = self::db();
        $nombre = trim($nombre);

        if ($nombre === '') {
            throw new \RuntimeException('El eje necesita un nombre (Talla, Color...).');
        }

        $orden = (int) $db->setQuery('SELECT COALESCE(MAX(ordering), -1) + 1 FROM #__hmbshop_ejes WHERE producto_id = ' . $productoId)->loadResult();
        $eje   = (object) ['producto_id' => $productoId, 'nombre' => $nombre, 'obligatorio' => $obligatorio ? 1 : 0, 'ordering' => $orden];
        $db->insertObject('#__hmbshop_ejes', $eje);
        $ejeId = (int) $db->insertid();

        self::anadirValores($ejeId, $valores);
        self::regenerarVariantes($productoId);
    }

    public static function anadirValores(int $ejeId, string $valores): void
    {
        $db   = self::db();
        $orden = (int) $db->setQuery('SELECT COALESCE(MAX(ordering), -1) + 1 FROM #__hmbshop_eje_valores WHERE eje_id = ' . $ejeId)->loadResult();

        foreach (preg_split('/[,\n;]+/', $valores) as $v) {
            $v = trim($v);

            if ($v === '') {
                continue;
            }

            $recargo = 0.0;

            if (strpos($v, ':') !== false) {
                [$v, $r] = array_map('trim', explode(':', $v, 2));
                $recargo = (float) str_replace(',', '.', $r);
            }

            self::insertar('#__hmbshop_eje_valores', ['eje_id' => $ejeId, 'valor' => mb_substr($v, 0, 60), 'recargo' => $recargo, 'ordering' => $orden++]);
        }
    }

    public static function borrarEje(int $ejeId): void
    {
        $db = self::db();
        $pid = (int) $db->setQuery('SELECT producto_id FROM #__hmbshop_ejes WHERE id = ' . $ejeId)->loadResult();
        $db->setQuery('DELETE FROM #__hmbshop_eje_valores WHERE eje_id = ' . $ejeId)->execute();
        $db->setQuery('DELETE FROM #__hmbshop_variante_valores WHERE eje_id = ' . $ejeId)->execute();
        $db->setQuery('DELETE FROM #__hmbshop_ejes WHERE id = ' . $ejeId)->execute();

        if ($pid) {
            self::regenerarVariantes($pid);
        }
    }

    public static function borrarValor(int $valorId): void
    {
        $db  = self::db();
        $pid = (int) $db->setQuery('SELECT e.producto_id FROM #__hmbshop_eje_valores v JOIN #__hmbshop_ejes e ON e.id = v.eje_id WHERE v.id = ' . $valorId)->loadResult();
        $db->setQuery('DELETE FROM #__hmbshop_eje_valores WHERE id = ' . $valorId)->execute();

        if ($pid) {
            self::regenerarVariantes($pid);
        }
    }

    /** Todo producto tiene al menos una variante ("por defecto") para llevar el stock siempre igual. */
    public static function asegurarVariante(int $productoId): void
    {
        $db = self::db();

        if (!(int) $db->setQuery('SELECT COUNT(*) FROM #__hmbshop_variantes WHERE producto_id = ' . $productoId)->loadResult()) {
            self::insertar('#__hmbshop_variantes', ['producto_id' => $productoId, 'etiqueta' => '', 'stock' => 0, 'recargo' => 0, 'published' => 1]);
        }
    }

    /** Una variante por combinacion de valores; conserva el stock de las que ya existian con la misma etiqueta. */
    public static function regenerarVariantes(int $productoId): void
    {
        $db   = self::db();
        $ejes = $db->setQuery('SELECT id FROM #__hmbshop_ejes WHERE producto_id = ' . $productoId . ' ORDER BY ordering, id')->loadColumn() ?: [];
        $listas = [];

        foreach ($ejes as $e) {
            $vals = $db->setQuery('SELECT * FROM #__hmbshop_eje_valores WHERE eje_id = ' . (int) $e . ' ORDER BY ordering, id')->loadObjectList() ?: [];

            if ($vals) {
                if (!(int) $db->setQuery('SELECT obligatorio FROM #__hmbshop_ejes WHERE id = ' . (int) $e)->loadResult()) {
                    $vals[] = null; // "sin elegir": variante que no lleva ese eje
                }

                $listas[(int) $e] = $vals;
            }
        }

        $combos = [[]];

        foreach ($listas as $eje => $vals) {
            $nuevo = [];

            foreach ($combos as $c) {
                foreach ($vals as $v) {
                    $nuevo[] = $c + [$eje => $v]; // + conserva las claves (array_merge renumeraria los ids de eje)
                }
            }

            $combos = $nuevo;
        }

        $viejas = [];

        foreach ($db->setQuery('SELECT id, etiqueta, stock, sku FROM #__hmbshop_variantes WHERE producto_id = ' . $productoId)->loadObjectList() ?: [] as $v) {
            $viejas[$v->etiqueta] = $v;
        }

        $db->setQuery('DELETE FROM #__hmbshop_variante_valores WHERE variante_id IN (SELECT id FROM #__hmbshop_variantes WHERE producto_id = ' . $productoId . ')')->execute();
        $mantener = [];

        foreach ($combos as $combo) {
            $elegidos = array_filter($combo);
            $etiqueta = implode(' / ', array_map(fn($v) => $v->valor, $elegidos));
            $recargo  = array_sum(array_map(fn($v) => (float) $v->recargo, $elegidos));

            if (isset($viejas[$etiqueta])) {
                $vid = (int) $viejas[$etiqueta]->id;
                $db->setQuery('UPDATE #__hmbshop_variantes SET recargo = ' . $recargo . ', published = 1 WHERE id = ' . $vid)->execute();
            } else {
                self::insertar('#__hmbshop_variantes', ['producto_id' => $productoId, 'etiqueta' => $etiqueta, 'stock' => 0, 'recargo' => $recargo, 'published' => 1]);
                $vid = (int) $db->insertid();
            }

            $mantener[] = $vid;

            foreach ($elegidos as $eje => $v) {
                self::insertar('#__hmbshop_variante_valores', ['variante_id' => $vid, 'eje_id' => (int) $eje, 'valor_id' => (int) $v->id]);
            }
        }

        // variantes que ya no corresponden a ninguna combinacion
        $sobran = array_diff(array_map(fn($v) => (int) $v->id, $viejas), $mantener);

        if ($sobran) {
            $db->setQuery('DELETE FROM #__hmbshop_variantes WHERE id IN (' . implode(',', $sobran) . ')')->execute();
        }

        self::asegurarVariante($productoId);
    }

    public static function guardarStock(int $varianteId, int $stock, bool $publicada = true): void
    {
        $db = self::db();
        $db->setQuery('UPDATE #__hmbshop_variantes SET stock = ' . $stock . ', published = ' . ($publicada ? 1 : 0) . ' WHERE id = ' . $varianteId)->execute();
    }

    // ---------------------------------------------------------------- fotos

    public static function subirFoto(int $productoId, array $archivo): string
    {
        if (empty($archivo['tmp_name']) || (int) ($archivo['error'] ?? 1) !== UPLOAD_ERR_OK) {
            return 'No se pudo subir el fichero.';
        }

        if ((int) $archivo['size'] > 8 * 1024 * 1024) {
            return 'La foto supera 8 MB.';
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
        $ext  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;

        if (!$ext) {
            return 'Solo JPG, PNG o WebP.';
        }

        $dir = JPATH_ROOT . '/images/tienda/productos/' . $productoId;

        if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
            return 'No se pudo crear la carpeta de fotos.';
        }

        $nombre = bin2hex(random_bytes(6)) . '.' . $ext;

        if (!move_uploaded_file($archivo['tmp_name'], $dir . '/' . $nombre)) {
            return 'No se pudo guardar la foto.';
        }

        $db   = self::db();
        $n    = (int) $db->setQuery('SELECT COUNT(*) FROM #__hmbshop_fotos WHERE producto_id = ' . $productoId)->loadResult();
        self::insertar('#__hmbshop_fotos', ['producto_id' => $productoId, 'fichero' => 'images/tienda/productos/' . $productoId . '/' . $nombre, 'principal' => $n ? 0 : 1, 'ordering' => $n]);

        return '';
    }

    public static function fotoPrincipal(int $fotoId): void
    {
        $db  = self::db();
        $pid = (int) $db->setQuery('SELECT producto_id FROM #__hmbshop_fotos WHERE id = ' . $fotoId)->loadResult();

        if ($pid) {
            $db->setQuery('UPDATE #__hmbshop_fotos SET principal = (id = ' . $fotoId . ') WHERE producto_id = ' . $pid)->execute();
        }
    }

    public static function borrarFoto(int $fotoId): void
    {
        $db = self::db();
        $f  = $db->setQuery('SELECT * FROM #__hmbshop_fotos WHERE id = ' . $fotoId)->loadObject();

        if ($f) {
            self::borrarFichero($f->fichero);
            $db->setQuery('DELETE FROM #__hmbshop_fotos WHERE id = ' . $fotoId)->execute();
        }
    }

    /** Solo borra ficheros dentro de images/tienda (no se puede salir de ahi con un nombre manipulado). */
    private static function borrarFichero(string $rel): void
    {
        $base = realpath(JPATH_ROOT . '/images/tienda');
        $ruta = realpath(JPATH_ROOT . '/' . ltrim($rel, '/'));

        if ($base && $ruta && strpos($ruta, $base . DIRECTORY_SEPARATOR) === 0 && is_file($ruta)) {
            @unlink($ruta);
        }
    }

    // ---------------------------------------------------------------- envios

    public static function envios(bool $soloPublicados = false): array
    {
        $db = self::db();
        $q  = $db->getQuery(true)->select('*')->from('#__hmbshop_envios')->order('ordering, id');

        if ($soloPublicados) {
            $q->where('published = 1');
        }

        return $db->setQuery($q)->loadObjectList() ?: [];
    }

    public static function envio(int $id): ?object
    {
        return self::db()->setQuery('SELECT * FROM #__hmbshop_envios WHERE id = ' . $id)->loadObject() ?: null;
    }

    public static function guardarEnvio(array $d): int
    {
        $db   = self::db();
        $id   = (int) ($d['id'] ?? 0);
        $fila = (object) [
            'nombre'         => trim((string) ($d['nombre'] ?? '')),
            'descripcion'    => trim((string) ($d['descripcion'] ?? '')),
            'coste'          => max(0.0, (float) str_replace(',', '.', (string) ($d['coste'] ?? '0'))),
            'pide_direccion' => !empty($d['pide_direccion']) ? 1 : 0,
            'published'      => !empty($d['published']) ? 1 : 0,
            'ordering'       => (int) ($d['ordering'] ?? 0),
        ];

        if ($fila->nombre === '') {
            throw new \RuntimeException('La forma de envío necesita un nombre.');
        }

        if ($id) {
            $fila->id = $id;
            $db->updateObject('#__hmbshop_envios', $fila, 'id');

            return $id;
        }

        $db->insertObject('#__hmbshop_envios', $fila);

        return (int) $db->insertid();
    }

    public static function borrarEnvio(int $id): void
    {
        self::db()->setQuery('DELETE FROM #__hmbshop_envios WHERE id = ' . $id)->execute();
    }

    /**
     * Variante publicada del producto que casa con los valores elegidos (eje_id => valor_id).
     * Los ejes obligatorios deben venir elegidos; los opcionales pueden quedar "sin elegir".
     */
    public static function varianteDe(int $productoId, array $elegidos): ?int
    {
        $db   = self::db();
        $ejes = $db->setQuery('SELECT id, obligatorio FROM #__hmbshop_ejes WHERE producto_id = ' . $productoId . ' AND id IN (SELECT eje_id FROM #__hmbshop_eje_valores)')->loadObjectList() ?: [];

        if (!$ejes) {
            return (int) $db->setQuery('SELECT id FROM #__hmbshop_variantes WHERE producto_id = ' . $productoId . ' AND published = 1 ORDER BY id LIMIT 1')->loadResult() ?: null;
        }

        $cond = [];

        foreach ($ejes as $e) {
            $v = (int) ($elegidos[(int) $e->id] ?? 0);

            if (!$v) {
                if ((int) $e->obligatorio) {
                    return null;
                }

                continue;
            }

            $cond[] = '(vv.eje_id = ' . (int) $e->id . ' AND vv.valor_id = ' . $v . ')';
        }

        $n = count($cond);
        $propias = '(SELECT COUNT(*) FROM #__hmbshop_variante_valores x WHERE x.variante_id = v.id)';

        if (!$n) {
            $q = 'SELECT v.id FROM #__hmbshop_variantes v WHERE v.producto_id = ' . $productoId . ' AND v.published = 1 AND ' . $propias . ' = 0 LIMIT 1';
        } else {
            $q = 'SELECT v.id FROM #__hmbshop_variantes v JOIN #__hmbshop_variante_valores vv ON vv.variante_id = v.id'
                . ' WHERE v.producto_id = ' . $productoId . ' AND v.published = 1 AND (' . implode(' OR ', $cond) . ')'
                . ' GROUP BY v.id HAVING COUNT(*) = ' . $n . ' AND ' . $propias . ' = ' . $n . ' LIMIT 1';
        }

        return (int) $db->setQuery($q)->loadResult() ?: null;
    }

    /**
     * Miniatura de una foto (s = 140 px, m = 360 px, l = 720 px de lado mayor), creada la primera vez que
     * se pide y guardada en images/tienda/thumbs. Si no se puede crear, devuelve la foto original.
     */
    public static function miniatura(string $rel, string $tam = 'm'): string
    {
        $lados = ['s' => 140, 'm' => 360, 'l' => 720];
        $lado  = $lados[$tam] ?? 360;
        $orig  = JPATH_ROOT . '/' . ltrim($rel, '/');

        if (!is_file($orig) || !function_exists('imagecreatetruecolor')) {
            return $rel;
        }

        $rel_t = 'images/tienda/thumbs/' . $tam . '_' . md5($rel) . '_' . preg_replace('#[^A-Za-z0-9._-]#', '_', basename($rel));
        $dest  = JPATH_ROOT . '/' . $rel_t;

        if (is_file($dest) && filemtime($dest) >= filemtime($orig)) {
            return $rel_t;
        }

        $info = @getimagesize($orig);

        if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            return $rel;
        }

        [$w, $h] = $info;

        if (max($w, $h) <= $lado) {
            return $rel;
        }

        $f  = $lado / max($w, $h);
        $nw = max(1, (int) round($w * $f));
        $nh = max(1, (int) round($h * $f));

        $origen = $info[2] === IMAGETYPE_JPEG ? @imagecreatefromjpeg($orig) : ($info[2] === IMAGETYPE_PNG ? @imagecreatefrompng($orig) : @imagecreatefromwebp($orig));

        if (!$origen) {
            return $rel;
        }

        $img = imagecreatetruecolor($nw, $nh);

        if ($info[2] !== IMAGETYPE_JPEG) {
            imagealphablending($img, false);
            imagesavealpha($img, true);
        }

        imagecopyresampled($img, $origen, 0, 0, 0, 0, $nw, $nh, $w, $h);

        if (!is_dir(dirname($dest)) && !@mkdir(dirname($dest), 0755, true)) {
            return $rel;
        }

        $ok = $info[2] === IMAGETYPE_JPEG ? imagejpeg($img, $dest, 85) : ($info[2] === IMAGETYPE_PNG ? imagepng($img, $dest, 6) : imagewebp($img, $dest, 85));

        return $ok ? $rel_t : $rel;
    }

    /** Clave que permite a un invitado ver su pedido sin cuenta. */
    public static function pedidoToken(object $p): string
    {
        return substr(hash_hmac('sha256', $p->id . '|' . $p->email, (string) Factory::getApplication()->get('secret')), 0, 24);
    }

    // ---------------------------------------------------------------- pagos

    /** Metodos de pago activos. Pago en tienda va siempre que este activado; el resto se enciende en Ajustes. */
    public static function metodosPago(): array
    {
        $p = self::params();
        $m = [];

        if ((int) $p->get('pago_recogida', 1)) {
            $m['recogida'] = ['Pago en tienda (al recoger)', (string) $p->get('texto_recogida', 'Pagas al recoger tu pedido.')];
        }

        if ((int) $p->get('pago_transferencia', 0) && trim((string) $p->get('iban', '')) !== '') {
            $m['transferencia'] = ['Transferencia bancaria', 'Te daremos el IBAN al confirmar y prepararemos el pedido al recibir el pago.'];
        }

        if ((int) $p->get('pago_contrareembolso', 0)) {
            $r = (float) $p->get('recargo_contrareembolso', 0);
            $m['contrareembolso'] = ['Contrareembolso', 'Pagas al recibir el paquete' . ($r > 0 ? ' (recargo ' . self::dinero($r) . ').' : '.')];
        }

        // Stripe y PayPal: la pasarela se conectara aqui cuando haya claves; sin pasarela no se ofrecen.
        return $m;
    }

    // ---------------------------------------------------------------- carrito (sesion)

    public static function carrito(): array
    {
        $c = Factory::getApplication()->getSession()->get('hmbshop.carrito', []);

        return is_array($c) ? $c : [];
    }

    private static function guardarCarrito(array $c): void
    {
        Factory::getApplication()->getSession()->set('hmbshop.carrito', $c);
    }

    public static function carritoAnadir(int $varianteId, int $cantidad): string
    {
        $v = self::variante($varianteId);

        if (!$v || !(int) $v->p_published || !(int) $v->published) {
            return 'Ese producto no está disponible.';
        }

        $c   = self::carrito();
        $nueva = ($c[$varianteId] ?? 0) + max(1, min(20, $cantidad));

        if ((int) self::params()->get('controlar_stock', 0) && $nueva > (int) $v->stock) {
            return 'No hay stock suficiente.';
        }

        $c[$varianteId] = min(20, $nueva);
        self::guardarCarrito($c);

        return '';
    }

    public static function carritoCambiar(int $varianteId, int $cantidad): void
    {
        $c = self::carrito();

        if ($cantidad <= 0) {
            unset($c[$varianteId]);
        } elseif (isset($c[$varianteId])) {
            $c[$varianteId] = min(20, $cantidad);
        }

        self::guardarCarrito($c);
    }

    public static function carritoVaciar(): void
    {
        self::guardarCarrito([]);
    }

    public static function variante(int $id): ?object
    {
        $db = self::db();
        $q  = $db->getQuery(true)
            ->select('v.*, p.nombre AS producto_nombre, p.precio, p.iva_pct, p.published AS p_published, p.alias AS producto_alias, p.id AS producto_id, p.categoria_id')
            ->select('(SELECT fichero FROM #__hmbshop_fotos WHERE producto_id = p.id ORDER BY principal DESC, ordering, id LIMIT 1) AS foto')
            ->from('#__hmbshop_variantes v')
            ->join('INNER', '#__hmbshop_productos p ON p.id = v.producto_id')
            ->where('v.id = ' . $id);

        return $db->setQuery($q)->loadObject() ?: null;
    }

    /** [['Talla', 'L'], ['Color', 'Negra']] de una variante, en el orden de los ejes. */
    public static function atributosVariante(int $varianteId): array
    {
        $db = self::db();
        $q  = 'SELECT e.nombre, ev.valor FROM #__hmbshop_variante_valores vv'
            . ' JOIN #__hmbshop_ejes e ON e.id = vv.eje_id JOIN #__hmbshop_eje_valores ev ON ev.id = vv.valor_id'
            . ' WHERE vv.variante_id = ' . $varianteId . ' ORDER BY e.ordering, e.id';

        return array_map(fn($r) => [$r->nombre, $r->valor], $db->setQuery($q)->loadObjectList() ?: []);
    }

    /** Total de unidades del carrito (para el modulo lateral). */
    public static function unidadesCarrito(): int
    {
        return (int) array_sum(self::carrito());
    }

    /** Lineas del carrito con precios al dia + totales. Quita lo que ya no existe. */
    public static function detalleCarrito(): array
    {
        $lineas = [];
        $total  = 0.0;
        $iva    = 0.0;
        $c      = self::carrito();

        foreach ($c as $vid => $cant) {
            $v = self::variante((int) $vid);

            if (!$v || !(int) $v->p_published || !(int) $v->published) {
                unset($c[$vid]);
                continue;
            }

            $unit = round((float) $v->precio + (float) $v->recargo, 2);
            $sub  = round($unit * $cant, 2);
            $lineas[] = (object) [
                'variante_id' => (int) $vid, 'producto_id' => (int) $v->producto_id, 'producto_alias' => $v->producto_alias, 'categoria_id' => (int) $v->categoria_id,
                'nombre' => $v->producto_nombre, 'etiqueta' => $v->etiqueta, 'foto' => $v->foto,
                'atributos' => self::atributosVariante((int) $vid), 'cantidad' => (int) $cant, 'unitario' => $unit, 'subtotal' => $sub, 'iva_pct' => (float) $v->iva_pct, 'stock' => (int) $v->stock,
            ];
            $total += $sub;
            $iva   += $sub * (float) $v->iva_pct / (100 + (float) $v->iva_pct);
        }

        if (count($c) !== count(self::carrito())) {
            self::guardarCarrito($c);
        }

        return ['lineas' => $lineas, 'total' => round($total, 2), 'iva' => round($iva, 2)];
    }

    // ---------------------------------------------------------------- pedidos

    /** @return array [bool ok, int|string idPedido o mensaje] */
    public static function crearPedido(array $cliente, int $envioId, string $metodo): array
    {
        $db      = self::db();
        $detalle = self::detalleCarrito();

        if (Factory::getApplication()->getIdentity()->guest) {
            return [false, 'Inicia sesión o crea una cuenta para finalizar la compra.'];
        }

        if (!$detalle['lineas']) {
            return [false, 'El carrito está vacío.'];
        }

        $metodos = self::metodosPago();

        if (!isset($metodos[$metodo])) {
            return [false, 'Elige una forma de pago válida.'];
        }

        $nombre = trim((string) ($cliente['nombre'] ?? ''));
        $email  = trim((string) ($cliente['email'] ?? ''));

        if ($nombre === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [false, 'Escribe tu nombre y un correo válido.'];
        }

        $envio = $envioId ? self::envio($envioId) : null;

        if ($envio && !(int) $envio->published) {
            $envio = null;
        }

        if (!$envio) {
            $todos = self::envios(true);
            $envio = $todos[0] ?? null;
        }

        $coste = $envio ? (float) $envio->coste : 0.0;

        if ($metodo === 'contrareembolso') {
            $coste += (float) self::params()->get('recargo_contrareembolso', 0);
        }

        if ($envio && (int) $envio->pide_direccion && (trim((string) ($cliente['direccion'] ?? '')) === '' || trim((string) ($cliente['ciudad'] ?? '')) === '')) {
            return [false, 'Para el envío necesitamos dirección y ciudad.'];
        }

        $controlar = (int) self::params()->get('controlar_stock', 0);
        $db->transactionStart();

        try {
            if ($controlar) {
                foreach ($detalle['lineas'] as $l) {
                    $db->setQuery('UPDATE #__hmbshop_variantes SET stock = stock - ' . (int) $l->cantidad . ' WHERE id = ' . (int) $l->variante_id . ' AND stock >= ' . (int) $l->cantidad)->execute();

                    if ($db->getAffectedRows() !== 1) {
                        throw new \RuntimeException('Sin stock suficiente de «' . $l->nombre . ($l->etiqueta ? ' (' . $l->etiqueta . ')' : '') . '».');
                    }
                }
            }

            $user = Factory::getApplication()->getIdentity();
            $total = round($detalle['total'] + $coste, 2);
            self::insertar('#__hmbshop_pedidos', [
                'user_id' => (int) ($user->id ?? 0), 'email' => mb_substr($email, 0, 190), 'nombre' => mb_substr($nombre, 0, 190),
                'telefono' => mb_substr(trim((string) ($cliente['telefono'] ?? '')), 0, 30) ?: null,
                'envio_id' => $envio ? (int) $envio->id : null, 'envio_nombre' => $envio ? $envio->nombre : null, 'envio_coste' => $coste,
                'direccion' => mb_substr(trim((string) ($cliente['direccion'] ?? '')), 0, 255) ?: null,
                'ciudad' => mb_substr(trim((string) ($cliente['ciudad'] ?? '')), 0, 100) ?: null,
                'cp' => mb_substr(trim((string) ($cliente['cp'] ?? '')), 0, 12) ?: null,
                'importe_total' => $total, 'iva_total' => $detalle['iva'], 'metodo_pago' => $metodo, 'estado' => 'pendiente',
                'created' => (new Date())->toSql(),
            ]);
            $pid = (int) $db->insertid();

            foreach ($detalle['lineas'] as $l) {
                self::insertar('#__hmbshop_pedido_lineas', [
                    'pedido_id' => $pid, 'variante_id' => $l->variante_id, 'producto_nombre' => $l->nombre, 'etiqueta_variante' => (string) $l->etiqueta,
                    'cantidad' => $l->cantidad, 'precio_unitario' => $l->unitario, 'iva_pct' => $l->iva_pct,
                ]);
            }

            $db->transactionCommit();
        } catch (\Throwable $e) {
            $db->transactionRollback();

            return [false, $e->getMessage()];
        }

        self::carritoVaciar();
        self::avisarPedido($pid);

        return [true, $pid];
    }

    /** El aviso por correo es "lo mejor posible": el pedido ya esta guardado y se ve en el panel. */
    private static function avisarPedido(int $pid): void
    {
        $destino = trim((string) self::params()->get('email_pedidos', ''));
        $p       = self::pedido($pid);

        if (!$p) {
            return;
        }

        $texto = "Pedido #{$pid} de {$p->nombre} ({$p->email})\n";

        foreach ($p->lineas as $l) {
            $texto .= "- {$l->cantidad} x {$l->producto_nombre} {$l->etiqueta_variante}  " . self::dinero((float) $l->precio_unitario) . "\n";
        }

        $texto .= 'Total: ' . self::dinero((float) $p->importe_total) . "\nPago: {$p->metodo_pago}\n";

        foreach (array_filter([$destino, $p->email]) as $a) {
            try {
                $m = Factory::getContainer()->get(\Joomla\CMS\Mail\MailerFactoryInterface::class)->createMailer();
                $m->addRecipient($a);
                $m->setSubject('Pedido #' . $pid . ' - Heavy Metal Brothers');
                $m->setBody($texto);
                $m->Send();
            } catch (\Throwable $e) {
                // sin correo: el pedido sigue en el panel
            }
        }
    }

    public static function pedidos(string $estado = ''): array
    {
        $db = self::db();
        $q  = $db->getQuery(true)->select('*')->from('#__hmbshop_pedidos')->order('id DESC');

        if ($estado !== '' && isset(self::ESTADOS[$estado])) {
            $q->where('estado = ' . $db->quote($estado));
        }

        return $db->setQuery($q, 0, 300)->loadObjectList() ?: [];
    }

    public static function pedido(int $id): ?object
    {
        $db = self::db();
        $p  = $db->setQuery('SELECT * FROM #__hmbshop_pedidos WHERE id = ' . $id)->loadObject();

        if (!$p) {
            return null;
        }

        $p->lineas = $db->setQuery('SELECT * FROM #__hmbshop_pedido_lineas WHERE pedido_id = ' . $id . ' ORDER BY id')->loadObjectList() ?: [];

        return $p;
    }

    public static function pedidosDeUsuario(int $userId): array
    {
        return $userId ? self::db()->setQuery('SELECT * FROM #__hmbshop_pedidos WHERE user_id = ' . $userId . ' ORDER BY id DESC', 0, 100)->loadObjectList() ?: [] : [];
    }

    public static function cambiarEstado(int $id, string $estado): void
    {
        if (!isset(self::ESTADOS[$estado])) {
            return;
        }

        $db  = self::db();
        $ped = self::pedido($id);

        if (!$ped || $ped->estado === $estado) {
            return;
        }

        // al cancelar se devuelve el stock; al reabrir un cancelado se vuelve a descontar
        if ((int) self::params()->get('controlar_stock', 0)) {
            if ($estado === 'cancelado') {
                foreach ($ped->lineas as $l) {
                    $db->setQuery('UPDATE #__hmbshop_variantes SET stock = stock + ' . (int) $l->cantidad . ' WHERE id = ' . (int) $l->variante_id)->execute();
                }
            } elseif ($ped->estado === 'cancelado') {
                foreach ($ped->lineas as $l) {
                    $db->setQuery('UPDATE #__hmbshop_variantes SET stock = GREATEST(0, stock - ' . (int) $l->cantidad . ') WHERE id = ' . (int) $l->variante_id)->execute();
                }
            }
        }

        $db->setQuery('UPDATE #__hmbshop_pedidos SET estado = ' . $db->quote($estado) . ', actualizado = ' . $db->quote((new Date())->toSql()) . ' WHERE id = ' . $id)->execute();
    }
}
