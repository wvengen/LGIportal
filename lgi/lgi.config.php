<?php
/**
 * This file stores the user specified configurations to use this application
 *
 * @package default
 */

/** CA certificate file to be used when requesting to project server */
$LGI_CA_FILE=dirname(__FILE__)."/lgi-ca.crt";

/** Web location where this application is deployed, relative to web root. */
$LGI_ROOT="/lgi";

/** LGI server to communicate with
 *
 * Ideally LGIportal would implement the following behaviour:
 * - when a single value is present, this is enforced
 * - when the value is a array, the user can select one from this list
 * - when the value is empty, the user may input any value
 * This is not yet fully implemented though.
 */
$LGI_SERVER='https://brokkenpiloot.nikhef.nl/LGI/';

/** LGI applications that can be used; first is default
 *
 * (note at _LGI_SERVER_ applies here as well)
 */
$LGI_APPLICATION=array('R-2.11', 'R-2.12', 'helloworld');

/** LGI repositories to allow
 *
 * Each repository location accessed through the portal must begin
 * with one of these. Since LGIportal serves as a proxy when accessing
 * repositories, this is an important security measure.
 *
 * Defaults to {@link $LGI_SERVER $LGI_SERVER}, since in the default LGI
 * setup repositories are located on the project server.
 */
$LGI_REPOSITORY=$LGI_SERVER;

/** Hostname of MySQL database server
 *
 * Database details are sensitive. You may want to put this variable,
 * together with the other database variables, in a separate file outside
 * of the web root. Then require() that file here.
 */
$MYSQL_SERVER='localhost';
/** Database username */
$MYSQL_USER='root';
/** Database password */
$MYSQL_PASSWORD='';
/** Database name */
$MYSQL_DBNAME='lgi';
/** Table prefix to use (leave empty unless required) */
$MYSQL_TBLPREFIX='';

/** SimpleSAMLphp installation location (optional).
 *
 * To use other authentication mechanisms than a local username/password
 * combination, SimpleSAMLphp can be used. Please see INSTALL.md for
 * more information.
 *
 * @see http://www.simplesamlphp.org/
 *
 * This variable should point to the full codebase, not just its web root. That
 * means that the file $SIMPLESAMLPHP_DIR/lib/_autoload.php should exist.
 */
$SIMPLESAMLPHP_DIR='/home/wvengen/wrk/oauth2lib/simplesamlphp';


/*
 * You probably won't need to change the following settings.
 */

/** SimpleSAMLphp attributes for user identification.
 *
 * The first non-null attribute of this array will be looked up in the
 * authid field of the auth_simplesamlphp table.
 */
$SIMPLESAMLPHP_ATTR_USER=array(
	'urn:mace:dir:attribute-def:eduPersonPrincipalName',
	'eduPersonPrincipalName',
	'twitter_screen_n_realm',
);

/** File system location where this application is installed. */
$LGI_PREFIX=dirname(__FILE__);

/** File system location of template directory */
$TEMPLATE_DIR=$LGI_PREFIX.'/template';

/** Web location of index.php. You could customize this for use with mod_rewrite. */
$LGI_APPROOT=$LGI_ROOT.'/index.php';

/** Default page to show after login */
$LGI_DEFAULTPAGE='jobs';

/** Version of LGIportal */
$LGI_VERSION='0.4';
/** Hexadecimal version of LGIportal (useful to check version in code) */
$LGI_HEXVERSION=0x040000;

?>
