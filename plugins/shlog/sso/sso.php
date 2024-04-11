<?php
/**
<<<<<<< HEAD
 * PHP Version 5.3
=======
 * PHP Version 8.1
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
 *
 * @package     Shmanic.Plugin
 * @subpackage  Log.Sso
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

<<<<<<< HEAD
jimport('joomla.plugin.plugin');
=======
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8

/**
 * SSO logging plugin.
 *
 * @package     Shmanic.Plugin
 * @subpackage  Log.Sso
 * @since       2.0
 */
<<<<<<< HEAD
class PlgShlogSso extends JPlugin
=======
class PlgShlogSso extends CMSPlugin
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
{
	const LOGGER_FILE = 'formattedtext';

	const LOGGER_SCREEN = 'messagequeue';

	const SSO_CATEGORY = 'sso';

	const AUTH_CATEGORY = 'auth';

	/**
	 * Fired on log initialiser.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function onLogInitialise()
	{
		// This is the columns that the log files will use
		$fileFormat = str_replace('\t', "\t", $this->params->get('file_format', '{DATETIME}\t{ID}\t{MESSAGE}'));

		/*
		 * Deals with the Information level logs.
		 */
		if ($this->params->get('enable_info', true))
		{
			// Setup a information file logger
<<<<<<< HEAD
			JLog::addLogger(
=======
			Log::addLogger(
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
				array(
					'logger' => self::LOGGER_FILE,
					'text_file' => $this->params->get('log_name_info', 'sso.info.php'),
					'text_entry_format' => $fileFormat
				),
<<<<<<< HEAD
				JLog::INFO,
=======
				Log::INFO,
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
				array(self::SSO_CATEGORY, self::AUTH_CATEGORY)
			);
		}

		/*
		 * Deals with the Debugging level logs (which includes all levels internally).
		 */
		if ($this->params->get('enable_debug', true))
		{
			// Setup a debugger file logger
<<<<<<< HEAD
			JLog::addLogger(
=======
			Log::addLogger(
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
				array(
					'logger' => self::LOGGER_FILE,
					'text_file' => $this->params->get('log_name_debug', 'sso.debug.php'),
					'text_entry_format' => $fileFormat
				),
<<<<<<< HEAD
				JLog::ALL,
=======
				Log::ALL,
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
				array(self::SSO_CATEGORY, self::AUTH_CATEGORY)
			);
		}

		/*
		 * Deals with the Error level logs.
		 */
		if ($this->params->get('enable_error', true))
		{
			// Setup a error file logger
<<<<<<< HEAD
			JLog::addLogger(
=======
			Log::addLogger(
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
				array(
					'logger' => self::LOGGER_FILE,
					'text_file' => $this->params->get('log_name_error', 'sso.error.php'),
					'text_entry_format' => $fileFormat
				),
<<<<<<< HEAD
				JLog::ERROR,
=======
				Log::ERROR,
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
				array(self::SSO_CATEGORY, self::AUTH_CATEGORY)
			);

			if ($this->params->get('error_to_screen', true))
			{
				// Setup a error on-screen logger
<<<<<<< HEAD
				JLog::addLogger(
					array('logger' => self::LOGGER_SCREEN),
					JLog::ERROR,
=======
				Log::addLogger(
					array('logger' => self::LOGGER_SCREEN),
					Log::ERROR,
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
					array(self::SSO_CATEGORY, self::AUTH_CATEGORY)
				);
			}
		}
	}
}
