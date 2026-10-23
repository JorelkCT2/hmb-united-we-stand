<?php
/**
 * @package     com_metalmap
 * @copyright   (C) 2026 Heavy Metal Brothers
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Component\Metalmap\Site\View\Map;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

final class HtmlView extends BaseHtmlView
{
    public array $events = [];
    public $params;
    public array $countryList = [];

    public function display($tpl = null): void
    {
        /** @var \Joomla\Component\Metalmap\Site\Model\MapModel $model */
        $model        = $this->getModel();
        $this->events = $model->getMapData();
        $this->params = ComponentHelper::getParams('com_metalmap');

        // Lista única de países para el filtro lateral
        $countries = [];
        foreach ($this->events as $e) {
            if (!empty($e['country_code']) && !isset($countries[$e['country_code']])) {
                $countries[$e['country_code']] = $e['country'];
            }
        }
        asort($countries);
        $this->countryList = $countries;

        parent::display($tpl);
    }
}
