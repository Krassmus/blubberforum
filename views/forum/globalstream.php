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
    #forum_threads {
        margin-left: 100px;
    }
</style>
<input type="hidden" id="last_check" value="<?= time() ?>">
<input type="hidden" id="base_url" value="plugins.php/blubber/forum/">
<input type="hidden" id="user_id" value="<?= htmlReady($GLOBALS['user']->id) ?>">
<input type="hidden" id="stream" value="all">
<div id="editing_question" style="display: none;"><?= _("Wollen Sie den Beitrag wirklich bearbeiten?") ?></div>

<!--
<div id="threadwriter">
    <textarea id="new_posting" placeholder="<?= _("Schreib was, frag was.") ?>"></textarea>
</div>
-->

<ul id="forum_threads" class="globalstream">
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