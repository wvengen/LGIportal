<?php
/**
 * This file stores the user specified configurations to use this application
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
$LGI_SERVER='https://example.com/LGI/';

/** LGI project on server to work on
 *
 * (note at _LGI_SERVER_ applies here as well)
 */
$LGI_PROJECT='helloworld';

/** LGI applications that can be used; first is default
 *
 * (note at _LGI_SERVER_ applies here as well)
 */
$LGI_APPLICATION=array('R', 'helloworld');

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


/*
 * You probably won't need to change the following settings.
 */

/** Absolute path to file where database access details are specified. Expecting a php file. */
$DB_CONFIG_FILE=dirname(__FILE__)."/php/includes/db.inc.php";

/** File system location where this application is installed. */
$LGI_PREFIX=dirname(__FILE__);

/** File system location of template directory */
$TEMPLATE_DIR=$LGI_PREFIX.'/dwoo';

?>
