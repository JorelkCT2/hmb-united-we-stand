<?php
namespace Joomla\Component\Artistas\Administrator\Controller;
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

class DisplayController extends BaseController
{
    public function display($cachable = false, $urlparams = []): static
    {
        $app  = Factory::getApplication();
        $view = $app->getInput()->getString('view', 'cpanel');

        if ($view === 'artista') {
            $app->getDocument()->addScriptDeclaration('
var _si = setInterval(function(){
    var bp = document.querySelector("select[name=\"jform[banda_id]\"]");
    if (!bp) return;
    if (document.getElementById("chk-solista")) { clearInterval(_si); return; }
    clearInterval(_si);
    var wrap = bp.closest(".control-group") || bp.parentElement;
    var d = document.createElement("div");
    d.className = "form-check form-switch mb-2";
    d.innerHTML = "<input class=\"form-check-input\" type=\"checkbox\" id=\"chk-solista\" name=\"jform[solista]\" value=\"1\" role=\"switch\" onchange=\"var s=document.querySelector(\'select[name=\\\\\"jform[banda_id]\\\\\"]\');s.disabled=this.checked;s.style.opacity=this.checked?\'0.3\':\'1\';\"><label class=\"form-check-label text-light\" for=\"chk-solista\">Solista</label>";
    wrap.parentNode.insertBefore(d, wrap);
}, 300);
            ');
        }

        parent::display($cachable, $urlparams);
        return $this;
    }
}
