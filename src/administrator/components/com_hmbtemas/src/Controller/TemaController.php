<?php
namespace HeavyMetalBrothers\Component\HmbTemas\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;

/**
 * Maneja: add, edit, save, cancel, delete, publish, unpublish
 * Hereda todo de FormController — sin código extra necesario.
 */
class TemaController extends FormController
{
    protected $view_list  = 'temas';
    protected $view_item  = 'tema';
    protected $text_prefix = 'COM_HMBTEMAS';

    /**
     * Convierte duración "mm:ss" a segundos antes de guardar.
     */
    protected function allowSave($data, $key = 'id'): bool
    {
        return parent::allowSave($data, $key);
    }

    public function save($key = null, $urlVar = null): bool
    {
        $app   = $this->app;
        $input = $app->getInput();
        $data  = $input->post->get('jform', [], 'array');

        // Convertir duración "mm:ss" → segundos
        if (!empty($data['duration']) && str_contains((string) $data['duration'], ':')) {
            [$min, $sec]       = explode(':', $data['duration'], 2);
            $data['duration']  = (int) $min * 60 + (int) $sec;
            $input->post->set('jform', $data);
        }

        return parent::save($key, $urlVar);
    }
}
