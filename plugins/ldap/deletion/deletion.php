<?php
/**
<<<<<<< HEAD
 * PHP Version 5.3
=======
 * PHP Version 8.1
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
 *
 * @package     Shmanic.Plugin
 * @subpackage  Ldap.Deletion
 * @author      Shaun Maunder <shaun@shmanic.com>
 *
 * @copyright   Copyright (C) 2011-2013 Shaun Maunder. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

<<<<<<< HEAD
jimport('joomla.plugin.plugin');
=======
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8

/**
 * LDAP User Deletion Plugin.
 *
 * @package     Shmanic.Plugin
 * @subpackage  Ldap.Deletion
 * @since       2.0
 */
<<<<<<< HEAD
class PlgLdapDeletion extends JPlugin
=======
class PlgLdapDeletion extends CMSPlugin
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
{
	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An array that holds the plugin configuration
	 *
	 * @since  2.0
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * Deletes the user from LDAP if the deletion in the Joomla database was successful.
	 *
	 * Method is called after user data is deleted from the database.
	 *
	 * @param   array    $user     Holds the user data.
	 * @param   boolean  $success  True if user was successfully deleted from the database.
	 * @param   string   $msg      An error message.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function onUserAfterDelete($user, $success, $msg)
	{
		if ($success)
		{
			try
			{
				$username = $user['username'];
<<<<<<< HEAD
				SHLog::add(JText::sprintf('PLG_LDAP_DELETION_DEBUG_12905', $username), 12905, JLog::DEBUG, 'ldap');
=======
				SHLog::add(Text::sprintf('PLG_LDAP_DELETION_DEBUG_12905', $username), 12905, Log::DEBUG, 'ldap');
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8

				// Pick up the user and delete it using the User Adapter
				$adapter = SHFactory::getUserAdapter($username);
				$adapter->delete();

<<<<<<< HEAD
				SHLog::add(JText::sprintf('PLG_LDAP_DELETION_INFO_12908', $username), 12908, JLog::INFO, 'ldap');
			}
			catch (Exception $e)
			{
				SHLog::add($e, 12901, JLog::ERROR, 'ldap');
=======
				SHLog::add(Text::sprintf('PLG_LDAP_DELETION_INFO_12908', $username), 12908, Log::INFO, 'ldap');
			}
			catch (Exception $e)
			{
				SHLog::add($e, 12901, Log::ERROR, 'ldap');
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
			}
		}
	}
}
