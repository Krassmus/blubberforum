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
<style>
    #forum_threads, #forum_threads > li > ul {
        list-style-type: none;
    }
    #forum_threads {
        padding: 0px;
        margin: 5px;
        border-left: thin solid lightblue;
    }
    #forum_threads > li > ul {
        border-left: thin solid lightblue;
        background-color: #f5f5ff;
        margin-left: 60px;
        padding-left: 0px;
    }
    #forum_threads .more, #forum_threads .loading {
        text-align: center;
    }
    #forum_threads > li, #forum_threads > li > ul > li {
        border-top: thin solid lightblue;
        padding: 7px;
        margin: 0px;
    }
    #forum_threads > li:first-child, #forum_threads > li > ul > li:first-child {
        border: none;
    }
    #forum_threads h2 {
        margin-top: 0px;
        margin-bottom: 5px;
        color: #dddddd;
        text-shadow: 0px -1px 0px #bbbbbb;
    }
    #forum_threads .content {
        margin-bottom: 5px;
    }
    #forum_threads .timer {
        float: right;
        color: #bbbbbb;
        visibility: hidden;
    }
    #forum_threads li:hover > .timer {
        visibility: visible;
        padding: 3px;
    }
    #forum_threads .writer {
        margin-left: 60px;
        margin-top: 5px;
    }
    #forum_threads .writer textarea, #threadwriter input {
        width: 97%;
        height: 20px;
    }
    #forum_threads .writer textarea {
        opacity: 0.5;
    }
    #forum_threads .writer textarea:hover {
        opacity: 1;
    }
    #forum_threads .avatar, #forum_threads .content {
        vertical-align: middle;
        display: inline-block;
    }
    #forum_threads .avatar_column {
        vertical-align: top;
        padding: 5px;
        width: 55px;
        max-width: 55px;
        overflow: hidden;
        text-align: center;
        display: table-cell;
    }
    #forum_threads .avatar_column .avatar_image {
        margin-left: auto;
        margin-right: auto;
        width: 50px;
        height: 50px;
        background-size: 100%; 
        background-repeat: no-repeat; 
        background-position: center center; 
    }
    #forum_threads .content_column {
        display: table-cell;
        padding: 5px;
        width: 100%;
    }
    #forum_threads .content_column > .name a {
        color: #888888;
        font-weight: bold;
        font-size: 0.8em;
    }
    #forum_threads .content_column > .content {
        margin-top: 7px;
    }
    

    #forum_threads li.more {
        cursor: pointer;
    }
    #forum_threads li.more:hover {
        background-color: #e5e5ff;
    }
    #threadwriter {
        margin: 5px;
        padding: 5px;
    }
    #threadwriter textarea {
        width: 97%;
        height: 20px;
    }
</style>
<input type="hidden" id="last_check" value="<?= time() ?>">
<input type="hidden" id="seminar_id" value="<?= $_SESSION['SessionSeminar'] ?>">
<input type="hidden" id="base_url" value="plugins.php/blubber/forum/">

<div id="threadwriter">
    <textarea id="new_posting" placeholder="<?= _("Schreib was, frag was.") ?>"></textarea>
</div>

<ul id="forum_threads">
    <? foreach ($threads as $thread) : ?>
    <?= $this->render_partial("forum/thread.php", array('thread' => $thread)) ?>
    <? endforeach ?>
    <? if ($more_threads) : ?>
    <li class="more">...</li>
    <? endif ?>
</ul>

<?

$infobox = array(
    array("kategorie" => _("Informationen"),
          "eintrag"   =>
        array(
            array(
                "icon" => "icons/16/black/info.png",
                "text" => _("Ein Facebook-Style Forum.")
            ),
            array(
                "icon" => "icons/16/black/date.png",
                "text" => _("Kein Seitenneuladen nötig. Du siehst sofort, wenn sich was getan hat.")
            )
        )
    )
);
$infobox = array(
    'picture' => $assets_url . "/images/foam.png",
    'content' => $infobox
);