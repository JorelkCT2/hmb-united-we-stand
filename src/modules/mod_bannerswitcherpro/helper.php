<?php
defined('_JEXEC') or die;

class ModBannerSwitcherProHelper
{
    public static function getSlides($params)
    {
        $slides      = [];
        $globalTarget = $params->get('link_target', '_self');

        for ($i = 1; $i <= 10; $i++) {

            $desktop = $params->get("desktop$i");
            $mobile  = $params->get("mobile$i");
            $link    = $params->get("link$i", '');
            $target  = $params->get("target$i", $globalTarget);

            // Normalizar: objeto MediaField → string path
            if (is_object($desktop) && isset($desktop->path)) {
                $desktop = $desktop->path;
            }
            if (is_object($mobile) && isset($mobile->path)) {
                $mobile = $mobile->path;
            }

            $desktop = is_string($desktop) ? trim($desktop) : '';
            $mobile  = is_string($mobile)  ? trim($mobile)  : '';
            $link    = is_string($link)    ? trim($link)    : '';
            $target  = in_array($target, ['_self', '_blank']) ? $target : '_self';

            if ($desktop !== '' || $mobile !== '') {
                $slides[] = [
                    'desktop' => $desktop,
                    'mobile'  => $mobile,
                    'link'    => $link,
                    'target'  => $target,
                ];
            }
        }

        return $slides;
    }
}
