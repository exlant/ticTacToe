<?php
namespace Project\Exlant;
use core\startCore;
use Project\Exlant\registration\controller\registrationController;
use Project\Exlant\ticTacToe\controller\mainController as ticTacToe;
use Project\Exlant\model;
use Project\Exlant\administration\controllerAdmin;

class controller extends model
{
    private $_captchaP = null; // каптча пришедшея в post запросе
    private $_captchaS = null; // каптча хранящеяся в сессии
    private $_serverRequestMethod = null; // метод ответа сервера
    private $_route = null;    // путь (первая переменная после / в url)
    private $_nick = null;     // ник 
    private $_password = null;  // пароль
    private $_passTest  = null; // проверка пароля
    private $_mail = null;       // почта
    private $_type = null;       // регистрация/авторизация
    
    public function __construct()
    {
        parent::__construct();
        startCore::setJS('jquery-1.11.3.js');
        startCore::setJS('lib.js');
        startCore::setCSS('main.css');
        $this->setGPS(); // устанавливаем get, post, session переменные
        $this->methodGet();
        if($this->getServerRequestMethod() === 'POST'){
            $this->authorization();
        }
        
        if(startCore::$authorization->userID){ //если пользователь зарегистрирован
            $this->accessAlloed();
        }else{ //если пользователь не зарегистрирован
            startCore::setJS('registration.js');
            startCore::setCSS('registration.css');
            // устанавливает параметры страницы(title, keywords, description, etc)
            if(filter_input(INPUT_GET, 'route') === 'registration'){
                $this->setPageParams("registration");
            }else if(filter_input(INPUT_GET, 'route') === 'manager' ){
                $this->setPageParams("manager");
            }else{
                $this->setPageParams("main");
            }
            
        }       
    }
    
    private function authorization() //авторизация
    {   
        if(!$this->checkCaptcha()){
            return false;
        }
        if($this->getType() === 'registration'){
            new registrationController($_POST['nick'],$_POST['password'],$_POST['passTest'],$_POST['mail']);
        }
        if($this->getType() === 'authorization'){
            startCore::$authorization->setAuthorization($_POST['nick'],$_POST['password']);
        } 
    }
    
    private function accessAlloed()
    {
        if(startCore::$authorization->getAccessLvl() === 'manager'){
            startCore::setCSS('administration.css');
            startCore::setJS('administration.js');
            startCore::setObject('manager', new controllerAdmin());
            $this->setPageParams("admin");
            return true;
        }
        
        $login = startCore::$authorization->userData['nick'];
        startCore::$authorization->setUsersOnline(); //достать из базы пользователей онлайн

        if(!$this->getRoute()){
            $this->_route = 'tictactoe';
        }
        // запуск объекта с игрой крестики нолики
        startCore::setJS('main.js');
        if($this->getRoute() === 'tictactoe'){
            $this->setPageParams('tictactoe');
            startCore::setObject('ticTacToe', new ticTacToe($login));
        }
    }
    
    private function outAuthorization()
    {
        startCore::$authorization->out(startCore::$authorization->userID);
    }
    
    protected function checkCaptcha()
    {
        if($this->getCaptchaS() === $this->getCaptchaP()){
            
            return true;
        }
        trigger_error('Не верно введена каптча', E_USER_WARNING);
        return false;   
    }
    
    private function setGPS()
    {
        $this->_captchaP = filter_input(INPUT_POST, 'captcha');
        $this->_captchaS = (isset($_SESSION['captcha']['code'])) 
                ? $_SESSION['captcha']['code']
                : null;
        unset($_SESSION['captcha']['code']);
        $this->_serverRequestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
        $this->_route = filter_input(INPUT_GET, 'route');
        $this->_nick = filter_input(INPUT_POST, 'nick');
        $this->_password = filter_input(INPUT_POST, 'password');
        $this->_passTest = filter_input(INPUT_POST, 'passTest');
        $this->_mail = filter_input(INPUT_POST, 'mail');
        $this->_type = filter_input(INPUT_POST, 'type');
    }
    
    private function methodGet()
    {
        if($this->getServerRequestMethod() === 'GET'){
            if($this->getRoute() === 'out'){
                $this->outAuthorization();
            }
        }
    }
    
    public function getCaptchaP()
    {
        return $this->_captchaP;
    }
    
    public function getCaptchaS()
    {
        return $this->_captchaS;
    }
    
    public function getServerRequestMethod()
    {
        return $this->_serverRequestMethod;
    }
    
    public function getRoute()
    {
        return $this->_route;
    }
    
    public function getNick()
    {
        return $this->_nick;
    }
    
    public function getPassword()
    {
        return $this->_password;
    }
    
    public function getPassTest()
    {
        return $this->_passTest;
    }
    
    public function getMail()
    {
        return $this->_mail;
    }
    
    public function getType()
    {
        return $this->_type;
    } 
}