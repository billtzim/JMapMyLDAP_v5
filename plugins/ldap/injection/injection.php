<?php
/**
<<<<<<< HEAD
 * PHP Version 5.3
=======
 * PHP Version 8.1
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
 *
 * @package     Shmanic.Plugin
 * @subpackage  Ldap.Injection
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
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8

/**
 * LDAP Form Injection Plugin
 *
 * @package     Shmanic.Plugin
 * @subpackage  Ldap.Injection
 * @since       2.0
 */
<<<<<<< HEAD
class PlgLdapInjection extends JPlugin
=======
class PlgLdapInjection extends CMSPlugin
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
{
	protected $passwordForms = array();

	protected $domainForms = array();

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

		// Split and trim the permitted forms
		$this->passwordForms = explode(';', $this->params->get('ldap_password_forms'));
		array_walk($this->passwordForms, 'self::_trimValue');

		$this->domainForms = explode(';', $this->params->get('ldap_domain_forms'));
		array_walk($this->domainForms, 'self::_trimValue');
	}

	/**
	 * Trims an array's elements. Use with array_walk.
	 *
	 * @param   string  &$value  Value of element.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	private function _trimValue(&$value)
	{
		$value = trim($value);
	}

	/**
	 * Injects several fields into specific forms.
	 *
<<<<<<< HEAD
	 * @param   JForm  $form  The form to be altered.
=======
	 * @param   Form  $form  The form to be altered.
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
	 * @param   array  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   2.0
	 */
	public function onContentPrepareForm($form, $data)
	{
		// Check we are manipulating a valid form
<<<<<<< HEAD
		if (!($form instanceof JForm))
=======
		if (!($form instanceof Form))
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		// Check if the password field needs injecting
		if (($this->params->get('use_ldap_password', false)) && (in_array($form->getName(), $this->passwordForms)))
		{
			// Check if this user should have a profile
			if (SHLdapHelper::isUserLdap(isset($data->id) ? $data->id : 0))
			{
				if ($this->params->get('ldap_password_layout_edit', true))
				{
					// Check if this is in the 'edit' layout or in the save state
<<<<<<< HEAD
					if ((strtolower(JFactory::getApplication()->input->get('layout')) === 'edit')
						|| (strtolower(JFactory::getApplication()->input->get('task')) === 'save'))
=======
					if ((strtolower(Factory::getApplication()->input->get('layout')) === 'edit')
						|| (strtolower(Factory::getApplication()->input->get('task')) === 'save'))
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
					{
						$form->loadFile(realpath(__DIR__) . '/forms/ldap_password.xml', false, false);
					}
				}
				else
				{
					$form->loadFile(realpath(__DIR__) . '/forms/ldap_password.xml', false, false);
				}
			}
		}

		// Check if the domain field needs injecting
		if (($this->params->get('use_ldap_domain', false)) && (in_array($form->getName(), $this->domainForms)))
		{
			$form->loadFile(realpath(__DIR__) . '/forms/ldap_domain.xml', false, false);
		}

		return true;
	}
}
