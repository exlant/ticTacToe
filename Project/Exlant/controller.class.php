<?php
namespace Project\Exlant;
use core\startCore;
use Project\Exlant\registration\controller\registrationController;
use Project\Exlant\ticTacToe\controller\mainController as ticTacToe;
use Project\Exlant\model;
use Project\Exlant\administration\controllerAdmin;
use Project\Exlant\mail\mail;
use Project\Exlant\users\usersController;

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
    private $_type = null;       // регистрация/авторизация/зайти как гость
    public $quickMessage = null; // быстрые сообщения
    public $messager = null;     // объект с почтой
    
    
    public function __construct()
    {
        parent::__construct();
        $this->setGPS(); // устанавливаем get, post, session переменные
        $this->methodGet();
        if($this->getServerRequestMethod() === 'POST'){
            $this->authorization();
        }
        startCore::setJS('jquery-1.11.3.js');
        startCore::setJS('lib.js');
        startCore::setCSS('main.css');
        if(startCore::$authorization->userID){ //если пользователь зарегистрирован
            $this->accessAlloed();
        }else{ //если пользователь не зарегистрирован
            startCore::setJS('registration.js');
            startCore::setCSS('registration.css');
            // устанавливает параметры страницы(title, keywords, description, etc)
            $page = (filter_input(INPUT_GET, 'route')) 
                    ? filter_input(INPUT_GET, 'route'):
                    'main';
            $this->setPageParams($page);      
        }
        
    }
    
    private function authorization() //авторизация
    {   
        if($this->getType() === 'registration'){
            if(!$this->checkCaptcha()){
                return false;
            }
            $registration = new registrationController($_POST['nick'],$_POST['password'],$_POST['passTest'],$_POST['mail']);
            $this->quickMessage = $registration->quickMessage;
        }
        if($this->getType() === 'authorization'){
            if(isset($_SESSION['triesAuth']) 
                    and $_SESSION['triesAuth'] > 5 
                    and !$this->checkCaptcha()){
                return false;
            }
            startCore::$authorization->setAuthorization($_POST['nick'],$_POST['password']);
        }
        if($this->getType() === 'guest'){
            if(!$this->checkCaptcha()){
                return false;
            }
            $guestCounter = $this->getGuestCounter();
            $registration = new registrationController('guest_'.$guestCounter,'guest1','guest1','empty@empty.com', 'guest');
            $this->quickMessage = $registration->quickMessage;
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
        
        if(!$this->getRoute()){
            $this->_route = TICTACTOE;
        }
        
        $login = startCore::$authorization->userData['nick'];
        startCore::$authorization->setUsersOnline(); //достать из базы пользователей онлайн

        if($this->getRoute() === SENDMESSAGE){
            $this->sendMessage($login);
        }
        
        if($this->getRoute() === USERS){
            $this->setUsersObject($login);
        }
        
        if($this->getRoute() === GUIDE){
            startCore::setCSS('guide.css');
        }
        
        // запуск объекта с игрой крестики нолики
        startCore::setJS('main.js');
        if($this->getRoute() === 'tictactoe'){
            startCore::setObject('ticTacToe', new ticTacToe($login));
        }
        
        $this->setPageParams($this->getRoute());
    }
    
    private function setUsersObject($login)
    {
        startCore::setCSS('viewUsers.css');
        startCore::setJS('viewUsers.js');
        startCore::setObject('viewUsers', new usersController(
                $login,
                filter_input(INPUT_GET, 'action')
                ));
    }
    
    private function sendMessage($login)
    {
        startCore::setCSS('sendMessage.css');
        startCore::setJS('sendMessage.js');
        if($this->getServerRequestMethod() === 'POST' and $this->getType() === 'sendMessage'){
            if(!$this->checkCaptcha()){
                return false;
            }
            $this->messager = new mail(
                    $login,
                    filter_input(INPUT_POST, 'body'),
                    filter_input(INPUT_POST, 'subject'),
                    filter_input(INPUT_POST, 'mail')
                    );
            $this->messager->sendMessage();
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
                : $this->generateString();
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
    
    public function generateString($len=32) //генерируем случайную строку с заданной длиной
    {
        $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789';
        $str_len = strlen($str)-1;
        $text = '';
        while(strlen($text)<$len){
            $text.=$str[mt_rand (0, $str_len)];
        }
        return $text;
    }
}