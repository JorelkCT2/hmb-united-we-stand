<?php
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class ModFotosNoticiaHelper
{
    public static function getImages($basefolder, $maximages)
    {
        $app = Factory::getApplication();
        $input = $app->input;
        $id = $input->getInt('id');

        if (!$id) {
            return [];
        }

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true)
            ->select($db->quoteName('publish_up'))
            ->from($db->quoteName('#__content'))
            ->where($db->quoteName('id') . ' = ' . (int) $id);

        $db->setQuery($query);
        $date = $db->loadResult();

        if (!$date) {
            return [];
        }

        $timestamp = strtotime($date);
        $year  = date('Y', $timestamp);
        $month = date('m', $timestamp);
        $day   = date('d', $timestamp);

        $folder = $basefolder . '/' . $year . '/' . $month . '/' . $day;
        $path = JPATH_ROOT . '/images/' . $folder;

        if (!is_dir($path)) {
            return [];
        }

        $files = scandir($path);
        $images = [];

        foreach ($files as $file) {
            if (preg_match('/\.(jpg|jpeg|png|webp)$/i', $file)) {
                $images[] = '/images/' . $folder . '/' . $file;
            }
        }

        sort($images);

        return array_slice($images, 0, $maximages);
    }
}
