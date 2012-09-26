<?php
/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

require_once 'lib/classes/SimpleORMap.class.php';
require_once 'lib/forum.inc.php';

class ForumPosting extends SimpleORMap {

    protected $db_table = "px_topics";

    static public function mention($mention, $thread_id) {
        $username = stripslashes(substr($mention, 1));
        if ($username[0] !== '"') {
            $user_id = get_userid($username);
        } else {
            $name = substr($username, 1, strlen($username) -2);
            $db = DBManager::get();
            $user_id = $db->query(
                "SELECT user_id FROM auth_user_md5 WHERE CONCAT(Vorname, ' ', Nachname) = ".$db->quote($name)." " .
            "")->fetch(PDO::FETCH_COLUMN, 0);
        }
        if ($user_id && $user_id !== $GLOBALS['user']->id) {
            $user = new User($user_id);
            $messaging = new messaging();
            $url = $GLOBALS['ABSOLUTE_URI_STUDIP']."plugins.php/blubber/forum/thread/".$thread_id.'?cid='.$_SESSION['SessionSeminar'];
            $messaging->insert_message(
                sprintf(
                    _("%s hat Sie in einem Blubber erw�hnt. Zum Beantworten klicken auf Sie auf folgenen Link:\n\n%s\n"),
                    get_fullname(), $url
                ),
                get_username($user_id),
                $GLOBALS['user']->id,
                null, null, null, null,
                _("Sie wurden erw�hnt.")
            );
            return '['.$user['Vorname']." ".$user['Nachname'].']'.$GLOBALS['ABSOLUTE_URI_STUDIP']."about.php?username=".$user['username'];
        } else {
            return stripslashes($mention);
        }
    }

    static public function expireThreads($stream) {
        StudipCacheFactory::getCache()->expire("BLUBBERTHREADS_FROM_".$stream);
    }

    static public function getThreads($parameter = array()) {
        $defaults = array(
            'seminar_id' => null,
            'user_id' => null,
            'search' => null,
            'linear_in_time' => false,
            'offset' => 0,
            'limit' => null
        );
        $parameter = array_merge($defaults, $parameter);
        $db = DBManager::get();
        
        $joins = $where = array();
        $limit = "";
        
        if ($parameter['seminar_id']) {
            $where[] = "AND px_topics.Seminar_id = ".$db->quote($parameter['seminar_id']);
        }
        if ($parameter['user_id']) {
            $where[] = "AND px_topics.Seminar_id = ".$db->quote($parameter['user_id']);
        }
        if ($parameter['linear_in_time']) {
            $where[] = "AND mkdate <= ".$db->quote($parameter['linear_in_time']);
        }
        if ($parameter['limit'] > 0) {
            $limit = "LIMIT ".((int) $parameter['offset']).", ".((int) $parameter['limit']);
        }
        if (!$parameter['seminar_id'] && !$parameter['user_id']) {
            //Globaler Stream:
            $seminar_ids = $db->query(
                "SELECT Seminar_id " .
                "FROM seminar_user " .
                    "INNER JOIN plugins_activated ON (plugins_activated.poiid = CONCAT('sem', seminar_user.Seminar_id)) " .
                    "INNER JOIN plugins ON (plugins_activated.pluginid = plugins.pluginid) " .
                "WHERE user_id = ".$db->quote($GLOBALS['user']->id)." " .
                    "AND plugins_activated.state = 'on' " .
                    "AND plugins.pluginclassname = 'Blubber' " .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
            $where[] = "AND (px_topics.Seminar_id IS NULL " .
                            (count($seminar_ids) ? "OR px_topics.Seminar_id IN (".$db->quote($seminar_ids).") " : "") .
                       ") ";
        }
        
        $thread_ids = $db->query(
            "SELECT px_topics.root_id " .
            "FROM px_topics " .
                implode(" ", $joins) . " " .
            "WHERE 1=1 " .
                implode(" ", $where) . " " .
            "GROUP BY px_topics.root_id " .
            "ORDER BY MAX(px_topics.mkdate) DESC " .
            $limit." " .
        "")->fetchAll(PDO::FETCH_COLUMN, 0);
        $threads = array();
        foreach ($thread_ids as $thread_id) {
            $threads[] = new ForumPosting($thread_id);
        }
        return $threads;
    }

    static public function getPostings($parameter = array()) {
        $defaults = array(
            'seminar_id' => null,
            'user_id' => null,
            'search' => null,
            'since' => null
        );
        $parameter = array_merge($defaults, $parameter);
        $db = DBManager::get();

        $joins = $where = array();
        $limit = "";

        if ($parameter['since'] > 0) {
            $where[] = "AND px_topics.chdate >= ".$db->quote($parameter['since']);
        }
        if ($parameter['seminar_id']) {
            $where[] = "AND px_topics.Seminar_id = ".$db->quote($parameter['seminar_id']);
        }
        if ($parameter['user_id']) {
            $joins[] = "INNER JOIN px_topics AS thread ON (thread.topic_id = px_topics.root_id) ";
            $where[] = "AND thread.Seminar_id = ".$db->quote($parameter['user_id']);
        }
        if (!$parameter['seminar_id'] && !$parameter['user_id']) {
            //Globaler Stream:
            $seminar_ids = $db->query(
                "SELECT Seminar_id " .
                "FROM seminar_user " .
                    "INNER JOIN plugins_activated ON (plugins_activated.poiid = CONCAT('sem', seminar_user.Seminar_id)) " .
                    "INNER JOIN plugins ON (plugins_activated.pluginid = plugins.pluginid) " .
                "WHERE user_id = ".$db->quote($GLOBALS['user']->id)." " .
                    "AND plugins_activated.state = 'on' " .
                    "AND plugins.pluginclassname = 'Blubber' " .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
            $where[] = "AND (px_topics.Seminar_id IS NULL " .
                            (count($seminar_ids) ? "OR px_topics.Seminar_id IN (".$db->quote($seminar_ids).") " : "") .
                       ") ";
        }

        $thread_ids = $db->query(
            "SELECT px_topics.topic_id " .
            "FROM px_topics " .
                implode(" ", $joins) . " " .
            "WHERE 1=1 " .
                implode(" ", $where) . " " .
            "ORDER BY px_topics.mkdate ASC " .
        "")->fetchAll(PDO::FETCH_COLUMN, 0);
        $threads = array();
        foreach ($thread_ids as $thread_id) {
            $threads[] = new ForumPosting($thread_id);
        }
        return $threads;
    }
    
    public function restore() {
        parent::restore();
        if ($this['topic_id'] === $this['root_id']) {
            $db = DBManager::get();
            $this->content['discussion_time'] = $db->query(
                "SELECT mkdate " .
                "FROM px_topics " .
                "WHERE root_id = ".$db->quote($this->getId())." " .
                "ORDER BY mkdate DESC " .
                "LIMIT 1 " .
            "")->fetch(PDO::FETCH_COLUMN, 0);
        } else {
            $this->content['discussion_time'] = $this['mkdate'];
        }
    }
    
    public function isThread() {
        return $this['parent_id'] === "0";
    }
	
    public function getChildren() {
        if ($this->isThread()) {
            $db = DBManager::get();
            return self::findBySQL(__class__, "root_id = ".$db->quote($this->getId())." AND parent_id != '0' ORDER BY mkdate ASC");
        } else {
            return false;
        }
    }
    
    public function delete() {
        foreach ((array) self::findBySQL(__class__, "parent_id = ".DBManager::get()->quote($this->getId())) as $child_posting) {
            $child_posting->delete();
        }
        return parent::delete();
    }

}