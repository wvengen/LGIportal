-------
-----
----- LGIportal database structure
-----
-------


---
--- users and their properties
---
CREATE TABLE users (
	id            INTEGER PRIMARY KEY AUTO_INCREMENT,
	--- username
	name          VARCHAR(20) PRIMARY KEY,
	--- local password
	passwd_hash   VARCHAR(150),
	--- any other authentication mechanisms can add fields here
);


---
--- users' LGI credentials
---   also contains data about the certificate because PHP has no
---   built-in certificate parsing functions; and this allows us
---   to make more sophisticated queries :)
---
CREATE TABLE usercerts (
	id            INTEGER PRIMARY KEY AUTO_INCREMENT,
	--- username
	userid        INTEGER REFERENCES users(id),
	--- user's certificate and key (paths to files for now)
	cert          TEXT,
	key           TEXT,
	--- username as present in the certificate
	username      VARCHAR(20),
	--- whether the user has a number of fixed groups, or can choose any
	---   when the certificate has defined groups, this should be TRUE
	fixedgroups   BOOLEAN
);

---
--- groups a user can choose
---   When a certificate has fixedgroups TRUE, these are the groups that
---   are present in the certificate. Else these are groups the user
---   may choose from the user-interface.
---
CREATE TABLE usergroups (
	usercertid    INTEGER REFERENCES usercerts(id),
	name          VARCHAR(20)
);

---
--- projects a user can choose
---   Should be populated from a users' certificate
---
CREATE TABLE userprojects (
	usercertid    INTEGER REFERENCES usercerts(id),
	name          VARCHAR(20)
);

