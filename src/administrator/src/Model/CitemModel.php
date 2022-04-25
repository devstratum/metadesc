<?php
/**
 * @package         Metadesc
 * @version         1.12
 * @author          Sergey Osipov <info@devstratum.ru>
 * @website         https://devstratum.ru
 * @copyright       Copyright (c) 2022 Sergey Osipov. All Rights Reserved
 * @license         GNU General Public License v2.0
 * @report          https://github.com/devstratum/metadesc/issues
 */

namespace Devstratum\Component\Metadesc\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Metadesc Component Category Model
 */
class CitemModel extends BaseDatabaseModel
{
    /**
     * Method to get a single record
     *
     * @param   integer  $pk  The id of the primary key
     *
     * @return  mixed  Object on success, false on failure
     */
    public function getItem($pk = null)
    {
        $pk = (int) $pk;

        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName('#__categories'))
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
     *
     * @return  boolean  True on success
     */
    public function save($data)
    {
        $app = Factory::getApplication();
        $user = $app->getIdentity();
        $db = $this->getDbo();

        $data->modified_time = date('Y-m-d H:i:s');
        $data->modified_user_id = $user->id;

        $result = $db->updateObject('#__categories', $data, 'id');

        return $result;
    }
}