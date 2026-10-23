<?php
namespace Hmb\Component\Quiz\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

class GenrechoiceField extends ListField
{
	protected $type = 'Genrechoice';

	protected function getOptions()
	{
		$params = ComponentHelper::getParams('com_hmb_quiz');
		$raw    = (string) $params->get('genres_list', '');
		$lines  = preg_split('/\r\n|\r|\n/', $raw);

		$multiple = \in_array((string) $this->element['multiple'], ['true', 'multiple', '1'], true);
		$options  = [];

		if (!$multiple) {
			$options[] = HTMLHelper::_('select.option', '', '– ' . Text::_('COM_HMB_QUIZ_SELECT_GENRE') . ' –');
		}

		$seen = [];

		foreach ($lines as $line) {
			$line = trim($line);

			if ($line === '' || isset($seen[mb_strtolower($line)])) {
				continue;
			}

			$seen[mb_strtolower($line)] = true;
			$options[] = HTMLHelper::_('select.option', $line, $line);
		}

		return array_merge($options, parent::getOptions());
	}
}
