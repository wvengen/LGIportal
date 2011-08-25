LGIportal source code overview
==============================

* **lgi**
  * **lgi.config.php** - main configuration file
  * **css** - stylesheets and dependant images
  * **js** - javascript
  * **dwoo** - [Dwoo][] templates for the portal's pages
  * **php**
    * **icons** - icons used in portal
    * **LGI** - code for accessing the [LGI][] project server
    * **\*.php** - pages to be accessed using the web browser when logged in
    * **utilities** 
      * **common.php** - general stuff, included in most files
      * **dwoo.php** - template functionality, wraps Dwoo
      * **errors.php** - error handling
      * **jobs.php** - for communication with LGI project server, wraps LGIClient
      * **login.php** - user account functions
      * **sessions.php** - session handling
    * **includes**
      * **db.inc.php** - default database configuration file, referenced by
        lgi.config.php


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

Two tables are currently used:

1. For user account details: **users**

        +--------------+--------------+------+-----+---------+-------+
        | Field        | Type         | Null | Key | Default | Extra |
        +--------------+--------------+------+-----+---------+-------+
        | userId       | varchar(20)  | NO   | PRI | NULL    |       | 
        | passwordHash | varchar(150) | YES  |     | NULL    |       | 
        | salt         | varchar(50)  | YES  |     | NULL    |       | 
        +--------------+--------------+------+-----+---------+-------+
   Passwords are hashed with a salt using the SHA512 algorithm.
     

2. For user certificates and keys: **usercertificates**

        +-------------+--------------+------+-----+---------+-------+
        | Field       | Type         | Null | Key | Default | Extra |
        +-------------+--------------+------+-----+---------+-------+
        | userId      | varchar(20)  | NO   | PRI | NULL    |       | 
        | certificate | varchar(100) | YES  |     | NULL    |       | 
        | userkey     | varchar(100) | YES  |     | NULL    |       | 
        +-------------+--------------+------+-----+---------+-------+
   Certificate and userkey are currently full paths to a user's LGI
   certificate and private key. In the future this may change to be
   the key and certificate itself.


[Dwoo]: http://www.dwoo.org/
[MySQL]: http://www.mysql.org/
[LGI]: http://gliteui.wks.gorlaeus.net/LGI/

