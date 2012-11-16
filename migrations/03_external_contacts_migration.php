<?php
class ExternalContactsMigration extends DBMigration
{
    function up() {
        $db = DBManager::get();
        $db->exec("
            ALTER TABLE `px_topics` ADD `external_contact` TINYINT NOT NULL DEFAULT '0' AFTER `user_id` 
        ");
        $db->exec("
            ALTER TABLE `blubber_mentions` ADD `external_contact` TINYINT NOT NULL DEFAULT '0' AFTER `user_id` 
        ");
        $db->exec("
            CREATE TABLE IF NOT EXISTS `blubber_external_contact` (
                `external_contact_id` varchar(32) NOT NULL,
                `mail_identifier` varchar(256) DEFAULT NULL,
                `contact_type` varchar(16) NOT NULL DEFAULT 'anonymous',
                `name` varchar(256) NOT NULL,
                `chdate` bigint(20) NOT NULL,
                `mkdate` bigint(20) NOT NULL,
                PRIMARY KEY (`external_contact_id`)
            ) ENGINE=MyISAM
        ");
    }
}