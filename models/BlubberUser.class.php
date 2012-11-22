<?php

interface BlubberContact {
    public function getName();
    
    public function getURL();
    
    public function getAvatar();
}

class BlubberUser extends User implements BlubberContact {
    
    public function getName() {
        return trim($this['Vorname']." ".$this['Nachname']);
    }
    
    public function getURL() {
        return URLHelper::getURL("about.php", array('username' => $this['username']), true);
    }
    
    public function getAvatar() {
        return Avatar::getAvatar($this->getId());
    }
}