DROP TABLE IF EXISTS `OpenM_SSO_ADMIN`;
CREATE TABLE IF NOT EXISTS `OpenM_SSO_ADMIN` (
  `user_id` varchar(200) NOT NULL,
  `user_level` smallint(1) NOT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `OpenM_SSO_API_SESSION`;
CREATE TABLE IF NOT EXISTS `OpenM_SSO_API_SESSION` (
  `SSID` varchar(100) NOT NULL,
  `api_url` varchar(200) NOT NULL,
  `api_SSID` varchar(110) NOT NULL,
  `end_time` int(20) NOT NULL,
  PRIMARY KEY (`SSID`,`api_url`),
  KEY `end_time` (`end_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `OpenM_SSO_CLIENT`;
CREATE TABLE IF NOT EXISTS `OpenM_SSO_CLIENT` (
  `client_id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_hash` varchar(200) NOT NULL,
  `is_valid` smallint(1) NOT NULL,
  `install_user_id` varchar(200) NOT NULL,
  `time` int(20) NOT NULL,
  PRIMARY KEY (`client_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

DROP TABLE IF EXISTS `OpenM_SSO_CLIENT_RIGHTS`;
CREATE TABLE IF NOT EXISTS `OpenM_SSO_CLIENT_RIGHTS` (
  `rights_id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `rights_pattern` varchar(40) NOT NULL,
  PRIMARY KEY (`rights_id`),
  KEY `client_id` (`client_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=10 ;

DROP TABLE IF EXISTS `OpenM_SSO_SESSION`;
CREATE TABLE IF NOT EXISTS `OpenM_SSO_SESSION` (
  `SSID` varchar(100) NOT NULL,
  `oid` varchar(256) NOT NULL,
  `ip_hash` varchar(100) NOT NULL,
  `begin_time` int(20) NOT NULL,
  `api_sso_token` varchar(100) NOT NULL,
  PRIMARY KEY (`SSID`),
  KEY `begin_time` (`begin_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;