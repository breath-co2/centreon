-- Change version of Centreon
UPDATE `informations` SET `value` = '2.8.0-beta2' WHERE CONVERT( `informations`.`key` USING utf8 )  = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '2.8.0-beta1' LIMIT 1;

-- Remove failover field from graphite broker output
DELETE cbfr FROM cb_type_field_relation cbfr
INNER JOIN cb_field cbf ON cbfr.cb_field_id=cbf.cb_field_id
INNER JOIN cb_type cbt ON cbfr.cb_type_id=cbt.cb_type_id
AND cbf.fieldname = 'failover'
AND cbt.type_shortname = 'graphite';

-- Insert Centreon Backup menu in topology
INSERT INTO `topology` (
`topology_id`, `topology_name`, `topology_parent`, `topology_page`, `topology_order`,
`topology_group`, `topology_url`, `topology_url_opt`, `topology_popup`, `topology_modules`, `topology_show`,
`topology_style_class`, `topology_style_id`, `topology_OnClick`, `readonly`
) VALUES (
NULL,'Backup',501,50165,90,
1,'./include/Administration/parameters/parameters.php','&o=backup','0','0','1',
NULL,NULL,NULL,'1'
);

-- Insert Centreon Backup base conf
INSERT INTO `options` (`key`, `value`)
VALUES
('backup_enabled', '0'),
('backup_configuration_files', '1'),
('backup_database_centreon', '1'),
('backup_database_centreon_storage', '1'),
('backup_database_type', '1'),
('backup_database_level', '0'),
('backup_backup_directory', '/var/backup'),
('backup_tmp_directory', '/tmp/backup'),
('backup_retention', '7'),
('backup_mysql_conf', '/etc/my.cnf.d/centreon.cnf'),
('backup_zend_conf', '/etc/php.d/zendguard.ini');

-- Insert KB configuration
INSERT INTO `options` (`key`, `value`) VALUES ('kb_db_name', ''), ('kb_db_user', ''), ('kb_db_password', ''), ('kb_db_host', ''), ('kb_db_prefix', ''), ('kb_WikiURL', '');
INSERT INTO `topology` (`topology_id` ,`topology_name` ,`topology_parent` ,`topology_page` ,`topology_order` ,`topology_group` ,`topology_url` ,`topology_url_opt` ,`topology_popup` ,`topology_modules` ,`topology_show` ,`topology_style_class` ,`topology_style_id` ,`topology_OnClick`) VALUES (NULL , 'Knowledge Base', '501', '50133', 90, 1, './include/Administration/parameters/parameters.php', '&o=knowledgeBase' , NULL , '1', '1', NULL , NULL , NULL);
UPDATE `topology` SET topology_name = 'Graphs' WHERE topology_name = 'Edit View' AND topology_page = '10201';
