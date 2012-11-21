<?php

require_once dirname(__file__)."/BlubberUser.class.php";
require_once dirname(__file__)."/BlubberContactAvatar.class.php";

class BlubberExternalContact extends SimpleORMap implements BlubberContact {
    
    static public function find($user_id) {
        return self::find(__class__, $user_id);
    }
    
    public function getName() {
        return $this['name'];
    }
    
    public function getURL() {
        return $this['mail_identifier'] ? "mailto:".$this['mail_identifier'] : null;
    }
    
    public function getAvatar() {
        return BluberContactAvatar::getAvatar($this->getId());
    }
    
    function __construct($id = null)
    {
        $this->db_table = 'blubber_external_contact';
        $this->registerCallback('before_store', 'cbSerializeData');
        $this->registerCallback('after_store after_initialize', 'cbUnserializeData');
        parent::__construct($id);
    }

    function cbSerializeData()
    {
        $this->content['data'] = serialize($this->content['data']);
        $this->content_db['data'] = serialize($this->content_db['data']);
        return true;
    }

    function cbUnserializeData()
    {
        $this->content['data'] = (array)unserialize($this->content['data']);
        $this->content_db['data'] = (array)unserialize($this->content_db['data']);
        return true;
    }
}