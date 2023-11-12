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

\defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

/**
 * The content service provider
 */
return new class implements ServiceProviderInterface
{
	/**
	 * Registers the service provider with a DI container
	 *
	 * @param   Container  $container  The DI container
	 *
	 * @return  void
	 */
	public function register(Container $container)
	{
        $container->registerServiceProvider(new MVCFactory('\\Devstratum\\Component\\Metadesc'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Devstratum\\Component\\Metadesc'));

		$container->set(
			ComponentInterface::class,
			function (Container $container) {
                $component = new MVCComponent($container->get(ComponentDispatcherFactoryInterface::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));

                return $component;
			}
		);
	}
};
