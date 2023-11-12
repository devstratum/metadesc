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

namespace Devstratum\Component\Metadesc\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;

/**
 * Component Controller
 * @since   1.0.0
 */
class DisplayController extends BaseController
{
    /**
     * The default view
     * @var     string
     * @since   1.0.0
     */
    protected $default_view = 'menus';

    /**
     * Method to display a view
     *
     * @param   boolean  $cachable   If true, the view output will be cached
     * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}
     * @throws
     * @return  BaseController|boolean  This object to support chaining
     * @since   1.0.0
     */
    public function display($cachable = false, $urlparams = array())
    {
        return parent::display();
    }
}