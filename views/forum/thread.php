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
<li id="<?= htmlReady($thread->getId()) ?>" mkdate="<?= htmlReady($thread['discussion_time']) ?>" class="thread posting<?= $last_visit < $thread['mkdate'] ? " new" : "" ?>">
    <div class="avatar_column">
        <div class="avatar">
            <a href="<?= URLHelper::getLink("about.php", array('username' => get_username($thread['user_id']))) ?>">
                <div style="background-image: url('<?= Avatar::getAvatar($thread['user_id'])->getURL(Avatar::MEDIUM)?>');" class="avatar_image"></div>
            </a>
        </div>
    </div>
    <div class="content_column">
        <div class="timer">
            <?= (date("j.n.Y", $thread['mkdate']) == date("j.n.Y")) ? sprintf(_("%s Uhr"), date("G:i", $thread['mkdate'])) : date("j.n.Y", $thread['mkdate']) ?>
            <? if ($GLOBALS['perm']->have_studip_perm("tutor", $_SESSION['SessionSeminar']) or ($thread['user_id'] === $GLOBALS['user']->id)) : ?>
            <a href="#" class="edit" title="<?= _("Bearbeiten") ?>" onClick="return false;" style="vertical-align: middle; opacity: 0.6; width: 14px; height:14px; display: inline-block; background: url('<?= Assets::image_path("icons/16/grey/tools.png") ?>') center center; background-position: center center;"></a>
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
        <li class="more">...</li>
        <?= $this->render_partial("forum/comment.php", array('posting' => $postings[count($postings)-3])) ?>
        <?= $this->render_partial("forum/comment.php", array('posting' => $postings[count($postings)-2])) ?>
        <?= $this->render_partial("forum/comment.php", array('posting' => $postings[count($postings)-1])) ?>
        <? else : ?>
        <? foreach ($postings as $posting) : ?>
        <?= $this->render_partial("forum/comment.php", array('posting' => $posting, 'last_visit' => $last_visit)) ?>
        <? endforeach ?>
        <? endif ?>
    </ul>
    <div class="writer">
        <textarea placeholder="<?= _("Kommentiere dies") ?>"></textarea>
    </div>
</li>