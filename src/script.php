<?php
/**
 * @package         Metadesc Component
 * @version         0.95
 * @author          Sergey Osipov <info@devstratum.ru>
 * @website         https://devstratum.ru
 * @copyright       Copyright (c) 2022 Sergey Osipov. All Rights Reserved
 * @license         GNU General Public License v2.0
 * @report          https://github.com/devstratum/metadesc/issues
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Version;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

class com_metadescInstallerScript
{
	protected $minimumPhp = '7.2';

	protected $minimumJoomla = '4.1.0';

	public function preflight()
	{
		// Check old Joomla!
		if (!class_exists('Joomla\CMS\Version')) {
			JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_METADESC_ERROR_COMPATIBLE_JOOMLA',
				$this->minimumJoomla), 'error');

			return false;
		}

		$app      = Factory::getApplication();
		$jversion = new Version();

		// Check PHP
		if (!(version_compare(PHP_VERSION, $this->minimumPhp) >= 0)) {
			$app->enqueueMessage(Text::sprintf('COM_METADESC_ERROR_COMPATIBLE_PHP',
				$this->minimumPhp), 'error');

			return false;
		}

		// Check Joomla version
		if (!$jversion->isCompatible($this->minimumJoomla)) {
			$app->enqueueMessage(Text::sprintf('COM_METADESC_ERROR_COMPATIBLE_JOOMLA',
				$this->minimumJoomla), 'error');

			return false;
		}

		return true;
	}
    
	public function postflight($type, $parent)
	{
		// Parse layouts
		$this->parseLayouts($parent->getParent()->getManifest()->layouts, $parent->getParent());
	}

	public function parseLayouts(SimpleXMLElement $element, $installer)
	{
		if (!$element || !count($element->children())) return false;

		// Get destination
		$folder      = ((string) $element->attributes()->destination) ? '/' . $element->attributes()->destination : null;
		$destination = Path::clean(JPATH_ROOT . '/layouts' . $folder);

		// Get source
		$folder = (string) $element->attributes()->folder;
		$source = ($folder && file_exists($installer->getPath('source') . '/' . $folder)) ?
			$installer->getPath('source') . '/' . $folder : $installer->getPath('source');

		// Prepare files
		$copyFiles = array();
		foreach ($element->children() as $file) {
			
			$path['src']  = Path::clean($source . '/' . $file);
			$path['dest'] = Path::clean($destination . '/' . $file);

			// Is this path a file or folder?
			$path['type'] = $file->getName() === 'folder' ? 'folder' : 'file';
			if (basename($path['dest']) !== $path['dest']) {
				$newdir = dirname($path['dest']);
				if (!Folder::create($newdir)) {
					Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_CREATE_DIRECTORY', $newdir), Log::WARNING, 'jerror');

					return false;
				}
			}

			$copyFiles[] = $path;
		}

		return $installer->copyFiles($copyFiles);
	}

	public function uninstall($parent)
	{
		// Remove layouts
		$this->removeLayouts($parent->getParent()->getManifest()->layouts);
	}

	protected function removeLayouts(SimpleXMLElement $element)
	{
		if (!$element || !count($element->children())) return false;

		// Get the array of file nodes to process
		$files = $element->children();

		// Get source
		$folder = ((string) $element->attributes()->destination) ? '/' . $element->attributes()->destination : null;
		$source = Path::clean(JPATH_ROOT . '/layouts' . $folder);

		// Process each file in the $files array (children of $tagName).
		foreach ($files as $file) {
			$path = Path::clean($source . '/' . $file);

			// Actually delete the files/folders
			if (is_dir($path)) $val = Folder::delete($path);
			else $val = File::delete($path);

			if ($val === false) {
				Log::add('Failed to delete ' . $path, Log::WARNING, 'jerror');

				return false;
			}
		}

		if (!empty($folder)) Folder::delete($source);

		return true;
	}
}