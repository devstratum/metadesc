<?php
/**
 * @package         Metadesc
 * @version         1.54.2
 * @author          Sergey Osipov <info@devstratum.ru>
 * @website         https://devstratum.ru
 * @copyright       Copyright (c) 2022 Sergey Osipov. All Rights Reserved
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
 * Metadesc Component Categories Model
 */
class CategoriesModel extends ListModel
{
    /**
     * Constructor
     *
     * @param   array  $config  An optional associative array of configuration settings
     * @throws
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'title', 'a.title',
                'created', 'a.created_time',
                'author_id', 'a.created_user_id',
                'category_id', 'a.parent_id',
                'language', 'a.language',
                'published', 'a.published',
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to get a database query to list categories
     *
     * @return  mixed
     * @throws
     */
    protected function getListQuery()
    {
        $user = Factory::getApplication()->getIdentity();

        // Create a new query object
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $extension = 'com_content';

        // Select the required fields from the table
        $query->select(
            $this->getState(
                'list.select',
                [
                    $db->quoteName('a.id'),
                    $db->quoteName('a.title'),
                    $db->quoteName('a.alias'),
                    $db->quoteName('a.note'),
                    $db->quoteName('a.metadesc'),
                    $db->quoteName('a.checked_out'),
                    $db->quoteName('a.checked_out_time'),
                    $db->quoteName('a.created_time', 'created'),
                    $db->quoteName('a.created_user_id', 'author_id'),
                    $db->quoteName('a.parent_id', 'category_id'),
                    $db->quoteName('a.language'),
                    $db->quoteName('a.published'),
                ]
            )
        );

        $query->from($db->quoteName('#__categories', 'a'));

        // Join over authors
        $query->select($db->quoteName('ua.name', 'author_name'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'ua'),
                $db->quoteName('ua.id') . ' = ' . $db->quoteName('a.created_user_id')
            );

        // Join over categories
        $query->select($db->quoteName('ca.title', 'category_title'))
            ->join(
                'LEFT',
                $db->quoteName('#__categories', 'ca'),
                $db->quoteName('ca.id') . ' = ' . $db->quoteName('a.parent_id')
            );

        $query->where($db->quoteName('a.extension') . ' = :extension')
            ->bind(':extension', $extension);

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
            } else {
                $search = '%' . str_replace(' ', '%', trim($search)) . '%';
                $query->extendWhere(
                    'AND',
                    [
                        $db->quoteName('a.title') . ' LIKE :title',
                        $db->quoteName('a.alias') . ' LIKE :alias',
                        $db->quoteName('a.note') . ' LIKE :note',
                    ],
                    'OR'
                )
                    ->bind(':title', $search)
                    ->bind(':alias', $search)
                    ->bind(':note', $search);
            }
        }

        // Filter by categories
        $categoryId = $this->getState('filter.category_id');

        if (is_numeric($categoryId)) {
            $categoryId = (int) $categoryId;
            $type = $this->getState('filter.category_id.include', true) ? ' = ' : ' <> ';
            $query->where($db->quoteName('a.parent_id') . $type . ':categoryId')
                ->bind(':categoryId', $categoryId, ParameterType::INTEGER);
        } elseif (is_array($categoryId)) {
            $categoryId = ArrayHelper::toInteger($categoryId);
            $query->whereIn($db->quoteName('a.parent_id'), $categoryId);
        }

        // Filter by author
        $authorId = $this->getState('filter.author_id');

        if (is_numeric($authorId)) {
            $authorId = (int) $authorId;
            $type = $this->getState('filter.author_id.include', true) ? ' = ' : ' <> ';
            $query->where($db->quoteName('a.created_user_id') . $type . ':authorId')
                ->bind(':authorId', $authorId, ParameterType::INTEGER);
        } elseif (is_array($authorId)) {
            if (in_array('by_me', $authorId)) {
                $authorId['by_me'] = $user->id;
            }

            $authorId = ArrayHelper::toInteger($authorId);
            $query->whereIn($db->quoteName('a.created_user_id'), $authorId);
        }

        // Filter on the language
        $language = $this->getState('filter.language');

        if ($language) {
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