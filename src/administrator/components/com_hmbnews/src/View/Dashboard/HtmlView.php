<?php
namespace Joomla\Component\Hmbnews\Administrator\View\Dashboard;
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

class HtmlView extends BaseHtmlView
{
    public $params;
    public $token;
    public $apiKey;
    public $catId;
    public $num;
    public $ajaxBase;

    public function display($tpl = null)
    {
        $app  = Factory::getApplication();
        $doc  = $app->getDocument();
        $root = Uri::root(true);

        $doc->addStyleSheet($root . '/administrator/components/com_hmbnews/css/admin.css?v=200');
        $doc->addScript($root . '/administrator/components/com_hmbnews/js/admin.js?v=201');

        // Leer params DIRECTAMENTE de BD (saltarse el cache de ComponentHelper)
        $db = Factory::getDbo();
        $raw = $db->setQuery("SELECT params FROM `#__extensions` WHERE element='com_hmbnews' AND type='component'")->loadResult();
        $p = json_decode($raw ?? '{}', true);
        if (!is_array($p)) $p = [];
        if (isset($p['params']) && is_array($p['params'])) $p = $p['params']; // sin doble anidamiento
        $this->params = new Registry($p);

        $this->token    = Session::getFormToken();
        $this->apiKey   = (string)($p['api_key'] ?? '');
        $this->catId    = (int)($p['default_category'] ?? 21);
        $this->num      = (int)($p['articles_per_run'] ?? 2);
        $this->ajaxBase = 'index.php?option=com_hmbnews&format=json&' . $this->token . '=1&task=ajax.';

        ToolbarHelper::title('HMB Importador de Noticias', 'newspaper');
        ToolbarHelper::preferences('com_hmbnews');

        return parent::display($tpl);
    }
}
