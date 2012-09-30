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

    public function before_filter(&$action, &$args)
    {
        parent::before_filter($action, $args);
    }

    public function globalstream_action() {
        PageLayout::addHeadElement("script", array('src' => $this->assets_url."/javascripts/autoresize.jquery.min.js"), "");
        PageLayout::addHeadElement("script", array('src' => $this->assets_url."/javascripts/blubberforum.js"), "");
        PageLayout::addHeadElement("script", array('src' => $this->assets_url."/javascripts/formdata.js"), "");
        PageLayout::setTitle($this->plugin->getDisplayTitle());

        $this->threads = ForumPosting::getThreads(array(
            'limit' => $this->max_threads + 1
        ));
        $this->more_threads = count($this->threads) > $this->max_threads;
        if ($this->more_threads) {
            $this->threads = array_slice($this->threads, 0, $this->max_threads);
        }
    }

    public function forum_action() {
        object_set_visit($_SESSION['SessionSeminar'], "forum");
        PageLayout::addHeadElement("script", array('src' => $this->assets_url."/javascripts/autoresize.jquery.min.js"), "");
        PageLayout::addHeadElement("script", array('src' => $this->assets_url."/javascripts/blubberforum.js"), "");
        PageLayout::addHeadElement("script", array('src' => $this->assets_url."/javascripts/formdata.js"), "");
        PageLayout::setTitle($GLOBALS['SessSemName']["header_line"]." - ".$this->plugin->getDisplayTitle());
        Navigation::getItem("/course/blubberforum")->setImage($this->plugin->getPluginURL()."/assets/images/blubber.png");

        $this->threads = ForumPosting::getThreads(array(
            'seminar_id' => $_SESSION['SessionSeminar'],
            'limit' => $this->max_threads + 1
        ));
        $this->more_threads = count($this->threads) > $this->max_threads;
        $this->course_id = $_SESSION['SessionSeminar'];
        if ($this->more_threads) {
            $this->threads = array_slice($this->threads, 0, $this->max_threads);
        }
    }

    public function profile_action() {
        PageLayout::addHeadElement("script", array('src' => $this->assets_url."/javascripts/autoresize.jquery.min.js"), "");
        PageLayout::addHeadElement("script", array('src' => $this->assets_url."/javascripts/blubberforum.js"), "");
        PageLayout::addHeadElement("script", array('src' => $this->assets_url."/javascripts/formdata.js"), "");
        PageLayout::setTitle("Blubber");

        $this->user_id = get_userid(Request::get("username"));
        PageLayout::addHeadElement("link", array(
            'rel' => "alternate",
            'type' => "application/atom+xml",
            'href' => "",
            'title' => "Blubber von ".get_fullname($user_id)
        ));
        
        $this->threads = ForumPosting::getThreads(array(
            'user_id' => $this->user_id,
            'limit' => $this->max_threads + 1
        ));
        $this->more_threads = count($this->threads) > $this->max_threads;
        $this->course_id = $_SESSION['SessionSeminar'];
        if ($this->more_threads) {
            $this->threads = array_slice($this->threads, 0, $this->max_threads);
        }
    }

    public function more_comments_action() {
        $thread = new ForumPosting(Request::option("thread_id"));
        if ($thread['user_id'] !== $thread['Seminar_id'] && !$GLOBALS['perm']->have_studip_perm("autor", $thread['Seminar_id'])) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        $output = array();
        $factory = new Flexi_TemplateFactory($this->plugin->getPluginPath()."/views");
        $comments = $thread->getChildren();
        foreach ($comments as $posting) {
            $template = $factory->open("forum/comment.php");
            $template->set_attribute('posting', $posting);
            $template->set_attribute('course_id', $thread['Seminar_id']);
            $output['comments'][] = array(
                'content' => studip_utf8encode($template->render()),
                'mkdate' => $posting['mkdate'],
                'posting_id' => $posting->getId()
            );
        }
        $this->render_json($output);
    }

    public function more_postings_action() {
        if (Request::get("stream") === "course" && (!$_SESSION['SessionSeminar'] || !$GLOBALS['perm']->have_studip_perm("autor", $_SESSION['SessionSeminar']))) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        $output = array();
        $parameter = array(
            'offset' => 20 * Request::int("offset"),
            'limit' => $this->max_threads + 1
        );
        if (Request::get("stream") === "course") {
            $parameter['seminar_id'] = $_SESSION['SessionSeminar'];
        }
        $threads = ForumPosting::getThreads($parameter);
        $output['more'] = count($this->threads) > $this->max_threads;
        if ($output['more']) {
            $threads = array_slice($threads, 0, $this->max_threads);
        }
        $output['threads'] = array();
        $factory = new Flexi_TemplateFactory($this->plugin->getPluginPath()."/views");
        foreach ($threads as $posting) {
            $template = $factory->open("forum/thread.php");
            $template->set_attribute('thread', $posting);
            $template->set_attribute('course_id', $_SESSION['SessionSeminar']);
            $template->set_attribute('controller', $this);
            $output['threads'][] = array(
                'content' => studip_utf8encode($template->render()),
                'mkdate' => $posting['mkdate'],
                'posting_id' => $posting->getId()
            );
        }
        $this->render_json($output);
    }

    public function new_posting_action() {
        $context = Request::option("context");
        $context_type = Request::option("context_type");
        if (!$context 
                || ($context_type === "course" && !$GLOBALS['perm']->have_studip_perm("autor", $context))) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        $output = array();
        $thread = new ForumPosting(Request::option("thread"));
        $thread['seminar_id'] = $context_type === "course" ? $context : $GLOBALS['user']->id;
        $thread['parent_id'] = 0;
        $content = transformBeforeSave(studip_utf8decode(Request::get("content")));
        if ($thread->isNew() && !$thread->getId()) {
            $thread->setId($thread->getNewId());
        }
        
        //mentions einbauen:
        $content = preg_replace("/(@\"[^\n\"]*\")/e", "ForumPosting::mention('\\1', '".$thread->getId()."')", $content);
        $content = preg_replace("/(@[^\s]+)/e", "ForumPosting::mention('\\1', '".$thread->getId()."')", $content);
        
        if (strpos($content, "\n") !== false) {
            $thread['name'] = substr($content, 0, strpos($content, "\n"));
            $thread['description'] = $content;
        } else {
            if (strlen($content) > 255) {
                $thread['name'] = "";
            } else {
                $thread['name'] = $content;
            }
            $thread['description'] = $content;
        }
        $thread['user_id'] = $GLOBALS['user']->id;
        $thread['author'] = get_fullname();
        $thread['author_host'] = $_SERVER['REMOTE_ADDR'];
        $thread['root_id'] = $thread->getId();
        if ($thread->store()) {
            $factory = new Flexi_TemplateFactory($this->plugin->getPluginPath()."/views");
            $template = $factory->open("forum/thread.php");
            $template->set_attribute('thread', $thread);
            $template->set_attribute('controller', $this);
            $output['content'] = studip_utf8encode($template->render());
            $output['mkdate'] = time();
            $output['posting_id'] = $thread->getId();
        }
        $this->render_json($output);
    }

    public function get_source_action() {
        $posting = new ForumPosting(Request::get("topic_id"));
        if (($posting['user_id'] !== $GLOBALS['user']->id)
                && (!$GLOBALS['perm']->have_studip_perm("autor", $posting['Seminar_id']))) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        echo studip_utf8encode(forum_kill_edit($posting['description']));
        $this->render_nothing();
    }

    public function edit_posting_action () {
        $posting = new ForumPosting(Request::get("topic_id"));
        if (($posting['user_id'] !== $GLOBALS['user']->id) 
                && (!$GLOBALS['perm']->have_studip_perm("tutor", $posting['Seminar_id']))) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        $old_content = $posting['description'];
        $messaging = new messaging();
        $new_content = transformBeforeSave(studip_utf8decode(Request::get("content")));
        if ($new_content && $old_content !== $new_content) {
            $posting['description'] = $new_content;
            if ($posting['topic_id'] === $posting['root_id']) {
                if (strpos($new_content, "\n") !== false) {
                    $posting['name'] = substr($new_content, 0, strpos($new_content, "\n"));
                } else {
                    if (strlen($new_content) > 255) {
                        $posting['name'] = "";
                    } else {
                        $posting['name'] = $new_content;
                    }
                }
            }
            $posting->store();
            if ($posting['user_id'] !== $GLOBALS['user']->id) {
                $messaging->insert_message(
                    sprintf(
                        _("%s hat als Moderator gerade Ihren Beitrag im Blubberforum editiert.\n\nDie alte Version des Beitrags lautete:\n\n%s\n\nDie neue lautet:\n\n%s\n"),
                        get_fullname(), $old_content, $posting['description']
                    ),
                    get_username($posting['user_id']),
                    $GLOBALS['user']->id,
                    null, null, null, null,
                    _("Änderungen an Ihrem Posting.")
                );
            }
        } elseif(!$new_content) {
            if ($posting['user_id'] !== $GLOBALS['user']->id) {
                $messaging->insert_message(
                    sprintf(
                        _("%s hat als Moderator gerade Ihren Beitrag im Blubberforum GELÖSCHT.\n\nDer alte Beitrag lautete:\n\n%s\n"),
                        get_fullname(), $old_content
                    ),
                    get_username($posting['user_id']),
                    $GLOBALS['user']->id,
                    null, null, null, null,
                    _("Ihr Posting wurde gelöscht.")
                );
            }
            $posting->delete();
        }
        $this->render_text(studip_utf8encode(formatReady($posting['description'])));
    }

    public function refresh_posting_action() {
        $posting = new ForumPosting(Request::get("topic_id"));
        if (!$GLOBALS['perm']->have_studip_perm("autor", $posting['Seminar_id'])) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        $this->render_text(studip_utf8encode(formatReady($posting['description'])));
    }

    public function comment_action() {
        $context = Request::option("context");
        $context_type = Request::option("context_type");
        $thread = new ForumPosting(Request::option("thread"));
        if (!$context
                || ($thread['Seminar_id'] !== $thread['user_id'] && !$GLOBALS['perm']->have_studip_perm("autor", $context))) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        if (Request::option("thread") && $thread['Seminar_id'] === $context) {
            $output = array();
            $posting = new ForumPosting();
            
            $content = transformBeforeSave(studip_utf8decode(Request::get("content")));
            
            //mentions einbauen:
            $content = preg_replace("/(@\"[^\n\"]*\")/e", "ForumPosting::mention('\\1', '".$thread->getId()."')", $content);
            $content = preg_replace("/(@[^\s]+)/e", "ForumPosting::mention('\\1', '".$thread->getId()."')", $content);
            
            $posting['description'] = $content;
            $posting['seminar_id'] = $thread['Seminar_id'];
            $posting['root_id'] = $posting['parent_id'] = Request::option("thread");
            $posting['name'] = "Re: ".$thread['name'];
            $posting['user_id'] = $GLOBALS['user']->id;
            $posting['author'] = get_fullname();
            $posting['author_host'] = $_SERVER['REMOTE_ADDR'];
            if ($posting->store()) {
                $factory = new Flexi_TemplateFactory($this->plugin->getPluginPath()."/views/forum");
                $template = $factory->open("comment.php");
                $template->set_attribute('posting', $posting);
                $template->set_attribute('course_id', $thread['Seminar_id']);
                $output['content'] = studip_utf8encode($template->render($template->render()));
                $output['mkdate'] = time();
                $output['posting_id'] = $posting->getId();
                
                //Notifications:
                if (class_exists("PersonalNotifications")) {
                    $user_ids = array();
                    if ($thread['user_id'] !== $GLOBALS['user']->id) {
                        $user_ids[] = $thread['user_id'];
                    }
                    foreach ($thread->getChildren() as $comments) {
                        if ($comments['user_id'] !== $GLOBALS['user']->id) {
                            $user_ids[] = $comments['user_id'];
                        }
                    }
                    $user_ids = array_unique($user_ids);
                    PersonalNotifications::add(
                        $user_ids,
                        PluginEngine::getURL($this->plugin, array(), "forum/thread/".$thread->getId()),
                        get_fullname()." hat einen Kommentar geschrieben",
                        "posting_".$posting->getId(),
                        Avatar::getAvatar($GLOBALS['user']->id)->getURL(Avatar::MEDIUM)
                    );
                }
            }
            $this->render_json($output);
        } else {
            $this->render_json(array(
                'error' => "Konnte thread nicht zuordnen."
            ));
        }
    }

    public function post_files_action() {
        $context = Request::option("context");
        $context_type = Request::option("context_type");
        if (!Request::isPost()
                || !$context
                || ($context_type === "course" && !$GLOBALS['perm']->have_studip_perm("autor", $context))) {
            throw new AccessDeniedException("Kein Zugriff");
        }
        //check folders
        $db = DBManager::get();
        $folder_id = md5("Blubber_".$context."_".$GLOBALS['user']->id);
        $parent_folder_id = md5("Blubber_".$context);
        $folder = $db->query(
            "SELECT * " .
            "FROM folder " .
            "WHERE folder_id = ".$db->quote($context_type === "course" ? $folder_id : $parent_folder_id)." " .
        "")->fetch(PDO::FETCH_COLUMN, 0);
        if (!$folder) {
            $folder = $db->query(
                "SELECT * " .
                "FROM folder " .
                "WHERE folder_id = ".$db->quote($parent_folder_id)." " .
            "")->fetch(PDO::FETCH_COLUMN, 0);
            if (!$folder) {
                $db->exec(
                    "INSERT IGNORE INTO folder " .
                    "SET folder_id = ".$db->quote($parent_folder_id).", " .
                        "range_id = ".$db->quote($context).", " .
                        "user_id = ".$db->quote($GLOBALS['user']->id).", " .
                        "name = ".$db->quote("BlubberDateien").", " .
                        "permission = '7', " .
                        "mkdate = ".$db->quote(time()).", " .
                        "chdate = ".$db->quote(time())." " .
                "");
            }
            if ($context_type === "course") {
                $db->exec(
                    "INSERT IGNORE INTO folder " .
                    "SET folder_id = ".$db->quote($folder_id).", " .
                        "range_id = ".$db->quote($parent_folder_id).", " .
                        "user_id = ".$db->quote($GLOBALS['user']->id).", " .
                        "name = ".$db->quote(get_fullname()).", " .
                        "permission = '7', " .
                        "mkdate = ".$db->quote(time()).", " .
                        "chdate = ".$db->quote(time())." " .
                "");
            }
        }

        $output = array();

        foreach ($_FILES as $file) {
            $GLOBALS['msg'] = '';
            validate_upload($file);
            if ($GLOBALS['msg']) {
                $output['errors'][] = $file['name'] . ': ' . studip_utf8encode(html_entity_decode(trim(substr($GLOBALS['msg'],6), '§')));
                continue;
            }
            if ($file['size']) {
                $document['name'] = $document['filename'] = studip_utf8decode(strtolower($file['name']));
                $document['user_id'] = $GLOBALS['user']->id;
                $document['author_name'] = get_fullname();
                $document['seminar_id'] = $context;
                $document['range_id'] = $context_type === "course" ? $folder_id : $parent_folder_id;
                $document['filesize'] = $file['size'];
                if ($newfile = StudipDocument::createWithFile($file['tmp_name'], $document)) {
                    $type = null;
                    strpos($file['type'], 'image') === false || $type = "img";
                    strpos($file['type'], 'video') === false || $type = "video";
                    if (strpos($file['type'], 'audio') !== false || strpos($document['filename'], '.ogg') !== false) {
                         $type = "audio";
                    }
                    if ($type) {
                        $output['inserts'][] = "[".$type."]".GetDownloadLink($newfile->getId(), $newfile['filename']);
                    } else {
                        $output['inserts'][] = "[".$newfile['filename']."]".GetDownloadLink($newfile->getId(), $newfile['filename']);
                    }
                }
            }
        }
        $this->render_json($output);
    }
    
    public function thread_action($thread_id)
    {
        //object_set_visit($_SESSION['SessionSeminar'], "forum");
        PageLayout::addHeadElement("script", array('src' => $this->assets_url."/javascripts/autoresize.jquery.min.js"), "");
        PageLayout::addHeadElement("script", array('src' => $this->assets_url."/javascripts/blubberforum.js"), "");
        PageLayout::addHeadElement("script", array('src' => $this->assets_url."/javascripts/formdata.js"), "");
        PageLayout::setTitle($GLOBALS['SessSemName']["header_line"]." - ".$this->plugin->getDisplayTitle());

        if (Navigation::hasItem('/course/blubberforum')) {
            Navigation::getItem("/course/blubberforum")->setImage($this->plugin->getPluginURL()."/assets/images/blubber.png");
            Navigation::activateItem('/course/blubberforum');
        } else {
            Navigation::activateItem('/community/blubber');
        }
        
        $this->thread        = new ForumPosting($thread_id);
        $this->course_id     = $_SESSION['SessionSeminar'];
        $this->single_thread = true;
    }

}