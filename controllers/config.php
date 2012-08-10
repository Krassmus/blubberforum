<?php
require 'application.php';

class ConfigController extends ApplicationController {

	function before_filter(&$action, &$args) {
		if(!$GLOBALS['perm']->have_perm('root')) throw new Studip_AccessDeniedException('Keine Berechtigung');
		parent::before_filter($action, $args);
	}
	
	function index_action() {
		
		if(Request::submitted('ok')){
			if($this->plugin->config['displayname'] != Request::get('mein_name')){
				$this->plugin->config['displayname'] = Request::get('mein_name');
				$this->flash_set('msg', 'Mein Name wurde geändert.');
				$this->plugin->storeConfig();
				$this->redirect('config');
			}
		}
		
		$this->infobox = array();
		$this->infobox['picture'] = 'literaturelist.jpg';
		$this->infobox['content'] = array(
			array(
				'kategorie'=>_("Information"),
				'eintrag'=>array(
					array("text"=>_("Mit einer Infobox."),"icon"=>"ausruf_small2.gif")
					)
				)
			);
	}
}

