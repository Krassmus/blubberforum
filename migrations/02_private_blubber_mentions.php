<?php
class PrivateBlubberMentions extends DBMigration
{
    function up(){
        $db = DBManager::get();
        $db->exec("
            ALTER TABLE `px_topics` 
            ADD `context_type` ENUM( 'public', 'private', 'course' ) NOT NULL DEFAULT 'public' AFTER `root_id`
        ");
        $db->exec("
            UPDATE `px_topics` 
            SET context_type = IF(Seminar_id = user_id, 'public', 'course')
        ");
        $public_comments = $db->query(
            "SELECT comments.topic_id " .
            "FROM px_topics AS comments " .
                "INNER JOIN px_topics AS thread ON (thread.topic_id = comments.root_id) " .
            "WHERE thread.context_type = 'public' " .
        "")->fetchAll(PDO::FETCH_COLUMN, 0);
        foreach ($public_comments AS $comment) {
            $db->exec(
                "UPDATE `px_topics` " .
                "SET context_type = 'public' " .
                "WHERE topic_id = ".$db->quote($comment)." " .
            "");
        }
        $db->exec("
            CREATE TABLE IF NOT EXISTS `blubber_mentions` (
            `topic_id` varchar(32) NOT NULL,
            `user_id` varchar(32) NOT NULL,
            `mkdate` int(11) NOT NULL,
            UNIQUE KEY `unique_users_per_topic` (`topic_id`,`user_id`)
            ) ENGINE=MyISAM
        ");
    }
}