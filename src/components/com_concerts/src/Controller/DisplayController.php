<?php
namespace Joomla\Component\Concerts\Site\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
    protected $default_view = 'concerts';

    public function ics(): void
    {
        $id   = (int)$this->input->get('id', 0);
        $db   = Factory::getContainer()->get('DatabaseDriver');
        $item = $db->setQuery(
            $db->getQuery(true)->select('*')->from('#__concerts')->where('id='.$id)->where('state=1')
        )->loadObject();
        if (!$item) return;

        $dt = fn($d,$t) => str_replace('-','',$d).($t?'T'.str_replace(':','',substr($t,0,5)).'00':'');
        $ds = $dt($item->date_start, $item->concert_time);
        $de = (!empty($item->date_end)&&$item->date_end!==$item->date_start)?$dt($item->date_end,'23:59'):$ds;
        $e  = fn($s) => addcslashes(strip_tags($s??''),',;\\');

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="concierto-'.$item->id.'.ics"');
        echo "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//com_concerts//ES\r\nMETHOD:PUBLISH\r\n"
           . "BEGIN:VEVENT\r\nUID:concert-{$item->id}@site\r\nDTSTAMP:".gmdate('Ymd\THis\Z')."\r\n"
           . "DTSTART:{$ds}\r\nDTEND:{$de}\r\n"
           . "SUMMARY:".$e($item->band.($item->city?" — {$item->city}":''))
           . "\r\nLOCATION:".$e(implode(', ',array_filter([$item->venue,$item->city,$item->country_name])))
           . "\r\nDESCRIPTION:".$e($item->description)
           . "\r\nEND:VEVENT\r\nEND:VCALENDAR\r\n";
        Factory::getApplication()->close();
    }
}
