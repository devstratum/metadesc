<?php
/**
 * @package         Metadesc Component
 * @version         0.84
 * @author          Sergey Osipov <info@devstratum.ru>
 * @website         https://devstratum.ru
 * @copyright       Copyright (c) 2022 Sergey Osipov. All Rights Reserved
 * @license         GNU General Public License v2.0
 * @report          https://github.com/devstratum/metadesc/issues
 */

namespace Devstratum\Component\Metadesc\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Input\Json;

/**
 * Component Controller Ajax
 */
class MetadescController extends BaseController
{
    /**
     * Constructor
     *
     * @param   array  $config  An optional associative array of configuration settings
     * @throws
     */
    public function __construct($config = [])
    {
        // Add json input
        $app = Factory::getApplication();
        if ($data = new Json())
        {
            foreach ($data->getArray() as $name => $value)
            {
                $app->input->def($name, $value);
            }
        }

        // Parent constructor
        $config['default_task'] = false;
        $config['default_view'] = false;

        $result = parent::__construct($config);

        return $result;
    }

    /**
     * Method action article
     *
     * @throws
     */
    public function article()
    {
        $app = Factory::getApplication();

        $response = [];

        $id = $app->input->getInt('id');
        $description = $app->input->getString('description');

        if ($id && $description) {
            /** @var \Devstratum\Component\Metadesc\Administrator\Model\AitemModel $model */
            $model = $app->bootComponent('com_metadesc')->getMVCFactory()->createModel('Aitem', 'Administrator', []);
            $data = $model->getItem($id);

            $data->metadesc = $description;

            $result = $model->save($data);

            if ($result) {
                $message = ['type' => 'success', 'text' => 'Update: Data!'];
                $response = ['id' => $id, 'description' => $description];
            } else {
                $message = ['type' => 'warning', 'text' => 'Warning: DB problem!'];
            }

        } else {
            $message = ['type' => 'danger', 'text' => 'Error: Data!'];
        }

        $this->setResponse($response, $message);
    }

    /**
     * Method action category
     *
     * @throws
     */
    public function category()
    {
        $app = Factory::getApplication();

        $response = [];

        $id = $app->input->getInt('id');
        $description = $app->input->getString('description');

        if ($id && $description) {
            /** @var \Devstratum\Component\Metadesc\Administrator\Model\CitemModel $model */
            $model = $app->bootComponent('com_metadesc')->getMVCFactory()->createModel('Citem', 'Administrator', []);
            $data = $model->getItem($id);

            $data->metadesc = $description;

            $result = $model->save($data);

            if ($result) {
                $message = ['type' => 'success', 'text' => 'Update: Data!'];
                $response = ['id' => $id, 'description' => $description];
            } else {
                $message = ['type' => 'warning', 'text' => 'Warning: DB problem!'];
            }

        } else {
            $message = ['type' => 'danger', 'text' => 'Error: Data!'];
        }

        $this->setResponse($response, $message);
    }

    /**
     * Method action response
     *
     * @param array $response
     * @param array $message
     *
     * @throws
     */
    public function setResponse($response, $message)
    {
        $app = Factory::getApplication();
        $code = (!empty($this->code)) ? $this->code : 200;

        header('Content-Type: application/json');
        echo new JsonResponse($response, $message);
        $app->close($code);
    }
}