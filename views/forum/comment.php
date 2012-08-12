<?php

/*
 *  Copyright (c) 2012  Rasmus Fuhse <fuhse@data-quest.de>
 * 
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the GNU General Public License as
 *  published by the Free Software Foundation; either version 2 of
 *  the License, or (at your option) any later version.
 */
if (!$last_visit) {
    $last_visit = object_get_visit($_SESSION['SessionSeminar'], "forum");
}
?>
<li class="posting<?= $posting['mkdate'] > $last_visit ? " new" : "" ?>" id="<?= $posting->getId() ?>" mkdate="<?= htmlReady($posting['mkdate']) ?>">
    <div class="avatar_column">
        <div class="avatar">
            <a href="<?= URLHelper::getLink("about.php", array('username' => get_username($posting['user_id']))) ?>">
                <div style="background-image: url('<?= Avatar::getAvatar($posting['user_id'])->getURL(Avatar::MEDIUM)?>');" class="avatar_image"></div>
            </a>
        </div>
    </div>
    <div class="content_column">
        <div class="timer">
            <?= (date("j.n.Y", $posting['mkdate']) == date("j.n.Y")) ? sprintf(_("%s Uhr"), date("G:i", $posting['mkdate'])) : date("j.n.Y", $posting['mkdate']) ?>
        </div>
        <div class="name">
            <a href="<?= URLHelper::getLink("about.php", array('username' => get_username($posting['user_id']))) ?>">
                <?= htmlReady(get_fullname($posting['user_id'])) ?>
            </a>
        </div>
        <div class="content">
            <?= formatReady(forum_kill_edit($posting['description'])) ?>
        </div>
    </div>
</li>