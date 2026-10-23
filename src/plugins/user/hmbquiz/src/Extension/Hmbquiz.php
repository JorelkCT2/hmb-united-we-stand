<?php
namespace Joomla\Plugin\User\Hmbquiz\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

final class Hmbquiz extends CMSPlugin implements SubscriberInterface
{
	protected $autoloadLanguage = true;

	public static function getSubscribedEvents(): array
	{
		return ['onContentPrepareForm' => 'onContentPrepareForm'];
	}

	public function onContentPrepareForm(Event $event): void
	{
		// Resolve the form across Joomla event API variations.
		$form = null;

		if (method_exists($event, 'getForm')) {
			$form = $event->getForm();
		} else {
			$args = $event->getArguments();
			$form = $args['subject'] ?? ($args[0] ?? null);
		}

		if (!$form instanceof Form) {
			return;
		}

		$allowed = [
			'com_users.registration',
			'com_users.profile',
			'com_users.user',
			'com_admin.profile',
		];

		if (!\in_array($form->getName(), $allowed, true)) {
			return;
		}

		Form::addFormPath(JPATH_PLUGINS . '/user/hmbquiz/forms');
		$form->loadFile('hmbquiz');
	}
}
