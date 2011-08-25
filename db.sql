/*
 * Initial database for LGIportal
 */

CREATE TABLE users (
	userId        VARCHAR(20) PRIMARY KEY,
	passwordHash  VARCHAR(150),
	salt          VARCHAR(50)
);

/* user 'demo' with password 'demo' */
INSERT INTO users SET userId='demo', passwordHash='e77b0f5cad5cf53cece557d8111c72393844b3a960605af028efebe7f9ebb61f3b1ffae674a7cf959a9bf09e8f439adf0682cdf1cad44e1bdc552be1eee394a7', salt='abc';

CREATE TABLE usercertificates (
	userId        VARCHAR(20),
	certificate   TEXT,
	userkey       TEXT
);

