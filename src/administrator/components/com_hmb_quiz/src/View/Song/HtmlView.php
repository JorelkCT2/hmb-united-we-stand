<?php
namespace Hmb\Component\Quiz\Administrator\View\Song;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView
{
	protected $form;
	protected $item;

	public function display($tpl = null)
	{
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');

		if (\count($errors = $this->get('Errors'))) {
			throw new \Exception(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		parent::display($tpl);
	}

	protected function addToolbar()
	{
		$isNew = empty($this->item->id);

		ToolbarHelper::title(
			Text::_($isNew ? 'COM_HMB_QUIZ_SONG_NEW' : 'COM_HMB_QUIZ_SONG_EDIT'),
			'music'
		);

		ToolbarHelper::apply('song.apply');
		ToolbarHelper::save('song.save');
		ToolbarHelper::save2new('song.save2new');
		ToolbarHelper::custom('song.saveCopyBand', 'copy', 'copy', 'COM_HMB_QUIZ_SAVE_COPY_BAND', false);

		if (!$isNew) {
			ToolbarHelper::divider();
			ToolbarHelper::custom('song.savePrev', 'arrow-left', 'arrow-left', 'COM_HMB_QUIZ_SAVE_PREV', false);
			ToolbarHelper::custom('song.saveNext', 'arrow-right', 'arrow-right', 'COM_HMB_QUIZ_SAVE_NEXT', false);
		}

		ToolbarHelper::cancel('song.cancel', $isNew ? 'JTOOLBAR_CANCEL' : 'JTOOLBAR_CLOSE');
	}
}
