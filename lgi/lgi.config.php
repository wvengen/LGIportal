<?php
/**
 * This file stores the user specified configurations to use this application
 */

/** CA certificate file to be used when requesting to project server */
$CA_FILE=dirname(__FILE__)."/lgi-ca.crt";

/** Authentication mechanism to use. Currently two methods are possible.
 *  1. Form based - username and password stored in database
 *  2. HTTP digest authentication
 */
define('_AUTH_MECHANISM_',"DATABASE");  //Possible "DATABASE","DIGEST"

/** Web location where this application is deployed, relative to web root. */
define('_LGI_ROOT_',"/lgi");

/** LGI server to communicate with
 *
 * Ideally LGIportal would implement the following behaviour:
 * - when a single value is present, this is enforced
 * - when the value is an semicolon-separated list, the user can select one from this list
 * - when the value is empty, the user may input any value
 * This is not yet fully implemented though.
 */
define('_LGI_SERVER_', 'https://example.com/LGI/');

/** LGI project on server to work on
 *
 * (note at _LGI_SERVER_ applies here as well)
 */
define('_LGI_PROJECT_', 'helloworld');

/** LGI applications that can be used; first is default
 *
 * (note at _LGI_SERVER_ applies here as well)
 */
define('_LGI_APPLICATION_', 'R; helloworld');


/*
 * You probably won't need to change the following settings.
 */

/** Absolute path to file where database access details are specified. Expecting a php file. */
$DB_CONFIG_FILE=dirname(__FILE__)."/php/includes/db.inc.php";

/** File system location where this application is installed. */
define('_LGI_PREFIX_',dirname(__FILE__));

/** File system location of template directory */
define('_TEMPLATE_DIR_', _LGI_PREFIX_.'/dwoo');

?>
