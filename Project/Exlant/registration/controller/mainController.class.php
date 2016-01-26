<?php
namespace Project\Exlant\registration\controller;
use core\db\mongoDB;

class mainController
{
    const MIN_LOGIN = 3;            //минимальный логин
    const MAX_LOGIN = 20;           //максимальный логин
    const MIN_PASS = 6;             //минимальный пароль
    const MAX_PASS = 64;            //максимальный пароль
    const C_SALT = 'okqd[jqop';     //постоянная соль
    const D_SALT = 8;               //длина символов для генерации динамической соли
    
    public $userID = null; // зарегистрированный пользователь/ id - пользователя
    public $userData = null;    // данные пользователя
    private $usersOnline = null; // юзеры онлайн, удаляются при выходе, и кроном
    
    public $errorNum = 0;          //колличество ошибок
    
    public function setUsersOnline()  // выбрать пользователей онлайн
    {
        $connect = new mongoDB();
        $cursor = $connect->setCollection('usersOnline')
                          ->find(array(), array('login'));
        foreach($cursor as $value){
            $data[] = $value;
        }
        $this->usersOnline = $data;
        return $this;
    }
    
    public function getUsersOnline()  // отдать пользователей онлайн
    {
        return $this->usersOnline;
    }

    protected function checkLogin($login) //метод, проверки логина через регулярку
    {
        $pattern='/^(?!empty|draw)[-A-z0-9_]{'.self::MIN_LOGIN.','.self::MAX_LOGIN.'}$/';
        if(!preg_match($pattern, $login)){
           return FALSE; 
        }  
        return TRUE;  
    }
    
    protected function lengthMin($var,$minL) //проверка строки на минимально разрешенную длину
    {
        if(strlen(trim($var))<$minL){
            return FALSE;
        }
        return TRUE;
    }
    
    protected function lengthMax($var,$maxL)//проверка строки на максимально разрешенную длину
    {
        if(strlen(trim($var))>$maxL){
            return FALSE;
        }
        return TRUE;
    }
    
    protected function generateString($len=32) //генерируем случайную строку с заданной длиной
    {
        $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789';
        $str_len = strlen($str)-1;
        $text = '';
        while(strlen($text)<$len){
            $text.=$str[mt_rand (0, $str_len)];
        }
        return $text;
    }
    
    protected function cryptPass($pass,$d_salt) //хешируем пароль
    {        
       return sha1(substr(md5($pass),0,20).$d_salt.substr(md5($pass),5,10).self::C_SALT);
    }
    
    protected function setError($msg,$type=512,$text='') //метод генерации ошибок
    {
        $error = array(
            0 => $text,
            'incorect_login' => 'Допустимы латинские символы, цифры(0-9), знаки "-", "_"!'
                                . 'Количество символов от '.self::MIN_LOGIN.' до '.self::MAX_LOGIN.'!',
            'reg_isset_login' => 'Такой логин уже существует!',
            'reg_emty_pass' => 'Вы не ввели пароль',
            'reg_min_pass' => 'Пароль меньше - '.self::MIN_PASS,
            'reg_max_pass' => 'Пароль больше - '.self::MAX_PASS,
            'reg_different_pass' => 'Пароли не совпадают',
            'reg_incorect_mail' => 'Не допустимый mail',
            'auth_incorect_data' => 'Введенные логин и пароль не верны!',
            'cant_set_user_hash' => 'хеш не был установлен для пользователя!', 
        );
        $this->errorNum++;
        
        trigger_error($error[$msg],$type);  
    }
}
