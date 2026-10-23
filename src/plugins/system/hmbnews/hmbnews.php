<?php
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

class PlgSystemHmbnews extends CMSPlugin
{
    private bool $isHmb    = false;
    private string $hmbHtml = '';

    // ── Detectar petición com_hmb y manejar AJAX ─────────────────────────────
    public function onAfterRoute(): void
    {
        $app = Factory::getApplication();
        if (!$app->isClient('administrator')) return;

        $option = $app->input->getString('option', '');
        if ($option !== 'com_hmb') return;

        $fmt  = $app->input->getString('format', 'html');
        $task = $app->input->getString('task', '');

        // ── AJAX ────────────────────────────────────────────────────────────
        if ($fmt === 'json' || strpos($task, 'ajax.') === 0) {
            header('Content-Type: application/json; charset=utf-8');
            $method    = str_replace('ajax.', '', $task);
            $readOnly  = ['getSources', 'getArticles'];

            if (!in_array($method, $readOnly, true)) {
                $ok = false;
                try { $ok = Session::checkToken('post') || Session::checkToken('get'); } catch(\Throwable $e) {}
                if (!$ok) {
                    echo json_encode(['success'=>false,'message'=>'Token invalido'], JSON_UNESCAPED_UNICODE);
                    $app->close();
                    return;
                }
            }

            $helper = __DIR__ . '/assets/ajax.php';
            if (is_file($helper)) {
                require_once $helper;
                $h = new HmbAjaxHelper();
                if (method_exists($h, $method)) $h->$method();
                else echo json_encode(['success'=>false,'message'=>'Metodo desconocido: '.htmlspecialchars($method)], JSON_UNESCAPED_UNICODE);
            } else {
                echo json_encode(['success'=>false,'message'=>'Helper no encontrado'], JSON_UNESCAPED_UNICODE);
            }
            $app->close();
            return;
        }

        // ── HTML: preparar ──────────────────────────────────────────────────
        $this->isHmb = true;

        try {
            $doc  = $app->getDocument();
            $root = Uri::root(true);
            $doc->addStyleSheet($root . '/plugins/system/hmbnews/assets/admin.css?v=100');
            $doc->addScript($root . '/plugins/system/hmbnews/assets/admin.js?v=100');
            ToolbarHelper::title('HMB Importador de Noticias', 'newspaper');
            ToolbarHelper::preferences('com_hmb');
        } catch(\Throwable $e) {}

        // Renderizar dashboard ahora (variables en scope)
        $hmbParams = null;
        try { $hmbParams = ComponentHelper::getParams('com_hmb'); } catch(\Throwable $e) {}
        $hmbToken  = '';
        try { $hmbToken = Session::getFormToken(); } catch(\Throwable $e) {}
        $hmbApiKey = $hmbParams ? $hmbParams->get('api_key', '')               : '';
        $hmbCatId  = $hmbParams ? (int)$hmbParams->get('default_category', 21) : 21;
        $hmbNum    = $hmbParams ? (int)$hmbParams->get('articles_per_run', 5)   : 5;
        $hmbAjax   = 'index.php?option=com_hmb&format=json&' . $hmbToken . '=1&task=ajax.';
        $tmpl      = __DIR__ . '/assets/dashboard.php';

        ob_start();
        if (is_file($tmpl)) {
            require $tmpl;
        } else {
            echo '<h2 style="color:green;font-family:sans-serif;padding:20px">HMB Plugin activo</h2>';
        }
        $this->hmbHtml = ob_get_clean();
    }

    // ── Inyectar HTML después del dispatch ───────────────────────────────────
    public function onAfterDispatch(): void
    {
        if (!$this->isHmb) return;
        try {
            Factory::getApplication()->getDocument()->setBuffer($this->hmbHtml, 'component');
        } catch(\Throwable $e) {}
    }

    // ── Backup: inyectar justo antes de renderizar ───────────────────────────
    public function onBeforeRender(): void
    {
        if (!$this->isHmb) return;
        try {
            Factory::getApplication()->getDocument()->setBuffer($this->hmbHtml, 'component');
        } catch(\Throwable $e) {}
    }
}
