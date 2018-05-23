<?php
namespace Project\Exlant\registration\controller;
use Project\Exlant\registration\controller\mainController;
use Project\Exlant\registration\model\authorizationModel;

class authorizationController extends mainController
{
    private $cookie = array();  //массив для хеша и ид из куки
    private $model = null;
    private $_accessLvl = null;  // уровень доступа
    public $usersOnline = null; // юзеры онлайн, удаляются при выходе, и кроном
    
    function __construct()
    {
        $cookie = array( // массив с cookie id и hash пользователя
            'id' => filter_input(INPUT_COOKIE, 'string'),
            'hash' => filter_input(INPUT_COOKIE, 'hash'),
        );
        $this->model = new authorizationModel(); 
        
        $this->setCookie($cookie)     
             ->getAuthorization(); //запускаем проверку хеша и ид из базы с хешом и ид из куки
                                   // при успехе авторизируем пользователя
    }
    
    //записуем количество попыток авторизироваться
    private function setTries()
    {
        $_SESSION['triesAuth'] = (isset($_SESSION['triesAuth'])) ? ++$_SESSION['triesAuth'] : 1;
    }
    
    public function setAuthorization($login,$pass) // авторизация пользователя
    {
        if(!$this->checkLogin($login)){ // проверка логина регуляркой
            $this->setError('auth_incorect_data');
            $this->setTries();
            return FALSE;
        }
        
        $user = $this->model->getUser($login); // достаем данные пользователя(pass,salt)
               
        if(!$user){   //если пользователя не существует
            $this->setError('auth_incorect_data');
            $this->setTries();
            return FALSE;
        }
        $cryptPass = $this->cryptPass($pass, $user['salt']); //хешируэм входящий пароль
        if($user['pass']!=$cryptPass){ //сравниваем хеш из базы с хешем, пришедшего пароля 
            $this->setError('auth_incorect_data');
            $this->setTries();
            return FALSE;
        }
        $id = '$id';
        if($this->model->getRequestLvl()==='manager'){
            $this->setAccessLvl();
        }
        $this->model->setFind('_id', new \MongoId($user['_id']->$id));
        $this->setHash($user['_id']->$id);
        if(isset($_SESSION['triesAuth'])){
            unset($_SESSION['triesAuth']);
        }
        $this->model->updateDB();
        header('location:'.DOMEN); //переадресация 
        exit();     //остановка скрипта
    }
    
    private function setCookie($cookie)//сетер для куки
    {
        $this->cookie = $cookie;
        return $this;
    }
        
    private function getCookie() //гетер для куки
    {
        return $this->cookie;
    }
    
    private function getAuthorization() //авторизируемся
    {
        // если не пусты куки с id и хешом
        if(!empty($this->getCookie()['id']) and !empty($this->getCookie()['hash'])){
            //если id и хеш найдены в базе, то авторизируем пользователя
            $user = $this->model->getUserById($this->getCookie()['id'], $this->getCookie()['hash']);
            if($user){
                 $this->model->setFind('_id', new \MongoId($this->getCookie()['id']));
                if($this->checkCookieTimeUpdate($user['cookieTime'])){
                    //создаем новый хеш для идентификации пользователя
                    $this->setHash($this->getCookie()['id']);
                }
                
                $id = '$id';
                $this->userID = $user['_id']->$id; // записуем mongoId пользователя
                $this->userData = $user; //данные пользователя
                //добавляем пользователя в список онлайн
                if($this->getAccessLvl() !== 'manager'){
                    $this->model->addUserToListOnline();
                }
                $this->model->updateDB();
                return TRUE;
            }
        }
        return FALSE;
    }
    
    private function checkCookieTimeUpdate($time)
    {
        if($time + parent::TIME_STORE_COOKIE < time()){
            return TRUE;
        }
        return FALSE;
    }
    
    private function setHash($userId)
    { //создаем хеш для идентификации пользователя
        
        $hash = $this->generateString(); // генерируем хеш
        //записуем хеш в куки
        setcookie('hash', $hash, time()+3600*24*30,'/', JUSTDOMEN); 
        setcookie('string', $userId, time()+3600*24*30,'/', JUSTDOMEN);
        //записуем хеш в базу данных
        $this->model->setHash($hash);
        return $this;
    } 
    public function out($userId)
    {
        // удаляем куки с ид и хешем
        setcookie('hash','',0,'/', JUSTDOMEN);
        setcookie('string','',0,'/', JUSTDOMEN);
        $this->model->setFind('_id', new \MongoId($userId));
        //удаляем хеш из базы
        $this->model->setHash();
        if($this->getAccessLvl() === 'manager'){
            unset($_SESSION['manager']);
        }else{
            //удаляем из списка онлайн
            $this->model->dropUserFromListOnline();
        }
        $this->model->updateDB();
        header('location:'.DOMEN); //переадресация 
        exit();
    }
    
    protected function setAccessLvl()
    {
        $_SESSION['manager']['access'] = 'manager';    
    }
    
    public function getAccessLvl()
    {
        if(!$this->_accessLvl){
            $this->_accessLvl = (isset($_SESSION['manager']['access'])) ? $_SESSION['manager']['access'] : null;   
        }
        return $this->_accessLvl;
    }
    
    public function setUsersOnline()  
    {
        $this->usersOnline = $this->model->getUsersOnline();
    }
    // отдать пользователей онлайн
    public function getUsersOnline()  
    {
        return $this->usersOnline;
    }
}
