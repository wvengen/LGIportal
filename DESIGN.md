LGIportal source code overview
==============================

* **lgi**
  * **index.php** - main program
  * **lgi.config.php** - main configuration file
  * **css** - stylesheets and dependant images
  * **js** - javascript
  * **icons** - icons used for buttons and indicators
  * **page** - each page has a corresponding php file here
  * **template** - [Dwoo][] templates for the portals pages
  * **inc** 
     * **LGI** - code for accessing the [LGI][] project server
     * **common.php** - general stuff, included in all files
     * **dwoo.php** - template functionality, wraps Dwoo
     * **errors.php** - error handling
     * **jobs.php** - for communication with LGI project server, wraps LGIClient
     * **user.php** - user account functions, including login
     * **sessions.php** - session handling
     * **db.php** - database handling

All pages are accessed using index.php. PATH\_INFO is used to have clean URLs. In
index.php, PATH\_INFO is parsed into $argv, which is accessible to all pages. The
first (or actually zeroth) element is the page name; the php file included from
page/ with the same name is require()d.


Authentication
==============

The system implements a form based user login. The username and hashed
passwords are stored in a [MySQL][] database (using the [modular crypt
format][]). Further activities of user uses this session. The portal expects an
SSL connection between server and client.

While there has been an option for http-digest authentication in LGIportal,
this has been removed from the current version because of maintenance issues.

Authentication using [SimpleSAMLphp][] is on the roadmap, which would enable
SAML single sign-on.


Database
========

All information is stored in a [MySQL][] database, except the LGI key and
certificate files, which are stored on the web server (though these should
_not_ be accessible to the web server!).

Each user has a single LGI key and certificate, referenced from the table
`usercerts`. When the certificate is imported into the database, it is
parsed and the relevant properties are stored the tables `usergroups` and
`userprojects`.


[Dwoo]: http://www.dwoo.org/
[MySQL]: http://www.mysql.org/
[LGI]: http://gliteui.wks.gorlaeus.net/LGI/
[modular crypt format]: http://packages.python.org/passlib/modular_crypt_format.html
[SimpleSAMLphp]: http://simplesamlphp.org/

