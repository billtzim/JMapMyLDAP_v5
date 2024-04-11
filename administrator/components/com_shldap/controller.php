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
 * @edited		2024
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
 * @copyright   Copyright (C) 2011-2013 Shaun Maunder. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

<<<<<<< HEAD
jimport('joomla.application.component.controlleradmin');
=======
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

//jimport('joomla.application.component.controlleradmin');
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8

/**
 * Base controller class for Shldap.
 *
 * @package     Shmanic.Components
 * @subpackage  Shldap
 * @since       2.0
 */
<<<<<<< HEAD
class ShldapController extends JControllerLegacy
=======
class ShldapController extends BaseController
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8
{
	/**
	 * The default view.
	 *
	 * @var    string
	 * @since  2.0
	 */
	protected $default_view = 'dashboard';

	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController  A JController object to support chaining.
	 *
	 * @since	2.0
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Get the document object.
<<<<<<< HEAD
		$document = JFactory::getDocument();

		// Get the input class
		$input = JFactory::getApplication()->input;
=======
		$document = Factory::getDocument();

		// Get the input class
		$input = Factory::getApplication()->input;
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8

		// Set the default view name and format from the Request.
		$vName	 = $input->get('view', 'dashboard', 'cmd');
		$vFormat = $document->getType();
		$lName	 = $input->get('layout', 'default', 'cmd');
		$id		 = $input->get('id', null, 'cmd');

		// Check for edit form.
		if ($vName == 'host' && $lName == 'edit' && !$this->checkEditId('com_shldap.edit.host', $id))
		{
			// Somehow the person just went to the form - we don't allow that.
<<<<<<< HEAD
			$this->setRedirect(JRoute::_('index.php?option=com_shldap&view=hosts', false));
=======
			$this->setRedirect(Route::_('index.php?option=com_shldap&view=hosts', false));
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8

			return false;
		}

		// Add the submenu
		ComShldapHelper::addSubmenu($vName);

		parent::display($cachable, $urlparams);

		return $this;
	}
}
