<?php
namespace Hmb\Plugin\System\HmbPwa\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\SubscriberInterface;

final class HmbPwa extends CMSPlugin implements SubscriberInterface
{
	protected $autoloadLanguage = true;

	public static function getSubscribedEvents(): array
	{
		return ['onBeforeCompileHead' => 'injectPwa'];
	}

	public function injectPwa(): void
	{
		$app = $this->getApplication();

		if (!$app || !$app->isClient('site')) {
			return;
		}

		$doc = $app->getDocument();

		if (!$doc instanceof HtmlDocument) {
			return;
		}

		$base  = rtrim(Uri::root(true), '/');           // '' at domain root, '/sub' in a subfolder
		$media = $base . '/media/plg_system_hmb_pwa';
		$title = htmlspecialchars((string) $this->params->get('app_title', 'Metal Quiz'), ENT_QUOTES, 'UTF-8');
		$theme = htmlspecialchars((string) $this->params->get('theme_color', '#c1121f'), ENT_QUOTES, 'UTF-8');

		$doc->addCustomTag('<link rel="manifest" href="' . $media . '/manifest.webmanifest">');
		$doc->addCustomTag('<meta name="theme-color" content="' . $theme . '">');
		$doc->addCustomTag('<link rel="apple-touch-icon" href="' . $media . '/icons/icon-192.png">');
		$doc->addCustomTag('<meta name="mobile-web-app-capable" content="yes">');
		$doc->addCustomTag('<meta name="apple-mobile-web-app-capable" content="yes">');
		$doc->addCustomTag('<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">');
		$doc->addCustomTag('<meta name="apple-mobile-web-app-title" content="' . $title . '">');

		$sw = $base . '/sw.js';
		$doc->addScriptDeclaration(
			"if('serviceWorker' in navigator){window.addEventListener('load',function(){"
			. "navigator.serviceWorker.register('" . $sw . "').catch(function(){});"
			. "});}"
		);
	}
}
