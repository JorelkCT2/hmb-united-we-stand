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

	/** URL absoluta sin el prefijo de subcarpeta duplicado. */
	private static function abs(string $link): string
	{
		$path = Route::_($link, false);

		if (preg_match('#^https?://#', $path)) {
			return $path;
		}

		return rtrim(Uri::root(), '/') . '/' . ltrim(preg_replace('#^' . preg_quote(Uri::root(true), '#') . '#', '', $path), '/');
	}

	public function display($tpl = null)
	{
		$app        = Factory::getApplication();
		$this->slug = $app->getInput()->getString('slug', '');
		$this->tema = TemasHelper::get($this->slug);

		if (!$this->tema) {
			throw new \RuntimeException('Página no encontrada', 404);
		}

		$root  = rtrim(Uri::root(), '/');
		$base  = self::abs('index.php?option=com_hmb_quiz&view=game');
		$canon = self::abs('index.php?option=com_hmb_quiz&view=tema&slug=' . $this->slug);
		$this->jugarUrl = $base;
		$this->efemUrl = Route::_('index.php?option=com_efemerides&view=efemerides');

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

		// Bandas enlazadas: solo las que existen y están publicadas
		$db = Factory::getContainer()->get('DatabaseDriver');

		foreach ($this->tema['bandas'] as $nombre) {
			try {
				$db->setQuery('SELECT id, nombre FROM #__efemerides_bandas WHERE state = 1 AND nombre = ' . $db->quote($nombre) . ' LIMIT 1');
				$b = $db->loadObject();
			} catch (\Throwable $e) {
				$b = null;
			}

			if ($b) {
				$this->bandas[] = ['nombre' => $b->nombre, 'url' => Route::_('index.php?option=com_efemerides&view=banda&id=' . (int) $b->id)];
			}
		}

		// Resto de páginas de entrada
		foreach (TemasHelper::all() as $s => $t) {
			if ($s !== $this->slug) {
				$this->otros[] = ['nombre' => $t['nombre'], 'url' => Route::_('index.php?option=com_hmb_quiz&view=tema&slug=' . $s)];
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
				[
					'@type'           => 'BreadcrumbList',
					'itemListElement' => [
						['@type' => 'ListItem', 'position' => 1, 'name' => 'Inicio', 'item' => $root . '/'],
						['@type' => 'ListItem', 'position' => 2, 'name' => 'Metal Quiz', 'item' => $base],
						['@type' => 'ListItem', 'position' => 3, 'name' => $this->tema['nombre'], 'item' => $canon],
					],
				],
			],
		];
		$doc->addCustomTag('<script type="application/ld+json">' . json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>');

		parent::display($tpl);
	}
}
