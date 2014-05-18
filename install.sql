DROP TABLE IF EXISTS wbb1_post_history_version;
CREATE TABLE wbb1_post_history_version (
	versionID		INT(10) 		NOT NULL AUTO_INCREMENT PRIMARY KEY,
	postID			INT(10)			NOT NULL,
	userID 			INT(10)			DEFAULT NULL,
	username		VARCHAR(255)		NOT NULL DEFAULT '',
	time 			INT(10)			NOT NULL DEFAULT 0,
	message 		MEDIUMTEXT,
	ipAddress		VARCHAR(39)		NOT NULL DEFAULT ''
	KEY user (userID)
);

ALTER TABLE wbb1_post_history_version ADD FOREIGN KEY (userID) REFERENCES wcf1_user (userID) ON DELETE SET NULL;
ALTER TABLE wbb1_post_history_version ADD FOREIGN KEY (postID) REFERENCES wbb1_post (postID) ON DELETE CASCADE;