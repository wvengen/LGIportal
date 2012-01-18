Authentication in LGIportal
===========================

All pages require an authenticated user (except the login page, naturally).
For ease of use, local username/password based accounts are part of LGIportal.
For more flexibility, authentication can be done using [SimpleSAMLphp][],
bringing SAML SSO, OpenID, Twitter and Facebook support, and more.
Both mechanisms can be used at the same time.


Local users
-----------
The easiest way to get started is to use local users (the default when creating
a new user with [createuser.php][]). A local password hash is stored in the
database in the `users` table. Users can change their password on the settings
page.


External authentication
-----------------------
To use more advanced authentication options, one needs to install
SimpleSAMLphp. This is a web application that needs to be setup separately from
LGIportal first, as a service provider (SP). Configure the authentication
sources that you want to use.

To integrate it with LGIportal, set `$SIMPLESAMLPHP_DIR` in the [lgi config][]
to point to the SimpleSAMLphp installation (the full codebase, not just its
web-accessible part; `$SIMPLESAMLPHP_DIR/lib/_autoload.php` must be present).

SimpleSAMLphp returns a list of attributes after successful login. Which one of
these is used as a user identifier, is configured by `$SIMPLESAMLPHP_ATTR_USER`
in the [lgi config][]; the first non-null attribute found for the authsource is
looked up in the table `auth_simplesamlphp` to obtain the portal username.

To log in using a specific SimpleSAMLphp authentication source (defined in its
`config/authsources.php`), append the authentication source name to the login
url. When the LGIportal website is at <http://example.org/lgi/>, the url to
login using the `my-saml` authsource would become
<http://example.org/lgi/index.php/login/my-saml>.
Please note that `local` can not be used as an authsource name, since that
is used to do LGIportal's local authentication.

You probably need to update `templates/login.tpl` to give users an option
to login with the authsources you configured.


SimpleSAMLphp authentication sources
------------------------------------
Authsources are configured in SimpleSAMLphp's `config/authsources.php`. There
are numerous examples already present in that file. For SAML-based authsources,
LGIportal expects an [eduPersonPrincipalName][] attribute (or its scoped
variant). For some other common services like Twitter, Google and Facebook
some attributes have been defined by default; in other cases you may need
to update `$SIMPLESAMLPHP_ATTR_USER` in the [lgi config][].

Some authsource entries may need some extra attention. To enable Google as
authentication source, the `openid` module needs to be enabled in SimpleSAMLphp
by creating the (empty) file `modules/openid/enable` in the SimpleSAMLphp
codebase. Then email attribute should be requested from `authsources.php`:

```php
<?php
$config = array(
  // Authsource that authenticates against Google
  'google' => array(
    'openid:OpenIDConsumer',
    'target' => 'https://www.google.com/accounts/o8/id',
    'attributes.ax_required' => array('http://axschema.org/contact/email'),
  ),
);
?>
```

Just because it's possible: here is an authsource that authenticates against
LGIportal's local user database.

```php
<?php
// at beginning of authsources.php read LGI database config
global $MYSQL_SERVER, $MYSQL_DBNAME, $MYSQL_USER, $MYSQL_PASSWORD;
include_once('/path/to/lgi/lgi.config.php');

$config = array(
  // Local auth test using MySQL's crypt() (does not work on Windows) :)
  'local2' => array(
    'sqlauth:SQL',
    'dsn' => 'mysql:host='.$MYSQL_SERVER.';dbname='.$MYSQL_DBNAME,
    'username' => $MYSQL_USER,
    'password' => $MYSQL_PASSWORD,
    'query' => 'SELECT CONCAT(`name`,"@LGIportal") AS `eduPersonPrincipalName` FROM `users` WHERE `name`=:username AND `passwd_hash`=ENCRYPT(:password,`passwd_hash`)',
  ),
);
?>
```


[SimpleSAMLphp]: http://www.simplesamlphp.org/
[eduPersonPrincipalName]: http://middleware.internet2.edu/eduperson/
[lgi config]: lgi/lgi.config.php
[createuser.php]: lgi/createuser.php

