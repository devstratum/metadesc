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

namespace Devstratum\Component\Metadesc\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Input\Json;
use Joomla\CMS\Language\Text;

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
     * Method action menu
     *
     * @throws
     */
    public function menu()
    {
        $app = Factory::getApplication();
        $response = [];
        $id = $app->input->getInt('id');
        $description = $app->input->getString('description');

        if ($id && $description) {
            /** @var \Devstratum\Component\Metadesc\Administrator\Model\MitemModel $model */
            $model = $app->bootComponent('com_metadesc')->getMVCFactory()->createModel('Mitem', 'Administrator', []);
            $data = $model->getItem($id);

            if (!$data) {
                $message = ['type' => 'danger', 'text' => Text::_('COM_METADESC_ERROR_DATA')];
            } else {
                if (!$data->checked_out) {
                    $item_params = json_decode($data->params);
                    $item_params->{'menu-meta_description'} = $description;
                    $data->params = json_encode($item_params);
                    $result = $model->save($data);
                    if ($result) {
                        $message = ['type' => 'success', 'text' => Text::_('COM_METADESC_SUCCESS_UPDATE')];
                        $response = ['id' => $id, 'description' => $description];
                    } else {
                        $message = ['type' => 'danger', 'text' => Text::_('COM_METADESC_ERROR_SAVE')];
                    }
                } else {
                    $message = ['type' => 'warning', 'text' => Text::_('COM_METADESC_ERROR_CHECK')];
                    $response = ['id' => $id, 'checkout' => true];
                }
            }

        } else {
            $message = ['type' => 'warning', 'text' => Text::_('COM_METADESC_ERROR_INPUT')];
        }

        $this->setResponse($response, $message);
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

            if (!$data) {
                $message = ['type' => 'danger', 'text' => Text::_('COM_METADESC_ERROR_DATA')];
            } else {
                if (!$data->checked_out) {
                    $data->metadesc = $description;
                    $result = $model->save($data);
                    if ($result) {
                        $message = ['type' => 'success', 'text' => Text::_('COM_METADESC_SUCCESS_UPDATE')];
                        $response = ['id' => $id, 'description' => $description];
                    } else {
                        $message = ['type' => 'danger', 'text' => Text::_('COM_METADESC_ERROR_SAVE')];
                    }
                } else {
                    $message = ['type' => 'warning', 'text' => Text::_('COM_METADESC_ERROR_CHECK')];
                    $response = ['id' => $id, 'checkout' => true];
                }
            }

        } else {
            $message = ['type' => 'warning', 'text' => Text::_('COM_METADESC_ERROR_INPUT')];
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

            if (!$data) {
                $message = ['type' => 'danger', 'text' => Text::_('COM_METADESC_ERROR_DATA')];
            } else {
                if (!$data->checked_out) {
                    $data->metadesc = $description;
                    $result = $model->save($data);
                    if ($result) {
                        $message = ['type' => 'success', 'text' => Text::_('COM_METADESC_SUCCESS_UPDATE')];
                        $response = ['id' => $id, 'description' => $description];
                    } else {
                        $message = ['type' => 'danger', 'text' => Text::_('COM_METADESC_ERROR_SAVE')];
                    }
                } else {
                    $message = ['type' => 'warning', 'text' => Text::_('COM_METADESC_ERROR_CHECK')];
                    $response = ['id' => $id, 'checkout' => true];
                }
            }

        } else {
            $message = ['type' => 'warning', 'text' => Text::_('COM_METADESC_ERROR_INPUT')];
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