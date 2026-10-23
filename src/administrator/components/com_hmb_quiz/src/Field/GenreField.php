<?php
namespace Hmb\Component\Quiz\Administrator\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\FormField;

class GenreField extends FormField
{
	protected $type = 'Genre';

	protected function getInput()
	{
		$params = ComponentHelper::getParams('com_hmb_quiz');
		$raw    = (string) $params->get('genres_list', '');
		$lines  = preg_split('/\r\n|\r|\n/', $raw);

		$clean = [];
		$seen  = [];

		foreach ($lines as $line) {
			$line = trim($line);

			if ($line === '' || isset($seen[mb_strtolower($line)])) {
				continue;
			}

			$seen[mb_strtolower($line)] = true;
			$clean[] = $line;
		}

		// If nothing is configured yet, suggest a sensible default set.
		if (empty($clean)) {
			$clean = [
				'Heavy Metal', 'Thrash Metal', 'Death Metal', 'Black Metal', 'Power Metal',
				'Doom Metal', 'Speed Metal', 'Hard Rock', 'Progressive Metal', 'Folk Metal',
				'Symphonic Metal', 'Metalcore', 'Groove Metal', 'Nu Metal', 'Glam Metal',
			];
		}

		$listId  = $this->id . '_list';
		$options = '';

		foreach ($clean as $line) {
			$options .= '<option value="' . htmlspecialchars($line, ENT_QUOTES, 'UTF-8') . '"></option>';
		}

		$value    = htmlspecialchars((string) $this->value, ENT_QUOTES, 'UTF-8');
		$class    = $this->class ? $this->class : 'form-control';
		$required = $this->required ? ' required' : '';

		$html  = '<input type="text" name="' . $this->name . '" id="' . $this->id . '"'
			. ' value="' . $value . '" list="' . $listId . '" class="' . $class . '"'
			. ' autocomplete="off"' . $required . '>';
		$html .= '<datalist id="' . $listId . '">' . $options . '</datalist>';

		return $html;
	}
}
