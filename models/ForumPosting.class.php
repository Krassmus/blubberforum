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
    
    static public function expireThreads($seminar_id) {
        StudipCacheFactory::getCache()->expire("BLUBBERTHREADS_FROM_".$seminar_id);
    }

    static public function getThreads($seminar_id, $before = false, $limit = false) {
        $cache = StudipCacheFactory::getCache();
        $threads = $cache->read("BLUBBERTHREADS_FROM_".$seminar_id);
        if (!$threads) {
            $db = DBManager::get();
            $thread_ids = $db->query(
                "SELECT px_topics.root_id " .
                "FROM px_topics " .
                "WHERE px_topics.Seminar_id = ".$db->quote($seminar_id)." " .
                "GROUP BY px_topics.root_id " .
                "ORDER BY MAX(mkdate) DESC " .
            "")->fetchAll(PDO::FETCH_COLUMN, 0);
            $threads = array();
            foreach ($thread_ids as $thread_id) {
                $threads[] = new ForumPosting($thread_id);
            }
            $cache->write("BLUBBERTHREADS_FROM_".$seminar_id, serialize($threads));
        } else {
            $threads = unserialize($threads);
        }
        if ($before) {
            while ($threads[0]['mkdate'] > $before) {
                array_shift($threads);
            }
        }
        if ($limit) {
            $threads = array_slice($threads, 0, $limit);
        }
        return $threads;
    }
    
    static public function getPostings($seminar_id, $since = 0) {
        return self::findBySQL(__class__, "Seminar_id = ".DBManager::get()->quote($seminar_id)." ".($since ? "AND chdate > ".DBManager::get()->quote($since) :"")." ORDER BY mkdate ASC ");
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