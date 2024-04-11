<?php
/**
<<<<<<< HEAD
 * PHP Version 8
=======
 * PHP Version 8.1
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
 *
 * @package     Shmanic.Plugin
 * @subpackage  SSO
 * @author      Shaun Maunder <shaun@shmanic.com>
<<<<<<< HEAD
 *
=======
 * @edited		2024
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
 * @copyright   Copyright (C) 2011-2013 Shaun Maunder. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;
use Joomla\CMS\Plugin\CMSPlugin;
<<<<<<< HEAD
//jimport('joomla.plugin.plugin');
=======
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8

/**
 * Attempts to match a user based on the supplied username.
 *
 * @package     Shmanic.Plugin
 * @subpackage  SSO
 * @since       1.0
 */
class PlgSSODummy extends CMSPlugin
{
	/**
	 * This method returns the specified username.
	 *
	 * @return  string  Username
	 *
	 * @since   1.0
	 */
	public function detectRemoteUser()
	{
		return $this->params->get('username');
	}
}
