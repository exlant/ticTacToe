<?php

namespace Project\Exlant\registration\model;

//подключаем обертку для монго db
use core\db\mongoDB;

class registrationModel extends mongoDB
{
    const COLLECTION = 'users';     //коллекция по умолчанию users

    public function __construct()
    {
        parent::__construct(); //переопределяем конструктор
        $this->setCollection(self::COLLECTION);    //устанавливаем коллекцию
    }

    public function getLogin($login) //выбираем логин из коллекции
    {
        return $this->getCollection()
            ->findOne(array('nick' => $login));
    }

    public function addUser($user) //добавляем пользователя в коллекцию
    {
        $this->getCollection()
            ->insert($user);

        if (!empty($user['_id'])) {
            $id = '$id';

            return $user['_id']->$id;
        }

        return false;
    }
}
