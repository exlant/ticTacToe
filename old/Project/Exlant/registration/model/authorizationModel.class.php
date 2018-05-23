<?php
namespace Project\Exlant\registration\model;
//используем оберку для работы с монго дб
use core\db\mongoDB;

class authorizationModel extends mongoDB
{
    const COLLECTION = 'users';     //коллекция по умолчанию users
    private $_requestLevel = null;        //уровень доступа
    
    public function __construct()
    {
        parent::__construct(); //переопределяем конструктор
        $this->setRequestLvl();
        if($this->getRequestLvl() === 'manager'){
            $this->setCollection('manager');    //устанавливаем коллекцию
        }else{
            $this->setCollection(self::COLLECTION);    //устанавливаем коллекцию
        }     
    } 

    public function getUser($login) //достаем по логину пользователя из базы
    {
        $user = array(  // массив для поиска в базе данных
            'nick' => $login,
            'visibility' => 1,
        );
        return $this->getCollection()
             ->findOne($user,array('pass','salt'));
    }
    
    public function setHash($hash = '') //записуем уникальный хеш пользователя для идентификации через cookie
    {
        $this->setUpdate('hash', '$set', $hash)
             ->setUpdate('cookieTime', '$set', time());
        return $this;
    }
    
    public function getUserById($id,$hash) //достаем данные пользователя по id и хешу
    {
        $find = array(
            '_id' => new \MongoId($id),
            'hash' => $hash,
            'visibility' => 1,
            );
        $needle = array('nick','mail','date', 'statistics', 'online', 'timeOnline', 'cookieTime');
        if($this->getRequestLvl()==='manager'){
            $needle = array('nick', 'cookieTime');
        }
        return $this->getCollection()
                    ->findOne($find,$needle);
    }
    
    public function addUserToListOnline() //добавляем пользователя в список online
    {             
        $this->setUpdate('online', '$set', 1)
             ->setUpdate('timeOnline', '$set', time());
        return $this;
    }
    
    public function dropUserFromListOnline()
    {
        $this->setUpdate('online', '$set', 0)
             ->setUpdate('timeOnline', '$set', 0);
        return $this;
    }
    
    protected function setRequestLvl()
    {
        $access = (isset($_SESSION['manager']['access'])) ? $_SESSION['manager']['access'] : '';
        if($access === 'manager'){
            $this->_requestLevel = 'manager';
            return $this;
        }
        $this->_requestLevel = (filter_input(INPUT_GET, 'route') === 'manager') 
                ? 'manager' : 'user';
        return $this;
    }
    
    public function getRequestLvl()
    {
        return $this->_requestLevel;
    }
    
    public function getUsersOnline()  // выбрать пользователей онлайн
    {
        $find = array('online' => 1);
        $needle = array('nick');
        $cursor = $this->getCollection()
                          ->find($find, $needle);
        foreach($cursor as $value){
            $data[] = $value;
        }
        return $data;
    }
}

