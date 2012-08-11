<?php
/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

require_once dirname(__file__)."/application.php";

class ForumController extends ApplicationController {
    
    protected $max_threads = 20;
    
    public function forum_action() {
        PageLayout::addHeadElement("script", array('src' => $this->assets_url."/javascripts/autoresize.jquery.min.js"), "");
        PageLayout::addHeadElement("script", array('src' => $this->assets_url."/javascripts/ff.js"), "");
        Navigation::getItem("/course/blubberforum")->setImage($this->plugin->getPluginURL()."/assets/images/blubber.png");
        ForumPosting::expireThreads($_SESSION['SessionSeminar']);
        $this->threads = ForumPosting::getThreads($_SESSION['SessionSeminar'], false, $this->max_threads + 1);
        $this->more_threads = count($this->threads) > $max_threads;
        if ($this->more_threads) {
            $this->threads = array_slice($this->threads, 0, $max_threads);
        }
    }
    
    public function more_comments_action() {
        if (!$_SESSION['SessionSeminar'] || !$GLOBALS['perm']->have_studip_perm("autor", $_SESSION['SessionSeminar'])) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        $thread = new ForumPosting(Request::option("thread_id"));
        $output = array();
        $factory = new Flexi_TemplateFactory($this->plugin->getPluginPath()."/views");
        $comments = $thread->getChildren();
        foreach ($comments as $posting) {
            $template = $factory->open("forum/comment.php");
            $template->set_attribute('posting', $posting);
            $output['comments'][] = array(
                'content' => studip_utf8encode($template->render()),
                'mkdate' => $posting['mkdate'],
                'posting_id' => $posting->getId()
            );
        }
        $this->render_json($output);
    }
    
    public function more_postings_action() {
        if (!$_SESSION['SessionSeminar'] || !$GLOBALS['perm']->have_studip_perm("autor", $_SESSION['SessionSeminar'])) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        $output = array();
        $threads = ForumPosting::getThreads($_SESSION['SessionSeminar'], Request::int("before"), $this->max_threads + 1);
        $output['more'] = count($this->threads) > $max_threads;
        if ($output['more']) {
            $threads = array_slice($threads, 0, $max_threads);
        }
        $output['threads'] = array();
        $factory = new Flexi_TemplateFactory($this->plugin->getPluginPath()."/views");
        foreach ($threads as $posting) {
            $template = $factory->open("forum/thread.php");
            $template->set_attribute('thread', $posting);
            $output['threads'][] = array(
                'content' => studip_utf8encode($template->render()),
                'mkdate' => $posting['mkdate'],
                'posting_id' => $posting->getId()
            );
        }
        $this->render_json($output);
    }
    
    public function new_posting_action() {
        if (!$_SESSION['SessionSeminar'] || !$GLOBALS['perm']->have_studip_perm("autor", $_SESSION['SessionSeminar'])) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        $output = array();
        $thread = new ForumPosting(Request::option("thread"));
        $thread['seminar_id'] = $_SESSION['SessionSeminar'];
        $thread['parent_id'] = 0;
        $content = studip_utf8decode(Request::get("content"));
        if (strpos($content, "\n") !== false) {
            $thread['name'] = substr($content, 0, strpos($content, "\n"));
            $thread['description'] = substr($content, strpos($content, "\n") + 1);
        } else {
            if (strlen($content) > 255) {
                $thread['name'] = "";
                $thread['description'] = $content;
            } else {
                $thread['name'] = $content;
                $thread['description'] = "";
            }
        }
        $thread['user_id'] = $GLOBALS['user']->id;
        $thread['author'] = get_fullname();
        $thread['author_host'] = $_SERVER['REMOTE_ADDR'];
        if ($thread->store()) {
            $thread->restore();
            $thread['root_id'] = $thread->getId();
            $thread->store();
            $factory = new Flexi_TemplateFactory($this->plugin->getPluginPath()."/views");
            $template = $factory->open("forum/thread.php");
            $template->set_attribute('thread', $thread);
            $output['content'] = studip_utf8encode($template->render());
            $output['mkdate'] = time();
            $output['posting_id'] = $thread->getId();
        }
        $this->render_json($output);
    }
    
    public function post_action() {
        if (!$_SESSION['SessionSeminar'] || !$GLOBALS['perm']->have_studip_perm("autor", $_SESSION['SessionSeminar'])) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        $thread = new ForumPosting(Request::option("thread"));
        if (Request::option("thread")) {
            $output = array();
            $thread = new ForumPosting(Request::option("thread"));
            $posting = new ForumPosting();
            $posting['description'] = studip_utf8decode(Request::get("content"));
            $posting['seminar_id'] = $_SESSION['SessionSeminar'];
            $posting['root_id'] = $posting['parent_id'] = Request::option("thread");
            $posting['name'] = "Re: ".$thread['name'];
            $posting['user_id'] = $GLOBALS['user']->id;
            $posting['author'] = get_fullname();
            $posting['author_host'] = $_SERVER['REMOTE_ADDR'];
            if ($posting->store()) {
                $factory = new Flexi_TemplateFactory($this->plugin->getPluginPath()."/views/forum");
                $template = $factory->open("comment.php");
                $template->set_attribute('posting', $posting);
                $output['content'] = studip_utf8encode($template->render($template->render()));
                $output['mkdate'] = time();
                $output['posting_id'] = $posting->getId();
            }
            $this->render_json($output);
        } else {
            $this->render_json(array(
                'error' => "Konnte thread nicht zuordnen."
            ));
        }
    }
    
}