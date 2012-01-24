<?php
/**
 * PHP compatibility functions
 *
 * To allow running this on something that is not the latest and
 * greatest but is stable and maintainable.
 *
 * @author wvengen
 * @package utilities
 */
/** */

if ( !function_exists('sys_get_temp_dir')) {
	function sys_get_temp_dir() {
		// TODO cache result, perhaps
		// canonical variable
		if ($tmp=getenv('TMPDIR')) return $tmp;
		// sometimes used as well
		if ($tmp=getenv('TMP')) return $tmp;
		if ($tmp=getenv('TEMP')) return $tmp; // Windows
		if ($tmp=getenv('TEMPDIR')) return $tmp;
		// figure out by creating a tempfile
		$tmp=tempnam(__FILE__, '');
		if (file_exists($tmp)) {
			unlink($tmp);
			return dirname($tmp);
		}
		return null;
	}
}

?>
