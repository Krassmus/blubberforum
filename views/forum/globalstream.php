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
<input type="hidden" id="last_check" value="<?= time() ?>">
<input type="hidden" id="base_url" value="plugins.php/blubber/forum/">
<input type="hidden" id="user_id" value="<?= htmlReady($GLOBALS['user']->id) ?>">
<input type="hidden" id="stream" value="all">
<input type="hidden" id="stream_time" value="<?= time() ?>">
<input type="hidden" id="browser_start_time" value="">
<script>jQuery(function () { jQuery("#browser_start_time").val(Math.floor(new Date().getTime() / 1000)); });</script>
<input type="hidden" id="loaded" value="1">
<div id="editing_question" style="display: none;"><?= _("Wollen Sie den Beitrag wirklich bearbeiten?") ?></div>

<div id="threadwriter" class="globalstream">
    <div class="row">
        <div class="context_selector" title="<?= _("Kontext der Nachricht auswählen") ?>">
            <?= Assets::img("icons/16/blue/seminar", array('class' => "seminar")) ?>
            <?= Assets::img("icons/16/blue/community", array('class' => "community")) ?>
        </div>
        <textarea id="new_posting" placeholder="<?= _("Schreib was, frag was.") ?>"></textarea>
    </div>
    <div id="context_selector" style="display: none;">
        <table>
            <tbody>
                <tr>
                    <td><input type="radio" name="context_type" value="public"></td>
                    <td>
                        <?= _("Öffentlich") ?>
                    <div style="font-size: 0.8em"><?= _("Dein Beitrag wird allen angezeigt, die Dich als Buddy hinzugefügt haben.") ?></div>
                    </td>
                </tr>
                <tr>
                    <td><input type="radio" name="context_type" value="course"></td>
                    <td>
                        <?= _("Veranstaltungsbezogen") ?>
                        <div style="font-size: 0.8em">
                            <?= _("Im Kurs") ?>
                            <select name="context">
                                <? foreach (ForumPosting::getMyBlubberCourses() as $course_id) : ?>
                                <? $seminar = new Seminar($course_id) ?>
                                <option value="<?= htmlReady($course_id) ?>"><?= htmlReady($seminar->getName()) ?></option>
                                <? endforeach ?>
                            </select>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
        <div>
            <button class="button" id="submit_button" style="display: none;" onClick="STUDIP.Blubber.prepareSubmitGlobalPosting();">
                <?= _("abschicken") ?>
            </button>
        </div>
    </div>
</div>



<div id="context_background">
<ul id="forum_threads" class="globalstream">
    <? foreach ($threads as $thread) : ?>
    <?= $this->render_partial("forum/thread.php", array('thread' => $thread)) ?>
    <? endforeach ?>
    <? if ($more_threads) : ?>
    <li class="more">...</li>
    <? endif ?>
</ul>
</div>

<?

$infobox = array(
    array("kategorie" => _("Informationen"),
          "eintrag"   =>
        array(
            array(
                "icon" => "icons/16/black/info",
                "text" => _("Ein Echtzeit-ActivityFeed Deiner Freunde und Veranstaltungen.")
            ),
            array(
                "icon" => "icons/16/black/date",
                "text" => _("Kein Seitenneuladen nötig. Du siehst sofort, wenn sich was getan hat.")
            )
        )
    ),
    array("kategorie" => _("Profifunktionen"),
          "eintrag"   =>
        array(
            array(
                "icon" => "icons/16/black/forum",
                "text" => _("Drücke Shift-Enter, um einen Absatz einzufügen.")
            ),
            array(
                "icon" => "icons/16/black/smiley",
                "text" => sprintf(_("Verwende beim Tippen %sTextformatierungen%s und %sSmileys.%s"),
                        '<a href="http://docs.studip.de/help/2.2/de/Basis/VerschiedenesFormat" target="_blank">', '</a>',
                        '<a href="'.URLHelper::getLink("dispatch.php/smileys").'" target="_blank">', '</a>')
            ),
            array(
                "icon" => "icons/16/black/upload",
                "text" => _("Ziehe Dateien per Drag & Drop in ein Textfeld, um sie hochzuladen und zugleich zu verlinken.")
            )
        )
    )
);
$infobox = array(
    'picture' => $assets_url . "/images/foam.png",
    'content' => $infobox
);