<?php
namespace Joomla\Component\Efemerides\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;

class EfemeridController extends FormController
{
    protected $view_list = 'efemerides';

    /**
     * After saving: if categoria = Defunciones, sync fallecidos table.
     */
    protected function postSaveHook(\Joomla\CMS\MVC\Model\BaseDatabaseModel $model, $validData = []): void
    {
        $item = $model->getItem();
        if (!$item || !$item->id) {
            return;
        }

        // Get the name of the category
        $db = $this->app->bootComponent('com_efemerides')
            ? \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver')
            : \Joomla\CMS\Factory::getDbo();

        $db->setQuery(
            'SELECT name FROM #__efemerides_categorias WHERE id = ' . (int)$item->categoria_id
        );
        $catName = strtolower((string)$db->loadResult());

        if (strpos($catName, 'defunci') !== false || strpos($catName, 'falleci') !== false) {
            // Check if already in fallecidos
            $db->setQuery(
                'SELECT id FROM #__efemerides_fallecidos WHERE efemeride_id = ' . (int)$item->id
            );
            $existing = $db->loadResult();

            if (!$existing) {
                $obj = (object)[
                    'efemeride_id'        => $item->id,
                    'artista'             => $item->artista ?? '',
                    'fecha_fallecimiento' => $item->fecha_evento,
                    'state'               => 1,
                    'ordering'            => 0,
                    'created'             => (new \DateTime())->format('Y-m-d H:i:s'),
                    'modified'            => (new \DateTime())->format('Y-m-d H:i:s'),
                ];
                $db->insertObject('#__efemerides_fallecidos', $obj);
            }
        }
    }

    public function delete(&$data = null)
    {
        $q = $this->app->getInput()->getString('q', '');
        parent::delete($data);
        if ($q !== '') {
            $this->setRedirect('index.php?option=com_efemerides&view=busqueda&q=' . urlencode($q));
        }
    }
}
