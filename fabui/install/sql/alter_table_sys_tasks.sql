USE `fabtotum`;
SELECT count(*)
INTO @exist
FROM information_schema.columns 
WHERE table_schema = 'fabtotum'
and COLUMN_NAME = 'id_object'
AND table_name = 'sys_tasks';

set @query = IF(@exist <= 0, 'ALTER TABLE `sys_tasks` ADD `id_object` INT(11) NOT NULL AFTER `type`', 
'select \'Column Exists\' status');

prepare stmt from @query;
EXECUTE stmt;


USE `fabtotum`;
SELECT count(*)
INTO @exist
FROM information_schema.columns 
WHERE table_schema = 'fabtotum'
and COLUMN_NAME = 'id_file'
AND table_name = 'sys_tasks';

set @query = IF(@exist <= 0, 'ALTER TABLE `sys_tasks` ADD `id_file` INT(11) NOT NULL AFTER `id_object`', 
'select \'Column Exists\' status');

prepare stmt from @query;
EXECUTE stmt;