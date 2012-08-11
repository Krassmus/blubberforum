<?php
/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

require_once "lib/classes/UpdateInformation.class.php";
require_once dirname(__file__)."/models/ForumPosting.class.php";

class Blubber extends StudIPPlugin implements StandardPlugin, SystemPlugin {

    public $config = array();
    
    public function __construct() {
        global $perm;
        parent::__construct();
        $this->restoreConfig();
        if (UpdateInformation::isCollecting()) {
            $data = Request::getArray("page_info");
            if ($data['FF']['seminar_id']) {
                $output = array();
                $new_postings = ForumPosting::getPostings($data['FF']['seminar_id'], time() - (60 * 5));
                $factory = new Flexi_TemplateFactory($this->getPluginPath()."/views");
                foreach ($new_postings as $new_posting) {
                    if ($new_posting['root_id'] === $new_posting['topic_id']) {
                        $template = $factory->open("forum/thread.php");
                        $template->set_attribute('thread', $new_posting);
                    } else {
                        $template = $factory->open("forum/comment.php");
                        $template->set_attribute('posting', $new_posting);
                    }
                    $output['postings'][] = array(
                        'posting_id' => $new_posting['topic_id'],
                        'mkdate' => $new_posting['mkdate'],
                        'root_id' => $new_posting['root_id'],
                        'content' => studip_utf8encode($template->render())
                    );
                }
                UpdateInformation::setInformation("FF.getNewPosts", $output);
            }
        }
    }
    
    public function getTabNavigation($course_id) {
        $tab = new AutoNavigation($this->getDisplayTitle(), PluginEngine::getLink($this, array(), "forum/forum"));
        $tab->setImage($this->getPluginURL()."/assets/images/blubber_white.png");
        return array('blubberforum' => $tab);
    }

    public function restoreConfig() {
        $config = DBManager::get()
                ->query("SELECT comment FROM config WHERE field = 'CONFIG_" . $this->getPluginName() . "' AND is_default=1")
                ->fetchColumn();
        $this->config = unserialize($config);
        return $this->config != false;
    }

    public function storeConfig() {
        $config = serialize($this->config);
        $field = "CONFIG_" . $this->getPluginName();
        $st = DBManager::get()
        ->prepare("REPLACE INTO config (config_id, field, value, is_default, type, range, chdate, comment)
            VALUES (?,?,'do not edit',1,'string','global',UNIX_TIMESTAMP(),?)");
        return $st->execute(array(md5($field), $field, $config));
    }

    public function getIconNavigation($course_id, $last_visit) {
        $icon = new AutoNavigation($this->getDisplayTitle(), PluginEngine::getLink($this, array(), "forum/forum"));
        $db = DBManager::get();
        $last_own_posting_time = (int) $db->query(
            "SELECT mkdate " .
            "FROM px_topics " .
            "WHERE user_id = ".$db->quote($GLOBALS['user']->id)." " .
                "AND Seminar_id = ".$db->quote($course_id)." " .
        "")->fetch(PDO::FETCH_COLUMN, 0);
        $new_ones = $db->query(
            "SELECT COUNT(*) " .
            "FROM px_topics " .
            "WHERE chdate > ".$db->quote($last_visit > $last_own_posting_time ? $last_visit : $last_own_posting_time)." " .
                "AND user_id != ".$db->quote($GLOBALS['user']->id)." " .
                "AND Seminar_id = ".$db->quote($course_id)." " .
        "")->fetch(PDO::FETCH_COLUMN, 0);
        if ($new_ones) {
            $icon->setImage($this->getPluginURL()."/assets/images/blubber_red.png");
            $icon->setTitle($new_ones > 1 ? sprintf(_("%s neue Blubber"), $new_ones) : _("1 neuer Blubber"));
        } else {
            $icon->setImage($this->getPluginURL()."/assets/images/blubber_grey.png");
        }
        return $icon;
    }

    public function getInfoTemplate($course_id)  {
        return null;
    }
    
    public function getDisplayTitle() {
        return "Blubbern";
    }

    /**
    * This method dispatches and displays all actions. It uses the template
    * method design pattern, so you may want to implement the methods #route
    * and/or #display to adapt to your needs.
    *
    * @param  string  the part of the dispatch path, that were not consumed yet
    *
    * @return void
    */
    public function perform($unconsumed_path) {
        if(!$unconsumed_path) {
            header("Location: " . PluginEngine::getUrl($this), 302);
            return false;
        }
        $trails_root = $this->getPluginPath();
        $dispatcher = new Trails_Dispatcher($trails_root, null, 'show');
        $dispatcher->current_plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
    }
}
