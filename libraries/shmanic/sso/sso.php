<?php
/**
 * PHP Version 8.1
 *
 * ============== Original based on JAuthTools ===============
 * http://joomlacode.org/gf/project/jauthtools
 * Sam Moffatt <sam.moffatt@toowoombarc.qld.gov.au>
 * Toowoomba Regional Council Information Management Department
 * (C) 2008 Toowoomba Regional Council/Sam Moffatt
 * ============================================================
 *
 * @package     Shmanic.Libraries
 * @subpackage  SSO
 * @author      Shaun Maunder <shaun@shmanic.com>
 * @updated		2024 by Giannis Brailas
 * @copyright   Copyright (C) 2011-2013 Shaun Maunder. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
 
//namespace Joomla\CMS\Dispatcher;

defined('JPATH_PLATFORM') or die;

///jimport('joomla.user.authentication');
//JLoader::register('JAuthenticationResponse', JPATH_PLATFORM . '/joomla/user/authentication.php');
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Input\Input;
use Joomla\Event\Event;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Authentication\Authentication;
use Joomla\CMS\Authentication\AuthenticationResponse;
use Joomla\CMS\Version;
use Joomla\CMS\Log\Log;

/**
 * Provides a small pluggable framework for Single Sign On. This class
 * was orginally forked from JAuthTools.
 *
 * @package     Shmanic.Libraries
 * @subpackage  SSO
 * @since       2.0
 */
//class SHSso extends JDispatcher
//class SHSso extends Dispatcher
if (Version::MAJOR_VERSION < 4) {
	class SHSso extends JDispatcher
{
	/**
	 * Name of the method for SSO user detection.
	 *
	 * @var    string
	 * @since  2.0
	 */
	const DETECT_METHOD_NAME = 'detectRemoteUser';

	/**
	 * Key used in the URI for sso logout.
	 *
	 * @var    string
	 * @since  2.0
	 */
	const URI_LOGOUT_KEY = 'ssologout';

	/**
	 * When used the user is authorised against a plugin.
	 *
	 * @var    integer
	 * @since  2.0
	 */
	const AUTHORISE_TRUE = 1;

	/**
	 * Used for inheriting from the default or SSO plugin.
	 *
	 * @var    integer
	 * @since  2.0
	 */
	const AUTHORISE_INHERIT = 0;

	/**
	 * When used the user is authorised against the Joomla database.
	 *
	 * @var    integer
	 * @since  2.0
	 */
	const AUTHORISE_FALSE = -1;

	/**
	 * Constructor
	 *
	 * @param   string  $group  Plugin type for SSO.
	 *
	 * @since   2.0
	 */
	public function __construct($group = 'sso')
	{
		// Attempt to import the plugins using this class instance as a dispatcher
		JPluginHelper::importPlugin($group, null, true, $this);
	}

	/**
	 * Detect the remote SSO user by looping through all SSO
	 * plugins. Once a detection is found, it is put into
	 * the options parameter array and method is returned as
	 * true. Uses the same plug-in group as JAuthTools SSO.
	 *
	 * @return  Array|False  Array containing username on success or False on failure.
	 *
	 * @since   1.0
	 */
	public function detect()
	{
		$args = array();

		// Event to trigger for detection
		$event = strtolower(self::DETECT_METHOD_NAME);

		// Check if any plugins are attached to the event.
		if (!isset($this->_methods[$event]) || empty($this->_methods[$event]))
		{
			// No Plugins Associated To Event
			SHLog::add(JText::_('LIB_SHSSO_DEBUG_15068'), 15068, Log::DEBUG, 'sso');

			return false;
		}

		// Loop through all plugins having a method matching our event
		foreach ($this->_methods[$event] as $key)
		{
			// Check if the plugin is present.
			if (!isset($this->_observers[$key]))
			{
				continue;
			}

			// Check if parameters exist for this observer/plugin
			if (property_exists($this->_observers[$key], 'params'))
			{
				$params = $this->_observers[$key]->params;

				// Get the rule and list from the plug-in parameters
				$ipRule = $params->get('ip_rule', false);
				$ipList = $params->get('ip_list', false);

				// Check that both the rule and list have been set
				if ($ipRule !== false && $ipList !== false)
				{
					// Get the IP address of this client
					jimport('joomla.application.input');
					$input = new JInput($_SERVER);
					$myIp = $input->get('REMOTE_ADDR', false, 'string');

					// Split the list into newline entries
					$ranges = preg_split('/\r\n|\n|\r/', $ipList);

					if (!SHSsoHelper::doIPCheck($myIp, $ranges, $ipRule))
					{
						// IP address denies this plug-in from executing
						SHLog::add(JText::sprintf('LIB_SHSSO_DEBUG_15064', $this->_observers[$key]), 15064, JLog::DEBUG, 'sso');
						continue;
					}
				}
			}

			// Fire the event for an object based observer.
			if (is_object($this->_observers[$key]))
			{
				$args['event'] = $event;
				$value = $this->_observers[$key]->update($args);
			}

			// Fire the event for a function based observer.
			elseif (is_array($this->_observers[$key]))
			{
				$value = call_user_func_array($this->_observers[$key]['handler'], $args);
			}

			if (isset($value) && $value)
			{
				// Check if the detection has been successful for this plug-in
				if (is_string($value) || (is_array($value) && isset($value['username'])))
				{
					if (is_string($value))
					{
						// Convert the string to an array
						$value = array('username' => $value);
					}

					// Store the detection plug-in name
					$value['sso'] = get_class($this->_observers[$key]);

					// We have a detection result
					return $value;
				}
				else
				{
					// Error: invalid plug-in response
					SHLog::add(JText::sprintf('LIB_SHSSO_ERR_15061', get_class($this->_observers[$key])), 15061, JLog::ERROR, 'sso');

					// Try another plug-in.
					continue;
				}
			}
		}

		// No detection result found.
		return false;
	}

	/**
	 * If a detection has been successful then it will try to
	 * authenticate with the onUserAuthorisation method
	 * in any of the authentication plugins.
	 *
	 * @param   string  $username  String containing detected username.
	 * @param   array   $options   An array containing action, autoregister and detection name.
	 *
	 * @return  JAuthenticationResponse  Response from the authorise.
	 *
	 * @since   1.0
	 */
	public function authorise($username, $options)
	{
		$response = new JAuthenticationResponse;

		$response->username = $username;

		// Check for user attributes and set them into the authentication response if they exist
		if (isset($options['attributes']))
		{
			if (isset($options['attributes']['email']))
			{
				$response->email = $options['attributes']['email'];
			}

			if (isset($options['attributes']['fullname']))
			{
				$response->fullname = $options['attributes']['fullname'];
			}
		}

		// Import the authentication and user plug-ins in case they havent already
		// J! Pull Request: https://github.com/joomla/joomla-platform/pull/1305
		JPluginHelper::importPlugin('user');
		JPluginHelper::importPlugin('authentication');

		// We need to authorise our username to an authentication plugin
		$authorisations = JAuthentication::authorise($response, $options);

		foreach ($authorisations as $authorisation)
		{
			if ($authorisation->status === JAuthentication::STATUS_SUCCESS)
			{
				// This username is authorised to use the system
				$response->status = JAuthentication::STATUS_SUCCESS;

				return $response;
			}
		}

		// No authorises found
		$response->status = JAuthentication::STATUS_FAILURE;

		return $response;
	}

	/**
	 * Uses the Joomla! database to authorise. This can be used if
	 * there are no authentication plug-ins available to authorise the
	 * SSO user.
	 *
	 * @param   string  $username  String containing detected username.
	 * @param   array   $options   An array containing action, autoregister and detection name.
	 *
	 * @return  JAuthenticationResponse    Response from the Joomla authorise.
	 *
	 * @since   2.0
	 */
	public function jAuthorise($username, $options)
	{
		$response = new JAuthenticationResponse;

		$response->username = $username;

		// Check for user attributes and set them into the authentication response if they exist
		if (isset($options['attributes']))
		{
			if (isset($options['attributes']['email']))
			{
				$response->email = $options['attributes']['email'];
			}

			if (isset($options['attributes']['fullname']))
			{
				$response->fullname = $options['attributes']['fullname'];
			}
		}

		$response->type = 'Joomla';

		// Initialise variables.
		$conditions = '';

		// Get a database object
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select($db->quoteName('id'))
			->select($db->quoteName('block'))
			->from($db->quoteName('#__users'))
			->where($db->quoteName('username') . ' = ' . $db->quote($username));

		$db->setQuery($query);
		$result = $db->loadObject();

		// Check if the user exists in the database
		if ($result)
		{
			// Check if the user has been blocked
			if ($result->block)
			{
				$response->status = JAuthentication::STATUS_DENIED;
			}
			else
			{
				// Set the email and fullname to the current one if one doesnt exist
				$user = JUser::getInstance($result->id);
				$response->email = empty($response->email) ? $user->email : $response->email;
				$response->fullname = empty($response->fullname) ? $user->name : $response->fullname;

				// Set the user defined languages
				if (JFactory::getApplication()->isAdmin())
				{
					$response->language = $user->getParam('admin_language');
				}
				else
				{
					$response->language = $user->getParam('language');
				}

				// User exists in database and isnt blocked
				$response->status = JAuthentication::STATUS_SUCCESS;
				$response->error_message = '';
			}
		}
		else
		{
			// User doesn't exist and is unknown
			$response->status = JAuthentication::STATUS_UNKNOWN;
		}

		return $response;
	}

	/**
	 * Attempts to login a user via Single Sign On.
	 * Only if a username is detected can a login been attempted.
	 *
	 * @param   array  $detection  Optional SSO user.
	 * @param   array  $options    An optional array of options to override config settings.
	 *
	 * @return  boolean  True on successful login or False on fail.
	 *
	 * @since   1.0
	 * @throws  RuntimeException
	 */
	public function login($detection = null, $options = array())
	{
		$config = SHFactory::getConfig();

		// Get the SSO username and optional details from the plug-ins
		if (is_null($detection))
		{
			$detection = $this->detect();
		}

		if (!$detection)
		{
			return false;
		}

		SHLog::add(JText::sprintf('LIB_SHSSO_DEBUG_15066', $detection['username'], $detection['sso']), 15066, JLog::DEBUG, 'sso');

		// Set the action if its currently unset
		if (!isset($options['action']))
		{
			$options['action'] = JFactory::getApplication()->isAdmin() ?
				'core.login.admin' : 'core.login.site';
		}

		// Set the autoregister if its currently unset
		if (!isset($options['autoregister']))
		{
			$options['autoregister'] = $config->get('sso.autoregister', false);
		}

		// Set the doauthorise if its currently unset
		if (!isset($options['doauthorise']))
		{
			$options['doauthorise'] = $config->get('sso.doauthorise', self::AUTHORISE_INHERIT);
		}

		$username = $detection['username'];

		// Check if do authorised is based on the plug-in
		if ((int) $options['doauthorise'] === self::AUTHORISE_INHERIT)
		{
			if (isset($detection['doauthorise']))
			{
				// Set the do authorised to the plug-in option
				$options['doauthorise'] = ((boolean) $detection['doauthorise']) ? self::AUTHORISE_TRUE : self::AUTHORISE_FALSE;
			}
			else
			{
				// Default the doauthorise to true
				$options['doauthorise'] = self::AUTHORISE_TRUE;
			}
		}

		// Check for a domain
		if (isset($detection['domain']))
		{
			$options['domain'] = $detection['domain'];
		}

		// Check for any extra user attributes gathered from SSO
		if (isset($detection['attributes']))
		{
			$options['attributes'] = $detection['attributes'];
		}

		/*
		 * Authorising will call on onUserAuthorise() to attempt
		 * to authorise the detected SSO user. If this
		 * is disabled, then it will attempt to authorise with the
		 * Joomla database. If autoregister is turned on then
		 * it'll attempt to create the user in the Joomla database.
		 */
		if ($options['doauthorise'] !== self::AUTHORISE_FALSE)
		{
			// Do authentication plug-in authorisation
			$response = $this->authorise($username, $options);
		}
		else
		{
			// Do Joomla database authorisation
			$response = $this->jAuthorise($username, $options);
		}

		// Check the response status for invalid status'
		if (!((JAuthentication::STATUS_SUCCESS + JAuthentication::STATUS_UNKNOWN) & $response->status))
		{
			// We can only process success and unknown status'
			throw new RuntimeException(JText::sprintf('LIB_SHSSO_ERR_15072', $username), 15072);
		}
		elseif (($response->status === JAuthentication::STATUS_UNKNOWN) && !$options['autoregister'])
		{
			// The user is unknown and there is no autoregister - fail.
			throw new RuntimeException(JText::sprintf('LIB_SHSSO_ERR_15074', $username), 15074);
		}
		elseif (empty($response->email))
		{
			// There is not email set for this user - fail.
			throw new RuntimeException(JText::sprintf('LIB_SHSSO_ERR_15076', $username), 15076);
		}

		/*
		 * Username has been authorised. We can now proceed with the
		 * standard Joomla log-on by calling the onUserLogin event.
		 */
		$options[SHSsoHelper::SESSION_PLUGIN_KEY] = $detection['sso'];
		JPluginHelper::importPlugin('user');

		$results = JFactory::getApplication()->triggerEvent(
			'onUserLogin',
			array((array) $response,
			$options)
		);

		// Save the SSO plug-in name for logout later
		$session = JFactory::getSession();
		$session->set(SHSsoHelper::SESSION_PLUGIN_KEY, $detection['sso']);

		// Check if any of the events failed
		if (in_array(false, $results, true))
		{
			throw new RuntimeException(JText::sprintf('LIB_SHSSO_ERR_15078', $username), 15078);
		}

		SHLog::add(JText::sprintf('LIB_SHSSO_INFO_15079', $username), 15079, JLog::INFO, 'sso');

		// Do a check if URL redirect is required
		SHSsoHelper::redirect();

		// Everything successful - user should be logged on.
		return true;
	}

	/**
	 * Calls the logoutRemoteUser method within SSO plug-in if the user
	 * was logged on with SSO.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function logout()
	{
		$session = JFactory::getSession();

		$app = JFactory::getApplication();

		// Get the SSO plug-in name from login if we used SSO
		if ($class = $session->get(SHSsoHelper::SESSION_PLUGIN_KEY, false))
		{
			// Lets disable SSO until the user requests login
			SHSsoHelper::disable();

			$router = $app->getRouter();

			// We need to add a callback on the router to tell the routed page we just logged out from SSO
			$router->setVar('ssologoutkey', SHFactory::getConfig()->get('sso.bypasskey', 'nosso'));
			$router->setVar('ssologoutval', $session->get(SHSsoHelper::SESSION_STATUS_KEY, SHSsoHelper::STATUS_ENABLE));
			$router->attachBuildRule('SHSso::logoutRouterRule');

			$index = array_search($class, $this->_observers);

			// Ensure the SSO plug-in is still available
			if ($index !== false && method_exists($this->_observers[$index], 'logoutRemoteUser'))
			{
				$this->_observers[$index]->logoutRemoteUser();
			}
		}
	}

	/**
	 * Router callback rule for appending the SSO logout variable to the URL.
	 *
	 * @param   JRouter  $router  Reference to the router object.
	 * @param   JURI     $uri     Reference to the JURI object.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public static function logoutRouterRule(JRouter $router, JURI $uri)
	{
		$uri->setVar($router->getVar('ssologoutkey'), $router->getVar('ssologoutval'));
	}
}
}
else {
		class SHSso extends Joomla\CMS\Plugin\CMSPlugin
{
	
	protected $authenticationPluginType = 'authentication';
	
	/**
	 * Name of the method for SSO user detection.
	 *
	 * @var    string
	 * @since  2.0
	 */
	const DETECT_METHOD_NAME = 'detectRemoteUser';

	/**
	 * Key used in the URI for sso logout.
	 *
	 * @var    string
	 * @since  2.0
	 */
	const URI_LOGOUT_KEY = 'ssologout';

	/**
	 * When used the user is authorised against a plugin.
	 *
	 * @var    integer
	 * @since  2.0
	 */
	const AUTHORISE_TRUE = 1;

	/**
	 * Used for inheriting from the default or SSO plugin.
	 *
	 * @var    integer
	 * @since  2.0
	 */
	const AUTHORISE_INHERIT = 0;

	/**
	 * When used the user is authorised against the Joomla database.
	 *
	 * @var    integer
	 * @since  2.0
	 */
	const AUTHORISE_FALSE = -1;

	/**
	 * Constructor
	 *
	 * @param   string  $group  Plugin type for SSO.
	 *
	 * @since   2.0
	 */
	public function __construct($group = 'sso')
	{
		//$dispatcher = JEventDispatcher::getInstance();
		$dispatcher = Factory::getApplication()->getDispatcher();
		PluginHelper::importPlugin($group, null, true, $dispatcher);
	}

	/**
	 * Detect the remote SSO user by looping through all SSO
	 * plugins. Once a detection is found, it is put into
	 * the options parameter array and method is returned as
	 * true. Uses the same plug-in group as JAuthTools SSO.
	 *
	 * @return  Array|False  Array containing username on success or False on failure.
	 *
	 * @since   1.0
	 */
	public function detect()
	{
		/*
		$args = array();

		// Event to trigger for detection
		$event = strtolower(self::DETECT_METHOD_NAME);
		//error_log ("event is " . $event );
		
		//new way joomla 5
		$dispatcher = Factory::getApplication()->getDispatcher();
		$event = new Event('onCheckAnswer', [$post['recaptcha_response_field']]);
		$res = $dispatcher->dispatch('onCheckAnswer', $event);

		// Check if any plugins are attached to the event.
		if (!isset($dispatcher->_methods[$event]) || empty($dispatcher->_methods[$event]))
		{
			// No Plugins Associated To Event
			SHLog::add(Text::_('LIB_SHSSO_DEBUG_15068'), 15068, Log::DEBUG, 'sso');

			return false;
		}

		// Loop through all plugins having a method matching our event
		foreach ($dispatcher->_methods[$event] as $key)
		{
			// Check if the plugin is present.
			if (!isset($dispatcher->_observers[$key]))
			{
				continue;
			}

			// Check if parameters exist for this observer/plugin
			if (property_exists($dispatcher->_observers[$key], 'params'))
			{
				$params = $dispatcher->_observers[$key]->params;

				// Get the rule and list from the plug-in parameters
				$ipRule = $params->get('ip_rule', false);
				$ipList = $params->get('ip_list', false);

				// Check that both the rule and list have been set
				if ($ipRule !== false && $ipList !== false)
				{
					// Get the IP address of this client
					//jimport('joomla.application.input');
					//$input = new JInput($_SERVER);
					//$myIp = $input->get('REMOTE_ADDR', false, 'string');
					$myIp = $_SERVER('REMOTE_ADDR');
					error_log ("ip:" . $myIp);

					// Split the list into newline entries
					$ranges = preg_split('/\r\n|\n|\r/', $ipList);

					if (!SHSsoHelper::doIPCheck($myIp, $ranges, $ipRule))
					{
						// IP address denies this plug-in from executing
						SHLog::add(Text::sprintf('LIB_SHSSO_DEBUG_15064', $dispatcher->_observers[$key]), 15064, Log::DEBUG, 'sso');
						continue;
					}
				}
			}
			else 
				error_log ("_observers don't exist");

			// Fire the event for an object based observer.
			if (is_object($dispatcher->_observers[$key]))
			{
				$args['event'] = $event;
				$value = $dispatcher->_observers[$key]->update($args);
			}

			// Fire the event for a function based observer.
			elseif (is_array($dispatcher->_observers[$key]))
			{
				$value = call_user_func_array($dispatcher->_observers[$key]['handler'], $args);
			}

			if (isset($value) && $value)
			{
				// Check if the detection has been successful for this plug-in
				if (is_string($value) || (is_array($value) && isset($value['username'])))
				{
					if (is_string($value))
					{
						// Convert the string to an array
						$value = array('username' => $value);
					}

					// Store the detection plug-in name
					$value['sso'] = get_class($dispatcher->_observers[$key]);

					// We have a detection result
					return $value;
				}
				else
				{
					// Error: invalid plug-in response
					SHLog::add(Text::sprintf('LIB_SHSSO_ERR_15061', get_class($dispatcher->_observers[$key])), 15061, Log::ERROR, 'sso');

					// Try another plug-in.
					continue;
				}
			}
		}

		// No detection result found.
		return false;*/
		
		/*
		 * When legacy flag is true, it ensures compatibility with JSSOMySite 1.x by
		 * only returning a string username or false can be returned. This also means
		 * keeping compatibility with Joomla 1.6.
		 * When it is set to False, it can return an array and compatible with Joomla 2.5.
		 */
		//$legacy = $this->params->get('use_legacy', false);
		$legacy = false;

		// Get the array key of $_SERVER where the user can be located
		//$serverKey = strtoupper($this->params->get('userkey', 'REMOTE_USER'));
		$serverKey = strtoupper('REMOTE_USER');

		// Get the $_SERVER key and ensure its lowercase and doesn't filter
		if ($legacy)
		{
			// Get the $_SERVER value which should contain the SSO username
			$remoteUser = JRequest::getVar($serverKey, null, 'server', 'string', JREQUEST_ALLOWRAW);
		}
		else
		{
			// Get the $_SERVER value which should contain the SSO username
			$input = new Input($_SERVER);
			$remoteUser = $input->get($serverKey, null, 'USERNAME');
			unset($input);
		}

		// Ensures the returned user is lowercased
		$remoteUser = strtolower($remoteUser);
		//error_log("remote user is " . $remoteUser );

		// Get a username replacement parameter in lowercase and split by semi-colons
		/*$replace_set = explode(';', strtolower($this->params->get('username_replacement', '')));

		foreach ($replace_set as $replacement)
		{
			$remoteUser = str_replace(trim($replacement), '', $remoteUser);
		}		
		*/
		
		//Joomla 4: extends JDispatcher doesn't exist! Could not figure out how to load all sso plugins
		//Workaround: hard load the default http sso plugin inside this class.
		$value['sso'] = 'sso';
		$value = array('username' => $remoteUser);
		
		return $value;
	}

	/**
	 * If a detection has been successful then it will try to
	 * authenticate with the onUserAuthorisation method
	 * in any of the authentication plugins.
	 *
	 * @param   string  $username  String containing detected username.
	 * @param   array   $options   An array containing action, autoregister and detection name.
	 *
	 * @return  JAuthenticationResponse  Response from the authorise.
	 *
	 * @since   1.0
	 */
	public function authorise($username, $options)
	{
		$response = new AuthenticationResponse();
		$response->username = $username;

		// Check for user attributes and set them into the authentication response if they exist
		if (isset($options['attributes']))
		{
			if (isset($options['attributes']['email']))
			{
				$response->email = $options['attributes']['email'];
			}

			if (isset($options['attributes']['fullname']))
			{
				$response->fullname = $options['attributes']['fullname'];
				$response->name = $options['attributes']['fullname'];
			}
		}
		
		//new 04-11-2023: workaround
		$response->name = Factory::getUser($username)->name;
		$response->email = Factory::getUser($username)->email;
		
		//new 04-11-2023
		// Import the authentication and user plug-ins in case they havent already
		// J! Pull Request: https://github.com/joomla/joomla-platform/pull/1305
		PluginHelper::importPlugin('user');
		PluginHelper::importPlugin('authentication');

		// We need to authorise our username to an authentication plugin
		//$authorisations = JAuthentication::authorise($response, $options);
        $authenticate = Authentication::getInstance();
		$authorisations = $authenticate->authorise($response, $options);

            /*
             * Validate that the user should be able to login (different to being authenticated).
             * This permits authentication plugins blocking the user.
             */
            //$authorisations = $authenticate->authorise($response, $options);
           // $denied_states  = Authentication::STATUS_EXPIRED | Authentication::STATUS_DENIED;

			 foreach ($authorisations as $authorisation) {
					
				if ($authorisation->status === Authentication::STATUS_SUCCESS) {
					// This username is authorised to use the system
					$response->status = Authentication::STATUS_SUCCESS;

					return $response;
				}
			}

			// No authorises found
			$response->status = Authentication::STATUS_FAILURE;

		return $response;
	}

	/**
	 * Uses the Joomla! database to authorise. This can be used if
	 * there are no authentication plug-ins available to authorise the
	 * SSO user.
	 *
	 * @param   string  $username  String containing detected username.
	 * @param   array   $options   An array containing action, autoregister and detection name.
	 *
	 * @return  JAuthenticationResponse    Response from the Joomla authorise.
	 *
	 * @since   2.0
	 */
	public function jAuthorise($username, $options)
	{
		$response = new AuthenticationResponse();

		$response->username = $username;

		// Check for user attributes and set them into the authentication response if they exist
		if (isset($options['attributes']))
		{
			if (isset($options['attributes']['email']))
			{
				$response->email = $options['attributes']['email'];
			}

			if (isset($options['attributes']['fullname']))
			{
				$response->fullname = $options['attributes']['fullname'];
			}
		}

		$response->type = 'Joomla';

		// Initialise variables.
		$conditions = '';

		// Get a database object
		$db		= Factory::getDbo();
		$query	= $db->getQuery(true);

		$query->select($db->quoteName('id'))
			->select($db->quoteName('block'))
			->from($db->quoteName('#__users'))
			->where($db->quoteName('username') . ' = ' . $db->quote($username));

		$db->setQuery($query);
		$result = $db->loadObject();

		// Check if the user exists in the database
		if ($result)
		{
			// Check if the user has been blocked
			if ($result->block)
			{
				$response->status = Authentication::STATUS_DENIED;
			}
			else
			{
				// Set the email and fullname to the current one if one doesnt exist
				$user = User::getInstance($result->id);
				$response->email = empty($response->email) ? $user->email : $response->email;
				$response->fullname = empty($response->fullname) ? $user->name : $response->fullname;

				// Set the user defined languages
				if (Factory::getApplication()->isAdmin())
				{
					$response->language = $user->getParam('admin_language');
				}
				else
				{
					$response->language = $user->getParam('language');
				}

				// User exists in database and isnt blocked
				$response->status = Authentication::STATUS_SUCCESS;
				$response->error_message = '';
			}
		}
		else
		{
			// User doesn't exist and is unknown
			$response->status = Authentication::STATUS_UNKNOWN;
		}

		return $response;
	}

	/**
	 * Attempts to login a user via Single Sign On.
	 * Only if a username is detected can a login been attempted.
	 *
	 * @param   array  $detection  Optional SSO user.
	 * @param   array  $options    An optional array of options to override config settings.
	 *
	 * @return  boolean  True on successful login or False on fail.
	 *
	 * @since   1.0
	 * @throws  RuntimeException
	 */
	public function login($detection = null, $options = array())
	{
		$config = SHFactory::getConfig();

		// Get the SSO username and optional details from the plug-ins
		if (is_null($detection))
		{
			$detection = $this->detect();
		}

		if (!$detection)
		{
			return false;
		}

		if (isset($detection['sso']))
			SHLog::add(Text::sprintf('LIB_SHSSO_DEBUG_15066', $detection['username'], $detection['sso']), 15066, Log::DEBUG, 'sso');

		// Set the action if its currently unset
		if (!isset($options['action']))
		{
			$options['action'] = Factory::getApplication()->isClient('admin') ?
				'core.login.admin' : 'core.login.site';
		}

		// Set the autoregister if its currently unset
		if (!isset($options['autoregister']))
		{
			$options['autoregister'] = $config->get('sso.autoregister', false);
		}

		// Set the doauthorise if its currently unset
		if (!isset($options['doauthorise']))
		{
			$options['doauthorise'] = $config->get('sso.doauthorise', self::AUTHORISE_INHERIT);
		}

		$username = $detection['username'];

		// Check if do authorised is based on the plug-in
		if ((int) $options['doauthorise'] === self::AUTHORISE_INHERIT)
		{
			if (isset($detection['doauthorise']))
			{
				// Set the do authorised to the plug-in option
				$options['doauthorise'] = ((boolean) $detection['doauthorise']) ? self::AUTHORISE_TRUE : self::AUTHORISE_FALSE;
			}
			else
			{
				// Default the doauthorise to true
				$options['doauthorise'] = self::AUTHORISE_TRUE;
			}
		}

		// Check for a domain
		if (isset($detection['domain']))
		{
			$options['domain'] = $detection['domain'];
		}

		// Check for any extra user attributes gathered from SSO
		if (isset($detection['attributes']))
		{
			$options['attributes'] = $detection['attributes'];
		}

		/*
		 * Authorising will call on onUserAuthorise() to attempt
		 * to authorise the detected SSO user. If this
		 * is disabled, then it will attempt to authorise with the
		 * Joomla database. If autoregister is turned on then
		 * it'll attempt to create the user in the Joomla database.
		 */
		if ($options['doauthorise'] !== self::AUTHORISE_FALSE)
		{
			// Do authentication plug-in authorisation
			$response = $this->authorise($username, $options);
		}
		else
		{
			// Do Joomla database authorisation
			$response = $this->jAuthorise($username, $options);
		}

		// Check the response status for invalid status'
		if (!((Authentication::STATUS_SUCCESS + Authentication::STATUS_UNKNOWN) && $response->status))
		{
			// We can only process success and unknown status'
			throw new RuntimeException(Text::sprintf('LIB_SHSSO_ERR_15072', $username), 15072);
		}
		elseif (($response->status === Authentication::STATUS_UNKNOWN) && !$options['autoregister'])
		{
			// The user is unknown and there is no autoregister - fail.
			throw new RuntimeException(Text::sprintf('LIB_SHSSO_ERR_15074', $username), 15074);
		}
		elseif (empty($response->email))
		{
			// There is not email set for this user - fail.
			throw new RuntimeException(Text::sprintf('LIB_SHSSO_ERR_15076', $username), 15076);
		}

		/*
		 * Username has been authorised. We can now proceed with the
		 * standard Joomla log-on by calling the onUserLogin event.
		 */
		 if (isset($detection['sso']))
			$options[SHSsoHelper::SESSION_PLUGIN_KEY] = $detection['sso'];
		PluginHelper::importPlugin('user');
		
		$results = Factory::getApplication()->triggerEvent(
			'onUserLogin',
			array((array) $response,
			$options)
		);
		
		//new way joomla 5
		//$dispatcher = Factory::getApplication()->getDispatcher();
		//$UserLoginEvent = new Event('onUserLogin', array((array) $response,	$options));
		//$results = $dispatcher->dispatch('onUserLogin', $UserLoginEvent);
		
		

		// Save the SSO plug-in name for logout later
		$session = Factory::getSession();
		if (isset($detection['sso']))
			$session->set(SHSsoHelper::SESSION_PLUGIN_KEY, $detection['sso']);

		// Check if any of the events failed
		if (in_array(false, $results, true))
		{
			throw new RuntimeException(Text::sprintf('LIB_SHSSO_ERR_15078', $username), 15078);
		}

		SHLog::add(Text::sprintf('LIB_SHSSO_INFO_15079', $username), 15079, Log::INFO, 'sso');

		// Do a check if URL redirect is required
		SHSsoHelper::redirect();

		// Everything successful - user should be logged on.
		return true;
	}

	/**
	 * Calls the logoutRemoteUser method within SSO plug-in if the user
	 * was logged on with SSO.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public function logout()
	{
		$session = Factory::getSession();

		$app = Factory::getApplication();

		// Get the SSO plug-in name from login if we used SSO
		if ($class = $session->get(SHSsoHelper::SESSION_PLUGIN_KEY, false))
		{
			// Lets disable SSO until the user requests login
			SHSsoHelper::disable();

			$router = $app->getRouter();

			// We need to add a callback on the router to tell the routed page we just logged out from SSO
			$router->setVar('ssologoutkey', SHFactory::getConfig()->get('sso.bypasskey', 'nosso'));
			$router->setVar('ssologoutval', $session->get(SHSsoHelper::SESSION_STATUS_KEY, SHSsoHelper::STATUS_ENABLE));
			$router->attachBuildRule('SHSso::logoutRouterRule');

			$index = array_search($class, $this->_observers);

			// Ensure the SSO plug-in is still available
			if ($index !== false && method_exists($this->_observers[$index], 'logoutRemoteUser'))
			{
				$this->_observers[$index]->logoutRemoteUser();
			}
		}
	}

	/**
	 * Router callback rule for appending the SSO logout variable to the URL.
	 *
	 * @param   JRouter  $router  Reference to the router object.
	 * @param   JURI     $uri     Reference to the JURI object.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public static function logoutRouterRule(JRouter $router, JURI $uri)
	{
		$uri->setVar($router->getVar('ssologoutkey'), $router->getVar('ssologoutval'));
	}
}
}
