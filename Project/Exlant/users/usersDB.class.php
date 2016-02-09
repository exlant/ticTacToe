<?php

namespace Project\Exlant\users;
use core\db\mongoDB;

class usersDB extends mongoDB
{
    const COLLECTION = 'users';

    public function __construct()
    {
        parent::__construct();
        $this->setCollection(self::COLLECTION);
    }
    
    public function getAllUsers()
    {
        $find = array('visibility' => 1);
        $needle = array('nick');
        return $this->getCollection()
                    ->find($find, $needle);
    }
    
    public function getUserData($login)
    {
        $find = array(
            'nick' => $login,
            'visibility' => 1
            );
        $needle = array('date', 'mail', 'mailView', 'online', 'statistics');
        
        return $this->getCollection()
                    ->findOne($find, $needle);
    }
    
    
    public function setUserData($key, $value)
    {
        $this->setUpdate($key, '$set', $value);
        return $this;
    }
    
    public function updateUserData($login)
    {
        $this->setFind('nick', $login)
             ->setFind('visibility', 1)
             ->updateDB();
    }
}
