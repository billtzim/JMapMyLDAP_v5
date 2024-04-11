--
<<<<<<< HEAD
-- Table structure for table `jos_sh_config`
--

CREATE TABLE IF NOT EXISTS `#__sh_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_name` (`name`)
);

--
-- Default table data for `jos_sh_config`
--

REPLACE INTO `#__sh_config` 
  SET `name` = 'platform:version',
  `value` = '2.0.0.0';

REPLACE INTO `#__sh_config` 
  SET `name` = 'platform:import',
  `value` = '{}';

REPLACE INTO `#__sh_config` 
  SET `name` = 'user:autoregister',
  `value` = '2';

REPLACE INTO `#__sh_config` 
  SET `name` = 'user:defaultgroup',
  `value` = '2';

REPLACE INTO `#__sh_config` 
  SET `name` = 'user:type',
  `value` = '';
=======
-- Table structure for table `#__sh_config`
--

CREATE TABLE IF NOT EXISTS `#__sh_config` (
  `id` int NOT NULL,
  `name` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `value` text
);

INSERT INTO `ihs14_sh_config` (`id`, `name`, `value`) VALUES
(1, 'platform:version', '5.0'),
(2, 'platform:import', '[\"ldap\",\"sso\"]'),
(3, 'user:autoregister', '2'),
(4, 'user:defaultgroup', '2'),
(6, 'ldap:version', '5.0'),
(7, 'ldap:source', '1'),
(8, 'ldap:plugin', 'ldap'),
(9, 'user:type', 'ldap'),
(10, 'user:nullpassword', '1'),
(11, 'user:usedomain', '1'),
(12, 'user:blacklist', ''),
(13, 'sso:doauthorise', '1'),
(14, 'sso:autoregister', '1'),
(15, 'sso:backend', '0'),
(16, 'sso:forcelogin', '0'),
(17, 'sso:bypasskey', 'nosso'),
(18, 'sso:behaviour', '1'),
(19, 'sso:logintasks', '[\"user.login\",\"sso.login\"]'),
(20, 'ldap:defaultconfig', 'sample'),
(21, 'ldap:reqcert', '0'),
(22, 'ldap:config', '2'),
(23, 'ldap:table', '#__sh_ldap_config'),
(24, 'ldap:file', ''),
(25, 'ldap:namespaces', '');


ALTER TABLE `ihs14_sh_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_name` (`name`);
  
  
ALTER TABLE `ihs14_sh_config`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;
COMMIT;
>>>>>>> 900d22413d1e7811ec851730b296f2d48c37d7a8

