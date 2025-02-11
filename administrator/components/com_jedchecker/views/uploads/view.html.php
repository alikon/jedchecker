<?php
/**
 * @package    Joomla.JEDChecker
 *
 * @copyright  Copyright (C) 2017 - 2019 Open Source Matters, Inc. All rights reserved.
 * 			   Copyright (C) 2008 - 2016 compojoom.com . All rights reserved.
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 *
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.viewlegacy');

/**
 * Class JedcheckerViewUploads
 *
 * @since  1.0
 */
class JedcheckerViewUploads extends JViewLegacy
{
	/** @var string */
	protected $path;

	/** @var array */
	protected $jsOptions;

	/**
	 * Display method
	 *
	 * @param   string  $tpl  - the template
	 *
	 * @return mixed|void
	 */
	public function display($tpl = null)
	{
		$this->path = JFactory::getConfig()->get('tmp_path') . '/jed_checker';

		// Load translation for "JED Checker" title from sys.ini file
		JFactory::getLanguage()->load('com_jedchecker.sys', JPATH_ADMINISTRATOR);

		$this->setToolbar();
		$this->jsOptions['url'] = JUri::base();
		$this->jsOptions['rules'] = $this->getRules();
		parent::display($tpl);
	}

	/**
	 * Get the component rules
	 *
	 * @return array
	 */
	public function getRules()
	{
		$rules = array();
		$files = JFolder::files(JPATH_COMPONENT_ADMINISTRATOR . '/libraries/rules', '\.php$', false, false);

		JLoader::discover('jedcheckerRules', JPATH_COMPONENT_ADMINISTRATOR . '/libraries/rules/');

		foreach ($files as $file)
		{
			$rule = substr($file, 0, -4);
			$class = 'jedcheckerRules' . ucfirst($rule);

			if (class_exists($class) && is_subclass_of($class, 'JEDcheckerRule'))
			{
				$rules[$rule] = $class::$ordering;
			}
		}

		asort($rules, SORT_NUMERIC);
		$rules = array_keys($rules);

		return $rules;
	}

	/**
	 * Creates the toolbar options
	 *
	 * @return void
	 */
	public function setToolbar()
	{
		if ($this->filesExist('unzipped'))
		{
			JToolbarHelper::custom('check', 'search', 'search', JText::_('COM_JEDCHECKER_TOOLBAR_CHECK'), false);
		}

		JToolbarHelper::title(JText::_('COM_JEDCHECKER'));

		if (file_exists($this->path))
		{
			JToolbarHelper::custom('uploads.clear', 'delete', 'delete', JText::_('COM_JEDCHECKER_TOOLBAR_CLEAR'), false);
		}

		if (JFactory::getUser()->authorise('core.admin', 'com_jedchecker'))
		{
			JToolbarHelper::preferences('com_jedchecker');
		}
	}

	/**
	 * Checks if folder + files exist in the jed_checker tmp path
	 *
	 * @param   string  $type  - action
	 *
	 * @return boolean
	 */
	private function filesExist($type)
	{
		$path = JFactory::getConfig()->get('tmp_path') . '/jed_checker/' . $type;

		if (JFolder::exists($path))
		{
			if (JFolder::folders($path) || JFolder::files($path))
			{
				return true;
			}
		}
		else
		{
			$local = JFactory::getConfig()->get('tmp_path') . '/jed_checker/local.txt';

			if ($type === 'unzipped' && JFile::exists($local))
			{
				return true;
			}
		}

		return false;
	}
}
