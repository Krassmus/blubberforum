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

    static public function getThreads($seminar_id, $before = false, $limit = false) {
        if ($limit) {
            $limit_constrain = "LIMIT 0, ".(int) $limit;
        }
        return self::findBySQL(__class__, "parent_id = '0' AND Seminar_id = ".DBManager::get()->quote($seminar_id)." ".($before ? "AND mkdate < ".DBManager::get()->quote($before)." " : "")." ORDER BY mkdate DESC ".$limit_constrain);
    }
    
    static public function getPostings($seminar_id, $since = 0) {
        return self::findBySQL(__class__, "Seminar_id = ".DBManager::get()->quote($seminar_id)." ".($since ? "AND chdate > ".DBManager::get()->quote($since) :"")." ORDER BY mkdate ASC ");
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

}