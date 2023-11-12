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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Metadesc Component Article Model
 * @since   1.0.0
 */
class AitemModel extends BaseDatabaseModel
{
    /**
     * Method to get a single record
     *
     * @param   integer  $pk  The id of the primary key
     * @return  mixed  Object on success, false on failure
     * @since   1.0.0
     */
    public function getItem($pk = null)
    {
        $pk = (int) $pk;

        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName('#__content'))
            ->where($db->quoteName('id') . ' = ' . $pk);

        $db->setQuery($query);
        $data = $db->loadObject();

        return $data;
    }

    /**
     * Method to save the model data
     *
     * @param   object  $data  The model data
     * @throws
     * @return  boolean  True on success
     * @since   1.0.0
     */
    public function save($data)
    {
        $app = Factory::getApplication();
        $user = $app->getIdentity();
        $db = $this->getDbo();

        $data->modified = date('Y-m-d H:i:s');
        $data->modified_by = $user->id;

        $result = $db->updateObject('#__content', $data, 'id');

        return $result;
    }
}