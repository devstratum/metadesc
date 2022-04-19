<?php
/**
 * @package         Metadesc Component
 * @version         0.52
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
 * Metadesc Component Articles Model
 */
class ArticlesModel extends ListModel
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
                'created', 'a.created',
                'author_id', 'a.created_by',
                'category_id', 'a.catid',
                'language', 'a.language',
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to get a database query to list articles
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
                    $db->quoteName('a.created', 'created'),
                    $db->quoteName('a.created_by', 'author_id'),
                    $db->quoteName('a.catid', 'category_id'),
                    $db->quoteName('a.language'),
                ]
            )
        );

        $query->from($db->quoteName('#__content', 'a'));
        $query->select($db->quoteName('ua.name', 'author_name'))
            ->join(
                'LEFT',
                $db->quoteName('#__users', 'ua'),
                $db->quoteName('ua.id') . ' = ' . $db->quoteName('a.created_by')
            );

        $query->select($db->quoteName('ca.title', 'category_title'))
            ->join(
                'LEFT',
                $db->quoteName('#__categories', 'ca'),
                $db->quoteName('ca.id') . ' = ' . $db->quoteName('a.catid')
            );

        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $search = (int) substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :search')
                    ->bind(':search', $search, ParameterType::INTEGER);
            } elseif (stripos($search, 'author:') === 0) {
                $search = '%' . substr($search, 7) . '%';
                $query->where('(' . $db->quoteName('ua.name') . ' LIKE :search1 OR ' . $db->quoteName('ua.username') . ' LIKE :search2)')
                    ->bind([':search1', ':search2'], $search);
            } elseif (stripos($search, 'content:') === 0) {
                $search = '%' . substr($search, 8) . '%';
                $query->where('(' . $db->quoteName('a.introtext') . ' LIKE :search1 OR ' . $db->quoteName('a.fulltext') . ' LIKE :search2)')
                    ->bind([':search1', ':search2'], $search);
            } else {
                $search = '%' . str_replace(' ', '%', trim($search)) . '%';
                $query->where(
                    '(' . $db->quoteName('a.title') . ' LIKE :search1 OR ' . $db->quoteName('a.alias') . ' LIKE :search2'
                    . ' OR ' . $db->quoteName('a.note') . ' LIKE :search3)'
                )
                    ->bind([':search1', ':search2', ':search3'], $search);
            }
        }

        // Filter by categories
        $categoryId = $this->getState('filter.category_id');

        if (is_numeric($categoryId)) {
            $categoryId = (int) $categoryId;
            $type = $this->getState('filter.category_id.include', true) ? ' = ' : ' <> ';
            $query->where($db->quoteName('a.catid') . $type . ':categoryId')
                ->bind(':categoryId', $categoryId, ParameterType::INTEGER);
        } elseif (is_array($categoryId)) {
            $categoryId = ArrayHelper::toInteger($categoryId);
            $query->whereIn($db->quoteName('a.catid'), $categoryId);
        }

        // Filter by author
        $authorId = $this->getState('filter.author_id');

        if (is_numeric($authorId)) {
            $authorId = (int) $authorId;
            $type = $this->getState('filter.author_id.include', true) ? ' = ' : ' <> ';
            $query->where($db->quoteName('a.created_by') . $type . ':authorId')
                ->bind(':authorId', $authorId, ParameterType::INTEGER);
        } elseif (is_array($authorId)) {
            if (in_array('by_me', $authorId)) {
                $authorId['by_me'] = $user->id;
            }

            $authorId = ArrayHelper::toInteger($authorId);
            $query->whereIn($db->quoteName('a.created_by'), $authorId);
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