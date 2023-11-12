<?php
/**
 * @package         Metadesc
 * @version         2.0.0
 * @author          Sergey Osipov <info@devstratum.ru>
 * @website         https://devstratum.ru
 * @copyright       Copyright (c) 2023 Sergey Osipov. All Rights Reserved
 * @license         GNU General Public License v2.0
 * @report          https://github.com/devstratum/metadesc/issues
 */

namespace Devstratum\Component\Metadesc\Administrator\View\Articles;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Layout\LayoutHelper;

/**
 * View class for a list of Articles
 * @since   1.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * An array of items
     *
     * @var     array
     * @since   1.0.0
     */
    protected $items;

    /**
     * The pagination object
     *
     * @var     \Joomla\CMS\Pagination\Pagination
     * @since   1.0.0
     */
    protected $pagination;

    /**
     * The model state
     *
     * @var     \Joomla\CMS\Object\CMSObject
     * @since   1.0.0
     */
    protected $state;

    /**
     * Form object for search filters
     *
     * @var     \Joomla\CMS\Form\Form
     * @since   1.0.0
     */
    public $filterForm;

    /**
     * The active search filters
     *
     * @var     array
     * @since   1.0.0
     */
    public $activeFilters;

    /**
     * Display the view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths
     * @throws
     * @return  void
     * @since   1.0.0
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

        print $this->addFooter();
    }

    /**
     * Add the page title and toolbar
     *
     * @return  void
     * @throws
     * @since   1.0.0
     */
    protected function addToolbar() {
        $user  = Factory::getApplication()->getIdentity();

        // Get the toolbar object instance
        $toolbar = Toolbar::getInstance('toolbar');

        ToolbarHelper::title(Text::_('COM_METADESC_ARTICLES_TITLE'), 'code');

        if ($user->authorise('core.admin', 'com_metadesc') || $user->authorise('core.options', 'com_metadesc')) {
            $toolbar->preferences('com_metadesc');
        }
    }

    /**
     * Add the footer
     *
     * @return  mixed
     * @throws
     * @since   1.0.0
     */
    protected function addFooter() {
        $output = LayoutHelper::render('components.metadesc.administrator.footer', $this);
        return $output;
    }
}