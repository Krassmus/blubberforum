<?php

require_once dirname(__file__)."/BlubberUser.class.php";
require_once dirname(__file__)."/BlubberContactAvatar.class.php";

class BlubberExternalContact extends SimpleORMap implements BlubberContact {
    
    static public function find($user_id) {
        $user = parent::find(__class__, $user_id);
        if (class_exists($user['contact_type'])) {
            $new_user = new $user['contact_type']();
            $new_user->setData($user->getData());
            return $new_user;
        } else {
            return $user;
        }
    }
    
    static public function findByEmail($email) {
        $email = strtolower($email);
        $user = self::findBySQL(__class__, "mail_identifier = ".DBManager::get()->quote($email));
        if (!count($user)) {
            $user = new BlubberExternalContact();
            $user['mail_identifier'] = $email;
            return $user;
        }
        return $user[0];
    }
    
    public function getName() {
        return $this['name'];
    }
    
    public function getURL() {
        return $this['mail_identifier'] ? "mailto:".$this['mail_identifier'] : null;
    }
    
    public function getAvatar() {
        return BlubberContactAvatar::getAvatar($this->getId());
    }
    
    function __construct($id = null)
    {
        $this->db_table = 'blubber_external_contact';
        //$this->registerCallback('before_store', 'cbSerializeData');
        //$this->registerCallback('after_store after_initialize', 'cbUnserializeData');
        parent::__construct($id);
    }
    
    public function restore() {
        parent::restore();
        $this->cbUnserializeData();
    }
    
    public function store() {
        $this->cbSerializeData();
        parent::store();
    }

    function cbSerializeData()
    {
        $this->content['data'] = serialize($this->content['data']);
        //$this->content_db['data'] = serialize($this->content_db['data']);
        return true;
    }

    function cbUnserializeData()
    {
        $this->content['data'] = (array) unserialize($this->content['data']);
        //$this->content_db['data'] = (array)unserialize($this->content_db['data']);
        return true;
    }
}