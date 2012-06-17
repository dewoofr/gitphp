<?php
/**
 * GitPHP ProjectList
 *
 * Project list singleton instance and factory
 *
 * @author Christopher Han <xiphux@gmail.com>
 * @copyright Copyright (c) 2010 Christopher Han
 * @package GitPHP
 * @subpackage Git
 */

require_once(GITPHP_GITOBJECTDIR . 'projectlist/ProjectListDirectory.class.php');
require_once(GITPHP_GITOBJECTDIR . 'projectlist/ProjectListFile.class.php');
require_once(GITPHP_GITOBJECTDIR . 'projectlist/ProjectListArray.class.php');
require_once(GITPHP_GITOBJECTDIR . 'projectlist/ProjectListArrayLegacy.class.php');
require_once(GITPHP_GITOBJECTDIR . 'projectlist/ProjectListScmManager.class.php');

/**
 * ProjectList class
 *
 * @package GitPHP
 * @subpackage Git
 */
class GitPHP_ProjectList
{

	/**
	 * instance
	 *
	 * Stores the singleton instance of the projectlist
	 *
	 * @access protected
	 * @static
	 */
	protected static $instance = null;

	/**
	 * GetInstance
	 *
	 * Returns the singleton instance
	 *
	 * @access public
	 * @static
	 * @return mixed instance of projectlist
	 * @throws Exception if projectlist has not been instantiated yet
	 */
	public static function GetInstance()
	{
		return self::$instance;
	}

	/**
	 * DestroyInstance
	 *
	 * Releases the singleton instance
	 *
	 * @access public
	 * @static
	 */
	public static function DestroyInstance()
	{
		self::$instance = null;
	}

	/**
	 * Instantiate
	 *
	 * Instantiates the singleton instance
	 *
	 * @access private
	 * @static
	 * @param string $file config file with git projects
	 * @param boolean $legacy true if this is the legacy project config
	 * @throws Exception if there was an error reading the file
	 */
	public static function Instantiate($file = null, $legacy = false)
	{
		if (self::$instance)
			return;
			
		$projectRoot = GitPHP_Config::GetInstance()->GetValue('projectroot');


		if (!empty($file) && is_file($file) && include($file)) {
			if (isset($git_projects)) {
				if (is_string($git_projects)) {
					if (function_exists('simplexml_load_file') && GitPHP_ProjectListScmManager::IsSCMManager($git_projects)) {
						self::$instance = new GitPHP_ProjectListScmManager($projectRoot, $git_projects);
					} else {
						self::$instance = new GitPHP_ProjectListFile($projectRoot, $git_projects);
					}
				} else if (is_array($git_projects)) {
					if ($legacy) {
						self::$instance = new GitPHP_ProjectListArrayLegacy($projectRoot, $git_projects);
					} else {
						self::$instance = new GitPHP_ProjectListArray($projectRoot, $git_projects);
					}
				}
			}
		}

		if (!self::$instance) {

			self::$instance = new GitPHP_ProjectListDirectory($projectRoot, GitPHP_Config::GetInstance()->GetValue('exportedonly', false));
		}

		if (isset($git_projects_settings) && !$legacy)
			self::$instance->SetSettings($git_projects_settings);
	}

}
