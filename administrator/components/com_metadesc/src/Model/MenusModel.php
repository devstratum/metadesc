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

namespace Devstratum\Component\Metadesc\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

/**
 * Metadesc Component Menus Model
 * @since   1.0.0
 */
class MenusModel extends ListModel
{
    /**
     * Constructor
     *
     * @param   array  $config  An optional associative array of configuration settings
     * @throws
     * @since   1.0.0
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
                'language', 'a.language',
                'published', 'a.published',
                'menutype', 'a.menutype', 'menutype_title',
                'access', 'a.access', 'access_level',
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to get a database query to list menus
     *
     * @return  mixed
     * @throws
     * @since   1.0.0
     */
    protected function getListQuery()
    {
        $user = Factory::getApplication()->getIdentity();

        // Create a new query object
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $clientId = 0;
        $typeLink = 'component';

        // Select the required fields from the table
        $query->select(
            $this->getState(
                'list.select',
                [
                    $db->quoteName('a.id'),
                    $db->quoteName('a.title'),
                    $db->quoteName('a.alias'),
                    $db->quoteName('a.note'),
                    $db->quoteName('a.params'),
                    $db->quoteName('a.checked_out'),
                    $db->quoteName('a.checked_out_time'),
                    $db->quoteName('a.language'),
                    $db->quoteName('a.published'),
                    $db->quoteName('a.client_id'),
                    $db->quoteName('a.menutype'),
                    $db->quoteName('a.access'),
                    $db->quoteName('a.type'),
                    $db->quoteName('a.component_id'),
                    $db->quoteName('a.link'),
                ]
            )
        );

        $query->from($db->quoteName('#__menu', 'a'));

        // Join over components
        $query->select($db->quoteName('c.element', 'componentname'))
            ->join('LEFT',
                $db->quoteName('#__extensions', 'c'),
                $db->quoteName('c.extension_id') . ' = ' . $db->quoteName('a.component_id')
            );

        // Join over the menu types
        $query->select($db->quoteName('mt.title', 'menutype_title'))
            ->join(
                'LEFT',
                $db->quoteName('#__menu_types', 'mt'),
                $db->quoteName('mt.menutype') . ' = ' . $db->quoteName('a.menutype')
            );

        // Join over the asset groups
        $query->select($db->quoteName('ag.title', 'access_level'))
            ->join('LEFT',
                $db->quoteName('#__viewlevels', 'ag'),
                $db->quoteName('ag.id') . ' = ' . $db->quoteName('a.access')
            );

        // Exclude the root category
        $query->where(
            [
                $db->quoteName('a.id') . ' > 1',
                $db->quoteName('a.client_id') . ' = :clientId',
            ]
        )
            ->bind(':clientId', $clientId, ParameterType::INTEGER);
            
        // Exclude type of link except component
        $query->where($db->quoteName('a.type') . ' = :typeLink')
            ->bind(':typeLink', $typeLink, ParameterType::STRING);

        // Filter the items over the menu id if set
        $menuType = $this->getState('filter.menutype');

        // A value "" means all
        if ($menuType == '') {
            // Load all menu types we have manage access
            $query2 = $db->getQuery(true)
                ->select(
                    [
                        $db->quoteName('id'),
                        $db->quoteName('menutype'),
                    ]
                )
                ->from($db->quoteName('#__menu_types'))
                ->where($db->quoteName('client_id') . ' = :clientId')
                ->bind(':clientId', $clientId, ParameterType::INTEGER)
                ->order($db->quoteName('title'));

            // Show protected items on explicit filter only
            $query->where($db->quoteName('a.menutype') . ' != ' . $db->quote('main'));

            $menuTypes = $db->setQuery($query2)->loadObjectList();

            if ($menuTypes) {
                $types = array();

                foreach ($menuTypes as $type) {
                    if ($user->authorise('core.manage', 'com_menus.menu.' . (int) $type->id)) {
                        $types[] = $type->menutype;
                    }
                }

                if ($types) {
                    $query->whereIn($db->quoteName('a.menutype'), $types);
                }
                else {
                    $query->where(0);
                }
            }
        }
        // Default behavior => load all items from a specific menu
        elseif (strlen($menuType)) {
            $query->where($db->quoteName('a.menutype') . ' = :menuType')
                ->bind(':menuType', $menuType);
        }
        // Empty menu type => error
        else {
            $query->where('1 != 1');
        }

        // Filter by published state
        $published = (string) $this->getState('filter.published');

        if (is_numeric($published)) {
            $state = (int) $published;
            $query->where($db->quoteName('a.published') . ' = :published')
                ->bind(':published', $state, ParameterType::INTEGER);
        } elseif ($published === '') {
            $query->whereIn($db->quoteName('a.published'), [0, 1]);
        }

        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $search = (int) substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :search')
                    ->bind(':search', $search, ParameterType::INTEGER);
            } elseif (stripos($search, 'link:') === 0) {
                if ($search = str_replace(' ', '%', trim(substr($search, 5)))) {
                    $query->where($db->quoteName('a.link') . ' LIKE :search')
                        ->bind(':search', $search);
                }
            } else {
                $search = '%' . str_replace(' ', '%', trim($search)) . '%';
                $query->extendWhere(
                    'AND',
                    [
                        $db->quoteName('a.title') . ' LIKE :search1',
                        $db->quoteName('a.alias') . ' LIKE :search2',
                        $db->quoteName('a.note') . ' LIKE :search3',
                    ],
                    'OR'
                )
                    ->bind([':search1', ':search2', ':search3'], $search);
            }
        }

        // Filter on the access level
        if ($access = (int) $this->getState('filter.access')) {
            $query->where($db->quoteName('a.access') . ' = :access')
                ->bind(':access', $access, ParameterType::INTEGER);
        }

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            if ($groups = $user->getAuthorisedViewLevels())
            {
                $query->whereIn($db->quoteName('a.access'), $groups);
            }
        }

        // Filter on the language
        if ($language = $this->getState('filter.language')) {
            $query->where($db->quoteName('a.language') . ' = :language')
                ->bind(':language', $language);
        }

        // Add the list ordering clause
        $orderCol  = $this->state->get('list.ordering', 'a.id');
        $orderDirn = $this->state->get('list.direction', 'DESC');
        $ordering = $db->escape($orderCol) . ' ' . $db->escape($orderDirn);

        $query->order($ordering);

        return $query;
    }
}