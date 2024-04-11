<?php
/**
<<<<<<< HEAD
 * PHP Version 5.3
=======
 * PHP Version 8.1
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
 *
 * @package     Shmanic.Components
 * @subpackage  Shldap
 * @author      Shaun Maunder <shaun@shmanic.com>
<<<<<<< HEAD
 *
=======
 * $edited		2024
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
 * @copyright   Copyright (C) 2011-2013 Shaun Maunder. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

<<<<<<< HEAD
// Include dependancies
jimport('joomla.application.component.controller');

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_shldap'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
=======
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

// Include dependancies
//jimport('joomla.application.component.controller');

// Access check.
if (!Factory::getUser()->authorise('core.manage', 'com_shldap'))
{
	//return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
	return new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 404);
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
}

// Register the helper class for this component
JLoader::register('ComShldapHelper', JPATH_COMPONENT . '/helpers/shldap.php');

// Check if the Shmanic platform has already been imported
if (!defined('SHPATH_PLATFORM'))
{
	// Shmanic Platform import
	if (!file_exists(JPATH_PLATFORM . '/shmanic/import.php'))
	{
<<<<<<< HEAD
		JError::raiseError(500, JText::_('COM_SHLDAP_PLATFORM_MISSING'));

=======
		//JError::raiseError(500, JText::_('COM_SHLDAP_PLATFORM_MISSING'));
		throw new Exception(Text::_('COM_SHLDAP_PLATFORM_MISSING'), 500);
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
		return false;
	}

	require_once JPATH_PLATFORM . '/shmanic/import.php';
	SHImport('ldap');
}

// Get the input class
<<<<<<< HEAD
$input = JFactory::getApplication()->input;

// Launch the controller.
$controller = JControllerLegacy::getInstance('Shldap');
=======
$input = Factory::getApplication()->input;

// Launch the controller.
$controller = BaseController::getInstance('Shldap');
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
$controller->execute($input->get('task', 'display', 'cmd'));
$controller->redirect();
