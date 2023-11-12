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

namespace Devstratum\Component\Metadesc\Administrator\View\Menus;

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
 * View class for a list of Menus
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
        $lang = Factory::getApplication()->getLanguage();
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

        // Preprocess the list of items to find ordering divisions
        foreach ($this->items as $item) {
            // Item type text
            switch ($item->type) {
                case 'url':
                    $value = Text::_('COM_METADESC_TYPE_EXTERNAL_URL');
                    break;

                case 'alias':
                    $value = Text::_('COM_METADESC_TYPE_ALIAS');
                    break;

                case 'separator':
                    $value = Text::_('COM_METADESC_TYPE_SEPARATOR');
                    break;

                case 'heading':
                    $value = Text::_('COM_METADESC_TYPE_HEADING');
                    break;

                case 'container':
                    $value = Text::_('COM_METADESC_TYPE_CONTAINER');
                    break;

                case 'component':
                default:
                    // Load language
                    $lang->load($item->componentname . '.sys', JPATH_ADMINISTRATOR)
                    || $lang->load($item->componentname . '.sys', JPATH_ADMINISTRATOR . '/components/' . $item->componentname);

                    if (!empty($item->componentname)) {
                        $titleParts   = array();
                        $titleParts[] = Text::_($item->componentname);
                        $vars         = null;

                        parse_str($item->link, $vars);

                        if (isset($vars['view'])) {
                            // Attempt to load the view xml file
                            $file = JPATH_SITE . '/components/' . $item->componentname . '/views/' . $vars['view'] . '/metadata.xml';

                            if (!is_file($file)) {
                                $file = JPATH_SITE . '/components/' . $item->componentname . '/view/' . $vars['view'] . '/metadata.xml';
                            }

                            if (is_file($file) && $xml = simplexml_load_file($file)) {
                                // Look for the first view node off of the root node
                                if ($view = $xml->xpath('view[1]')) {
                                    // Add view title if present
                                    if (!empty($view[0]['title'])) {
                                        $viewTitle = trim((string) $view[0]['title']);

                                        // Check if the key is valid. Needed due to B/C so we don't show untranslated keys. This check should be removed with Joomla 4
                                        if ($lang->hasKey($viewTitle)) {
                                            $titleParts[] = Text::_($viewTitle);
                                        }
                                    }
                                }
                            }

                            $vars['layout'] = $vars['layout'] ?? 'default';

                            // Attempt to load the layout xml file
                            // If Alternative Menu Item, get template folder for layout file
                            if (strpos($vars['layout'], ':') > 0) {
                                // Use template folder for layout file
                                $temp = explode(':', $vars['layout']);
                                $file = JPATH_SITE . '/templates/' . $temp[0] . '/html/' . $item->componentname . '/' . $vars['view'] . '/' . $temp[1] . '.xml';

                                // Load template language file
                                $lang->load('tpl_' . $temp[0] . '.sys', JPATH_SITE)
                                ||	$lang->load('tpl_' . $temp[0] . '.sys', JPATH_SITE . '/templates/' . $temp[0]);
                            } else {
                                $base = $this->state->get('filter.client_id') == 0 ? JPATH_SITE : JPATH_ADMINISTRATOR;

                                // Get XML file from component folder for standard layouts
                                $file = $base . '/components/' . $item->componentname . '/tmpl/' . $vars['view'] . '/' . $vars['layout'] . '.xml';

                                if (!file_exists($file)) {
                                    $file = $base . '/components/' . $item->componentname . '/views/' . $vars['view'] . '/tmpl/' . $vars['layout'] . '.xml';

                                    if (!file_exists($file)) {
                                        $file = $base . '/components/' . $item->componentname . '/view/' . $vars['view'] . '/tmpl/' . $vars['layout'] . '.xml';
                                    }
                                }
                            }

                            if (is_file($file) && $xml = simplexml_load_file($file)) {
                                // Look for the first view node off of the root node
                                if ($layout = $xml->xpath('layout[1]')) {
                                    if (!empty($layout[0]['title'])) {
                                        $titleParts[] = Text::_(trim((string) $layout[0]['title']));
                                    }
                                }

                                if (!empty($layout[0]->message[0])) {
                                    $item->item_type_desc = Text::_(trim((string) $layout[0]->message[0]));
                                }
                            }

                            unset($xml);

                            // Special case if neither a view nor layout title is found
                            if (count($titleParts) == 1) {
                                $titleParts[] = $vars['view'];
                            }
                        }

                        $value = implode(' » ', $titleParts);
                    } else {
                        if (preg_match("/^index.php\?option=([a-zA-Z\-0-9_]*)/", $item->link, $result)) {
                            $value = Text::sprintf('COM_METADESC_TYPE_UNEXISTING', $result[1]);
                        } else {
                            $value = Text::_('COM_METADESC_TYPE_UNKNOWN');
                        }
                    }
                    break;
            }

            $item->item_type = $value;
            $item->protected = $item->menutype == 'main';
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

        ToolbarHelper::title(Text::_('COM_METADESC_MENUS_TITLE'), 'code');

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