<?php

namespace Project\Exlant\users;

use Project\Exlant\users\usersEntity;
use Project\Exlant\users\usersModel;

class usersController extends usersEntity
{
    public function __construct($selfLogin, $login = null) {
        parent::__construct();
        $this->setData(new usersModel())
             ->setSelfLogin($selfLogin)
             ->setLogin($login);
        if(!$login){
            $this->setAllUsers($this->getData()->getAllUsers())
                 ->setTitle("Все пользователи");
        }elseif($login === $selfLogin){
            $this->setTitle("Редактирование профиля")
                 ->setUserData($this->getData()->getUserData($selfLogin));
        }else{
            $this->setTitle('Просмотр профиля пользователя '.$login)
                 ->setUserData($this->getData()->getUserData($login));
        }
    }
}
