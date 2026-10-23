<?php
namespace HeavyMetalBrothers\Module\HmbTemas\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

class HmbTemasHelper
{
    public static function getTemas(\Joomla\Registry\Registry $params): array
    {
        $db = Factory::getContainer()->get('DatabaseDriver');

        $ordenRaw = $params->get('orden', 'album_year ASC,track_number ASC');

        // Whitelist de órdenes permitidos
        $ordenesOk = [
            'album_year ASC,track_number ASC',
            'album_year DESC,track_number ASC',
            'title ASC',
        ];
        if (!in_array($ordenRaw, $ordenesOk, true)) {
            $ordenRaw = 'album_year ASC,track_number ASC';
        }

        $query = $db->getQuery(true)
            ->select([
                'id','title','album','album_year','track_number',
                'duration','cover_image','audio_file',
                'spotify_url','youtube_url','description',
            ])
            ->from($db->quoteName('#__hmb_temas'))
            ->where($db->quoteName('published') . ' = 1')
            ->order($ordenRaw);

        $db->setQuery($query);

        try {
            $items = $db->loadObjectList();
        } catch (\Exception $e) {
            return [];
        }

        $siteRoot = Uri::root();

        foreach ($items as $item) {
            // Duración segundos → m:ss
            if (is_numeric($item->duration) && $item->duration > 0) {
                $s = (int) $item->duration;
                $item->duration_fmt = sprintf('%d:%02d', intdiv($s, 60), $s % 60);
            } else {
                $item->duration_fmt = $item->duration ?: '--:--';
            }

            // URL del audio
            $item->audio_url = '';
            if ($item->audio_file) {
                $item->audio_url = str_starts_with($item->audio_file, 'http')
                    ? $item->audio_file
                    : $siteRoot . implode('/', array_map('rawurlencode', explode('/', $item->audio_file)));
            }

            // URL de la portada
            $item->cover_url = '';
            if ($item->cover_image) {
                $item->cover_url = str_starts_with($item->cover_image, 'http')
                    ? $item->cover_image
                    : $siteRoot . implode('/', array_map('rawurlencode', explode('/', $item->cover_image)));
            }
        }

        return $items;
    }

    public static function getAlbums(array $temas): array
    {
        $albums = [];
        foreach ($temas as $t) {
            if ($t->album && !in_array($t->album, $albums, true)) {
                $albums[] = $t->album;
            }
        }
        return $albums;
    }
}
