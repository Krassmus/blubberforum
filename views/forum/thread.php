<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */
?>
<li id="<?= htmlReady($thread->getId()) ?>" mkdate="<?= htmlReady($thread['mkdate']) ?>">
    <div class="avatar_column">
        <div class="avatar">
            <a href="<?= URLHelper::getLink("about.php", array('username' => get_username($thread['user_id']))) ?>">
                <div style="background-image: url('<?= Avatar::getAvatar($thread['user_id'])->getURL(Avatar::MEDIUM)?>');" class="avatar_image"></div>
            </a>
        </div>
    </div>
    <div class="content_column">
        <div class="timer">
            <?= ($thread['mkdate'] / 86400 == time() / 86400) ? sprintf(_("%s Uhr"), date("G:i", $posting['mkdate'])) : date("j.n.Y", $thread['mkdate']) ?>
        </div>
        <div class="name">
            <a href="<?= URLHelper::getLink("about.php", array('username' => get_username($thread['user_id']))) ?>">
                <?= get_fullname($thread['user_id']) ?>
            </a>
        </div>
        <div class="title"><?= htmlReady($thread['name']) ?></div>
        <div class="content">
            <?= formatReady(forum_kill_edit($thread['description'])) ?>
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
        <?= $this->render_partial("forum/comment.php", array('posting' => $posting)) ?>
        <? endforeach ?>
        <? endif ?>
    </ul>
    <div class="writer">
        <textarea placeholder="<?= _("Kommentiere dies") ?>"></textarea>
    </div>
</li>