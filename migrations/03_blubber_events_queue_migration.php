<?php
class BlubberEventsQueueMigration extends DBMigration
{
    function up(){
        $db = DBManager::get();
        $db->exec("
            CREATE TABLE IF NOT EXISTS `blubber_events_queue` (
            `event_type` varchar(32) NOT NULL,
            `item_id` varchar(32) NOT NULL,
            `mkdate` int(11) NOT NULL
            ) ENGINE=MyISAM
        ");
    }
}