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
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Metadesc Component Menu Model
 */
class MitemModel extends BaseDatabaseModel
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
            ->from($db->quoteName('#__menu'))
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

        $result = $db->updateObject('#__menu', $data, 'id');

        return $result;
    }
}