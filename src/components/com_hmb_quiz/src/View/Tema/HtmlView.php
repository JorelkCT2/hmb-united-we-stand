<?php
namespace Hmb\Component\Quiz\Site\View\Tema;

\defined('_JEXEC') or die;

use Hmb\Component\Quiz\Site\Helper\TemasHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

class HtmlView extends BaseHtmlView
{
	protected $tema;
	protected $slug;
	protected $bandas = [];
	protected $otros = [];
	protected $jugarUrl = '';
	protected $efemUrl = '';

	/** Ruta amigable de la entrada de menú de com_efemerides con esa vista (misma que usa la canónica de las fichas). */
	private function menuBase($db, string $view): string
	{
		try {
			$db->setQuery('SELECT id FROM #__menu WHERE published = 1 AND client_id = 0 AND access = 1 AND link LIKE ' . $db->quote('index.php?option=com_efemerides&view=' . $view . '%') . ' ORDER BY id LIMIT 1');
			$id = (int) $db->loadResult();
		} catch (\Throwable $e) {
			return '';
		}

		if (!$id) {
			return '';
		}

		$u = html_entity_decode(Route::_('index.php?Itemid=' . $id), ENT_QUOTES | ENT_HTML5);

		return strtok($u, '?');
	}

	public function display($tpl = null)
	{
		$app        = Factory::getApplication();
		$this->slug = $app->getInput()->getString('slug', '');
		$this->tema = TemasHelper::get($this->slug);

		if (!$this->tema) {
			throw new \RuntimeException('Página no encontrada', 404);
		}

		// Ruta de la entrada de menú del Metal Quiz (normalmente «metal-quiz»); las páginas cuelgan de ella
		$active = $app->getMenu()->getActive();
		$route  = ($active && $active->route) ? trim($active->route, '/') : 'metal-quiz';
		$root   = rtrim(Uri::root(), '/');
		$base   = $root . '/' . $route;
		$canon  = $base . '/' . $this->slug;
		$this->jugarUrl = $base;
		$db = Factory::getContainer()->get('DatabaseDriver');
		$efem = $this->menuBase($db, 'efemerides') ?: $this->menuBase($db, 'lista');
		$this->efemUrl = $efem ?: $base;
		$bandasBase = $this->menuBase($db, 'bandas');

		$doc = $this->getDocument();
		$doc->setTitle($this->tema['title'] . ' | Heavy Metal Brothers');
		$doc->setDescription($this->tema['desc']);
		$doc->addHeadLink($canon, 'canonical');
		$doc->setMetaData('og:title', $this->tema['title'], 'property');
		$doc->setMetaData('og:description', $this->tema['desc'], 'property');
		$doc->setMetaData('og:type', 'website', 'property');
		$doc->setMetaData('og:url', $canon, 'property');
		$doc->setMetaData('og:image', $root . '/media/com_hmb_quiz/mq/og.jpg', 'property');
		$doc->setMetaData('twitter:card', 'summary_large_image');

		// Bandas enlazadas: solo las que existen y están publicadas (y solo si hay entrada de menú del archivo de bandas)

		foreach ($this->tema['bandas'] as $nombre) {
			try {
				$db->setQuery('SELECT id, nombre FROM #__efemerides_bandas WHERE state = 1 AND nombre = ' . $db->quote($nombre) . ' LIMIT 1');
				$b = $db->loadObject();
			} catch (\Throwable $e) {
				$b = null;
			}

			if ($b && $bandasBase) {
				$this->bandas[] = ['nombre' => $b->nombre, 'url' => $bandasBase . '?view=banda&id=' . (int) $b->id];
			}
		}

		// Resto de páginas de entrada
		foreach (TemasHelper::all() as $s => $t) {
			if ($s !== $this->slug) {
				$this->otros[] = ['nombre' => $t['nombre'], 'url' => $base . '/' . $s];
			}
		}

		// Datos estructurados: página + migas
		$ld = [
			'@context' => 'https://schema.org',
			'@graph'   => [
				[
					'@type'       => 'WebPage',
					'@id'         => $canon . '#webpage',
					'url'         => $canon,
					'name'        => $this->tema['title'],
					'description' => $this->tema['desc'],
					'inLanguage'  => 'es',
					'isPartOf'    => ['@type' => 'WebSite', 'name' => 'Heavy Metal Brothers', 'url' => $root . '/'],
					'about'       => ['@type' => 'Thing', 'name' => 'Heavy metal'],
				],
			],
		];
		$doc->addCustomTag('<script type="application/ld+json">' . json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>');

		parent::display($tpl);
	}
}
