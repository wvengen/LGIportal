<?php
/**
*	Dwoo include, use this instead of including Dwoo directly.
*
*	Dwoo may have different include directories, multiple are tried.
*       This makes sure that a cache directory is setup automatically when not available.
*
*	@author wvengen
*/

# try to include from PEAR location or Debian package location
if (!class_exists('Dwoo'))
	@include 'Dwoo/dwooAutoload.php';
if (!class_exists('Dwoo'))
	@include 'dwoo/dwooAutoload.php';

# not found, fatal error
if (!class_exists('Dwoo'))
	trigger_error("Cannot find Dwoo template library", E_USER_ERROR);

/**
 *	Dwoo templating object with LGIportal-specific settings.
 *
 *	It sets a number of default LGIportal-related variables and tries to find
 *	a default compile directory that works.
 */
class LGIDwoo extends Dwoo
{
	public function getCompileDir()
	{
		# use default first
		$localdir = DWOO_DIRECTORY.'compiled';
		if ($this->tryCompileDir($localdir))
			return parent::getCompileDir();
		# see if we can, create/use a local compile dir
		$localdir = join(DIRECTORY_SEPARATOR, array(dirname(__FILE__), '..', '..', 'dwoo_c'));
		if ($this->tryCompileDir($localdir))
			return parent::getCompileDir();
		# use temporary directory if all else fails
		$localdir = join(DIRECTORY_SEPARATOR, array(sys_get_temp_dir(), 'LGIportal_dwoo_c'));
		$this->tryCompileDir($localdir);
		return parent::getCompileDir();
	}

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
}

?>
