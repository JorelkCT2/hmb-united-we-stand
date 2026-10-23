<?php
/**
 * @package     com_metalmap
 * @copyright   (C) 2026 Heavy Metal Brothers
 * @license     GNU General Public License version 2 or later
 */

namespace Joomla\Component\Metalmap\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;

final class EventTable extends Table
{
    public function __construct(DatabaseDriver $db)
    {
        $this->setColumnAlias('published', 'state');
        parent::__construct('#__metalmap_events', 'id', $db);
    }

    public function check(): bool
    {
        $this->name = trim($this->name);

        if ($this->name === '') {
            $this->setError(Text::_('COM_METALMAP_ERR_NAME_REQUIRED'));
            return false;
        }

        // Auto-generar alias
        if (trim($this->alias ?? '') === '') {
            $this->alias = ApplicationHelper::stringURLSafe($this->name);
        } else {
            $this->alias = ApplicationHelper::stringURLSafe($this->alias);
        }
        if ($this->alias === '') {
            $this->alias = OutputFilter::stringURLSafe((string) time());
        }

        // Country code → minúsculas (formato flagcdn.com)
        if (!empty($this->country_code)) {
            $this->country_code = strtolower(trim($this->country_code));
        }

        // Validar lat/lng
        $this->latitude  = (float) $this->latitude;
        $this->longitude = (float) $this->longitude;

        if ($this->latitude < -90 || $this->latitude > 90) {
            $this->setError(Text::_('COM_METALMAP_ERR_INVALID_LAT'));
            return false;
        }
        if ($this->longitude < -180 || $this->longitude > 180) {
            $this->setError(Text::_('COM_METALMAP_ERR_INVALID_LNG'));
            return false;
        }

        return parent::check();
    }
}
