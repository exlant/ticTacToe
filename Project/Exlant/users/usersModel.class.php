<?php

namespace Project\Exlant\users;
use Project\Exlant\users\usersDB;
use Project\Exlant\registration\controller\mainController as registMainContr;

class usersModel
{
    private $_DB = null;            // объект с базой данных
    private $_registContr = null;   // объект registMainContr
    
    public function __construct() 
    {
        $this->setDB();
    }
    
    private function setDB()
    {
        $this->_DB = new usersDB();
        return $this;
    }
    
    private function getDB()
    {
        return $this->_DB;
    }
    
    private function setRegistContr()
    {
        $this->_registContr = new registMainContr();
        return $this;
    }
    
    private function getRegistContr()
    {
        return $this->_registContr;
    }
    // достает из базы всех активных пользователей
    public function getAllUsers()
    {
        $cursor = $this->getDB()->getAllUsers();
        $users = array();
        foreach($cursor as $val){
            $users[] = $val['nick'];
        }
        return $users;
    }
    // достает из базы данные пользователя
    public function getUserData($login)
    {
        $userData = $this->getDB()->getUserData($login);
        if(!$userData){
            return null;
        }
        $userData['date'] = $this->rusDate('j M Y', $userData['date']);
        return $userData;
    }
    // запписует данные, редактирование профиля
    public function setNewData($login, $key, $value)
    {
        if(!$this->checkKey($key)){
            return false;
        }
        $this->setRegistContr();
        if($key === 'nick' and !$this->getRegistContr()->checkLogin($value)){
            return false;
        }
        if($key === 'mailView'){
            $value = (int)$value;
        }
        if($key === 'mail' and !filter_var($value, FILTER_VALIDATE_EMAIL)){
            return false;
        }
        if($key === 'pass'){
            if(!$this->checkPass($value)){
                return false;
            }
            $salt = $this->getRegistContr()->generateString(registMainContr::D_SALT); //генерируем соль, которую запишем в бд
            $value = $this->getRegistContr()->cryptPass($value,$salt); //хешируем пароль
            $this->getDB()->setUserData('salt', $salt);
        }
        $this->getDB()->setUserData($key, $value)
                      ->updateUserData($login);
        return true;
    }
    // проверяет пришедший от пользователя ключ
    private function checkKey($key)
    {
        $allowKey = array('nick', 'mail', 'pass', 'mailView');
        if(!in_array($key, $allowKey)){
            return false;
        }
        return true;
    }
    
    private function checkPass($pass)
    {
        if(empty($pass)){ //проверка на пустоту
            return false;
        }elseif(!$this->getRegistContr()->lengthMin($pass,registMainContr::MIN_PASS)){ //проверка на минимальный пароль
            return false; 
        }elseif(!$this->getRegistContr()->lengthMax($pass,registMainContr::MAX_PASS)) { //проверка на максимальный пароль
            return false;
        }
        return true;
    }
    

    // заменяет английские месяца на русские 
    public function rusDate($param, $time=0) {
        if($time === 0){
            $time = time();
        }
	$MonthNames = array("Января", "Февраля", "Марта", "Апреля", "Мая", "Июня", "Июля", "Августа", "Сентября", "Октября", "Ноября", "Декабря");
        if(strpos($param,'M') === false){
            return date($param, $time);
        } else {
            return date(str_replace('M',$MonthNames[date('n',$time)-1],$param), $time);
        }
            
    }
}

