<div style="padding:1em">
Wie soll ich heissen?
<br>
<form action="<?=$controller->url_for()?>" method="post">
<input type="text" size="40" name="mein_name">
<span style="padding-left:10px">
<?=makeButton('ok','input','','ok')?>
</span>
</form>
<pre>
<?=print_r($plugin->config,1);?>
</pre>
</div>