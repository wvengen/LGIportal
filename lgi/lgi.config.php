<?php
/**
 * This file stores the user specified configurations to use this application
 * 
 */

/**
* $DB_CONFIG_FILE - Absolute Path to file where Database access details are specified. Expecting a php file.
*/
$DB_CONFIG_FILE=dirname(__FILE__)."/php/includes/db.inc.php";
/**
 * CA certificate file to be used when requesting to project server
 */
$CA_FILE=dirname(__FILE__)."/lgi-ca.crt";

/**
 * _AUTH_MECHANISM_ - defines which authentication mechanism to use. Currently two methods are possible.
 *  1. Form based - username and password stored in database
 *  2. HTTP digest authentication
 */
define('_AUTH_MECHANISM_',"DATABASE");  //Possible "DATABASE","DIGEST"

/**
 * _LGI_ROOT_ - Defines the root folder where this application is deployed. The path should be with respect to web root.
 */
define('_LGI_ROOT_',"/lgi");

/**
 * _LGI_SERVER_ - LGI server to communicate with
 *
 * Ideally LGIportal would implement the following behaviour:
 * - when a single value is present, this is enforced
 * - when the value is an array, the user can select one from this list
 * - when the key is not present, the user may input any value
 * This is not yet fully implemented though.
 */
define('_LGI_SERVER_', 'https://example.com/LGI/');

/**
 * _LGI_PROJECT_ - LGI project on server to work on
 *
 * (note at _LGI_SERVER_ applies here as well)
 */
define('_LGI_PROJECT_', 'helloworld');

/**
 * _LGI_APPLICATION- LGI application to use
 *
 * (note at _LGI_SERVER_ applies here as well)
 */
define('_LGI_APPLICATION_', 'helloworld');
 
?>
