<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */

$last_visit = object_get_visit($_SESSION['SessionSeminar'], "forum");

?>
<? if (@$single_thread): ?>
<input type="hidden" id="base_url" value="plugins.php/blubber/forum/">
<input type="hidden" id="seminar_id" value="<?= htmlReady($_SESSION['SessionSeminar']) ?>">
<input type="hidden" id="last_check" value="<?= time() ?>">
<input type="hidden" id="user_id" value="<?= htmlReady($GLOBALS['user']->id) ?>">
<div id="editing_question" style="display: none;"><?= _("Wollen Sie den Beitrag wirklich bearbeiten?") ?></div>
<p>
    <a href="<?= $controller->url_for('forum/forum') ?>">
        <?= Assets::img('icons/16/blue/arr_1left', array('class' => 'text-top')) ?>
        <?= _('Zurück zur Übersicht') ?>
    </a>
</p>

<ul id="forum_threads">
<? endif; ?>

<li id="<?= htmlReady($thread->getId()) ?>" mkdate="<?= htmlReady($thread['discussion_time']) ?>" class="thread posting<?= $last_visit < $thread['mkdate'] ? " new" : "" ?>" data-autor="<?= htmlReady($thread['user_id']) ?>">
    <div class="hiddeninfo">
        <input type="hidden" name="context" value="<?= htmlReady($thread['Seminar_id']) ?>">
        <input type="hidden" name="context_type" value="course">
    </div>
    <a href="<?= URLHelper::getLink("plugins.php/blubber/forum/forum", array('cid' => $thread['Seminar_id'])) ?>"
       <? $title = get_object_name($thread['Seminar_id'], "sem") ?>
       title="<?= _("Veranstaltung")." ".htmlReady($title['name']) ?>"
       class="contextinfo"
       style="background-image: url('<?= CourseAvatar::getAvatar($thread['Seminar_id'])->getURL(Avatar::NORMAL) ?>');">
    </a>
    <div class="avatar_column">
        <div class="avatar">
            <a href="<?= URLHelper::getLink("about.php", array('username' => get_username($thread['user_id']))) ?>">
                <div style="background-image: url('<?= Avatar::getAvatar($thread['user_id'])->getURL(Avatar::MEDIUM)?>');" class="avatar_image"></div>
            </a>
        </div>
    </div>
    <div class="content_column">
        <div class="timer">
            <a href="<?= $controller->url_for('forum/thread/' . $thread->getId(), array('cid' => $course_id)) ?>" class="permalink" title="<?= _("Permalink") ?>" style="background-image: url('<?= Assets::image_path("icons/16/grey/group") ?>');">
                <span class="time" data-timestamp="<?= (int) $thread['mkdate'] ?>">
                    <?= (date("j.n.Y", $thread['mkdate']) == date("j.n.Y")) ? sprintf(_("%s Uhr"), date("G:i", $thread['mkdate'])) : date("j.n.Y", $thread['mkdate']) ?>
                </span>
            </a>
            <? if ($GLOBALS['perm']->have_studip_perm("tutor", $_SESSION['SessionSeminar']) or ($thread['user_id'] === $GLOBALS['user']->id)) : ?>
            <a href="#" class="edit icon" title="<?= _("Bearbeiten") ?>" onClick="return false;" style="background-image: url('<?= Assets::image_path("icons/16/grey/tools") ?>');"></a>
            <? endif ?>
        </div>
        <div class="name">
            <a href="<?= URLHelper::getLink("about.php", array('username' => get_username($thread['user_id']))) ?>">
                <?= htmlReady(get_fullname($thread['user_id'])) ?>
            </a>
        </div>
        <div class="content">
            <? 
            $content = forum_kill_edit($thread['description']);
            if ($thread['name'] && strpos($thread['description'], $thread['name']) === false) {
                $content = $thread['name']."\n".$content;
            }
            ?>
            <?= formatReady($content) ?>
        </div>
    </div>

    <ul class="comments">
        <? $postings = $thread->getChildren() ?>
    <? if (count($postings) > 3) : ?>
        <li class="more">
            <?= sprintf(ngettext('%u weiterer Kommentar', '%u weitere Kommentare', count($postings) - 3), count($postings) - 3)?>
            ...
        </li>
    <? endif; ?>
    <? foreach (array_slice($postings, -3) as $posting) : ?>
        <?= $this->render_partial("forum/comment.php", array('posting' => $posting, 'last_visit' => $last_visit)) ?>
    <? endforeach ?>
    </ul>
    <div class="writer">
        <textarea placeholder="<?= _("Kommentiere dies") ?>"></textarea>
    </div>
</li>

<? if (@$single_thread): ?>
</ul>
<? endif; ?>