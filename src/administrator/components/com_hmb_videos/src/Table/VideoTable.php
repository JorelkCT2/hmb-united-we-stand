<?php
namespace Joomla\Component\HmbVideos\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;

class VideoTable extends Table
{
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__hmb_videos', 'id', $db);
        $this->_columnAlias = ['published' => 'state'];
    }

    public function check(): bool
    {
        if (!parent::check()) return false;

        if (empty($this->alias)) {
            $this->alias = \Joomla\CMS\Application\ApplicationHelper::stringURLSafe($this->title);
        }
        if (empty(trim(str_replace('-', '', $this->alias)))) {
            $this->alias = \Joomla\CMS\Factory::getDate()->format('Y-m-d-H-i-s');
        }

        if (!empty($this->youtube_id)) {
            if (str_contains($this->youtube_id, 'youtube.com')) {
                parse_str(parse_url($this->youtube_id, PHP_URL_QUERY), $p);
                if (!empty($p['v'])) $this->youtube_id = $p['v'];
            } elseif (str_contains($this->youtube_id, 'youtu.be')) {
                $this->youtube_id = basename(parse_url($this->youtube_id, PHP_URL_PATH));
            }
        }

        $now = \Joomla\CMS\Factory::getDate()->toSql();
        if (empty($this->created))  $this->created  = $now;
        if (empty($this->modified)) $this->modified = $now;

        return true;
    }
}
