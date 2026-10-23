<?php
defined('_JEXEC') or die;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Psr\Container\ContainerInterface;

return new class implements ServiceProviderInterface {
    public function register(Container $container): void {
        $container->set(ComponentInterface::class, static function () {
            return new class implements ComponentInterface {
                public function boot(ContainerInterface $c): void {}
                public function dispatch($x = null): void { $this->run(); }
                public function getDispatcher($a = null): object {
                    $s = $this;
                    return new class($s) {
                        private $c;
                        public function __construct($c) { $this->c = $c; }
                        public function dispatch(): void { $this->c->run(); }
                    };
                }
                public function setMVCFactory($f): void {}
                public function getMVCFactory() { return null; }
                public function __call(string $n, array $a) { $this->run(); return null; }

                public function run(): void {
                    static $done = false; if ($done) return; $done = true;
                    $base = JPATH_ADMINISTRATOR.'/components/com_hmbnews';
                    $app  = \Joomla\CMS\Factory::getApplication();
                    $task = $app->input->getString('task','');
                    $fmt  = $app->input->getString('format','html');

                    if ($fmt === 'json' || strpos($task,'ajax.') === 0) {
                        header('Content-Type: application/json; charset=utf-8');
                        $m = str_replace('ajax.','',$task);
                        $read = ['getSources','getArticles'];
                        if (!in_array($m,$read,true)) {
                            $ok=false;
                            try{$ok=\Joomla\CMS\Session\Session::checkToken('post')||\Joomla\CMS\Session\Session::checkToken('get');}catch(\Throwable $e){}
                            if(!$ok){echo json_encode(['success'=>false,'message'=>'Token invalido'],JSON_UNESCAPED_UNICODE);die();}
                        }
                        $h = $base.'/helpers/ajax.php';
                        if (is_file($h)) {
                            require_once $h;
                            $o = new HmbAjaxHelper();
                            if (method_exists($o,$m)) $o->$m();
                            else echo json_encode(['success'=>false,'message'=>'Metodo desconocido'],JSON_UNESCAPED_UNICODE);
                        }
                        die();
                    }

                    try {
                        $doc  = $app->getDocument();
                        $root = \Joomla\CMS\Uri\Uri::root(true);
                        $doc->addStyleSheet($root.'/administrator/components/com_hmbnews/css/admin.css?v=100');
                        $doc->addScript($root.'/administrator/components/com_hmbnews/js/admin.js?v=100');
                        \Joomla\CMS\Toolbar\ToolbarHelper::title('HMB Importador de Noticias','newspaper');
                        \Joomla\CMS\Toolbar\ToolbarHelper::preferences('com_hmbnews');
                    } catch(\Throwable $e) {}

                    $hmbParams = null;
                    try { $hmbParams = \Joomla\CMS\Component\ComponentHelper::getParams('com_hmbnews'); } catch(\Throwable $e) {}
                    $hmbToken = '';
                    try { $hmbToken = \Joomla\CMS\Session\Session::getFormToken(); } catch(\Throwable $e) {}
                    $hmbApiKey = $hmbParams ? $hmbParams->get('api_key','') : '';
                    $hmbCatId  = $hmbParams ? (int)$hmbParams->get('default_category',21) : 21;
                    $hmbNum    = $hmbParams ? (int)$hmbParams->get('articles_per_run',5) : 5;
                    $hmbAjax   = 'index.php?option=com_hmbnews&format=json&'.$hmbToken.'=1&task=ajax.';
                    $tmpl = $base.'/tmpl/dashboard/default.php';
                    if (is_file($tmpl)) require $tmpl;
                    else echo '<h2 style="color:green;font-family:sans-serif;padding:20px">HMB News OK</h2>';
                }
            };
        });
    }
};
