<?php
/**
 * @package    Joomla.JEDChecker
 *
 * @copyright  Copyright (C) 2017 - 2019 Open Source Matters, Inc. All rights reserved.
 * 			   Copyright (C) 2008 - 2016 compojoom.com . All rights reserved.
 * @author     Daniel Dimitrov <daniel@compojoom.com>
 *             eaxs <support@projectfork.net>
 *
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;


/**
 * class JEDcheckerRule
 *
 * Serves as a base class for all JED rules.
 *
 * @since  1.0
 */
class JEDcheckerRule extends JObject
{
	/**
	 * The formal ID of this rule. For example: SE1.
	 *
	 * @var    string
	 */
	protected $id;

	/**
	 * The title or caption of this rule.
	 *
	 * @var    string
	 */
	protected $title;

	/**
	 * The description of this rule.
	 *
	 * @var    string
	 */
	protected $description;

	/**
	 * The ordering value to sort rules in the menu.
	 *
	 * @var    integer
	 */
	public static $ordering = 10000;

	/**
	 * The absolute path to the target extension.
	 *
	 * @var    string
	 */
	protected $basedir;

	/**
	 * Optional rule parameters.
	 *
	 * @var    object
	 */
	protected $params;

	/**
	 * The report summary
	 *
	 * @var    array
	 */
	protected $report;

	/**
	 * Constructor. Initialises variables.
	 *
	 * @param   mixed  $properties  - See JObject::__construct
	 */
	public function __construct($properties = null)
	{
		// Construct JObject
		parent::__construct($properties);

		// Initialise vars
		if (empty($this->report))
		{
			// Create a new report
			require_once JPATH_COMPONENT_ADMINISTRATOR . '/models/item.php';
			require_once JPATH_COMPONENT_ADMINISTRATOR . '/models/report.php';
			$this->report = new JEDcheckerReport($properties);
		}

		// Try to load the params
		if (empty($this->params))
		{
			$this->params = $this->loadParams();
		}
	}

	/**
	 * Performs the rule check. This method should be overloaded!
	 *
	 * @return void
	 */
	public function check()
	{
		// Overload this method
	}

	/**
	 * Attempts to load a .ini param file of this rule.
	 *
	 * @return    Joomla\Registry\Registry
	 */
	protected function loadParams()
	{
		// Try to determine the name and location of the params file
		$file_name = str_replace('jedcheckerrules', '', strtolower(get_class($this)));
		$params_file = JPATH_COMPONENT_ADMINISTRATOR . '/libraries/rules/' . $file_name . '.ini';

		$params = new Registry('jedchecker.rule.' . $file_name);
		//$params = $registry->getInstance('jedchecker.rule.' . $file_name);

		//$params = Joomla\Registry\Registry::getInstance('jedchecker.rule.' . $file_name);

		// Load the params from the ini file
		if (file_exists($params_file))
		{
			// Due to a bug in Joomla 2.5.6, this method cannot be used
			// $params->loadFile($params_file, 'INI');

			// Get the contents of the file
			$data = file_get_contents($params_file);

			if ($data)
			{
				$obj = (object) parse_ini_string($data);

				if (is_object($obj))
				{
					$params->loadObject($obj);
				}
			}
		}

		return $params;
	}
}
