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
     * **login.php** - user account functions
     * **sessions.php** - session handling
     * **db.php** - default database configuration file, referenced by
                    lgi.config.php

All pages are accessed using index.php. PATH\_INFO is used to have clean URLs. In
index.php, PATH\_INFO is parsed into $argv, which is accessible to all pages. The
first (or actually zeroth) element is the page name; the php file included from
page/ with the same name is require()d.


Authentication
==============

The system implements a form based user login. The username and hashed
passwords are stored in a [MySQL][] database. Now we use SHA512 hashing with
salt to store password. This can be easily changed. Username and password are
compared against the data in database for authenticating a user. When a user is
authenticated, a new session is created for that user. Further activities of
user uses this session. Application expects an SSL connection between server
and client.

While there has been an option for http-digest authentication in LGIportal,
this has been removed from the current version because of maintenance issues.


Database
========

(TODO)

[Dwoo]: http://www.dwoo.org/
[MySQL]: http://www.mysql.org/
[LGI]: http://gliteui.wks.gorlaeus.net/LGI/

