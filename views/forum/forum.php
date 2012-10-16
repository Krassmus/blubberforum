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
<input type="hidden" id="stream" value="course">
<input type="hidden" id="context_id" value="<?= $_SESSION['SessionSeminar'] ?>">
<input type="hidden" id="stream_time" value="<?= time() ?>">
<input type="hidden" id="search" value="<?= htmlReady($search) ?>">
<input type="hidden" id="browser_start_time" value="">
<script>jQuery(function () { jQuery("#browser_start_time").val(Math.floor(new Date().getTime() / 1000)); });</script>
<input type="hidden" id="loaded" value="1">
<div id="editing_question" style="display: none;"><?= _("Wollen Sie den Beitrag wirklich bearbeiten?") ?></div>

<div id="threadwriter">
    <div id="context_selector" style="display: none;">
        <input type="hidden" name="context_type" value="course" checked="checked">
        <input type="hidden" name="context" value="<?= $_SESSION['SessionSeminar'] ?>">
    </div>
    <textarea id="new_posting" placeholder="<?= _("Schreib was, frag was.") ?>"><?= htmlReady($search) ?></textarea>
</div>

<ul id="forum_threads" class="coursestream">
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
                "icon" => "icons/16/black/info",
                "text" => _("Ein Echtzeitkommunikations-Forum.")
            ),
            array(
                "icon" => "icons/16/black/date",
                "text" => _("Kein Seitenneuladen n�tig. Du siehst sofort, wenn sich was getan hat.")
            )
        )
    ),
    array("kategorie" => _("Profifunktionen"),
          "eintrag"   =>
        array(
            array(
                "icon" => "icons/16/black/forum",
                "text" => _("Dr�cke Shift-Enter, um einen Absatz einzuf�gen.")
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
            ),
            array(
                "icon" => "icons/16/black/person",
                "text" => _("Erw�hne jemanden mit @username oder @\"Vorname Nachname\". Diese Person wird dann speziell auf Deinen Blubber hingewiesen.")
            )
        )
    )
);
$infobox = array(
    'picture' => $assets_url . "/images/foam.png",
    'content' => $infobox
);