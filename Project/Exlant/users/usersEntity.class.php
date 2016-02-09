<?php

namespace Project\Exlant\users;

class usersEntity
{
    private $_Data = null;      // объект с моделью (object)
    private $_login = null;     // логин который нужно просмотреть (string)
    private $_selfLogin = null; // логин пользователя (string)
    private $_allUsers = null;  // все пользователи (array)
    private $_userData = null;  // данные юзера (array)
    private $_title = null;     // титолка
    
    public function __construct() 
    {
        
    }
    
    public function setData($data)
    {
        $this->_Data = $data;
        return $this;
    }
    
    public function getData()
    {
        return $this->_Data;
    }
    
    public function setLogin($login)
    {
        $this->_login = $login;
        return $this;
    }
    
    public function getLogin()
    {
        return $this->_login;
    }
    
    public function setSelfLogin($selfLogin)
    {
        $this->_selfLogin = $selfLogin;
        return $this;
    }
    
    public function getSelfLogin()
    {
        return $this->_selfLogin;
    }
    
    public function setAllUsers($allUsers)
    {
        $this->_allUsers = $allUsers;
        return $this;
    }
    
    public function getAllUsers()
    {
        return $this->_allUsers;
    }
    
    public function setUserData($userData)
    {
        $this->_userData = $userData;
        return $this;
    }
    
    public function getUserData()
    {
        if(!$this->_userData){
            return null;
        }
        $data = $this->_userData;
        $viewMail = array('yes' => '', 'no' => '');
        if(isset($data['mailView']) and $data['mailView'] === 1){
            $viewMail['yes'] = 'selected = "selected"';
        }else{
            $viewMail['no'] = 'selected = "selected"';
        }
        $data['mailViewSelect'] = $viewMail;
        return $data;
    }
    
    public function setTitle($title)
    {
        $this->_title = $title;
        return $this;
    }
    
    public function getTitle()
    {
        return $this->_title;
    }
    
}
