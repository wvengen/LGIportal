<?php
/**
 * Dwoo include, use this instead of including Dwoo directly.
 *
 * Dwoo may have different include directories, multiple are tried.
 * Also makes sure that a cache directory is setup automatically when not available.
 *
 * @author wvengen
 * @package utilities
 */
/** */
require_once(dirname(__FILE__).'/common.php');
require_once('inc/errors.php');


# try to include from PEAR location or Debian package location
if (!class_exists('Dwoo'))
	@include 'Dwoo/dwooAutoload.php';
if (!class_exists('Dwoo'))
	@include 'dwoo/dwooAutoload.php';

# not found, fatal error
if (!class_exists('Dwoo'))
	trigger_error("Cannot find Dwoo template library", E_USER_ERROR);

/**
 * Dwoo templating object with LGIportal-specific settings.
 *
 * It sets a number of default LGIportal-related variables and tries to find
 * a default compile directory that works.
 *
 * @link http://dwoo.org/_phpdoc/Dwoo/Dwoo.html
 * @package utilities
 */
class LGIDwoo extends Dwoo
{
	/** Session variables to expose in templates by default
	 * @var array */
	protected $session_expose = array('user');

	/** Parses Dwoo template, adds default variables and default template directory.
	 *
	 * Default variables are set using {@link completeData completeData}.
	 * Templates are prefixed with {@link $template_dir $template_dir}.
	 *
	 * @param mixed $_tpl template, can either be a Dwoo_ITemplate object
	 * @param mixed $data Dwoo_Data or array object with variables
	 * @param Dwoo_ICompiler compiler to use, or null
	 * @param bool $_output flag that defines whether the function returns
	 *        the output of the template (false, default) or echoes it
	 *        directly (true)
	 * @link http://dwoo.org/_phpdoc/Dwoo/Dwoo.html#get
	 */
	public function get($_tpl, $data=array(), $_compiler=null, $_output=false)
	{
		# complete data
		$this->completeData($data);
		# add default template include path
		if (is_string($_tpl))
			$_tpl = new Dwoo_Template_File($_tpl);
		$inc = $_tpl->getIncludePath() ? $_tpl->getIncludePath() : array();
		$_tpl->setIncludePath($inc + array(config('TEMPLATE_DIR')));
		# default compiler with auto-escaping
		$cmp = Dwoo_Compiler::compilerFactory();
		$cmp->setAutoEscape(true);
		# parse template
		return parent::get($_tpl, $data, $_compiler, $_output);
	}

	/** Returns a suitable dwoo compile directory.
	 *
	 * This directory must be writable by the user running the
	 * php script. To avoid having to configure yet another
	 * option, this is autodetected.
	 *
	 * If a system-wide compile directory is present as part of
	 * the Dwoo installation, that one is used. If the directory
	 * dwoo_c is present in this application's web root and is
	 * writable, that is used. As a fallback a temporary directory
	 * is created which is used.
	 *
	 * @return string compile directory
	 * @link http://dwoo.org/_phpdoc/Dwoo/Dwoo.html#getCompileDir
	 */
	public function getCompileDir()
	{
		# use default first
		$localdir = DWOO_DIRECTORY.'compiled';
		if ($this->tryCompileDir($localdir))
			return parent::getCompileDir();
		# see if we can, create/use a local compile dir
		$localdir = join(DIRECTORY_SEPARATOR, array(dirname(__FILE__), '..', '..', 'template_c'));
		if ($this->tryCompileDir($localdir))
			return parent::getCompileDir();
		# use temporary directory if all else fails
		$localdir = join(DIRECTORY_SEPARATOR, array(sys_get_temp_dir(), 'LGIportal_template_c'));
		$this->tryCompileDir($localdir);
		return parent::getCompileDir();
	}

	/** Tries to use a compile dir, returns if that succeed.
	 *
	 * The directory is created when it doesn't exist, permissions
	 * are set, and a .htaccess that restricts access is created.
	 * Then it is set as the current compile directory.
	 *
	 * @return bool whether it succeeded or not
	 */
	protected function tryCompileDir($dir)
	{
		$dir = rtrim($dir, '/\\').DIRECTORY_SEPARATOR;
		# create directory if not currently usable
		if (!is_dir($dir)) {
			if (!@mkdir($dir, 02750))
				return false;
			# make sure we have an htaccess for security
			if (!file_exists($dir.'.htaccess'))
				file_put_contents($dir.'.htaccess', "order deny,allow\ndeny from all");
		} elseif (!is_writable($dir)) {
			return false;
		}
		# then set as compile directory
		$this->setCompileDir($dir);
		return true;
	}

	/**
	 * Assigns default variables.
	 *
	 * Assumes session_start() has been called.
	 *
	 * @param mixed $data data to complete, either an array or Dwoo_Data object
	 */
	public function completeData(&$data)
	{
		# expose some session variables
		foreach ($this->session_expose as $var)
		{
			if (!isset($_SESSION[$var])) continue;
			set_dwoo_or_array($data, $var, $_SESSION[$var]);
		}
		# lgi root
		set_dwoo_or_array($data, 'webroot', config('LGI_ROOT'));
		# lgi variables
		$user = isset($_SESSION['user']) ? $_SESSION['user'] : '';
		set_dwoo_or_array($data, 'lgi', array(
			'server'      => config('LGI_SERVER'),
			'project'     => config('LGI_PROJECT'),
			'user'        => $user,
		));
		# if browser is running on windows or not
		set_dwoo_or_array($data, 'ua_windows', preg_match('/windows|win32/i', $_SERVER['HTTP_USER_AGENT']));
		# add error message, if any
		set_dwoo_or_array($data, 'errormessage', getErrorMessage(), true);
		clearErrorMessage();
	}

	/** Outputs a template with data
	 *
	 * @param $tpl template
	 * @param $data data
	 *
	 * @see output
	 * @see get
	 * @link http://dwoo.org/_phpdoc/Dwoo/Dwoo.html#output
	 */
	public static function show($tpl, $data=array())
	{
		$dwoo = new LGIDwoo();
		return $dwoo->output($tpl, $data);
	}
}

/** Set variable in either array or Dwoo_Data
 * @access private */
function set_dwoo_or_array(&$arr, $key, $val, $overwrite=false)
{
	if ($arr instanceof Dwoo_Data && ($overwrite || !isset($arr->$key)))
		$arr->assign($key, $val);
	elseif ($overwrite || !isset($arr[$key]))
		$arr[$key] = $val;
}

?>
