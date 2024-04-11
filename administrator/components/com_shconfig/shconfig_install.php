<?php
/**
<<<<<<< HEAD
 * PHP Version 5.3
=======
 * PHP Version 8.1
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
 *
 * @package    Shmanic.Scripts
 * @author     Shaun Maunder <shaun@shmanic.com>
 *
 * @copyright  Copyright (C) 2011-2013 Shaun Maunder. All rights reserved.
<<<<<<< HEAD
=======
 * @edited 		2024
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

<<<<<<< HEAD
=======
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
/**
 * Installer script for com_shconfig.
 *
 * @package  Shmanic.Scripts
 * @since    2.0
 */
class Com_ShconfigInstallerScript
{
	/**
	 * Minimum PHP version to install this extension.
	 *
	 * @var    string
	 * @since  2.0
	 */
<<<<<<< HEAD
	const MIN_PHP_VERSION = '5.3.0';
=======
	const MIN_PHP_VERSION = '8.1.0';
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8

	/**
	 * Method to run before an install/update/uninstall method.
	 *
	 * @param   string  $type    Type of change (install, update or discover_install).
	 * @param   object  $parent  Object of class calling this method.
	 *
	 * @return  boolean  False to abort installation.
	 *
	 * @since   2.0
	 */
	public function preflight($type, $parent)
	{
<<<<<<< HEAD
		// Check the PHP version is at least at 5.3.0
		if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<'))
		{
			JFactory::getApplication()->enqueueMessage(
				JText::sprintf('COM_SHCONFIG_PREFLIGHT_PHP_VERSION', PHP_VERSION, self::MIN_PHP_VERSION),
=======
		// Check the PHP version is at least at 8.1.0
		if (version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<'))
		{
			Factory::getApplication()->enqueueMessage(
				Text::sprintf('COM_SHCONFIG_PREFLIGHT_PHP_VERSION', PHP_VERSION, self::MIN_PHP_VERSION),
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
				'error'
			);

			return false;
		}
	}

	/**
	 * Method to run after an install/update/uninstall method.
	 *
	 * @param   string  $type     Type of change (install, update or discover_install).
	 * @param   object  $parent   Object of class calling this method.
	 * @param   array   $results  Array of extension results.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function postflight($type, $parent, $results = array())
	{
		if ($type == 'install' || $type == 'update')
		{
<<<<<<< HEAD
			$db = JFactory::getDbo();
=======
			$db = Factory::getDbo();
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8

			// Update the platform version
			$db->setQuery(
				$db->getQuery(true)
					->update($db->quoteName('#__sh_config'))
					->set($db->quoteName('value') . ' = ' . $db->quote($parent->getManifest()->version))
					->where($db->quoteName('name') . ' = ' . $db->quote('platform:version'))
			)
			->execute();
		}
	}
}
