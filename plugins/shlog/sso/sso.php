<?php
/**
 * PHP Version 8.1
 *
 * @package     Shmanic.Plugin
 * @subpackage  Log.Sso
 * @author      Shaun Maunder <shaun@shmanic.com>
 * @edited		2024
 * @copyright   Copyright (C) 2011-2013 Shaun Maunder. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * SSO logging plugin.
 *
 * @package     Shmanic.Plugin
 * @subpackage  Log.Sso
 * @since       2.0
 */
class PlgShlogSso extends CMSPlugin
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
			Log::addLogger(
				array(
					'logger' => self::LOGGER_FILE,
					'text_file' => $this->params->get('log_name_info', 'sso.info.php'),
					'text_entry_format' => $fileFormat
				),
				Log::INFO,
				array(self::SSO_CATEGORY, self::AUTH_CATEGORY)
			);
		}

		/*
		 * Deals with the Debugging level logs (which includes all levels internally).
		 */
		if ($this->params->get('enable_debug', true))
		{
			// Setup a debugger file logger
			Log::addLogger(
				array(
					'logger' => self::LOGGER_FILE,
					'text_file' => $this->params->get('log_name_debug', 'sso.debug.php'),
					'text_entry_format' => $fileFormat
				),
				Log::ALL,
				array(self::SSO_CATEGORY, self::AUTH_CATEGORY)
			);
		}

		/*
		 * Deals with the Error level logs.
		 */
		if ($this->params->get('enable_error', true))
		{
			// Setup a error file logger
			Log::addLogger(
				array(
					'logger' => self::LOGGER_FILE,
					'text_file' => $this->params->get('log_name_error', 'sso.error.php'),
					'text_entry_format' => $fileFormat
				),
				Log::ERROR,
				array(self::SSO_CATEGORY, self::AUTH_CATEGORY)
			);

			if ($this->params->get('error_to_screen', true))
			{
				// Setup a error on-screen logger
				Log::addLogger(
					array('logger' => self::LOGGER_SCREEN),
					Log::ERROR,
					array(self::SSO_CATEGORY, self::AUTH_CATEGORY)
				);
			}
		}
	}
}
