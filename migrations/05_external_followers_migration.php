<?php
class ExternalFollowersMigration extends DBMigration
{
    function up() {
        $db = DBManager::get();
        $db->exec("
            CREATE TABLE IF NOT EXISTS `blubber_follower` (
                `studip_user_id` varchar(32) NOT NULL,
                `external_contact_id` varchar(32) NOT NULL,
                `left_follows_right` tinyint(1) NOT NULL
            ) ENGINE=MyISAM
        ");
    }
}