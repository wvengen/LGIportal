How to deploy the LGI portal
============================

Requirements
------------

0. An [LGI] deployment, ready to use
1. [Apache][] webserver
2. [PHP] 5+ with [MySQL][PHP-MySQL], [Curl][PHP-Curl] and [JSON][PHP-JSON] support.
     PHP 5.2.0 has the JSON module built-in, otherwise you may need to install it
     from PECL: `pecl install json`.
3. [Dwoo][] templating library.
    An installed system or PEAR package is sufficient (LGIportal tries
    to load both Dwoo/dwooAutoload.php or dwoo/dwooAutoload.php,
    whichever succeeds first).
4. [MySQL][] server


Setting up the database
-----------------------

1. Create a MySQL database and create a user with full permissions on that database.

2. Create initial tables from db.sql
       
2. Configure database access details in php/includes/db.inc.php
     If you want to put db.inc.php in a more secure place (like outside Apache's
     document root), configure the new path in lgi.config.php


Configuring the portal
----------------------

1. Copy the lgi/ folder to the webroot.

2. Modify configuration in lgi.config.php
   a. make sure _LGI_ROOT_ matches the subdirectory where the website is installed
   b. set the LGI project server, project and application(s) to the your LGI setup you're using
   c. get the LGI project server's CA certificate and make $CA_FILE point to that
   d. shortly review the other options

3. *(optional)* create a directory dwoo_c and give the PHP user full permissions.
     This can be skipped, then a directory will be created in the system's
     temporary directory. Creating dwoo_c manually may increase performance
     and stability.


Setting up users
----------------

You need a valid LGI certificate and private key. Then use the script
createuser.php, for example like this:

    cd /var/www/lgi/
    php createuser.php john s3cretpw /home/lgi/cert/john.crt /home/lgi/cert/john.key

This creates a user 'john' with password 's3cretpw' that has a key and certificate in
the directory /home/lgi/cert/.


Putting LGIportal on the LGI project server
-------------------------------------------

For small setups it may be useful to put LGIportal on the same web server and virtual
host as the LGI project server. The latter requires HTTPS, and it is highly
recommended to use it as well for LGIportal.

This does require one to set [SSLVerifyClient][] to optional: the LGI project server
requires certificates to interact, while LGIportal relies on username/password
authentication. Since this can not be set on a directory-basis but applies to the
virtual host, access with and without certificates should be allowed.

Please note that not all browsers will be able to use optional client certificates,
like some versions of Internet Explorer and Safari on the Mac.



[LGI]: http://gliteui.wks.gorlaeus.net/LGI/
[Apache]: http://httpd.apache.org/
[PHP]: http://php.net/
[PHP-MySQL]: http://php.net/manual/en/book.mysql.php
[PHP-Curl]: http://php.net/manual/en/book.curl.php
[MySQL]: http://www.mysql.org/
[Dwoo]: http://dwoo.org/                
[SSLVerifyClient]: http://httpd.apache.org/docs/current/mod/mod_ssl.html#sslverifyclient
