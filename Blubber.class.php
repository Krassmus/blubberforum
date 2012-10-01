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
require_once 'lib/datei.inc.php';
require_once dirname(__file__)."/models/ForumPosting.class.php";

if (!function_exists("transformBeforeSave")) {
    function transformBeforeSave($text) {
        return $text;
    }
}

class Blubber extends StudIPPlugin implements StandardPlugin, SystemPlugin {

    public $config = array();

    public function __construct() {
        global $perm;
        parent::__construct();
        if (UpdateInformation::isCollecting()) {
            $data = Request::getArray("page_info");
            if (strpos(Request::get("page"), "plugins.php/blubber") !== false) {
                $output = array();
                $context_id = $data['Blubber']['context_id'];
                $stream = $data['Blubber']['stream'];
                $last_check = $data['Blubber']['last_check'] ? $data['Blubber']['last_check'] : (time() - 5 * 60);

                $parameter = array(
                    'since' => $last_check
                );
                if ($stream === "thread") {
                    $parameter['thread'] = $context_id;
                }
                if ($stream === "course") {
                    $parameter['seminar_id'] = $context_id;
                }
                if ($stream === "profile") {
                    $parameter['user_id'] = $context_id;
                }
                $new_postings = ForumPosting::getPostings($parameter);

                $factory = new Flexi_TemplateFactory($this->getPluginPath()."/views");
                foreach ($new_postings as $new_posting) {
                    if ($new_posting['root_id'] === $new_posting['topic_id']) {
                        $template = $factory->open("forum/thread.php");
                        $template->set_attribute('thread', $new_posting);
                    } else {
                        $template = $factory->open("forum/comment.php");
                        $template->set_attribute('posting', $new_posting);
                    }
                    $template->set_attribute("course_id", $data['Blubber']['seminar_id']);
                    $output['postings'][] = array(
                        'posting_id' => $new_posting['topic_id'],
                        'mkdate' => $new_posting['mkdate'],
                        'root_id' => $new_posting['root_id'],
                        'content' => $template->render()
                    );
                }
                UpdateInformation::setInformation("Blubber.getNewPosts", $output);
            }
        }
        if (Navigation::hasItem("/course") && $this->isActivated() && version_compare($GLOBALS['SOFTWARE_VERSION'], "2.2") <= 0) {
            $tab = new AutoNavigation($this->getDisplayTitle(), PluginEngine::getLink($this, array(), "forum/forum"));
            $tab->setImage($this->getPluginURL()."/assets/images/blubber_white.png");
            Navigation::addItem("/course/blubberforum", $tab);
        }

        if (Navigation::hasItem("/community")) {
            $nav = new AutoNavigation($this->getDisplayTitle(), PluginEngine::getURL($this, array(), "forum/globalstream"));
            Navigation::insertItem("/community/blubber", $nav, "online");
            Navigation::getItem("/community")->setURL(PluginEngine::getURL($this, array(), "forum/globalstream"));
        }
        
        if (Navigation::hasItem("/profile")) {
            $nav = new AutoNavigation(_("Blubber"), PluginEngine::getURL($this, array('username' => Request::get("username")), "forum/profile"));
            Navigation::addItem("/profile/blubber", $nav);
        }
    }

    public function getTabNavigation($course_id) {
        $tab = new AutoNavigation($this->getDisplayTitle(), PluginEngine::getLink($this, array(), "forum/forum"));
        $tab->setImage($this->getPluginURL()."/assets/images/blubber_white.png");
        return array('blubberforum' => $tab);
    }

    public function getIconNavigation($course_id, $last_visit, $user_id = null) {
        if (!$user_id) {
            $user_id = $GLOBALS['user']->id;
        }
        $icon = new AutoNavigation($this->getDisplayTitle(), PluginEngine::getLink($this, array(), "forum/forum"));
        $db = DBManager::get();
        $last_own_posting_time = (int) $db->query(
            "SELECT mkdate " .
            "FROM px_topics " .
            "WHERE user_id = ".$db->quote($user_id)." " .
                "AND Seminar_id = ".$db->quote($course_id)." " .
        "")->fetch(PDO::FETCH_COLUMN, 0);
        $new_ones = $db->query(
            "SELECT COUNT(*) " .
            "FROM px_topics " .
            "WHERE chdate > ".$db->quote(max($last_visit, $last_own_posting_time))." " .
                "AND user_id != ".$db->quote($user_id)." " .
                "AND Seminar_id = ".$db->quote($course_id)." " .
        "")->fetch(PDO::FETCH_COLUMN, 0);
        if ($new_ones) {
            $title = $new_ones > 1 ? sprintf(_("%s neue Blubber"), $new_ones) : _("1 neuer Blubber");
            $icon->setImage($this->getPluginURL()."/assets/images/blubber_red.png", array('title' => $title));
            $icon->setTitle($title);
        } else {
            $icon->setImage($this->getPluginURL()."/assets/images/blubber_grey.png", array('title' => $this->getDisplayTitle()));
        }
        return $icon;
    }

    public function getNotificationObjects($course_id, $since, $user_id)
    {
        return array();
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
