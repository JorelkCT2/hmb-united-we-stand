<?php
namespace HeavyMetalBrothers\Component\Hmbfiles\Administrator\View\Explorer;

defined('_JEXEC') or die;

use HeavyMetalBrothers\Component\Hmbfiles\Administrator\Helper\FsHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

class HtmlView extends BaseHtmlView
{
    public string $token = '';
    public string $ajax = '';
    public array $config = [];

    public function display($tpl = null)
    {
        $doc  = Factory::getApplication()->getDocument();
        $root = Uri::root(true) . '/administrator/components/com_hmbfiles';

        $doc->addStyleSheet($root . '/css/files.css?v=101');
        $doc->addScript($root . '/js/files.js?v=101', [], ['defer' => true]);

        $this->token = Session::getFormToken();
        $this->ajax  = 'index.php?option=com_hmbfiles&format=json&task=ajax.';
        $this->config = [
            'raices'    => FsHelper::RAICES,
            'tipos'     => FsHelper::TIPOS,
            'extensiones' => FsHelper::extensionesPermitidas(),
        ];

        ToolbarHelper::title('HMB Archivos (images / media)', 'images');

        return parent::display($tpl);
    }
}
