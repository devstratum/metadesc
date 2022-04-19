<?php
/**
 * @package         Metadesc Component
 * @version         0.95
 * @author          Sergey Osipov <info@devstratum.ru>
 * @website         https://devstratum.ru
 * @copyright       Copyright (c) 2022 Sergey Osipov. All Rights Reserved
 * @license         GNU General Public License v2.0
 * @report          https://github.com/devstratum/metadesc/issues
 */

namespace Devstratum\Component\Metadesc\Administrator\View\Categories;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Language\Multilanguage;

/**
 * View class for a list of Categories
 */
class HtmlView extends BaseHtmlView
{
    /**
     * An array of items
     *
     * @var  array
     */
    protected $items;

    /**
     * The pagination object
     *
     * @var  \Joomla\CMS\Pagination\Pagination
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var   \Joomla\CMS\Object\CMSObject
     */
    protected $state;

    /**
     * Form object for search filters
     *
     * @var  \Joomla\CMS\Form\Form
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var  array
     */
    public $activeFilters;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths
     * @throws
     *
     * @return  void
     */
    public function display($tpl = null) {

        $this->items         = $this->get('Items');
        $this->pagination    = $this->get('Pagination');
        $this->state         = $this->get('State');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // We do not need to filter by language when multilingual is disabled
        if (!Multilanguage::isEnabled())
        {
            unset($this->activeFilters['language']);
            $this->filterForm->removeField('language', 'filter');
        }

        // Configure the toolbar.
        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar
     *
     * @return  void
     * @throws
     */
    protected function addToolbar() {
        $user  = Factory::getApplication()->getIdentity();

        // Get the toolbar object instance
        $toolbar = Toolbar::getInstance('toolbar');

        ToolbarHelper::title(Text::_('COM_METADESC_CATEGORIES_TITLE'), 'code');

        if ($user->authorise('core.admin', 'com_metadesc') || $user->authorise('core.options', 'com_metadesc')) {
            $toolbar->preferences('com_metadesc');
        }
    }
}