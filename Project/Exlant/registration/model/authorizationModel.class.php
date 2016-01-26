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
    
    public function setHash($id,$hash) //записуем уникальный хеш пользователя для идентификации через cookie
    {
        $userFound = array('_id' => new \MongoId($id)); // массив для поиска
        $userUpdate = array('$set' => array('hash' => $hash)); // массив для обновления хеша в базе 
        $this->getCollection()        // обновляем хеш в базе
             ->update($userFound,$userUpdate);
    }
    
    public function getUserById($id,$hash) //достаем данные пользователя по id и хешу
    {
        $find = array(
            '_id' => new \MongoId($id),
            'hash' => $hash,
            'visibility' => 1,
            );
        $needle = array('nick','mail','date', 'statistics');
        if($this->getRequestLvl()==='manager'){
            $needle = array('nick');
        }
        return $this->getCollection()
                    ->findOne($find);
    }
    
    public function addUserToListOnline($login,$id,$time) //добавляем пользователя в список online
    {                                                     //если пользователь уже онлайн обновляем время
        $this->setCollection('usersOnline');
        $find = array('login' => $login);
        if($this->getCollection()->findOne($find)){
            $update = array('$set' => array('time' => $time));
            $this->getCollection()->update($find,$update);
            return TRUE;
        }
        $user = array(
            'login' => $login,
            'id' => $id,
            'time' => $time,
        );
        
        $this->getCollection()
             ->insert($user);
    }
    
    public function dropUserToListOnline($userId)
    {
        
        $this->setCollection('usersOnline')
             ->remove(array('id' => $userId));
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
    
}

