<?php
/**
 * Import for JMapMyLDAP.
 *
 * PHP Version 8
 *
 * @package    Shmanic.Libraries
 * @author     Shaun Maunder <shaun@shmanic.com>
 *
 * @copyright  Copyright (C) 2011-2013 Shaun Maunder. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Version;
use Joomla\CMS\Plugin\PluginHelper;

if (!defined('SHPATH_PLATFORM'))
{
	// Load the platform
	require_once JPATH_PLATFORM . '/shmanic/import.php';
}

if (!defined('SHLDAP_VERSION'))
{
	// Define the JMapMyLDAP version
	define('SHLDAP_VERSION', SHFactory::getConfig()->get('ldap.version'));
}

// Load the global Ldap language file
Factory::getLanguage()->load('shmanic_ldap', JPATH_ROOT);

// Push the reqcert setting if defined
if ($reqcert = (int) SHFactory::getConfig()->get('ldap.reqcert', 0))
{
	if ($reqcert === 1)
	{
		putenv('LDAPTLS_REQCERT=never');
	}
	elseif ($reqcert === 2)
	{
		putenv('LDAPTLS_REQCERT=allow');
	}
	elseif ($reqcert === 3)
	{
		putenv('LDAPTLS_REQCERT=try');
	}
	elseif ($reqcert === 4)
	{
		putenv('LDAPTLS_REQCERT=hard');
	}
}

// Setup and get the Ldap dispatcher
/////$dispatcher = SHFactory::getDispatcher('ldap');
$dispatcher = Factory::getApplication()->getDispatcher();

// Start the LDAP event debugger only if global jdebug is switched on
if (defined('JDEBUG') && JDEBUG && class_exists('SHLdapEventDebug'))
{
	new SHLdapEventDebug($dispatcher);
}

// Import the Ldap group and use the ldap dispatcher
//JPluginHelper::importPlugin('ldap', null, true, $dispatcher);
PluginHelper::importPlugin('ldap', null, true, $dispatcher);

// Employ the event bouncer to control the global Joomla event triggers
if (class_exists('SHLdapEventBouncer'))
{
	if (Version::MAJOR_VERSION < 4)
		$dispatcher = JDispatcher::getInstance();
	else
		$dispatcher = Factory::getApplication()->getDispatcher();

	$instance = new SHLdapEventBouncer(
		$dispatcher
	);
}
