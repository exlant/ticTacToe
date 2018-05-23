<?php
namespace Project\Exlant\registration\controller;
//подключаем общий, для ауторизации и регистрации, контроллер
use Project\Exlant\registration\controller\mainController;
//подключаем модель для регистрации
use Project\Exlant\registration\model\registrationModel;

class registrationController extends mainController
{
    public $quickMessage = null;
    private $_type = null;
    //логин, пароль, почта
    public function __construct($login,
                                $pass,
                                $pass_test,
                                $mail,
                                $type = null
                                )
    {
        $this->_type = $type;
        //проверка логина
        if(!$this->checkLogin($login)){
            return FALSE;
        }
        //проверяем пароли 
        if(!$this->checkPass($pass, $pass_test)){
            return FALSE;
        }
        
        //проверяем email
        if(!$this->checkMail($mail)){
            return FALSE;
        }
        
        //добавляем пользователя
        if(!$this->NewUser($login, $pass, $mail)){
            return FALSE;
        }
        $this->quickMessage = ($pass === 'guest1') 
                ? 'Вы прошли проверку, и через 5 секунд будете перемещены в гостевой аккаунт!<br>'
                . 'Или просто перезагрузите страничку!'
                : 'Ваш аккаунт успешно добавлен в базу данных';
        header('Refresh: 5; URL='.DOMEN);
        
    }
    
        
    public  function checkLogin($login) {
        
        
        if($this->_type !== 'guest'){
            //переопределяем родительский метод
            if(!parent::checkLogin($login)){ //если не проходит регулярку, кидаем ошибку
                $this->setError('incorect_login');
                return FALSE;
            }
        }
        
        //подключаем об'ект модели 
        $instanceRegistModel = new registrationModel();
        $countLogin = $instanceRegistModel
              ->getLogin($login); //ищем добавляемый логин в базе
           
        if($countLogin){          //если логин был найден в базе, кидаем ошибку пользователя
            
            $this->setError('reg_isset_login');
            return FALSE;
        }
        return TRUE;
    }
    
    private function checkPass($pass,$pass_test)
    {
        if(empty($pass)){ //проверка на пустоту
            $this->setError('reg_emty_pass');
            return FALSE;       
        }elseif(!$this->lengthMin($pass,parent::MIN_PASS)){ //проверка на минимальный пароль
            $this->setError('reg_min_pass');
            return FALSE;
        }elseif(!$this->lengthMax($pass,parent::MAX_PASS)) { //проверка на максимальный пароль
            $this->setError('reg_max_pass');
            return FALSE;
        }elseif(empty($pass_test) || $pass!=$pass_test){ //проверка на совпадение паролей
            $this->setError('reg_different_pass');
            return FALSE;
        }
        return TRUE;
    }
    
    private function checkMail($mail)
    {
        if(!filter_var($mail, FILTER_VALIDATE_EMAIL)){ //проверка почты
            $this->setError('reg_incorect_mail',512);
            return FALSE;
        }
        return TRUE;
    }
    
    private function NewUser($login, $pass, $mail) //добавляем нового пользователя
    {
        $hash = $this->generateString();
        $pass = ($pass === 'guest1') ? $this->generateString() : $pass;
        $salt = $this->generateString(parent::D_SALT); //генерируем соль, которую запишем в бд
        $cryptPass = $this->cryptPass($pass,$salt); //хешируем пароль
                                                 
        //масив с данными пользователя, которые запишем в бд
        $user = array(                  
            'nick' => $login,
            'pass' => $cryptPass,
            'mail' => $mail,
            'salt' => $salt,
            'visibility' => 1,
            'date' => time(),
            'cookieTime'=> time(),
            'timeOnline' => time(),
            'online' => 1,
            'hash' => $hash,
            'mailView' => 0,
            'statistics' => array('entire' => array('win' => 0, 'lose' => 0, 'draw' => 0))
        );
          //добавляем нового пользователя
        $instanceRegistModel = new registrationModel();
        $user_id = $instanceRegistModel->addUser($user);
        if($user_id){
            setcookie('hash', $hash, time()+3600*24*30,'/', JUSTDOMEN); 
            setcookie('string', $user_id, time()+3600*24*30,'/', JUSTDOMEN);
            return TRUE;
        }
        return FALSE;
    }
}
